<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class TicketController extends CI_Controller
{
    public function __construct() {
        parent::__construct();
        $this->load->library(['form_validation']);
        $this->load->model('it_ticket/TicketModel');
        $this->load->model('it_ticket/ServiceModel');
        $this->load->model('it_ticket/RulesModel');
        header('Content-Type: application/json');
    }

    /**
     * GET /api/tickets
     * Get paginated tickets with filters
     */
    public function index()
    {
        try {
            $page = (int)$this->input->get('page') ?: 1;
            $perPage = (int)$this->input->get('per_page') ?: 10;
            
            $filters = [
                'status' => $this->input->get('status'),
                'priority' => $this->input->get('priority'),
                'service_group_id' => $this->input->get('service_group_id'),
                'assigned_to' => $this->input->get('assigned_to'),
                'requester_id' => $this->input->get('requester_id'),
                'search' => $this->input->get('search')
            ];

            $result = $this->TicketModel->get_tickets_paginated($page, $perPage, $filters);

            $this->_response([
                'success' => true,
                'data' => $result['data'],
                'total' => $result['total'],
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($result['total'] / $perPage)
            ]);

        } catch (Exception $e) {
            $this->_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/tickets/{id}
     * Get single ticket with full details
     */
    public function show($id)
    {
        try {
            $ticket = $this->TicketModel->get_ticket($id);

            if (!$ticket) {
                $this->_response(['success' => false, 'message' => 'Ticket not found'], 404);
                return;
            }

            // Get ticket history
            $history = $this->TicketModel->get_ticket_history($id);

            $this->_response([
                'success' => true,
                'data' => [
                    'ticket' => $ticket,
                    'history' => $history
                ]
            ]);

        } catch (Exception $e) {
            $this->_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/tickets
     * Create new ticket
     */
    public function store()
    {
        try {
            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);

            // Validate input
            $_POST = $input;
            $this->form_validation->set_rules('service_group_id', 'Service Group', 'required|integer');
            $this->form_validation->set_rules('ticket_type_id', 'Ticket Type', 'required|integer');
            $this->form_validation->set_rules('issue_type_id', 'Issue Type', 'required|integer');
            $this->form_validation->set_rules('subject', 'Subject', 'required|trim|max_length[255]');
            $this->form_validation->set_rules('description', 'Description', 'required|trim');
            $this->form_validation->set_rules('priority', 'Priority', 'required|in_list[low,medium,high,critical]');
            $this->form_validation->set_rules('requester_id', 'Requester', 'required|integer');

            if (!$this->form_validation->run()) {
                $this->_response(['success' => false, 'message' => validation_errors()], 400);
                return;
            }

            // Verify foreign keys exist
            if (!$this->ServiceModel->get_group($input['service_group_id'])) {
                $this->_response(['success' => false, 'message' => 'Service Group not found'], 400);
                return;
            }

            if (!$this->ServiceModel->get_ticket_type($input['ticket_type_id'])) {
                $this->_response(['success' => false, 'message' => 'Ticket Type not found'], 400);
                return;
            }

            $issueType = $this->ServiceModel->get_issue_type($input['issue_type_id']);
            if (!$issueType) {
                $this->_response(['success' => false, 'message' => 'Issue Type not found'], 400);
                return;
            }

            // Generate ticket number
            $ticketNumber = $this->TicketModel->generate_ticket_number();

            // Calculate SLA due date
            $slaDueDate = $this->TicketModel->calculate_sla_due_date(
                $input['issue_type_id'],
                $input['priority']
            );

            // Check if approval is required
            $approvalRule = $this->RulesModel->get_approval_rule_by_issue_type($input['issue_type_id']);
            $requiresApproval = $approvalRule ? 1 : 0;

            // Prepare ticket data
            $ticketData = [
                'ticket_number' => $ticketNumber,
                'service_group_id' => $input['service_group_id'],
                'ticket_type_id' => $input['ticket_type_id'],
                'issue_type_id' => $input['issue_type_id'],
                'subject' => $input['subject'],
                'description' => $input['description'],
                'custom_fields_data' => isset($input['custom_fields']) ? json_encode($input['custom_fields']) : null,
                'priority' => $input['priority'],
                'status' => 'new',
                'requester_id' => $input['requester_id'],
                'current_level' => 1,
                'max_level' => 1,
                'sla_due_date' => $slaDueDate,
                'sla_status' => 'on_time',
                'requires_approval' => $requiresApproval,
                'approval_status' => $requiresApproval ? 'pending' : 'not_required',
                'requires_solution' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Create ticket
            $ticketId = $this->TicketModel->create_ticket($ticketData);

            if ($ticketId) {
                // Add history
                $this->TicketModel->add_history($ticketId, $input['requester_id'], 'created', [
                    'comment' => 'Ticket created'
                ]);

                $this->_response([
                    'success' => true,
                    'message' => 'Ticket created successfully',
                    'data' => [
                        'id' => $ticketId,
                        'ticket_number' => $ticketNumber
                    ]
                ], 201);
            } else {
                throw new Exception('Failed to create ticket');
            }

        } catch (Exception $e) {
            $this->_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * PUT /api/tickets/{id}
     * Update ticket
     */
    public function update($id)
    {
        try {
            $ticket = $this->TicketModel->get_ticket($id);
            if (!$ticket) {
                $this->_response(['success' => false, 'message' => 'Ticket not found'], 404);
                return;
            }

            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);

            $updateData = [];
            $changes = [];

            // Track changes for history
            if (isset($input['subject']) && $input['subject'] != $ticket->subject) {
                $updateData['subject'] = $input['subject'];
                $changes[] = ['field' => 'subject', 'old' => $ticket->subject, 'new' => $input['subject']];
            }

            if (isset($input['description']) && $input['description'] != $ticket->description) {
                $updateData['description'] = $input['description'];
                $changes[] = ['field' => 'description', 'old' => $ticket->description, 'new' => $input['description']];
            }

            if (isset($input['priority']) && $input['priority'] != $ticket->priority) {
                if (!in_array($input['priority'], ['low', 'medium', 'high', 'critical'])) {
                    $this->_response(['success' => false, 'message' => 'Invalid priority'], 400);
                    return;
                }
                $updateData['priority'] = $input['priority'];
                $changes[] = ['field' => 'priority', 'old' => $ticket->priority, 'new' => $input['priority']];
            }

            if (empty($updateData)) {
                $this->_response(['success' => false, 'message' => 'No changes detected'], 400);
                return;
            }

            $updateData['updated_at'] = date('Y-m-d H:i:s');

            $updated = $this->TicketModel->update_ticket($id, $updateData);

            if ($updated) {
                // Add history for each change
                foreach ($changes as $change) {
                    $this->TicketModel->add_history($id, $input['employee_id'] ?? 1, 'status_changed', [
                        'field_name' => $change['field'],
                        'old_value' => $change['old'],
                        'new_value' => $change['new']
                    ]);
                }

                $this->_response(['success' => true, 'message' => 'Ticket updated successfully']);
            } else {
                throw new Exception('Failed to update ticket');
            }

        } catch (Exception $e) {
            $this->_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/tickets/{id}/assign
     * Assign ticket to user or team
     */
    public function assign($id)
    {
        try {
            $ticket = $this->TicketModel->get_ticket($id);
            if (!$ticket) {
                $this->_response(['success' => false, 'message' => 'Ticket not found'], 404);
                return;
            }

            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);

            $updateData = [
                'status' => 'assigned',
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if (!empty($input['assigned_to'])) {
                $updateData['assigned_to'] = $input['assigned_to'];
            }

            if (!empty($input['assigned_team_id'])) {
                $updateData['assigned_team_id'] = $input['assigned_team_id'];
            }

            $updated = $this->TicketModel->update_ticket($id, $updateData);

            if ($updated) {
                $this->TicketModel->add_history($id, $input['employee_id'] ?? 1, 'assigned', [
                    'comment' => 'Ticket assigned'
                ]);

                $this->_response(['success' => true, 'message' => 'Ticket assigned successfully']);
            } else {
                throw new Exception('Failed to assign ticket');
            }

        } catch (Exception $e) {
            $this->_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/tickets/{id}/resolve
     * Mark ticket as resolved
     */
    public function resolve($id)
    {
        try {
            $ticket = $this->TicketModel->get_ticket($id);
            if (!$ticket) {
                $this->_response(['success' => false, 'message' => 'Ticket not found'], 404);
                return;
            }

            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);

            if (empty($input['solution'])) {
                $this->_response(['success' => false, 'message' => 'Solution is required'], 400);
                return;
            }

            $updateData = [
                'status' => 'resolved',
                'solution' => $input['solution'],
                'solution_provided_at' => date('Y-m-d H:i:s'),
                'solution_provided_by' => $input['employee_id'] ?? 1,
                'resolved_at' => date('Y-m-d H:i:s'),
                'resolved_by' => $input['employee_id'] ?? 1,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $updated = $this->TicketModel->update_ticket($id, $updateData);

            if ($updated) {
                $this->TicketModel->add_history($id, $input['employee_id'] ?? 1, 'resolved', [
                    'comment' => 'Ticket resolved with solution'
                ]);

                $this->_response(['success' => true, 'message' => 'Ticket resolved successfully']);
            } else {
                throw new Exception('Failed to resolve ticket');
            }

        } catch (Exception $e) {
            $this->_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/tickets/{id}/close
     * Close ticket
     */
    public function close($id)
    {
        try {
            $ticket = $this->TicketModel->get_ticket($id);
            if (!$ticket) {
                $this->_response(['success' => false, 'message' => 'Ticket not found'], 404);
                return;
            }

            if ($ticket->status != 'resolved') {
                $this->_response(['success' => false, 'message' => 'Only resolved tickets can be closed'], 400);
                return;
            }

            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);

            $updateData = [
                'status' => 'closed',
                'closed_at' => date('Y-m-d H:i:s'),
                'closed_by' => $input['employee_id'] ?? 1,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $updated = $this->TicketModel->update_ticket($id, $updateData);

            if ($updated) {
                $this->TicketModel->add_history($id, $input['employee_id'] ?? 1, 'closed', [
                    'comment' => 'Ticket closed'
                ]);

                $this->_response(['success' => true, 'message' => 'Ticket closed successfully']);
            } else {
                throw new Exception('Failed to close ticket');
            }

        } catch (Exception $e) {
            $this->_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function _response($data, $statusCode = 200)
    {
        $this->output
            ->set_status_header($statusCode)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))
            ->_display();
        exit;
    }
}
