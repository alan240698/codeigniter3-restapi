<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class TemplateViewController extends CI_Controller
{
    public function __construct() {
        parent::__construct();
        $this->load->model('it_ticket/EmailTemplateModel');
    }

    /**
     * GET /template/view/{token}
     * View rendered template as standalone page
     */
    public function view($token)
    {
        try {
            // Get instance by token
            $instance = $this->EmailTemplateModel->get_instance_by_token($token);

            if (!$instance) {
                $this->_show_error('Template Not Found', 'The requested template does not exist.');
                return;
            }

            // Check if token is expired
            if (!$this->EmailTemplateModel->is_token_valid($token)) {
                $this->_show_error('Link Expired', 'This link has expired and is no longer valid.');
                return;
            }

            // Check if action already performed
            $latestAction = $this->EmailTemplateModel->get_latest_action($instance->id);

            // Render the template view
            $data = [
                'instance' => $instance,
                'latest_action' => $latestAction,
                'token' => $token
            ];

            $this->load->view('it_ticket/template_viewer', $data);

        } catch (Exception $e) {
            $this->_show_error('Error', 'An error occurred while loading the template.');
        }
    }

    /**
     * POST /template/action/{token}
     * Handle action on template (approve, reject, etc.)
     */
    public function action($token)
    {
        try {
            // Get action type from query or POST
            $action_type = $this->input->get('action') ?? $this->input->post('action');
            
            if (empty($action_type)) {
                $this->_show_error('Invalid Action', 'No action specified.');
                return;
            }

            // Get instance by token
            $instance = $this->EmailTemplateModel->get_instance_by_token($token);

            if (!$instance) {
                $this->_show_error('Template Not Found', 'The requested template does not exist.');
                return;
            }

            // Check if token is expired
            if (!$this->EmailTemplateModel->is_token_valid($token)) {
                $this->_show_error('Link Expired', 'This link has expired and is no longer valid.');
                return;
            }

            // Check if action already performed
            if ($this->EmailTemplateModel->has_action($instance->id, $action_type)) {
                $this->_show_message(
                    'Action Already Performed',
                    "This action has already been performed on this template.",
                    'warning'
                );
                return;
            }

            // Get additional data from POST
            $comment = $this->input->post('comment');
            $action_data = [
                'comment' => $comment,
                'context_data' => json_decode($instance->context_data, true)
            ];

            // Log the action
            $action_id = $this->EmailTemplateModel->log_action(
                $instance->id,
                $action_type,
                $action_data
            );

            if ($action_id) {
                // Here you can add additional logic based on action type
                // For example, update ticket status, send notifications, etc.
                
                $this->_process_action($instance, $action_type, $action_data);

                $this->_show_message(
                    'Action Successful',
                    "Your action has been recorded successfully. Thank you!",
                    'success'
                );
            } else {
                throw new Exception('Failed to log action');
            }

        } catch (Exception $e) {
            $this->_show_error('Error', 'An error occurred while processing your action.');
        }
    }

    /**
     * Process specific actions (can be extended)
     */
    private function _process_action($instance, $action_type, $action_data)
    {
        // Decode context data to get ticket information
        $context = json_decode($instance->context_data, true);

        // Example: Update ticket status based on action
        switch ($action_type) {
            case 'approve':
                // Add logic to approve ticket
                // Example: $this->load->model('it_ticket/TicketModel');
                // $this->TicketModel->approve_ticket($context['ticket_id']);
                log_message('info', "Template action: Approved - Instance ID: {$instance->id}");
                break;

            case 'reject':
                // Add logic to reject ticket
                log_message('info', "Template action: Rejected - Instance ID: {$instance->id}");
                break;

            default:
                log_message('info', "Template action: {$action_type} - Instance ID: {$instance->id}");
                break;
        }
    }

    /**
     * Show error page
     */
    private function _show_error($title, $message)
    {
        $data = [
            'title' => $title,
            'message' => $message,
            'type' => 'error'
        ];
        $this->load->view('it_ticket/template_message', $data);
    }

    /**
     * Show message page
     */
    private function _show_message($title, $message, $type = 'info')
    {
        $data = [
            'title' => $title,
            'message' => $message,
            'type' => $type
        ];
        $this->load->view('it_ticket/template_message', $data);
    }
}
