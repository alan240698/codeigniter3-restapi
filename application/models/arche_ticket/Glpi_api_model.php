<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Glpi_api_model extends CI_Model
{
    private $category_mapping = [];

    public function __construct()
    {
        parent::__construct();

        $this->load->library('glpi_api');
        $this->load->library('glpi_api_validation');
        $this->load->library('glpi_api_helper');
    }

    /**
     * Get enitities
     */
    public function getEntities()
    {
        if (!$this->glpi_api->initSession()) {
            log_message('error', 'GLPI: Cannot initialize session for getEntities');
            return [];
        }

        $data = $this->glpi_api->getGlpiEntities();

        $this->glpi_api->killSession();

        return $this->transformEntities($data);
    }

    /**
     * Transform entities data
     * 
     * @param mixed $data
     * @return array
     */
    private function transformEntities($data)
    {
        if (!$this->glpi_api_validation->isValidArray($data)) {
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
                'id'    => (int)$item['id'],
                'name'  => $this->glpi_api_helper->sanitizeString($item['name']),
                'key'   => $this->glpi_api_helper->slugify($this->glpi_api_helper->sanitizeString($item['name']))
            ];
        }

        return $entities;
    }

    /**
     * Get categories
     * 
     * @param int $entity_id
     * @return array
     */
    public function getCategories($entity_id)
    {
        if (!$this->glpi_api_validation->isValidId($entity_id)) {
            log_message('error', "GLPI: Invalid entity_id: {$entity_id}");
            return [];
        }

        if (!$this->glpi_api->initSession()) {
            log_message('error', 'GLPI: Cannot initialize session for getCategories');
            return [];
        }

        $data = $this->glpi_api->getCategoyByEntity($entity_id);

        $this->glpi_api->killSession();

        if (!$this->glpi_api_validation->isValidArray($data)) {
            return [];
        }

        return $this->transformCategories($data);
    }

    /**
     * Transform categories data
     * 
     * @param array $data
     * @return array
     */
    private function transformCategories($data)
    {
        if (!$this->glpi_api_validation->isValidArray($data)) {
            return [];
        }

        $result = [];
        foreach ($data as $item) {
            if (!isset($item[1]) || !isset($item[80])) {
                log_message('error', 'GLPI: Invalid category item structure');
                continue;
            }

            $entityName = $this->glpi_api_helper->extractEntityName($item[80]);
            $entityKey = $this->glpi_api_helper->slugify($entityName);

            if (!isset($result[$entityKey])) {
                $result[$entityKey] = $this->glpi_api_validation->getDefaultFormStructure();
            }

            $parts = explode(' > ', $item[1]);

            if (empty($parts)) {
                continue;
            }

            if (isset($item[2])) {
                $this->category_mapping[$item[2]] = $parts;
            }

            $this->addCategoryOption($result[$entityKey], $parts, $item);
        }

        return $result;
    }

    /**
     * 
     * 
     * @param array $ticket_data
     * @param array $files (optional)
     * @return array
     */
    public function createTicketWithAttachments($ticket_data, $files = [])
    {
        // Validate ticket data
        if (!$this->glpi_api_validation->validateTicketData($ticket_data)) {
            return $this->glpi_api_validation->errorResponse('Invalid ticket data');
        }

        // Init session
        if (!$this->glpi_api->initSession()) {
            return $this->glpi_api_validation->errorResponse('Unable to connect to GLPI API');
        }

        // Create ticket
        $ticket_result = $this->glpi_api->create_ticket($ticket_data);

        // Check ticket creation result
        if (!$this->glpi_api_validation->isSuccessResponse($ticket_result)) {
            $this->glpi_api->killSession();
            return $this->glpi_api_validation->errorResponse(
                $ticket_result['message'] ?? 'Cannot create ticket',
                $ticket_result
            );
        }

        // Get ticket ID
        $ticket_id = $ticket_result['data']['id'] ?? null;
        if (!$ticket_id) {
            $this->glpi_api->killSession();
            return $this->glpi_api_validation->errorResponse('Did not receive ticket ID');
        }

        $uploaded_files = [];
        if ($this->glpi_api_validation->hasFiles($files)) {
            $uploaded_files = $this->uploadAttachments($ticket_id, $files);
        }

        // Kill session
        $this->glpi_api->killSession();

        // Return success response
        return $this->glpi_api_validation->successResponse([
            'ticket_id' => $ticket_id,
            'uploaded_files' => $uploaded_files,
            'message' => 'Ticket has been successfully created'
        ]);
    }

    /**
     * Prepare ticket data from input form
     * 
     * @param string $category
     * @param array $form_data
     * @return array|false
     */
    public function prepare_ticket_data($category, $form_data)
    {
        if (!$this->glpi_api_validation->isValidString($category)) {
            log_message('error', 'GLPI: Invalid category provided');
            return false;
        }

        if (!$this->glpi_api_validation->isValidArray($form_data)) {
            log_message('error', 'GLPI: Invalid form_data provided');
            return false;
        }

        $category_id = $category . ' > '. $form_data['subcategory'] ?? '';

        $ticket_data = [
            'name' => $category_id,
            'content' => $this->glpi_api_validation->sanitizeContent($form_data['description'] ?? ''),
        ];

        if (isset($form_data['entity_id']) && $this->glpi_api_validation->isValidId($form_data['entity_id'])) {
            $ticket_data['entities_id'] = (int)$form_data['entity_id'];
        }

        return $ticket_data;
    }

    /**
     * Add category option vào form structure (recursive version - hỗ trợ nhiều cấp)
     * 
     * @param array &$entity_structure
     * @param array $parts
     * @param array $item
     * @return void
     */
    public function addCategoryOption(&$entity_structure, $parts, $item)
    {
        $this->insertCategoryRecursive($entity_structure['subcategory']['options'], $parts, $item);
    }

    /**
     * Recursive insert category (multi-level support)
     * 
     * @param array &$options
     * @param array $parts
     * @param array $item
     * @return void
     */
    public function insertCategoryRecursive(&$options, $parts, $item)
    {
        if (empty($parts)) return;

        $currentLabel = $this->glpi_api_helper->sanitizeString(array_shift($parts));
        $currentKey   = $this->glpi_api_helper->slugify($currentLabel);

        // If it does not exist, create a new node
        if (!isset($options[$currentKey])) {
            $options[$currentKey] = [
                'label' => $currentLabel,
                'value' => $currentKey,
                'children' => []
            ];
        }

        if (!empty($parts)) {
            $this->insertCategoryRecursive($options[$currentKey]['children'], $parts, $item);
        } else {
            $options[$currentKey]['value'] = $item[2] ?? $currentKey;
        }
    }

    /**
     * Upload multiple attachments cho ticket
     * 
     * @param int $ticket_id
     * @param array $files
     * @return array
     */
    public function uploadAttachments($ticket_id, $files)
    {
        $uploaded = [];

        foreach ($files as $file) {
            // Validate file structure
            if (!$this->glpi_api_validation->isValidFile($file)) {
                log_message('error', 'GLPI: Invalid file structure');
                continue;
            }

            // Upload file
            $result = $this->glpi_api->uploadDocument(
                $ticket_id,
                $file['full_path'],
                $file['file_name']
            );

            // Check upload result
            if ($this->glpi_api_validation->isSuccessResponse($result)) {
                $uploaded[] = $file['file_name'];
            } else {
                log_message('error', "GLPI: Failed to upload file: {$file['file_name']}");
            }
        }

        return $uploaded;
    }
}