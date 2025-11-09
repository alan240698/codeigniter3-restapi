<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Glpi_api_model extends CI_Model
{
    private $entity_model;
    private $category_model;
    private $ticket_model;
    private $document_model;

    public function __construct()
    {
        parent::__construct();

        $this->load->library('glpi/glpi_api');
        $this->load->library('glpi_api_validation');
        $this->load->library('glpi_api_helper');

        // Load sub-models
        $this->load->model('arche_ticket/glpi/glpi_entity_model');
        $this->load->model('arche_ticket/glpi/glpi_category_model');
        $this->load->model('arche_ticket/glpi/glpi_ticket_model');
        $this->load->model('arche_ticket/glpi/glpi_document_model');

        $this->entity_model = $this->glpi_entity_model;
        $this->category_model = $this->glpi_category_model;
        $this->ticket_model = $this->glpi_ticket_model;
        $this->document_model = $this->glpi_document_model;
    }

    // ========================================
    // Entity Methods (Proxy)
    // ========================================

    public function getEntities()
    {
        return $this->entity_model->getEntities();
    }

    // ========================================
    // Category Methods (Proxy)
    // ========================================

    public function getCategories($entity_id)
    {
        return $this->category_model->getCategories($entity_id);
    }

    // ========================================
    // Ticket Methods (Proxy)
    // ========================================

    public function getList()
    {
        return $this->ticket_model->getList();
    }

    public function getTicketById($id)
    {
        return $this->ticket_model->getById($id);
    }

    public function reopenTicketById($id)
    {
        return $this->ticket_model->reopenById($id);
    }

    public function createTicketWithAttachments($ticket_data, $files = [])
    {
        return $this->ticket_model->createWithAttachments($ticket_data, $files);
    }

    public function prepare_ticket_data($category, $form_data)
    {
        return $this->ticket_model->prepareData($category, $form_data);
    }

    // ========================================
    // Document Methods (Proxy)
    // ========================================

    public function downloadDocument($id)
    {
        return $this->document_model->download($id);
    }

    public function uploadAttachments($ticket_id, $files)
    {
        return $this->document_model->uploadMultiple($ticket_id, $files);
    }
}
