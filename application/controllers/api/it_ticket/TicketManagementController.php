<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Ticket Management Controller
 * Handles API endpoints for ticket management system
 */
class TicketManagementController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('it_ticket/TicketModel');
        $this->load->model('it_ticket/TicketManagementModel');
    }

    /**
     * GET /api/it_ticket/ticket-management/my-created
     * Get tickets created by current user
     */
    public function my_created()
    {
        try {
            $employee_id = $this->input->get('employee_id');
            
            if (empty($employee_id)) {
                $this->_response([
                    'success' => false,
                    'message' => 'Employee ID is required'
                ], 400);
                return;
            }

            $tickets = $this->TicketManagementModel->get_my_created_tickets($employee_id);

            $this->_response([
                'success' => true,
                'tickets' => $tickets,
                'total' => count($tickets)
            ]);
        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => 'Error fetching tickets: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/it_ticket/ticket-management/my-assigned
     * Get tickets assigned to current user
     */
    public function my_assigned()
    {
        try {
            $employee_id = $this->input->get('employee_id');
            
            if (empty($employee_id)) {
                $this->_response([
                    'success' => false,
                    'message' => 'Employee ID is required'
                ], 400);
                return;
            }

            $tickets = $this->TicketManagementModel->get_my_assigned_tickets($employee_id);

            $this->_response([
                'success' => true,
                'tickets' => $tickets,
                'total' => count($tickets)
            ]);
        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => 'Error fetching tickets: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/it_ticket/ticket-management/my-team
     * Get tickets assigned to current user's team
     */
    public function my_team()
    {
        try {
            $employee_id = $this->input->get('employee_id');
            
            if (empty($employee_id)) {
                $this->_response([
                    'success' => false,
                    'message' => 'Employee ID is required'
                ], 400);
                return;
            }

            $tickets = $this->TicketManagementModel->get_my_team_tickets($employee_id);

            $this->_response([
                'success' => true,
                'tickets' => $tickets,
                'total' => count($tickets)
            ]);
        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => 'Error fetching tickets: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/it_ticket/ticket-management/counts
     * Get ticket counts for all tabs
     */
    public function counts()
    {
        try {
            $employee_id = $this->input->get('employee_id');
            
            if (empty($employee_id)) {
                $this->_response([
                    'success' => false,
                    'message' => 'Employee ID is required'
                ], 400);
                return;
            }

            $counts = $this->TicketManagementModel->get_ticket_counts($employee_id);

            $this->_response([
                'success' => true,
                'counts' => $counts
            ]);
        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => 'Error fetching counts: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper: Send JSON response
     */
    private function _response($data, $statusCode = 200)
    {
        $this->output
            ->set_status_header($statusCode)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($data))
            ->_display();
        exit;
    }
}
