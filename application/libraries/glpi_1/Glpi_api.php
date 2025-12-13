<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Glpi_api
{
    private $CI;
    private $session_manager;
    private $http_client;
    private $ticket_service;
    private $document_service;
    private $entity_service;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->config('glpi');
        $this->CI->load->library('glpi_api_validation');
        $this->CI->load->library('session');

        // Load components
        $this->CI->load->library('glpi/glpi_session_manager');
        $this->CI->load->library('glpi/glpi_http_client');
        $this->CI->load->library('glpi/glpi_ticket_service');
        $this->CI->load->library('glpi/glpi_document_service');
        $this->CI->load->library('glpi/glpi_entity_service');

        // Initialize components
        $this->session_manager  = $this->CI->glpi_session_manager;
        $this->http_client      = $this->CI->glpi_http_client;
        $this->ticket_service   = $this->CI->glpi_ticket_service;
        $this->document_service = $this->CI->glpi_document_service;
        $this->entity_service   = $this->CI->glpi_entity_service;
    }

    // ========================================
    // Session Methods
    // ========================================

    /**
     * Init session
     */
    public function initSession()
    {
        return $this->session_manager->init();
    }

    /**
     * Kill session
     */
    public function killSession()
    {
        return $this->session_manager->kill();
    }

    // ========================================
    // Entity Methods
    // ========================================
    /**
     * Get glpi entities
     */

    public function getGlpiEntities()
    {
        return $this->entity_service->getEntities();
    }

    /**
     * Get categoryByEntity
     */
    public function getCategoyByEntity($id)
    {
        return $this->entity_service->getCategoriesByEntity($id);
    }

    /**
     * Get category id
     */
    public function get_category_id($category, $subcategory)
    {
        return $this->entity_service->getCategoryId($category, $subcategory);
    }

    // ========================================
    // Ticket Methods
    // ========================================

    /**
     * Create ticket
     */
    public function create_ticket($ticket_data, $auto_validate = true)
    {
        return $this->ticket_service->create($ticket_data, $auto_validate);
    }

    /**
     * Reopen ticket by id
     */
    public function reopen_ticket_by_id($id)
    {
        return $this->ticket_service->reopen($id);
    }

    /**
     * Get ticket by post
     */
    public function get_ticket_by_post()
    {
        return $this->ticket_service->getByRequester();
    }

    /**
     * Get ticket with attachements
     */
    public function get_ticket_with_attachments($ticket_id)
    {
        return $this->ticket_service->getWithAttachments($ticket_id);
    }

    /**
     * Get ticket with supporter
     */
    public function get_ticket_with_supporter($ticket_id)
    {
        return $this->ticket_service->getSupporterTicket($ticket_id);
    }

    /**
     * Add requester ticket
     */
    public function add_requester_ticket($response)
    {
        return $this->ticket_service->addRequester($response);
    }

    // ========================================
    // Document Methods
    // ========================================

    /**
     * Upload document
     */
    public function uploadDocument($ticket_id, $file_path, $file_name, $auto_validate = true)
    {
        return $this->document_service->upload($ticket_id, $file_path, $file_name, $auto_validate);
    }

    /**
     * Download document
     */
    public function download_document($document_id, $ticket_id)
    {
        return $this->document_service->download($document_id, $ticket_id);
    }
}