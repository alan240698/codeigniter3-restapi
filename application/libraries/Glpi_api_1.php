<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Glpi_api {
    
    private $CI;
    private $api_url;
    private $app_token;
    private $user_token;
    private $session_token = null;
    
    private $errors = [];
    
    private $validation_rules = [
        'ticket' => [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'content' => ['required', 'string', 'min:10'],
            'itilcategories_id' => ['required', 'integer', 'min:1'],
            'entities_id' => ['required', 'integer', 'min:0'],
            'urgency' => ['integer', 'in:1,2,3,4,5'],
            'impact' => ['integer', 'in:1,2,3,4,5'],
            'priority' => ['integer', 'in:1,2,3,4,5,6'],
            'type' => ['integer', 'in:1,2'],
            'status' => ['integer', 'in:1,2,3,4,5,6'],
        ],
        'document' => [
            'file_path' => ['required', 'file_exists', 'file_size:10240'], // Max 10MB
            'file_name' => ['required', 'string', 'max:255'],
        ],
        'entity' => [
            'id' => ['required', 'integer', 'min:0'],
        ],
    ];
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->config('glpi');
        
        $this->api_url = $this->CI->config->item('glpi_api_url');
        $this->app_token = $this->CI->config->item('glpi_app_token');
        $this->user_token = $this->CI->config->item('glpi_user_token');
    }
    
    /**
     * Initialize GLPI session
     * 
     * @return bool
     */
    public function init_session() {
        $response = $this->_curl_request('initSession', 'GET', null, [
            'Authorization: ' . $this->user_token
        ]);
        
        if ($response['success'] && isset($response['data']['session_token'])) {
            $this->session_token = $response['data']['session_token'];
            return true;
        }
        
        return false;
    }

    /**
     * Kill GLPI session
     * 
     * @return bool
     */
    public function kill_session() {
        if (!$this->session_token) {
            return false;
        }
        
        $response = $this->_curl_request('killSession', 'GET');
        $this->session_token = null;
        
        return $response['success'];
    }
    
    /**
     * Get all entities (with optional validation)
     * 
     * @return array
     */
    public function getCardEntities() {
        $response = $this->_curl_request('Entity', 'GET', null, [
            'Authorization: user_token ' . $this->user_token
        ]);

        return $response['data'];
    }

    /**
     * Get categories by entity (with validation)
     * 
     * @param int $id
     * @return array|false
     */
    public function getCategoyByEntity($id) {
        // Validate entity ID
        if (!$this->_validate_field('id', $id, ['required', 'integer', 'min:0'])) {
            return false;
        }
        
        $payload = [
            'criteria' => [
                [
                    'field' => 80,
                    'searchtype' => 'equals',
                    'value' => $id
                ]
            ]
        ];
        
        $response = $this->_curl_request('search/ITILCategory', 'POST', $payload);

        if (isset($response['success']) && $response['success'] && isset($response['data']['data'])) {
            return $response['data']['data'];
        }

        log_message('error', 'GLPI API error: ' . print_r($response, true));
        return false;
    }

    /**
     * Create a new ticket (with automatic validation)
     * 
     * @param array $ticket_data
     * @param bool $auto_validate (default: true)
     * @return array
     */
    public function create_ticket($ticket_data, $auto_validate = true) {
        if (!$this->session_token) {
            return [
                'success' => false,
                'message' => 'Session not initialized'
            ];
        }
        
        // Auto validate if enabled
        if ($auto_validate) {
            // Sanitize data first
            $ticket_data = $this->_sanitize_data($ticket_data);
            
            // Validate
            if (!$this->_validate_data($ticket_data, 'ticket')) {
                return [
                    'success' => false,
                    'message' => $this->get_first_error(),
                    'errors' => $this->get_errors(),
                    'validation_failed' => true
                ];
            }
        }
        
        $payload = [
            'input' => $ticket_data
        ];

        $response = $this->_curl_request('Ticket', 'POST', $payload);
        
        return $response;
    }
    
    /**
     * Upload document to ticket (with automatic validation)
     * 
     * @param int $ticket_id
     * @param string $file_path
     * @param string $file_name
     * @param bool $auto_validate (default: true)
     * @return array
     */
    public function upload_document($ticket_id, $file_path, $file_name, $auto_validate = true) {
        if (!$this->session_token) {
            return [
                'success' => false,
                'message' => 'Session not initialized'
            ];
        }
        
        // Auto validate if enabled
        if ($auto_validate) {
            $document_data = [
                'file_path' => $file_path,
                'file_name' => $file_name
            ];
            
            if (!$this->_validate_data($document_data, 'document')) {
                return [
                    'success' => false,
                    'message' => $this->get_first_error(),
                    'errors' => $this->get_errors(),
                    'validation_failed' => true
                ];
            }
            
            // Validate ticket_id
            if (!$this->_validate_field('ticket_id', $ticket_id, ['required', 'integer', 'min:1'])) {
                return [
                    'success' => false,
                    'message' => $this->get_first_error(),
                    'errors' => $this->get_errors(),
                    'validation_failed' => true
                ];
            }
        }
        
        // Chuẩn bị manifest
        $manifest = [
            'input' => [
                'name' => $file_name,
                '_filename' => [$file_name]
            ]
        ];
        
        // Chuẩn bị multipart form data
        $boundary = '----WebKitFormBoundary' . uniqid();
        $delimiter = '--' . $boundary;
        
        $post_data = $delimiter . "\r\n"
            . 'Content-Disposition: form-data; name="uploadManifest"' . "\r\n\r\n"
            . json_encode($manifest) . "\r\n"
            . $delimiter . "\r\n"
            . 'Content-Disposition: form-data; name="filename[0]"; filename="' . $file_name . '"' . "\r\n"
            . 'Content-Type: application/octet-stream' . "\r\n\r\n"
            . file_get_contents($file_path) . "\r\n"
            . $delimiter . '--';
        
        $response = $this->_curl_request(
            "Ticket/{$ticket_id}/Document",
            'POST',
            $post_data,
            ["Content-Type: multipart/form-data; boundary={$boundary}"],
            true
        );
        
        return $response;
    }
    
    /**
     * Get ticket by ID (with validation)
     * 
     * @param int $ticket_id
     * @return array
     */
    public function get_ticket($ticket_id) {
        if (!$this->session_token) {
            return [
                'success' => false,
                'message' => 'Session not initialized'
            ];
        }
        
        // Validate ticket_id
        if (!$this->_validate_field('ticket_id', $ticket_id, ['required', 'integer', 'min:1'])) {
            return [
                'success' => false,
                'message' => $this->get_first_error(),
                'errors' => $this->get_errors(),
                'validation_failed' => true
            ];
        }
        
        $response = $this->_curl_request("Ticket/{$ticket_id}", 'GET');
        
        return $response;
    }
    
    // =====================================================
    // VALIDATION METHODS
    // =====================================================
    
    /**
     * Validate data against rules
     * 
     * @param array $data
     * @param string $type (ticket, document, entity)
     * @return bool
     */
    private function _validate_data($data, $type) {
        $this->errors = [];
        
        if (!isset($this->validation_rules[$type])) {
            return true; // No rules defined
        }
        
        $rules = $this->validation_rules[$type];
        
        foreach ($rules as $field => $field_rules) {
            $value = isset($data[$field]) ? $data[$field] : null;
            $this->_validate_field($field, $value, $field_rules, $data);
        }
        
        return empty($this->errors);
    }
    
    /**
     * Validate single field
     * 
     * @param string $field
     * @param mixed $value
     * @param array $rules
     * @param array $all_data
     * @return bool
     */
    private function _validate_field($field, $value, $rules, $all_data = []) {
        foreach ($rules as $rule) {
            // Parse rule and parameters
            $params = [];
            if (strpos($rule, ':') !== false) {
                list($rule, $param_string) = explode(':', $rule, 2);
                $params = explode(',', $param_string);
            }
            
            $method = '_rule_' . $rule;
            
            if (method_exists($this, $method)) {
                if (!$this->$method($field, $value, $params, $all_data)) {
                    return false; // Stop on first error
                }
            }
        }
        
        return true;
    }
    
    /**
     * Sanitize data
     * 
     * @param array $data
     * @return array
     */
    private function _sanitize_data($data) {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = trim(strip_tags($value));
            } else if (is_array($value)) {
                $sanitized[$key] = $this->_sanitize_data($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }
    
    // =====================================================
    // VALIDATION RULES
    // =====================================================
    
    private function _rule_required($field, $value, $params, $all_data) {
        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            $this->_add_error($field, ucfirst(str_replace('_', ' ', $field)) . ' là bắt buộc');
            return false;
        }
        return true;
    }
    
    private function _rule_string($field, $value, $params, $all_data) {
        if ($value !== null && !is_string($value)) {
            $this->_add_error($field, ucfirst(str_replace('_', ' ', $field)) . ' phải là chuỗi ký tự');
            return false;
        }
        return true;
    }
    
    private function _rule_integer($field, $value, $params, $all_data) {
        if ($value !== null && !is_numeric($value) && !filter_var($value, FILTER_VALIDATE_INT)) {
            $this->_add_error($field, ucfirst(str_replace('_', ' ', $field)) . ' phải là số nguyên');
            return false;
        }
        return true;
    }
    
    private function _rule_min($field, $value, $params, $all_data) {
        if ($value === null) return true;
        
        $min = $params[0];
        
        if (is_numeric($value)) {
            if ($value < $min) {
                $this->_add_error($field, ucfirst(str_replace('_', ' ', $field)) . " phải lớn hơn hoặc bằng {$min}");
                return false;
            }
        } else if (is_string($value)) {
            if (mb_strlen($value) < $min) {
                $this->_add_error($field, ucfirst(str_replace('_', ' ', $field)) . " phải có ít nhất {$min} ký tự");
                return false;
            }
        }
        
        return true;
    }
    
    private function _rule_max($field, $value, $params, $all_data) {
        if ($value === null) return true;
        
        $max = $params[0];
        
        if (is_numeric($value)) {
            if ($value > $max) {
                $this->_add_error($field, ucfirst(str_replace('_', ' ', $field)) . " phải nhỏ hơn hoặc bằng {$max}");
                return false;
            }
        } else if (is_string($value)) {
            if (mb_strlen($value) > $max) {
                $this->_add_error($field, ucfirst(str_replace('_', ' ', $field)) . " không được vượt quá {$max} ký tự");
                return false;
            }
        }
        
        return true;
    }
    
    private function _rule_in($field, $value, $params, $all_data) {
        if ($value === null) return true;
        
        if (!in_array($value, $params)) {
            $this->_add_error($field, ucfirst(str_replace('_', ' ', $field)) . ' không hợp lệ');
            return false;
        }
        
        return true;
    }
    
    private function _rule_email($field, $value, $params, $all_data) {
        if ($value === null) return true;
        
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->_add_error($field, ucfirst(str_replace('_', ' ', $field)) . ' không phải là email hợp lệ');
            return false;
        }
        
        return true;
    }
    
    private function _rule_file_exists($field, $value, $params, $all_data) {
        if ($value === null) return true;
        
        if (!file_exists($value)) {
            $this->_add_error($field, 'File không tồn tại');
            return false;
        }
        
        return true;
    }
    
    private function _rule_file_size($field, $value, $params, $all_data) {
        if ($value === null || !file_exists($value)) return true;
        
        $max_size = $params[0]; // Size in KB
        $file_size = filesize($value) / 1024; // Convert to KB
        
        if ($file_size > $max_size) {
            $this->_add_error($field, 'Kích thước file không được vượt quá ' . round($max_size / 1024, 2) . 'MB');
            return false;
        }
        
        return true;
    }
    
    private function _rule_regex($field, $value, $params, $all_data) {
        if ($value === null) return true;
        
        $pattern = $params[0];
        
        if (!preg_match($pattern, $value)) {
            $this->_add_error($field, ucfirst(str_replace('_', ' ', $field)) . ' không đúng định dạng');
            return false;
        }
        
        return true;
    }
    
    // =====================================================
    // PUBLIC VALIDATION HELPER METHODS
    // =====================================================
    
    /**
     * Add custom validation rule dynamically
     * 
     * @param string $type (ticket, document, entity)
     * @param string $field
     * @param array $rules
     * @return void
     */
    public function add_validation_rule($type, $field, $rules) {
        if (!isset($this->validation_rules[$type])) {
            $this->validation_rules[$type] = [];
        }
        
        $this->validation_rules[$type][$field] = $rules;
    }
    
    /**
     * Get all validation errors
     * 
     * @return array
     */
    public function get_errors() {
        return $this->errors;
    }
    
    /**
     * Get first error message
     * 
     * @return string|null
     */
    public function get_first_error() {
        if (empty($this->errors)) {
            return null;
        }
        
        $first_field = array_key_first($this->errors);
        return $this->errors[$first_field][0];
    }
    
    /**
     * Check if has validation errors
     * 
     * @return bool
     */
    public function has_errors() {
        return !empty($this->errors);
    }
    
    /**
     * Add error message
     * 
     * @param string $field
     * @param string $message
     * @return void
     */
    private function _add_error($field, $message) {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        
        $this->errors[$field][] = $message;
    }
    
    /**
     * Custom validation callback
     * 
     * @param array $data
     * @param string $type
     * @param callable $callback
     * @return bool
     */
    public function validate_with_callback($data, $type, $callback) {
        // Basic validation first
        if (!$this->_validate_data($data, $type)) {
            return false;
        }
        
        // Custom callback validation
        if (is_callable($callback)) {
            $result = $callback($data, $this);
            
            if ($result !== true) {
                $this->_add_error('custom', is_string($result) ? $result : 'Custom validation failed');
                return false;
            }
        }
        
        return true;
    }
    
    // =====================================================
    // CURL REQUEST METHOD (Không thay đổi)
    // =====================================================
    
    /**
     * Private method to make CURL requests
     * 
     * @param string $endpoint
     * @param string $method
     * @param mixed $data
     * @param array $additional_headers
     * @param bool $raw_data
     * @return array
     */
    private function _curl_request($endpoint, $method = 'GET', $data = null, $additional_headers = [], $raw_data = false) {
        $url = $this->api_url . '/' . $endpoint;
        
        // Chuẩn bị headers
        $headers = [
            'Content-Type: application/json',
            'App-Token: ' . $this->app_token
        ];
        
        // Thêm Session-Token nếu đã có
        if ($this->session_token) {
            $headers[] = 'Session-Token: ' . $this->session_token;
        }
        
        // Merge additional headers
        $headers = array_merge($headers, $additional_headers);
        
        // Khởi tạo CURL
        $ch = curl_init();
        
        // Thiết lập options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Tắt verify SSL (chỉ dùng trong dev)
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        // Thiết lập method
        switch ($method) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                if ($data) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $raw_data ? $data : json_encode($data));
                }
                break;
            case 'PUT':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                if ($data) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
                break;
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
        }
        
        // Thực hiện request
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        
        curl_close($ch);
        
        // Xử lý response
        if ($curl_error) {
            return [
                'success' => false,
                'message' => 'CURL Error: ' . $curl_error,
                'http_code' => $http_code
            ];
        }
        
        $decoded_response = json_decode($response, true);
        
        if ($http_code >= 200 && $http_code < 300) {
            return [
                'success' => true,
                'data' => $decoded_response,
                'http_code' => $http_code
            ];
        } else {
            return [
                'success' => false,
                'message' => $decoded_response['message'] ?? 'Unknown error',
                'data' => $decoded_response,
                'http_code' => $http_code
            ];
        }
    }
    
    /**
     * Get category ID from config
     * 
     * @param string $category
     * @param string $subcategory
     * @return int
     */
    public function get_category_id($category, $subcategory) {
        $categories = $this->CI->config->item('glpi_categories');
        
        if (isset($categories[$category][$subcategory])) {
            return $categories[$category][$subcategory];
        }
        
        return 0;
    }
}