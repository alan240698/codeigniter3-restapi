<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Glpi_ticket_model extends CI_Model
{
    private $transformer;
    private $builder;
    private $document_model;

    public function __construct()
    {
        parent::__construct();
        $this->load->library('glpi/glpi_api');
        $this->load->library('glpi_api_validation');
        $this->load->model('arche_ticket/glpi/glpi_data_transformer');
        $this->load->model('arche_ticket/glpi/glpi_ticket_builder');
        $this->load->model('arche_ticket/glpi/glpi_document_model');
        
        $this->transformer = $this->glpi_data_transformer;
        $this->builder = $this->glpi_ticket_builder;
        $this->document_model = $this->glpi_document_model;
    }

    public function getList()
    {
        if (!$this->glpi_api->initSession()) {
            log_message('error', 'GLPI: Cannot initialize session for getList');
            return;
        }

        $data = $this->glpi_api->get_ticket_by_post();
        $tickets = $this->transformer->transformTickets($data);
        
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'data' => $tickets
        ]);

        $this->glpi_api->killSession();
    }

    public function getById($id)
    {
        if (!$this->glpi_api->initSession()) {
            return $this->glpi_api_validation->errorResponse('Unable to connect to GLPI API');
        }

        $data = $this->glpi_api->get_ticket_with_attachments($id);
        
        if (!$data['success']) {
            $this->glpi_api->killSession();
            return $this->glpi_api_validation->errorResponse($data['message'] ?? 'Failed to get ticket');
        }

        $transformed = $this->transformer->transformTicketDetail($data['data']);

        $this->glpi_api->killSession();

        echo json_encode([
            'success' => true,
            'data' => $transformed
        ]);
    }

    public function reopenById($id)
    {
        $this->glpi_api->initSession();
        $data = $this->glpi_api->reopen_ticket_by_id($id);
        
        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
        
        $this->glpi_api->killSession();
    }

    public function createWithAttachments($ticket_data, $files = [])
    {
        if (!$this->glpi_api_validation->validateTicketData($ticket_data)) {
            return $this->glpi_api_validation->errorResponse('Invalid ticket data');
        }

        if (!$this->glpi_api->initSession()) {
            return $this->glpi_api_validation->errorResponse('Unable to connect to GLPI API');
        }

        $ticket_result = $this->glpi_api->create_ticket($ticket_data);

        if (!$this->glpi_api_validation->isSuccessResponse($ticket_result)) {
            $this->glpi_api->killSession();
            return $this->glpi_api_validation->errorResponse(
                $ticket_result['message'] ?? 'Cannot create ticket',
                $ticket_result
            );
        }

        $ticket_id = $ticket_result['data']['id'] ?? null;
        if (!$ticket_id) {
            $this->glpi_api->killSession();
            return $this->glpi_api_validation->errorResponse('Did not receive ticket ID');
        }

        $uploaded_files = [];
        if ($this->glpi_api_validation->hasFiles($files)) {
            $uploaded_files = $this->document_model->uploadMultiple($ticket_id, $files);
        }

        $this->glpi_api->killSession();

        return $this->glpi_api_validation->successResponse([
            'ticket_id' => $ticket_id,
            'uploaded_files' => $uploaded_files,
            'message' => 'Ticket has been successfully created'
        ]);
    }

    public function prepareData($category, $form_data)
    {
        return $this->builder->build($category, $form_data);
    }
}
