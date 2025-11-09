<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Ticket extends CI_Controller
{
    private const MAX_FILES_DEFAULT     = 3;
    private const MAX_FILE_SIZE_DEFAULT = 10485760;

    public function __construct()
    {
        parent::__construct();

        $this->load->config('glpi');
        $this->load->library(['form_validation', 'upload']);
        $this->load->model('arche_ticket/glpi/glpi_api_model');
    }

    public function index()
    {
        try {
            $cards              = $this->glpi_api_model->getEntities();

            $categoryResults    = $this->getValidatedCategories($cards);

            $data = [
                'page_title' => 'IT Support Dashboard',
                'cards'      => $cards,
                'formData'   => $categoryResults
            ];

            $this->load->view('arche_ticket/index', $data);

        } catch (Exception $e) {
            log_message('error', 'Index page error: ' . $e->getMessage());
            show_error('Unable to load dashboard. Please contact administrator.');
        }
    }

    public function downloadTicketDocument($id)
    {
        return $this->glpi_api_model->downloadDocument($id);
    }

    public function view($id)
    {
        return $this->glpi_api_model->getTicketById($id);
    }

    public function reopen($id)
    {
        return $this->glpi_api_model->reopenTicketById($id);
    }

    public function create()
    {
        $this->output->set_content_type('application/json');

        try {
            // Get form data
            $input      = $this->collectInput();

            // Validate form data
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

            $result = $this->glpi_api_model->createTicketWithAttachments(
                $ticketData, 
                $uploadResult['files']
            );

            $this->sendJsonResponse($result);

        } catch (Exception $e) {
            log_message('error', 'Ticket creation error: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());

            $this->sendJsonResponse([
                'success' => false,
                'message' => 'An error occurred while creating the ticket. Please try again later.'
            ]);
        }
    }

    private function collectInput(): array
    {
        return [
            'category'    => trim($this->input->post('category')    ?? ''),
            'subcategory' => trim($this->input->post('subcategory') ?? ''),
            'description' => trim($this->input->post('description') ?? '')
        ];
    }

    private function validateInput(array $input): array
    {
        $errors = [];

        if (empty($input['category'])) {
            $errors[] = 'Category cannot be empty';
        }

        if (empty($input['subcategory']) && empty($input['description'])) {
            $errors[] = 'Please fill in at least Subcategory or Description';
        }

        if (!empty($input['description']) && mb_strlen($input['description']) < 10) {
            $errors[] = 'Description must have at least 10 characters';
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

    public function list()
    {
           return $this->glpi_api_model->getList();


    }

    private function handleFileUploads(string $category): array
    {
        if (empty($_FILES['attachments']['name'][0])) {
            return [
                'success' => true,
                'files' => []
            ];
        }

        $maxFiles = self::MAX_FILES_DEFAULT;
        $maxSize = self::MAX_FILE_SIZE_DEFAULT / 1024;
        $allowedTypes = $this->config->item('upload_allowed_types');

        $filesCount = count($_FILES['attachments']['name']);
        if ($filesCount > $maxFiles) {
            return [
                'success' => false,
                'message' => "Only maximum uploads are allowed {$maxFiles} files"
            ];
        }

        $uploadPath = $this->config->item('upload_path') . $category . '/';
        if (!is_dir($uploadPath)) {
            if (!mkdir($uploadPath, 0755, true)) {
                return [
                    'success' => false,
                    'message' => 'Unable to create upload folder'
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