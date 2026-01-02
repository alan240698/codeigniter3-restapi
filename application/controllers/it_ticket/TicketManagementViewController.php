<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Ticket Management View Controller
 * Handles loading the ticket management page
 */
class TicketManagementViewController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        // Load session library if not already loaded
        if (!isset($this->session)) {
            $this->load->library('session');
        }
    }

    /**
     * Load ticket management page
     */
    public function index()
    {
        // Get employee info from session
        $employee_id = $this->session->userdata('employee_id') ?? 1;
        $user_role = $this->session->userdata('role') ?? 'user';
        
        // Determine user role (user, tech, member)
        // For now, using simple logic - can be enhanced based on your role system
        $data = [
            'page_title' => 'Ticket Management',
            'employee_id' => $employee_id,
            'user_role' => $user_role
        ];
        
        $this->load->view('it_ticket/user/ticket_management', $data);
    }
}
