<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Glpi_entity_model extends CI_Model
{
    private $transformer;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->load->library('glpi/glpi_api');
        $this->load->library('glpi_api_validation');
        $this->load->model('arche_ticket/glpi/glpi_data_transformer');

        $this->transformer = $this->glpi_data_transformer;
    }

    /**
     * Get entities
     */
    public function getEntities()
    {
        if (!$this->glpi_api->initSession()) {
            log_message('error', 'GLPI: Cannot initialize session for getEntities');
            return [];
        }

        $data = $this->glpi_api->getGlpiEntities();

        $this->glpi_api->killSession();

        return $this->transformer->transformEntities($data);
    }
}
