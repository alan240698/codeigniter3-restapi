<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Glpi_api_model extends CI_Model
{
    private $category_mapping = [];


    public function __construct()
    {
        parent::__construct();
        $this->load->library('glpi_api');
    }

    public function getCards()
    {
        // Init session
        if (!$this->glpi_api->init_session()) {
            log_message('error', 'GLPI: Cannot initialize session for getCards');
            return [];
        }

        // Get data from API
        $data = $this->glpi_api->getCardEntities();

        // Kill session
        $this->glpi_api->kill_session();

        // Validate và transform data
        return $this->_transform_entities($data);
    }

    /**
     * Get categories
     * 
     * @param int $entity_id
     * @return array
     */
    public function getCategories($entity_id)
    {
        // Validate entity_id
        if (!$this->_is_valid_id($entity_id)) {
            log_message('error', "GLPI: Invalid entity_id: {$entity_id}");
            return [];
        }

        // Init session
        if (!$this->glpi_api->init_session()) {
            log_message('error', 'GLPI: Cannot initialize session for getCategories');
            return [];
        }

        // Get categories from API
        $data = $this->glpi_api->getCategoyByEntity($entity_id);

        // Kill session
        $this->glpi_api->kill_session();

        // Validate data
        if (!$this->_is_valid_array($data)) {
            return [];
        }

        return $this->_transform_categories($data);
    }

    /**
     * 
     * 
     * @param array $ticket_data
     * @param array $files (optional)
     * @return array Response với success status
     */
    public function create_ticket_with_attachments($ticket_data, $files = [])
    {
        // Validate ticket data
        if (!$this->_validate_ticket_data($ticket_data)) {
            return $this->_error_response('Dữ liệu ticket không hợp lệ');
        }

        // Init session
        if (!$this->glpi_api->init_session()) {
            return $this->_error_response('Không thể kết nối đến GLPI API');
        }

        // Create ticket
        $ticket_result = $this->glpi_api->create_ticket($ticket_data);

        // Check ticket creation result
        if (!$this->_is_success_response($ticket_result)) {
            $this->glpi_api->kill_session();
            return $this->_error_response(
                $ticket_result['message'] ?? 'Không thể tạo ticket',
                $ticket_result
            );
        }

        // Get ticket ID
        $ticket_id = $ticket_result['data']['id'] ?? null;
        if (!$ticket_id) {
            $this->glpi_api->kill_session();
            return $this->_error_response('Không nhận được ticket ID');
        }

        // Upload attachments nếu có
        $uploaded_files = [];
        if ($this->_has_files($files)) {
            $uploaded_files = $this->_upload_attachments($ticket_id, $files);
        }

        // Kill session
        $this->glpi_api->kill_session();

        // Return success response
        return $this->_success_response([
            'ticket_id' => $ticket_id,
            'uploaded_files' => $uploaded_files,
            'message' => 'Ticket đã được tạo thành công'
        ]);
    }

    /**
     * Chuẩn bị ticket data từ form input
     * 
     * @param string $category
     * @param array $form_data
     * @return array|false
     */
    public function prepare_ticket_data($category, $form_data)
    {
        // Validate inputs
        if (!$this->_is_valid_string($category)) {
            log_message('error', 'GLPI: Invalid category provided');
            return false;
        }

        if (!$this->_is_valid_array($form_data)) {
            log_message('error', 'GLPI: Invalid form_data provided');
            return false;
        }

        $category_id = $form_data['subcategory'] ?? '';

        $title = $this->_generate_reversed_ticket_title($category, $category_id);

        // Prepare ticket data
        $ticket_data = [
            'name' => $title,
            'content' => $this->_sanitize_content($form_data['description'] ?? ''),
            'itilcategories_id' => $category_id,
        ];

        if (isset($form_data['urgency']) && $this->_is_valid_urgency($form_data['urgency'])) {
            $ticket_data['urgency'] = (int)$form_data['urgency'];
        }

        if (isset($form_data['entity_id']) && $this->_is_valid_id($form_data['entity_id'])) {
            $ticket_data['entities_id'] = (int)$form_data['entity_id'];
        }

        return $ticket_data;
    }

    // =====================================================
    // PRIVATE METHODS - DATA TRANSFORMATION
    // =====================================================

    /**
     * Transform entities data từ API response
     * 
     * @param mixed $data
     * @return array
     */
    private function _transform_entities($data)
    {
        if (!$this->_is_valid_array($data)) {
            return [];
        }

        $entities = [];

        foreach ($data as $item) {
            if (!isset($item['id']) || !isset($item['name'])) {
                continue;
            }

            if (strtolower($item['name']) === 'root entity') {
                continue;
            }

            $entities[] = [
                'id' => (int)$item['id'],
                'name' => $this->_sanitize_string($item['name']),
                'key' => $this->_slugify($this->_sanitize_string($item['name']))
            ];
        }

        return $entities;
    }

    /**
     * Transform categories data thành form fields structure
     * 
     * @param array $data
     * @return array
     */
    private function _transform_categories($data)
    {
        if (!$this->_is_valid_array($data)) {
            return [];
        }

        $result = [];
        foreach ($data as $item) {
            // Validate item structure
            if (!isset($item[1]) || !isset($item[80])) {
                log_message('error', 'GLPI: Invalid category item structure');
                continue;
            }

            // Extract entity name và key
            $entityName = $this->_extract_entity_name($item[80]);
            $entityKey = $this->_slugify($entityName);

            // Initialize entity structure nếu chưa có
            if (!isset($result[$entityKey])) {
                $result[$entityKey] = $this->_get_default_form_structure();
            }

            // Parse category path (VD: "Hardware > Desktop")
            $parts = explode(' > ', $item[1]);

            if (empty($parts)) {
                continue;
            }

            // ===== THÊM ĐOẠN NÀY =====
            // Lưu mapping để tra cứu sau khi user chọn category
            if (isset($item[2])) {
                $this->category_mapping[$item[2]] = $parts;
            }
            // ===== HẾT ĐOẠN THÊM =====

            // Add category vào options
            $this->_add_category_option($result[$entityKey], $parts, $item);
        }

        // echo '<pre>';
        // print_r($result);
        // die;

        return $result;
    }

    /**
     * Add category option vào form structure (recursive version - hỗ trợ nhiều cấp)
     * 
     * @param array &$entity_structure
     * @param array $parts
     * @param array $item
     * @return void
     */
    private function _add_category_option(&$entity_structure, $parts, $item)
    {
        $this->_insert_category_recursive($entity_structure['subcategory']['options'], $parts, $item);
    }

    /**
     * Recursive insert category (multi-level support)
     * 
     * @param array &$options
     * @param array $parts
     * @param array $item
     * @return void
     */
    private function _insert_category_recursive(&$options, $parts, $item)
    {
        if (empty($parts)) return;

        $currentLabel = $this->_sanitize_string(array_shift($parts));
        $currentKey   = $this->_slugify($currentLabel);

        // Nếu chưa tồn tại thì tạo node mới
        if (!isset($options[$currentKey])) {
            $options[$currentKey] = [
                'label' => $currentLabel,
                'value' => $currentKey,
                'children' => []
            ];
        }

        // Nếu còn cấp con → đệ quy tiếp
        if (!empty($parts)) {
            $this->_insert_category_recursive($options[$currentKey]['children'], $parts, $item);
        } else {
            // Nếu là cấp cuối → set đúng ID GLPI
            $options[$currentKey]['value'] = $item[2] ?? $currentKey;
        }
    }

    // =====================================================
    // PRIVATE METHODS - FILE UPLOAD
    // =====================================================

    /**
     * Upload multiple attachments cho ticket
     * 
     * @param int $ticket_id
     * @param array $files
     * @return array Danh sách files đã upload thành công
     */
    private function _upload_attachments($ticket_id, $files)
    {
        $uploaded = [];

        foreach ($files as $file) {
            // Validate file structure
            if (!$this->_is_valid_file($file)) {
                log_message('error', 'GLPI: Invalid file structure');
                continue;
            }

            // Upload file
            $result = $this->glpi_api->upload_document(
                $ticket_id,
                $file['full_path'],
                $file['file_name']
            );

            // Check upload result
            if ($this->_is_success_response($result)) {
                $uploaded[] = $file['file_name'];
            } else {
                log_message('error', "GLPI: Failed to upload file: {$file['file_name']}");
            }
        }

        return $uploaded;
    }

    // =====================================================
    // PRIVATE METHODS - VALIDATION
    // =====================================================

    /**
     * Validate ticket data
     * 
     * @param array $data
     * @return bool
     */
    private function _validate_ticket_data($data)
    {
        if (!$this->_is_valid_array($data)) {
            return false;
        }

        // Required fields
        $required = ['name', 'content'];

        foreach ($required as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                log_message('error', "GLPI: Missing required field: {$field}");
                return false;
            }
        }

        return true;
    }

    /**
     * Check if valid ID
     * 
     * @param mixed $id
     * @return bool
     */
    private function _is_valid_id($id)
    {
        return is_numeric($id) && $id > 0;
    }

    /**
     * Check if valid urgency level (1-5)
     * 
     * @param mixed $urgency
     * @return bool
     */
    private function _is_valid_urgency($urgency)
    {
        return is_numeric($urgency) && $urgency >= 1 && $urgency <= 5;
    }

    /**
     * Check if valid array
     * 
     * @param mixed $data
     * @return bool
     */
    private function _is_valid_array($data)
    {
        return is_array($data) && !empty($data);
    }

    /**
     * Check if valid string
     * 
     * @param mixed $str
     * @return bool
     */
    private function _is_valid_string($str)
    {
        return is_string($str) && !empty(trim($str));
    }

    /**
     * Check if valid file structure
     * 
     * @param array $file
     * @return bool
     */
    private function _is_valid_file($file)
    {
        return isset($file['full_path'])
            && isset($file['file_name'])
            && file_exists($file['full_path']);
    }

    /**
     * Check if has files to upload
     * 
     * @param array $files
     * @return bool
     */
    private function _has_files($files)
    {
        return $this->_is_valid_array($files);
    }

    /**
     * Check if response is success
     * 
     * @param array $response
     * @return bool
     */
    private function _is_success_response($response)
    {
        return isset($response['success']) && $response['success'] === true;
    }

    // =====================================================
    // PRIVATE METHODS - STRING MANIPULATION
    // =====================================================

    /**
     * Extract entity name từ full path
     * VD: "Root entity > Network" => "Network"
     * 
     * @param string $fullPath
     * @return string
     */
    private function _extract_entity_name($fullPath)
    {
        $cleaned = trim(str_replace('Root entity >', '', $fullPath));
        return strtolower($cleaned);
    }

    /**
     * Convert string thành slug (lowercase, no spaces)
     * VD: "User Computer" => "user-computer"
     * 
     * @param string $str
     * @return string
     */
    private function _slugify($str)
    {
        $str = strtolower(trim($str));
        $str = str_replace([' ', ',', '_'], '-', $str);
        $str = preg_replace('/[^a-z0-9-]/', '', $str);

        return preg_replace('/-+/', '-', $str);
    }

    /**
     * Sanitize string (remove HTML, trim)
     * 
     * @param string $str
     * @return string
     */
    private function _sanitize_string($str)
    {
        return trim(strip_tags($str));
    }

    /**
     * Sanitize content (cho description/textarea)
     * 
     * @param string $content
     * @return string
     */
    private function _sanitize_content($content)
    {
        // Allow basic HTML tags
        $allowed_tags = '<p><br><b><i><u><ul><ol><li>';
        $cleaned = strip_tags($content, $allowed_tags);
        return trim($cleaned);
    }

    // ===== XÓA METHOD CŨ _generate_ticket_title =====
    // ===== THAY BẰNG METHOD MỚI Ở DƯỚI =====

    /**
     * Generate ticket title với thứ tự đảo ngược (từ cấp nhỏ → cấp lớn)
     * 
     * Logic:
     * 1. Nhận category_id từ form (VD: 25)
     * 2. Tìm path trong mapping: ["Hardware", "Desktop", "Laptop"]
     * 3. Đảo ngược mảng: ["Laptop", "Desktop", "Hardware"]
     * 4. Join lại: "Laptop > Desktop > Hardware"
     * 5. Kết hợp với main category: "Network - Laptop > Desktop > Hardware"
     * 
     * @param string $main_category Loại chính (network, user-computer, etc.)
     * @param int $category_id GLPI category ID được chọn
     * @return string Ticket title đã format
     */
    private function _generate_reversed_ticket_title($main_category, $category_id)
    {
        // Get display name cho main category
        $category_display = ucfirst($main_category);

        // Kiểm tra xem có mapping không
        if (empty($this->category_mapping)) {
            log_message('error', 'GLPI: Category mapping is empty');
            return $category_display . ' - Request';
        }

        // Kiểm tra category_id có trong mapping không
        if (!isset($this->category_mapping[$category_id])) {
            log_message('error', "GLPI: Category ID {$category_id} not found in mapping");
            return $category_display . ' - Request';
        }

        // Lấy path từ mapping
        // VD: ["Hardware", "Desktop", "Laptop"]
        $path = $this->category_mapping[$category_id];

        // Đảo ngược mảng: từ cấp nhỏ nhất lên cấp lớn nhất
        // VD: ["Laptop", "Desktop", "Hardware"]
        $reversed_path = array_reverse($path);

        // Join thành chuỗi với separator " > "
        // VD: "Laptop > Desktop > Hardware"
        $subcategory_title = implode(' > ', $reversed_path);

        // Format final title
        // VD: "Network - Laptop > Desktop > Hardware"
        return "{$category_display} - {$subcategory_title}";
    }

    // =====================================================
    // PRIVATE METHODS - RESPONSE HELPERS
    // =====================================================

    /**
     * Success response format
     * 
     * @param array $data
     * @return array
     */
    private function _success_response($data = [])
    {
        return array_merge(['success' => true], $data);
    }

    /**
     * Error response format
     * 
     * @param string $message
     * @param array $additional_data
     * @return array
     */
    private function _error_response($message, $additional_data = [])
    {
        return array_merge([
            'success' => false,
            'message' => $message
        ], $additional_data);
    }

    // =====================================================
    // PRIVATE METHODS - FORM STRUCTURE
    // =====================================================

    /**
     * Get default form structure cho mỗi entity
     * 
     * @return array
     */
    private function _get_default_form_structure()
    {
        return [
            'subcategory' => [
                'type' => 'select',
                'label' => 'Sub-Category',
                'required' => true,
                'options' => []
            ],
            'description' => [
                'type' => 'textarea',
                'label' => 'Description',
                'required' => true,
                'placeholder' => 'Mô tả chi tiết vấn đề của bạn...',
                'rows' => 5
            ],
            'attachments' => [
                'type' => 'file',
                'label' => 'Đính kèm tài liệu/hình ảnh',
                'required' => false,
                'max_files' => 5,
                'max_size' => 10485760, // 10MB in bytes
                'allowed_types' => 'jpg|jpeg|png|gif|pdf|doc|docx|xls|xlsx|txt'
            ]
        ];
    }

    // =====================================================
    // PUBLIC HELPER METHODS
    // =====================================================

    /**
     * Get category display name
     * 
     * @param string $category_slug
     * @return string
     */
    public function get_category_display_name($category_slug)
    {
        return ucfirst($category_slug);
    }

    /**
     * Get all category names
     * 
     * @return array
     */
    public function get_all_category_names()
    {
        return $this->category_names;
    }

    /**
     * Get category path info
     * 
     * @param int $category_id
     * @return array|null
     */
    public function get_category_path_info($category_id)
    {
        if (!isset($this->category_mapping[$category_id])) {
            return null;
        }

        $path = $this->category_mapping[$category_id];
        $reversed_path = array_reverse($path);

        return [
            'id' => $category_id,
            'original_path' => implode(' > ', $path),
            'reversed_path' => implode(' > ', $reversed_path), // "Laptop > Desktop > Hardware"
            'levels' => count($path)                          // 3
        ];
    }
}
