<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Glpi_entity_service
{
    private $CI;
    private $http_client;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->library('glpi/glpi_http_client');
        $this->CI->load->library('glpi_api_validation');
        
        $this->http_client = $this->CI->glpi_http_client;
        $this->user_token = $this->CI->config->item('glpi_user_token');
    }

    public function getEntities()
    {
        $response = $this->http_client->request(
            'Entity',
            'GET',
            null,
            ['Authorization: user_token ' . $this->user_token]
        );

        return $response['data'];
    }

    public function getCategoriesByEntity($id)
    {
        if (!$this->CI->glpi_api_validation->_validate_field('id', $id, ['required', 'integer', 'min:0'])) {
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

        $response = $this->http_client->request('search/ITILCategory', 'POST', $payload);

        if (isset($response['success']) && $response['success'] && isset($response['data']['data'])) {
            return $response['data']['data'];
        }

        log_message('error', 'GLPI API error: ' . print_r($response, true));
        return false;
    }

    public function getCategoryId($category, $subcategory)
    {
        $categories = $this->CI->config->item('glpi_categories');

        if (isset($categories[$category][$subcategory])) {
            return $categories[$category][$subcategory];
        }

        return 0;
    }
}

