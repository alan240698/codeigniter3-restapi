<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Glpi_api_model extends CI_Model
{
    private $entity_model;
    private $category_model;
    private $ticket_model;
    private $document_model;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        // Load all libraries
        $this->load->library('glpi/glpi_api');
        $this->load->library('glpi_api_validation');
        $this->load->library('glpi_api_helper');

        // Load all models
        $this->load->model('arche_ticket/glpi/glpi_entity_model');
        $this->load->model('arche_ticket/glpi/glpi_category_model');
        $this->load->model('arche_ticket/glpi/glpi_ticket_model');
        $this->load->model('arche_ticket/glpi/glpi_document_model');

        $this->entity_model   = $this->glpi_entity_model;
        $this->category_model = $this->glpi_category_model;
        $this->ticket_model   = $this->glpi_ticket_model;
        $this->document_model = $this->glpi_document_model;
    }

    /**
     * Get entities
     */
    public function getEntities()
    {
        return $this->entity_model->getEntities();
    }

    /**
     * Get categories
     */
    public function getCategories($entityId)
    {
        return $this->category_model->getCategories($entityId);
    }

    /**
     * Get list
     */
    public function getList()
    {
        return $this->ticket_model->getList();
    }

    /**
     * Get ticket by id
     */
    public function getTicketById($id)
    {
        return $this->ticket_model->getById($id);
    }

    /**
     * Reopen ticket by id
     */
    public function reopenTicketById($id)
    {
        return $this->ticket_model->reopenById($id);
    }

    /**
     * Create ticket with attachements
     */
    public function createTicketWithAttachments($ticketData, $files = [])
    {
        return $this->ticket_model->createWithAttachments($ticketData, $files);
    }

    /**
     * Prepare ticket data
     */
    public function prepareTicketData($category, $formData)
    {
        return $this->ticket_model->prepareData($category, $formData);
    }

    /**
     * Download document
     */
    public function downloadDocument($id, $ticketId)
    {
        return $this->document_model->download($id, $ticketId);
    }

    /**
     * Upload attachments
     */
    public function uploadAttachments($ticketId, $files)
    {
        return $this->document_model->uploadMultiple($ticketId, $files);
    }
}
