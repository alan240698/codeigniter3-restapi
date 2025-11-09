<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Glpi_category_model extends CI_Model
{
    private $transformer;
    private $category_mapping = [];

    public function __construct()
    {
        parent::__construct();
        $this->load->library('glpi/glpi_api');
        $this->load->library('glpi_api_validation');
        $this->load->library('glpi_api_helper');
        $this->load->model('arche_ticket/glpi/glpi_data_transformer');
        
        $this->transformer = $this->glpi_data_transformer;
    }

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

    public function addCategoryOption(&$entity_structure, $parts, $item)
    {
        $this->insertCategoryRecursive($entity_structure['subcategory']['options'], $parts, $item);
    }

    private function insertCategoryRecursive(&$options, $parts, $item)
    {
        if (empty($parts)) return;

        $currentLabel = $this->glpi_api_helper->sanitizeString(array_shift($parts));
        $currentKey   = $this->glpi_api_helper->slugify($currentLabel);

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

    public function getCategoryMapping()
    {
        return $this->category_mapping;
    }
}