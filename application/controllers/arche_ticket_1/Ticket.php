<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Ticket extends CI_Controller
{
    private const ALLOWED_CATEGORIES = ['network', 'user-computer', 'group-app', 'cyber-security'];
    private const MAX_FILES_DEFAULT = 3;
    private const MAX_FILE_SIZE_DEFAULT = 10485760;

    public function __construct()
    {
        parent::__construct();

        $this->load->config('glpi');
        $this->load->library(['form_validation', 'upload']);
        $this->load->model(['arche_ticket/glpi_api_model', 'arche_ticket/ticket_model']);
    }

    public function index()
    {
        try {
            $cards = $this->glpi_api_model->getCards();
            $categoryResults = $this->getValidatedCategories($cards);

            $data = [
                'cards' => $cards,
                'page_title' => 'IT Support Dashboard',
                'formData' => $categoryResults
            ];

            $this->load->view('arche_ticket/index', $data);
        } catch (Exception $e) {
            log_message('error', 'Index page error: ' . $e->getMessage());
            show_error('Unable to load dashboard. Please contact administrator.');
        }
    }

    public function create()
    {
        @ob_clean();
        $this->output->set_content_type('application/json');

        try {
            $input = $this->collectInput();
            $validation = $this->validateInput($input);

            if (!$validation['success']) {
                $this->sendJsonResponse($validation);
                return;
            }

            $uploadResult = $this->handleFileUploads($input['category']);
            
            if (!$uploadResult['success']) {
                $this->sendJsonResponse($uploadResult);
                return;
            }

            $ticketData = $this->prepareTicketData($input);

            $result = $this->glpi_api_model->create_ticket_with_attachments(
                $ticketData, 
                $uploadResult['files']
            );

            $this->sendJsonResponse($result);

        } catch (Exception $e) {
            log_message('error', 'Ticket creation error: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            
            $this->sendJsonResponse([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo ticket. Vui lòng thử lại sau.'
            ]);
        }
    }

    public function get_form_fields()
    {
        $this->output->set_content_type('application/json');

        $category = $this->input->get('category');
        $formFields = $this->config->item('form_fields');

        if (isset($formFields[$category])) {
            $this->sendJsonResponse([
                'success' => true,
                'fields' => $formFields[$category]
            ]);
        } else {
            $this->sendJsonResponse([
                'success' => false,
                'message' => 'Category không tồn tại'
            ]);
        }
    }

    public function history()
    {
        $this->output->set_content_type('application/json');

        $userId = $this->session->userdata('user_id');
        if (!$userId) {
            $this->sendJsonResponse([
                'success' => false,
                'message' => 'Unauthorized'
            ]);
            return;
        }

        $page = max(1, (int)$this->input->get('page') ?: 1);
        $limit = max(1, min(100, (int)$this->input->get('limit') ?: 10));
        $offset = ($page - 1) * $limit;

        $filters = [
            'category' => $this->input->get('category'),
            'status' => $this->input->get('status'),
            'user_id' => $userId
        ];

        $tickets = $this->ticket_model->get_logs($filters, $limit, $offset);
        $totalCount = $this->ticket_model->count_logs($filters);

        $this->sendJsonResponse([
            'success' => true,
            'data' => $tickets,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $totalCount,
                'total_pages' => ceil($totalCount / $limit)
            ]
        ]);
    }

    private function collectInput(): array
    {
        return [
            'category' => trim($this->input->post('category') ?? ''),
            'subcategory' => trim($this->input->post('subcategory') ?? ''),
            'description' => trim($this->input->post('description') ?? '')
        ];
    }

    private function validateInput(array $input): array
    {
        $errors = [];

        if (empty($input['category'])) {
            $errors[] = 'Category không được để trống';
        } elseif (!in_array($input['category'], self::ALLOWED_CATEGORIES)) {
            $errors[] = 'Category không hợp lệ: ' . htmlspecialchars($input['category']);
        }

        if (empty($input['subcategory']) && empty($input['description'])) {
            $errors[] = 'Vui lòng điền ít nhất Subcategory hoặc Description';
        }

        if (!empty($input['description']) && mb_strlen($input['description']) < 10) {
            $errors[] = 'Description phải có ít nhất 10 ký tự';
        }

        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => implode('. ', $errors),
                'errors' => $errors
            ];
        }

        return ['success' => true];
    }

    private function handleFileUploads(string $category): array
    {
        if (empty($_FILES['attachments']['name'][0])) {
            return [
                'success' => true,
                'files' => []
            ];
        }

        $formFields = $this->config->item('form_fields');
        $fileConfig = $formFields[$category]['attachments'] ?? [];

        $maxFiles = $fileConfig['max_files'] ?? self::MAX_FILES_DEFAULT;
        $maxSize = ($fileConfig['max_size'] ?? self::MAX_FILE_SIZE_DEFAULT) / 1024; // Convert to KB
        $allowedTypes = $fileConfig['allowed_types'] ?? $this->config->item('upload_allowed_types');

        $filesCount = count($_FILES['attachments']['name']);
        if ($filesCount > $maxFiles) {
            return [
                'success' => false,
                'message' => "Chỉ được phép tải lên tối đa {$maxFiles} files"
            ];
        }

        $uploadPath = $this->config->item('upload_path') . $category . '/';
        if (!is_dir($uploadPath)) {
            if (!mkdir($uploadPath, 0755, true)) {
                return [
                    'success' => false,
                    'message' => 'Không thể tạo thư mục upload'
                ];
            }
        }

        $config = [
            'upload_path' => $uploadPath,
            'allowed_types' => $allowedTypes,
            'max_size' => $maxSize,
            'encrypt_name' => true,
            'remove_spaces' => true
        ];

        $this->upload->initialize($config);

        $uploadedFiles = [];
        $errors = [];

        for ($i = 0; $i < $filesCount; $i++) {
            if ($_FILES['attachments']['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }

            $_FILES['file'] = [
                'name' => $_FILES['attachments']['name'][$i],
                'type' => $_FILES['attachments']['type'][$i],
                'tmp_name' => $_FILES['attachments']['tmp_name'][$i],
                'error' => $_FILES['attachments']['error'][$i],
                'size' => $_FILES['attachments']['size'][$i]
            ];

            if ($this->upload->do_upload('file')) {
                $uploadedFiles[] = $this->upload->data();
            } else {
                $errors[] = strip_tags($this->upload->display_errors());
            }
        }

        if (!empty($errors) && empty($uploadedFiles)) {
            return [
                'success' => false,
                'message' => 'Upload thất bại: ' . implode(', ', $errors)
            ];
        }

        return [
            'success' => true,
            'files' => $uploadedFiles,
            'message' => count($uploadedFiles) . ' file(s) uploaded successfully'
        ];
    }

    private function prepareTicketData(array $input): array
    {
        $formData = [
            'subcategory' => $input['subcategory'],
            'description' => $input['description']
        ];

        return $this->glpi_api_model->prepare_ticket_data($input['category'], $formData);
    }

    private function sendJsonResponse(array $data): void
    {
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    private function validateCategories(array $categories): array
    {
        $errors = [];
        $seenIds = [];
        $seenNames = [];

        foreach ($categories as $index => $category) {
            if (!isset($category['id']) || !isset($category['name'])) {
                $errors[] = "Category at index $index missing 'id' or 'name'";
                continue;
            }

            if (!is_int($category['id'])) {
                $errors[] = "Category ID at index $index must be an integer";
            }

            if (!is_string($category['name'])) {
                $errors[] = "Category name at index $index must be a string";
            }

            if ($category['id'] <= 0) {
                $errors[] = "Category ID at index $index must be > 0";
            }

            if (trim($category['name']) === '') {
                $errors[] = "Category name at index $index cannot be empty";
            }

            if (in_array($category['id'], $seenIds)) {
                $errors[] = "Duplicate category ID: {$category['id']}";
            }
            $seenIds[] = $category['id'];

            $lowerName = strtolower($category['name']);
            if (in_array($lowerName, $seenNames)) {
                $errors[] = "Duplicate category name: {$category['name']}";
            }
            $seenNames[] = $lowerName;
        }

        return $errors;
    }

    private function getValidatedCategories(array $categories): array
    {
        $errors = $this->validateCategories($categories);
        if (!empty($errors)) {
            throw new Exception('Category validation failed: ' . implode(', ', $errors));
        }

        $results = [];
        foreach ($categories as $category) {
            $key = $category['key'];
            $results[$key] = $this->glpi_api_model->getCategories($category['id']);
            $results[$key]['is_renderHtml'] = !empty($results[$key]) ? 1 : 0;
        }

        return $results;
    }
}