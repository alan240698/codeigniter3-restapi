<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Ticket extends CI_Controller
{
    private const MAX_FILES_DEFAULT     = 3;
    private const MAX_FILE_SIZE_DEFAULT = 10485760;
    private const DEFAULT_COUNTRY       = 'Vietnam';

    /**
     * Contructor
     */
    public function __construct()
    {
        parent::__construct();

        // Load config
        $this->load->config('glpi');
        // Load libraries
        $this->load->library(['form_validation', 'upload']);
        // Load model
        $this->load->model('arche_ticket/glpi/glpi_api_model');
    }

    /**
     * Index page
     */
    public function index()
    {
        try {
            $country = $this->input->get('country');

            $cards           = $this->glpi_api_model->getEntities();
            $categoryResults = $this->getValidatedCategories($cards);

            $data = [
                'page_title' => 'Request IT/IS Support',
                'cards'      => $cards,
                'formData'   => $categoryResults,
                'country'    => !empty($country) ? $country : self::DEFAULT_COUNTRY
            ];

            // Send data to view
            $this->load->view('arche_ticket/index', $data);

        } catch (Exception $e) {
            log_message('error', 'Index page error: ' . $e->getMessage());
            show_error('Unable to load dashboard. Please contact administrator.');
        }
    }

    /**
     * Get list
     */
    public function list()
    {
        return $this->glpi_api_model->getList();
    }

    /**
     * View detail ticket
     */
    public function view($id)
    {
        return $this->glpi_api_model->getTicketById($id);
    }

    /**
     * Download ticket document
     */
    public function downloadTicketDocument($id)
    {
        return $this->glpi_api_model->downloadDocument($id);
    }

    /**
     * Reopen ticket
     */
    public function reopen($id)
    {
        return $this->glpi_api_model->reopenTicketById($id);
    }

    /**
     * Create ticket
     */
    public function create()
    {
        $this->output->set_content_type('application/json');

        try {
            // Get form data
            $input = $this->collectInput();

            // Validate form data
            $validation = $this->validateInput($input);
            if (!$validation['success']) {
                $this->sendJsonResponse($validation);
                return;
            }

            $uploadResult = $this->handleFileUploads();

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

    /**
     * Collect input
     */
    private function collectInput(): array
    {
        return [
            'category'    => trim($this->input->post('category')    ?? ''),
            'subcategory' => trim($this->input->post('subcategory') ?? ''),
            'description' => trim($this->input->post('description') ?? ''),
            'countryGlpi' => trim($this->input->post('country_glpi') ?? ''),
        ];
    }

    /**
     * Validate input
     */
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

        if (!empty($input['description']) && mb_strlen($input['description']) > 1000) {
            $errors[] = 'Description must be less than 1000 characters';
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

    /**
     * Handle file uploads
     */
    private function handleFileUploads(): array
    {
        if (empty($_FILES['attachments']['name'][0])) {
            return [
                'success' => true,
                'files' => []
            ];
        }

        $maxFiles     = self::MAX_FILES_DEFAULT;
        $maxSize      = self::MAX_FILE_SIZE_DEFAULT; // bytes
        $allowedTypes = explode('|', $this->config->item('upload_allowed_types'));

        $filesCount = count($_FILES['attachments']['name']);

        // Validate the number of files
        if ($filesCount > $maxFiles) {
            return [
                'success' => false,
                'message' => "Only maximum {$maxFiles} file(s) are allowed"
            ];
        }

        $uploadedFiles = [];
        $errors = [];

        for ($i = 0; $i < $filesCount; $i++) {
            if ($_FILES['attachments']['error'][$i] !== UPLOAD_ERR_OK) {
                if ($_FILES['attachments']['error'][$i] !== UPLOAD_ERR_NO_FILE) {
                    $errors[] = $this->getUploadErrorMessage($_FILES['attachments']['error'][$i]);
                }
                continue;
            }

            $fileName = $_FILES['attachments']['name'][$i];
            $fileSize = $_FILES['attachments']['size'][$i];
            $fileTmp  = $_FILES['attachments']['tmp_name'][$i];
            $fileType = $_FILES['attachments']['type'][$i];

            // Validate file size
            if ($fileSize > $maxSize) {
                $errors[] = "{$fileName}: File size exceeds " . ($maxSize / 1024) . "KB";
                continue;
            }

            // Validate file types
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            if (!in_array($fileExt, $allowedTypes)) {
                $errors[] = "{$fileName}: File type not allowed";
                continue;
            }

            // Validate file actually exists and can be read
            if (!file_exists($fileTmp) || !is_readable($fileTmp)) {
                $errors[] = "{$fileName}: Unable to read uploaded file";
                continue;
            }

            // Add files to the list to upload to GLPI
            $uploadedFiles[] = [
                'name'      => $fileName,
                'type'      => $fileType,
                'tmp_name'  => $fileTmp,
                'size'      => $fileSize,
                'extension' => $fileExt,
                'file_name' => $fileName,
                'full_path' => $fileTmp
            ];
        }

        // If there is an error and no files are successful
        if (!empty($errors) && empty($uploadedFiles)) {
            return [
                'success' => false,
                'message' => 'Upload failed: ' . implode(', ', $errors)
            ];
        }

        // If some files are successful, success is still returned
        return [
            'success'   => true,
            'files'     => $uploadedFiles,
            'warnings'  => $errors, // Invalid file errors
            'message'   => count($uploadedFiles) . ' file(s) ready to upload'
        ];
    }

    /**
     * Get readable error message from upload error code
     */
    private function getUploadErrorMessage(int $errorCode): string
    {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return 'File size exceeds limit';
            case UPLOAD_ERR_PARTIAL:
                return 'File was only partially uploaded';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing temporary folder';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk';
            case UPLOAD_ERR_EXTENSION:
                return 'File upload stopped by extension';
            default:
                return 'Unknown upload error';
        }
    }

    /**
     * Prepare ticket data
     */
    private function prepareTicketData(array $input): array
    {
        $formData = [
            'subcategory' => $input['subcategory'],
            'description' => $input['description'],
            'countryGlpi' => $input['countryGlpi']
        ];

        return $this->glpi_api_model->prepareTicketData($input['category'], $formData);
    }

    /**
     * Send json response
     */
    private function sendJsonResponse(array $data): void
    {
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Validate categories
     */
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
