<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Glpi_ticket_builder extends CI_Model
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->load->library('glpi_api_validation');
    }

    /**
     * Build
     */
    public function build($category, $form_data)
    {
        if (!$this->glpi_api_validation->isValidString($category)) {
            log_message('error', 'GLPI: Invalid category provided');
            return false;
        }

        if (!$this->glpi_api_validation->isValidArray($form_data)) {
            log_message('error', 'GLPI: Invalid form_data provided');
            return false;
        }

        $category_id = '[VN]'. ' - '. $category . ' > ' . ($form_data['subcategory'] ?? '');

        $ticket_data = [
            'name'    => $category_id,
            'content' => $this->glpi_api_validation->sanitizeContent($form_data['description'] ?? ''),
        ];

        if (isset($form_data['entity_id']) && 
            $this->glpi_api_validation->isValidId($form_data['entity_id'])) {
            $ticket_data['entities_id'] = (int)$form_data['entity_id'];
        }

        return $ticket_data;
    }

    /**
     * Build from array
     */
    public function buildFromArray($data)
    {
        $required = ['name', 'content'];
        
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                log_message('error', "GLPI: Missing required field: {$field}");
                return false;
            }
        }

        return [
            'name'              => $data['name'],
            'content'           => $this->glpi_api_validation->sanitizeContent($data['content']),
            'entities_id'       => $data['entities_id']         ?? null,
            'itilcategories_id' => $data['itilcategories_id']   ?? null,
            'priority'          => $data['priority']            ?? 3,
            'urgency'           => $data['urgency']             ?? 3,
            'impact'            => $data['impact']              ?? 3,
            'type'              => $data['type']                ?? 1
        ];
    }
}