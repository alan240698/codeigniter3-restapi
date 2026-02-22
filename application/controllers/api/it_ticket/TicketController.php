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
        $this->load->model('it_ticket/WorkflowModel');
        $this->load->model('it_ticket/NotificationModel');
        header('Content-Type: application/json');
    }

    /**
     * POST /api/tickets
     * Create new ticket with complete logic
     */
    public function store()
    {
        try {
            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);

            // ========================================
            // 1. VALIDATION
            // ========================================
            $_POST = $input;
            $this->form_validation->set_rules('service_group_id', 'Service Group', 'required|integer');
            $this->form_validation->set_rules('it_service_id', 'IT Service', 'required|integer');
            $this->form_validation->set_rules('subject', 'Subject', 'required|trim|max_length[255]');
            $this->form_validation->set_rules('description', 'Description', 'required|trim');
            $this->form_validation->set_rules('priority', 'Priority', 'required|in_list[low,medium,high,critical]');
            $this->form_validation->set_rules('requester_id', 'Requester', 'required|integer');

            if (!$this->form_validation->run()) {
                $this->_response(['success' => false, 'message' => validation_errors()], 400);
                return;
            }

            // Verify Service Group exists
            $serviceGroup = $this->ServiceModel->get_group($input['service_group_id']);
            if (!$serviceGroup || $serviceGroup->status !== 'active') {
                $this->_response(['success' => false, 'message' => 'Service Group not found or inactive'], 400);
                return;
            }

            // Verify IT Service exists
            $itService = $this->ServiceModel->get_it_service($input['it_service_id']);
            if (!$itService || $itService->status !== 'active') {
                $this->_response(['success' => false, 'message' => 'IT Service not found or inactive'], 400);
                return;
            }

            // ========================================
            // 2. VALIDATE CUSTOM FIELDS
            // ========================================
            $customFields = $this->RulesModel->get_custom_fields($input['it_service_id']);
            $customFieldsData = isset($input['custom_fields']) ? $input['custom_fields'] : [];
            
            foreach ($customFields as $field) {
                if ($field->is_required && empty($customFieldsData[$field->field_name])) {
                    $this->_response([
                        'success' => false, 
                        'message' => "{$field->field_label} is required"
                    ], 400);
                    return;
                }
            }

            // ========================================
            // 3. GET WORKFLOW & INITIAL STATE
            // ========================================
            $workflow = $this->WorkflowModel->get_workflow_by_service($input['it_service_id']);
            $workflowId = $workflow ? $workflow->id : null;
            $currentStateId = null;

            if ($workflowId) {
                $initialState = $this->WorkflowModel->get_initial_state_v1($workflowId);
                $currentStateId = $initialState ? $initialState->id : null;
            }

            // ========================================
            // 4. CALCULATE SLA DUE DATE
            // ========================================
            $slaDueDate = $this->TicketModel->calculate_sla_due_date(
                $input['it_service_id'],
                $input['priority']
            );

            // ========================================
            // 5. GET LEVEL RULES & MAX LEVEL
            // ========================================
            $levelRules = $this->RulesModel->get_level_rules_v1($input['it_service_id']);
            $maxLevel = count($levelRules);
            
            // Get Level 1 team (default assignment)
            $level1Team = null;
            foreach ($levelRules as $rule) {
                if ($rule->level_number == 1 && $rule->status === 'active') {
                    $level1Team = $rule->support_team_id;
                    break;
                }
            }

            // ========================================
            // 6. CHECK APPROVAL REQUIREMENTS
            // ========================================
            $approvalRule = $this->RulesModel->get_approval_rule_by_it_service($input['it_service_id']);
            $requiresApproval = $approvalRule && $approvalRule->requires_approval ? 1 : 0;
            $approvalStatus = $requiresApproval ? 'pending' : 'not_required';

            // ========================================
            // 7. APPLY ROUTING RULES
            // ========================================
            $assignedTo = null;
            $assignedTeamId = $level1Team; // Default to Level 1 team
            
            $routingRule = $this->RulesModel->get_routing_rule_by_service($input['it_service_id']);
            if ($routingRule && $routingRule->status === 'active') {
                switch ($routingRule->assignment_type) {
                    case 'team':
                        $assignedTeamId = $routingRule->target_team_id ?: $level1Team;
                        break;
                        
                    case 'user':
                        $assignedTo = $routingRule->target_user_id;
                        $assignedTeamId = $routingRule->target_team_id ?: $level1Team;
                        break;
                }
            }

            // ========================================
            // 8. DETERMINE INITIAL STATUS
            // ========================================
            $initialStatus = 'new';
            if ($requiresApproval) {
                $initialStatus = 'pending_approval';
            } elseif ($assignedTo) {
                $initialStatus = 'assigned';
            }

            // ========================================
            // 9. GENERATE TICKET NUMBER
            // ========================================
            $ticketNumber = $this->TicketModel->generate_ticket_number();

            // ========================================
            // 10. PREPARE TICKET DATA
            // ========================================
            $ticketData = [
                'ticket_number' => $ticketNumber,
                'service_group_id' => $input['service_group_id'],
                'it_service_id' => $input['it_service_id'],
                'workflow_id' => $workflowId,
                'current_state_id' => $currentStateId,
                'subject' => $input['subject'],
                'description' => $input['description'],
                'custom_fields_data' => !empty($customFieldsData) ? json_encode($customFieldsData) : null,
                'priority' => $input['priority'],
                'status' => $initialStatus,
                'requester_id' => $input['requester_id'],
                'assigned_to' => $assignedTo,
                'assigned_team_id' => $assignedTeamId,
                'current_level' => 1,
                'max_level' => $maxLevel > 0 ? $maxLevel : 1,
                'sla_due_date' => $slaDueDate,
                'sla_status' => 'on_time',
                'requires_approval' => $requiresApproval,
                'approval_status' => $approvalStatus,
                'requires_solution' => $itService->requires_solution === 'yes' ? 1 : 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // ========================================
            // 11. CREATE TICKET (START TRANSACTION)
            // ========================================
            $this->db->trans_start();

            $ticketId = $this->TicketModel->create_ticket($ticketData);

            if (!$ticketId) {
                throw new Exception('Failed to create ticket');
            }

            // ========================================
            // 12. ADD TICKET HISTORY
            // ========================================
            $this->TicketModel->add_history($ticketId, $input['requester_id'], 'created', [
                'comment' => 'Ticket created'
            ]);

            // ========================================
            // 13. CREATE APPROVAL REQUEST IF NEEDED
            // ========================================
            if ($requiresApproval && $approvalRule) {
                $approverId = $this->_determine_approver($approvalRule, $input['requester_id']);
                
                if ($approverId) {
                    $approvalData = [
                        'ticket_id' => $ticketId,
                        'approval_rule_id' => $approvalRule->id,
                        'approver_id' => $approverId,
                        'status' => 'pending',
                        'requested_at' => date('Y-m-d H:i:s')
                    ];
                    
                    $this->db->insert('it_ticket_approvals', $approvalData);
                    
                    // Add history
                    $this->TicketModel->add_history($ticketId, $input['requester_id'], 'approval_requested', [
                        'comment' => 'Approval requested',
                        'approver_id' => $approverId
                    ]);
                    
                    // Send notification to approver
                    $this->NotificationModel->create_notification([
                        'employee_id' => $approverId,
                        'ticket_id' => $ticketId,
                        'type' => 'approval_request',
                        'title' => 'Ticket Approval Request',
                        'message' => "Ticket #{$ticketNumber} requires your approval",
                        'link' => "/tickets/{$ticketId}/approve"
                    ]);
                }
            }

            // ========================================
            // 14. CREATE NOTIFICATIONS
            // ========================================
            
            // Notify requester
            $this->NotificationModel->create_notification([
                'employee_id' => $input['requester_id'],
                'ticket_id' => $ticketId,
                'type' => 'ticket_created',
                'title' => 'Ticket Created',
                'message' => "Your ticket #{$ticketNumber} has been created successfully",
                'link' => "/tickets/{$ticketId}"
            ]);

            // Notify assignee if assigned
            if ($assignedTo) {
                $this->NotificationModel->create_notification([
                    'employee_id' => $assignedTo,
                    'ticket_id' => $ticketId,
                    'type' => 'ticket_assigned',
                    'title' => 'New Ticket Assigned',
                    'message' => "Ticket #{$ticketNumber} has been assigned to you",
                    'link' => "/tickets/{$ticketId}"
                ]);
                
                $this->TicketModel->add_history($ticketId, $input['requester_id'], 'assigned', [
                    'comment' => 'Auto-assigned by routing rule',
                    'assigned_to' => $assignedTo
                ]);
            }

            // Notify team if assigned to team
            if ($assignedTeamId && !$assignedTo) {
                $teamMembers = $this->db
                    ->select('employee_id')
                    ->from('it_ticket_team_members')
                    ->where('team_id', $assignedTeamId)
                    ->where('is_active', 1)
                    ->get()
                    ->result();

                foreach ($teamMembers as $member) {
                    $this->NotificationModel->create_notification([
                        'employee_id' => $member->employee_id,
                        'ticket_id' => $ticketId,
                        'type' => 'ticket_assigned_team',
                        'title' => 'New Team Ticket',
                        'message' => "New ticket #{$ticketNumber} assigned to your team",
                        'link' => "/tickets/{$ticketId}"
                    ]);
                }
            }

            // ========================================
            // 15. SEND EMAIL (Optional)
            // ========================================
            // You can implement email sending here
            // $this->_send_ticket_created_email($ticketId, $ticketData);

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Transaction failed');
            }

            // ========================================
            // 16. RETURN RESPONSE
            // ========================================
            $this->_response([
                'success' => true,
                'message' => 'Ticket created successfully',
                'data' => [
                    'id' => $ticketId,
                    'ticket_number' => $ticketNumber,
                    'status' => $initialStatus,
                    'requires_approval' => $requiresApproval,
                    'assigned_to' => $assignedTo,
                    'assigned_team_id' => $assignedTeamId,
                    'sla_due_date' => $slaDueDate
                ]
            ], 201);

        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'Ticket creation error: ' . $e->getMessage());
            $this->_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Determine approver based on approval rule
     */
    private function _determine_approver($approvalRule, $requesterId)
    {
        switch ($approvalRule->approval_type) {
            case 'specific_user':
                return $approvalRule->approver_user_id;
                
            case 'manager':
                // Get requester's manager from employee table
                $employee = $this->db->get_where('employees', ['id' => $requesterId])->row();
                return $employee ? $employee->manager_id : null;
                
            case 'department_head':
                // Get department head
                $employee = $this->db->get_where('employees', ['id' => $requesterId])->row();
                if ($employee) {
                    $deptHead = $this->db
                        ->get_where('departments', ['code' => $employee->department_code])
                        ->row();
                    return $deptHead ? $deptHead->head_id : null;
                }
                return null;
                
            case 'custom':
                // Implement custom logic based on conditions in approval_rule
                return null;
                
            default:
                return null;
        }
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
 * Get ticket details for modal display
 */
public function show($id)
{
    $this->load->model('it_ticket/TicketModel');
    $this->load->model('it_ticket/ServiceModel');
    $this->load->model('it_ticket/WorkflowModel');
    $this->load->model('it_ticket/RulesModel');
    
    try {
        // ========================================
        // 1. GET TICKET DATA WITH ALL FIELDS
        // ========================================
        $ticket = $this->db
            ->select('*')
            ->from('it_ticket_tickets')
            ->where('id', $id)
            ->get()
            ->row();
        
        if (!$ticket) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'message' => 'Ticket not found'
                ]));
            return;
        }
        
        // ========================================
        // 2. GET SERVICE INFORMATION
        // ========================================
        $serviceGroup = $this->ServiceModel->get_group_by_id($ticket->service_group_id);
        $itService = $this->ServiceModel->get_it_service_by_id($ticket->it_service_id);
        
        // Check if it's a sub-service
        $parentService = null;
        if ($itService && is_array($itService)) {
            $itService = (object) $itService;
        }
        
        if ($itService && $itService->parent_id) {
            $parentService = $this->ServiceModel->get_it_service_by_id($itService->parent_id);
            if (is_array($parentService)) {
                $parentService = (object) $parentService;
            }
        }
        
        // Build service breadcrumb for title
        $serviceTitle = '';
        if ($serviceGroup && is_array($serviceGroup)) {
            $serviceGroup = (object) $serviceGroup;
        }
        if ($parentService) {
            $serviceTitle = ($serviceGroup ? $serviceGroup->name : '') . ' > ' . $parentService->name . ' > ' . $itService->name;
        } else {
            $serviceTitle = ($serviceGroup ? $serviceGroup->name : '') . ' > ' . ($itService ? $itService->name : '');
        }
        
        // ========================================
        // 3. GET REQUESTER INFORMATION
        // ========================================
        $requester = $this->_get_employee_info($ticket->requester_id);
        
        // ========================================
        // 4. GET ASSIGNED USER INFORMATION
        // ========================================
        $assignedUser = null;
        if ($ticket->assigned_to) {
            $assignedUser = $this->_get_employee_info($ticket->assigned_to);
        }
        
        // ========================================
        // 5. GET ASSIGNED TEAM INFORMATION
        // ========================================
        $assignedTeam = null;
        if ($ticket->assigned_team_id) {
            $assignedTeam = $this->db
                ->select('id, name, code')
                ->from('it_ticket_support_teams')
                ->where('id', $ticket->assigned_team_id)
                ->get()
                ->row();
        }
        
        // ========================================
        // 6. GET ALL TEAMS FOR DROPDOWN
        // ========================================
        $allTeams = $this->db
            ->select('id, name, code, support_level')
            ->from('it_ticket_support_teams')
            ->where('status', 'active')
            ->order_by('name', 'ASC')
            ->get()
            ->result();
        
        // ========================================
        // 7. GET WORKFLOW STATES
        // ========================================
        $workflowStates = [];
        if ($ticket->workflow_id) {
            $workflowStates = $this->WorkflowModel->get_workflow_states($ticket->workflow_id);
        }
        
        // ========================================
        // 8. GET TICKET HISTORY (LOGS)
        // ========================================
        $history = $this->db
            ->select('*')
            ->from('it_ticket_history')
            ->where('ticket_id', $id)
            ->order_by('created_at', 'DESC')
            ->get()
            ->result();
        
        // Format history with user names
        if ($history) {
            foreach ($history as &$log) {
                if (is_array($log)) {
                    $log = (object) $log;
                }
                
                if ($log->employee_id) {
                    $employeeInfo = $this->_get_employee_info($log->employee_id);
                    $log->employee_name = $employeeInfo ? $employeeInfo->employee_name : 'Unknown';
                }
            }
        }
        
        // ========================================
        // 9. GET TICKET COMMENTS
        // ========================================
        $comments = $this->db
            ->select('tc.*')
            ->from('it_ticket_comments tc')
            ->where('tc.ticket_id', $id)
            ->where('tc.is_internal', 0) // Only public comments
            ->order_by('tc.created_at', 'ASC')
            ->get()
            ->result();
        
        // Add employee info to comments
        if ($comments) {
            foreach ($comments as &$comment) {
                if (is_array($comment)) {
                    $comment = (object) $comment;
                }
                $employeeInfo = $this->_get_employee_info($comment->employee_id);
                $comment->employee_name = $employeeInfo ? $employeeInfo->employee_name : 'Unknown';
                $comment->employee_email = $employeeInfo ? $employeeInfo->employee_email : '';
                $comment->employee_firstname = $employeeInfo ? $employeeInfo->employee_firstname : '';
            }
        }
        
        // ========================================
        // 10. GET TICKET ATTACHMENTS
        // ========================================
        $attachments = $this->db
            ->select('*')
            ->from('it_ticket_attachments')
            ->where('ticket_id', $id)
            ->order_by('created_at', 'ASC')
            ->get()
            ->result();
        
        // Format file sizes and add uploader info
        if ($attachments) {
            foreach ($attachments as &$file) {
                if (is_array($file)) {
                    $file = (object) $file;
                }
                $file->formatted_size = $this->_format_file_size($file->file_size);
                
                if ($file->employee_id) {
                    $uploaderInfo = $this->_get_employee_info($file->employee_id);
                    $file->uploader_name = $uploaderInfo ? $uploaderInfo->employee_name : 'Unknown';
                }
            }
        }
        
        // ========================================
        // 11. GET PRIVATE NOTES
        // ========================================
        $privateNotes = $this->db
            ->select('tc.*')
            ->from('it_ticket_comments tc')
            ->where('tc.ticket_id', $id)
            ->where('tc.is_internal', 1) // Only internal notes
            ->order_by('tc.created_at', 'DESC')
            ->get()
            ->result();
        
        // Add employee info to notes
        if ($privateNotes) {
            foreach ($privateNotes as &$note) {
                if (is_array($note)) {
                    $note = (object) $note;
                }
                $employeeInfo = $this->_get_employee_info($note->employee_id);
                $note->employee_name = $employeeInfo ? $employeeInfo->employee_name : 'Unknown';
            }
        }
        
        // ========================================
        // 12. GET APPROVAL INFORMATION
        // ========================================
        $approvals = [];
        if ($ticket->requires_approval) {
            $approvalsRaw = $this->db
                ->select('ta.*, ar.rule_name')
                ->from('it_ticket_approvals ta')
                ->join('it_ticket_approval_rules ar', 'ta.approval_rule_id = ar.id', 'left')
                ->where('ta.ticket_id', $id)
                ->order_by('ta.requested_at', 'ASC')
                ->get()
                ->result();
            
            // Add employee info to approvals
            if ($approvalsRaw) {
                foreach ($approvalsRaw as $approval) {
                    if (is_array($approval)) {
                        $approval = (object) $approval;
                    }
                    $approverInfo = $this->_get_employee_info($approval->approver_id);
                    $approval->approver_name = $approverInfo ? $approverInfo->employee_name : 'Unknown';
                    $approvals[] = $approval;
                }
            }
        }
        
        // ========================================
        // 13. GET OBSERVERS (from notifications or custom table if exists)
        // ========================================
        // Note: Schema không có bảng it_ticket_observers, có thể thêm sau
        $observers = [];
        
        // ========================================
        // 14. GET LEVEL RULES FOR ESCALATION
        // ========================================
        $levelRules = $this->RulesModel->get_level_rules_v1($ticket->it_service_id);
        
        // ========================================
        // 15. GET SOLUTION INFORMATION
        // ========================================
        $solution = null;
        if ($ticket->active_solution_id) {
            $solution = $this->db
                ->select('s.*')
                ->from('it_ticket_solutions s')
                ->where('s.id', $ticket->active_solution_id)
                ->get()
                ->row();
            
            if ($solution) {
                if (is_array($solution)) {
                    $solution = (object) $solution;
                }
                $providerInfo = $this->_get_employee_info($solution->provided_by);
                $solution->provided_by_name = $providerInfo ? $providerInfo->employee_name : 'Unknown';
            }
        }
        
        // Get all solutions for this ticket (history)
        $allSolutions = $this->db
            ->select('s.*')
            ->from('it_ticket_solutions s')
            ->where('s.ticket_id', $id)
            ->order_by('s.version', 'DESC')
            ->get()
            ->result();
        
        if ($allSolutions) {
            foreach ($allSolutions as &$sol) {
                if (is_array($sol)) {
                    $sol = (object) $sol;
                }
                $providerInfo = $this->_get_employee_info($sol->provided_by);
                $sol->provided_by_name = $providerInfo ? $providerInfo->employee_name : 'Unknown';
            }
        }
        
        // ========================================
        // 16. GET PRIORITY BADGE
        // ========================================
        $priorityBadge = [
            'low' => ['label' => 'Bronze', 'class' => 'badge-bronze'],
            'medium' => ['label' => 'Silver', 'class' => 'badge-silver'],
            'high' => ['label' => 'Gold', 'class' => 'badge-gold'],
            'critical' => ['label' => 'Platinum', 'class' => 'badge-platinum']
        ];
        
        $badge = $priorityBadge[$ticket->priority] ?? $priorityBadge['medium'];
        
        // ========================================
        // 17. CALCULATE SLA INFORMATION
        // ========================================
        $slaInfo = [
            'due_date' => $ticket->sla_due_date,
            'status' => $ticket->sla_status,
            'is_overdue' => $ticket->sla_status === 'overdue',
            'time_remaining' => null
        ];
        
        if ($ticket->sla_due_date && $ticket->status !== 'closed' && $ticket->status !== 'resolved') {
            $now = new DateTime();
            $dueDate = new DateTime($ticket->sla_due_date);
            $interval = $now->diff($dueDate);
            
            if ($dueDate > $now) {
                $slaInfo['time_remaining'] = $interval->format('%d days %h hours');
            } else {
                $slaInfo['time_remaining'] = 'Overdue by ' . $interval->format('%d days %h hours');
            }
        }
        
        // ========================================
        // 18. GET ESCALATION HISTORY
        // ========================================
        $escalations = $this->db
            ->select('e.*, st.name as to_team_name')
            ->from('it_ticket_escalations e')
            ->join('it_ticket_support_teams st', 'e.to_team_id = st.id', 'left')
            ->where('e.ticket_id', $id)
            ->order_by('e.escalated_at', 'DESC')
            ->get()
            ->result();
        
        if ($escalations) {
            foreach ($escalations as &$esc) {
                if (is_array($esc)) {
                    $esc = (object) $esc;
                }
                $fromInfo = $this->_get_employee_info($esc->from_employee_id);
                $esc->from_employee_name = $fromInfo ? $fromInfo->employee_name : 'Unknown';
                
                if ($esc->to_employee_id) {
                    $toInfo = $this->_get_employee_info($esc->to_employee_id);
                    $esc->to_employee_name = $toInfo ? $toInfo->employee_name : 'Unknown';
                }
                
                if ($esc->responded_by) {
                    $responderInfo = $this->_get_employee_info($esc->responded_by);
                    $esc->responder_name = $responderInfo ? $responderInfo->employee_name : 'Unknown';
                }
            }
        }
        
        // ========================================
        // 19. PREPARE RESPONSE DATA
        // ========================================
        $responseData = [
            'success' => true,
            'data' => [
                // Basic ticket info
                'ticket' => [
                    'id' => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                    'subject' => $ticket->subject,
                    'description' => $ticket->description,
                    'priority' => $ticket->priority,
                    'status' => $ticket->status,
                    'created_at' => $ticket->created_at,
                    'updated_at' => $ticket->updated_at,
                    'resolved_at' => $ticket->resolved_at,
                    'closed_at' => $ticket->closed_at,
                    'custom_fields_data' => $ticket->custom_fields_data ? json_decode($ticket->custom_fields_data, true) : null,
                    'requires_approval' => $ticket->requires_approval,
                    'approval_status' => $ticket->approval_status,
                    'requires_solution' => $ticket->requires_solution,
                    'current_level' => $ticket->current_level,
                    'max_level' => $ticket->max_level,
                    'first_response_at' => $ticket->first_response_at,
                    'first_response_by' => $ticket->first_response_by,
                    'solution_provided_at' => $ticket->solution_provided_at,
                    'solution_provided_by' => $ticket->solution_provided_by,
                    'resolved_by' => $ticket->resolved_by,
                    'closed_by' => $ticket->closed_by,
                    'cancelled_at' => $ticket->cancelled_at,
                    'cancelled_by' => $ticket->cancelled_by,
                    'cancel_reason' => $ticket->cancel_reason
                ],
                
                // Display info
                'display' => [
                    'service_title' => $serviceTitle,
                    'priority_badge' => $badge,
                    'service_group_name' => $serviceGroup ? $serviceGroup->name : null,
                    'service_name' => $itService ? $itService->name : null
                ],
                
                // People
                'requester' => $requester,
                'assigned_user' => $assignedUser,
                'assigned_team' => $assignedTeam,
                'observers' => $observers,
                
                // Lists for dropdowns
                'teams' => $allTeams,
                'workflow_states' => $workflowStates,
                
                // Activity
                'history' => $history ?: [],
                'comments' => $comments ?: [],
                'attachments' => $attachments ?: [],
                'private_notes' => $privateNotes ?: [],
                'escalations' => $escalations ?: [],
                
                // Business logic
                'approvals' => $approvals,
                'level_rules' => $levelRules,
                'solution' => $solution,
                'all_solutions' => $allSolutions ?: [],
                'sla_info' => $slaInfo
            ]
        ];
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($responseData));
            
    } catch (Exception $e) {
        log_message('error', 'Ticket show error: ' . $e->getMessage());
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => false,
                'message' => 'Failed to load ticket: ' . $e->getMessage()
            ]));
    }
}

/**
 * Get employee information from HR tables
 */
private function _get_employee_info($employee_id)
{
    if (!$employee_id) {
        return null;
    }
    
    $employee = $this->db
        ->select("
            htm.ide AS employee_id,
            CONCAT(htm.fname, ' ', htm.lname) AS employee_name,
            htm.fname AS employee_firstname,
            htm.lname AS employee_lastname,
            htm.arche_email AS employee_email,
            htm.phonework AS employee_phone,
            htm.country AS employee_country,
            hmd.code AS employee_department,
            hmof.offices AS employee_office,
            CONCAT(mgr.fname, ' ', mgr.lname) AS employee_manager_name,
            mgr.arche_email AS employee_manager_email
        ", false)
        ->from('hr_table_main AS htm')
        ->join('hr_table_contract AS htc', 'htm.ide = htc.id_e AND CURDATE() BETWEEN htc.cfrom AND htc.cto AND htc.tdate IS NULL', 'left')
        ->join('hr_menu_job_title AS hmjt', 'htm.job_title_id = hmjt.idjt', 'left')
        ->join('hr_menu_department AS hmd', 'hmjt.dept = hmd.id', 'left')
        ->join('hr_menu_offices AS hmof', 'htm.working_location = hmof.idf', 'left')
        ->join('hr_table_direct_manager AS htdm', 'htm.ide = htdm.id_e AND htdm.is_main = 1', 'left')
        ->join('hr_table_main AS mgr', 'htdm.id_m = mgr.ide', 'left')
        ->where('htm.ide', $employee_id)
        ->get()
        ->row();
    
    return $employee;
}

/**
 * Helper function to format file size
 */
private function _format_file_size($bytes)
{
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

        /**
     * GET /api/tickets/team-unassigned
     * Get unassigned tickets for team (tickets that no one has picked yet)
     */
    public function team_unassigned()
    {
        try {
            $page = (int)$this->input->get('page') ?: 1;
            $perPage = (int)$this->input->get('per_page') ?: 10;
            $employeeId = (int)$this->input->get('employee_id');
            $country = $this->input->get('country');

            if (!$employeeId) {
                $this->_response(['success' => false, 'message' => 'Employee ID is required'], 400);
                return;
            }

            // Get teams that this employee belongs to
            $teamIds = $this->db
                ->select('team_id')
                ->from('it_ticket_team_members')
                // ->where('employee_id', $employeeId)
                ->where('is_active', 1)
                ->get()
                ->result_array();

            if (empty($teamIds)) {
                $this->_response([
                    'success' => true,
                    'data' => [],
                    'total' => 0,
                    'page' => $page,
                    'per_page' => $perPage,
                    'total_pages' => 0
                ]);
                return;
            }

            $teamIdList = array_column($teamIds, 'team_id');

            // Build query for unassigned team tickets
            $this->db->select('
                t.*,
                sg.name as service_group_name,
                its.name as it_service_name,
                team.name as assigned_team_name
            ');
            $this->db->from('it_ticket_tickets t');
            $this->db->join('it_ticket_service_groups sg', 'sg.id = t.service_group_id', 'left');
            $this->db->join('it_ticket_services its', 'its.id = t.it_service_id', 'left');
            // $this->db->join('employees req', 'req.employee_id = t.requester_id', 'left');
            $this->db->join('it_ticket_support_teams team', 'team.id = t.assigned_team_id', 'left');
            
            // Conditions: assigned to team but not to specific user
            $this->db->where_in('t.assigned_team_id', $teamIdList);
            $this->db->where('(t.assigned_to IS NULL OR t.assigned_to = 0)');
            $this->db->where('t.status !=', 'closed');
            
            // Country filter (if employees table has country field)
            // if ($country) {
            //     $this->db->where('req.country', $country);
            // }
            // Additional filters
            $status = $this->input->get('status');
            if ($status) {
                $this->db->where('t.status', $status);
            }

            $priority = $this->input->get('priority');
            if ($priority) {
                $this->db->where('t.priority', $priority);
            }

            $search = $this->input->get('search');
            if ($search) {
                $this->db->group_start();
                $this->db->like('t.ticket_number', $search);
                $this->db->or_like('t.subject', $search);
                $this->db->or_like('t.description', $search);
                $this->db->group_end();
            }

            // Get total count
            $total = $this->db->count_all_results('', FALSE);

            // Pagination
            $offset = ($page - 1) * $perPage;
            $this->db->order_by('t.created_at', 'DESC');
            $this->db->limit($perPage, $offset);

            $tickets = $this->db->get()->result();

            $this->_response([
                'success' => true,
                'data' => $tickets,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage)
            ]);

        } catch (Exception $e) {
            log_message('error', 'Team unassigned tickets error: ' . $e->getMessage());
            $this->_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/tickets/my-assigned
     * Get tickets assigned to current employee
     */
    public function my_assigned()
    {
        try {
            $page = (int)$this->input->get('page') ?: 1;
            $perPage = (int)$this->input->get('per_page') ?: 10;
            $employeeId = (int)$this->input->get('employee_id');
            $country = $this->input->get('country');

            if (!$employeeId) {
                $this->_response(['success' => false, 'message' => 'Employee ID is required'], 400);
                return;
            }

            // Build query for assigned tickets
            $this->db->select('
                t.*,
                sg.name as service_group_name,
                its.name as it_service_name,
                team.name as assigned_team_name,
            ');
            $this->db->from('it_ticket_tickets t');
            $this->db->join('it_ticket_service_groups sg', 'sg.id = t.service_group_id', 'left');
            $this->db->join('it_ticket_services its', 'its.id = t.it_service_id', 'left');
            // $this->db->join('employees req', 'req.employee_id = t.requester_id', 'left');
            // $this->db->join('employees assignee', 'assignee.employee_id = t.assigned_to', 'left');
            // $this->db->join('employees observer', 'observer.employee_id = t.observer_id', 'left');
            $this->db->join('it_ticket_support_teams team', 'team.id = t.assigned_team_id', 'left');
            
            // Main condition: assigned to this employee
            $this->db->where('t.assigned_to', $employeeId);
            $this->db->where('t.status !=', 'closed');
            
            // Country filter (if employees table has country field)
            // if ($country) {
            //     $this->db->where('req.country', $country);
            // }

            // Additional filters
            $status = $this->input->get('status');
            if ($status) {
                $this->db->where('t.status', $status);
            }

            $priority = $this->input->get('priority');
            if ($priority) {
                $this->db->where('t.priority', $priority);
            }

            $search = $this->input->get('search');
            if ($search) {
                $this->db->group_start();
                $this->db->like('t.ticket_number', $search);
                $this->db->or_like('t.subject', $search);
                $this->db->or_like('t.description', $search);
                $this->db->group_end();
            }

            // Get total count
            $total = $this->db->count_all_results('', FALSE);

            // Pagination
            $offset = ($page - 1) * $perPage;
            $this->db->order_by('t.created_at', 'DESC');
            $this->db->limit($perPage, $offset);

            $tickets = $this->db->get()->result();

            $this->_response([
                'success' => true,
                'data' => $tickets,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage)
            ]);

        } catch (Exception $e) {
            log_message('error', 'My assigned tickets error: ' . $e->getMessage());
            $this->_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/tickets/{id}/pick
     * Pick/claim an unassigned ticket
     */
    public function pick_ticket($ticketId)
    {
        try {
            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);

            $employeeId = isset($input['employee_id']) ? (int)$input['employee_id'] : 0;

            if (!$employeeId) {
                $this->_response(['success' => false, 'message' => 'Employee ID is required'], 400);
                return;
            }

            // Get ticket
            $ticket = $this->TicketModel->get_ticket($ticketId);
            
            if (!$ticket) {
                $this->_response(['success' => false, 'message' => 'Ticket not found'], 404);
                return;
            }

            // Check if already assigned
            if ($ticket->assigned_to && $ticket->assigned_to != 0) {
                $this->_response(['success' => false, 'message' => 'Ticket already assigned to someone'], 400);
                return;
            }

            // Verify employee is in the assigned team
            $isMember = $this->db
                ->where('team_id', $ticket->assigned_team_id)
                ->where('employee_id', $employeeId)
                ->where('is_active', 1)
                ->count_all_results('it_ticket_team_members');

            if (!$isMember) {
                $this->_response(['success' => false, 'message' => 'You are not a member of the assigned team'], 403);
                return;
            }

            $this->db->trans_start();

            // Update ticket
            $this->db->where('id', $ticketId);
            $this->db->update('it_ticket_tickets', [
                'assigned_to' => $employeeId,
                'status' => 'assigned',
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // Add history
            $this->TicketModel->add_history($ticketId, $employeeId, 'assigned', [
                'comment' => 'Ticket picked/claimed by user'
            ]);

            // Notify requester
            $this->NotificationModel->create_notification([
                'employee_id' => $ticket->requester_id,
                'ticket_id' => $ticketId,
                'type' => 'ticket_picked',
                'title' => 'Ticket Assigned',
                'message' => "Your ticket #{$ticket->ticket_number} has been picked up",
                'link' => "/tickets/{$ticketId}"
            ]);

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Failed to pick ticket');
            }

            $this->_response([
                'success' => true,
                'message' => 'Ticket picked successfully',
                'data' => [
                    'ticket_id' => $ticketId,
                    'assigned_to' => $employeeId
                ]
            ]);

        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'Pick ticket error: ' . $e->getMessage());
            $this->_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/tickets/{id}/update
     * Update ticket details
     */
    public function update($ticketId)
    {
        try {
            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);

            $employeeId = isset($input['employee_id']) ? (int)$input['employee_id'] : 0;

            if (!$employeeId) {
                $this->_response(['success' => false, 'message' => 'Employee ID is required'], 400);
                return;
            }

            // Get ticket
            $ticket = $this->TicketModel->get_ticket($ticketId);
            
            if (!$ticket) {
                $this->_response(['success' => false, 'message' => 'Ticket not found'], 404);
                return;
            }

            $updateData = [];
            $changes = [];

            // Update assigned team
            if (isset($input['assigned_team_id']) && $input['assigned_team_id'] != $ticket->assigned_team_id) {
                $updateData['assigned_team_id'] = $input['assigned_team_id'] ?: null;
                $changes[] = "Team changed";
            }

            // Update assigned user
            if (isset($input['assigned_to']) && $input['assigned_to'] != $ticket->assigned_to) {
                $updateData['assigned_to'] = $input['assigned_to'] ?: null;
                $changes[] = "Assigned to changed";
            }

            // Update observer (based on schema, there's no observer_id field in tickets table)
            // Commenting this out since the field doesn't exist in the schema
            // if (isset($input['observer_id']) && $input['observer_id'] != $ticket->observer_id) {
            //     $updateData['observer_id'] = $input['observer_id'] ?: null;
            //     $changes[] = "Observer changed";
            // }

            // Update priority
            if (isset($input['priority']) && $input['priority'] != $ticket->priority) {
                $updateData['priority'] = $input['priority'];
                $changes[] = "Priority changed to {$input['priority']}";
            }

            if (empty($updateData)) {
                $this->_response(['success' => true, 'message' => 'No changes made']);
                return;
            }

            $this->db->trans_start();

            // Update ticket
            $updateData['updated_at'] = date('Y-m-d H:i:s');
            $this->db->where('id', $ticketId);
            $this->db->update('it_ticket_tickets', $updateData);

            // Add history
            $this->TicketModel->add_history($ticketId, $employeeId, 'updated', [
                'comment' => 'Ticket updated: ' . implode(', ', $changes),
                'changes' => $changes
            ]);

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Failed to update ticket');
            }

            $this->_response([
                'success' => true,
                'message' => 'Ticket updated successfully',
                'data' => [
                    'ticket_id' => $ticketId,
                    'changes' => $changes
                ]
            ]);

        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'Update ticket error: ' . $e->getMessage());
            $this->_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/tickets/{id}/comment
     * Add comment to ticket
     */
    public function add_comment($ticketId)
    {
        try {
            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);

            $employeeId = isset($input['employee_id']) ? (int)$input['employee_id'] : 0;
            $comment = isset($input['comment']) ? trim($input['comment']) : '';

            if (!$employeeId || !$comment) {
                $this->_response(['success' => false, 'message' => 'Employee ID and comment are required'], 400);
                return;
            }

            // Get ticket
            $ticket = $this->TicketModel->get_ticket($ticketId);
            
            if (!$ticket) {
                $this->_response(['success' => false, 'message' => 'Ticket not found'], 404);
                return;
            }

            $this->db->trans_start();

            // Insert comment
            $commentData = [
                'ticket_id' => $ticketId,
                'employee_id' => $employeeId,
                'comment' => $comment,
                'is_internal' => 0,
                'is_solution' => 0,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $this->db->insert('it_ticket_comments', $commentData);
            $commentId = $this->db->insert_id();

            // Add history
            $this->TicketModel->add_history($ticketId, $employeeId, 'commented', [
                'comment' => 'Comment added',
                'comment_id' => $commentId
            ]);

            // Update ticket updated_at
            $this->db->where('id', $ticketId);
            $this->db->update('it_ticket_tickets', ['updated_at' => date('Y-m-d H:i:s')]);

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Failed to add comment');
            }

            $this->_response([
                'success' => true,
                'message' => 'Comment added successfully',
                'data' => [
                    'comment_id' => $commentId,
                    'ticket_id' => $ticketId
                ]
            ]);

        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'Add comment error: ' . $e->getMessage());
            $this->_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/tickets/{id}/note
     * Add private note to ticket (stored in solutions table with note flag)
     */
    public function add_note($ticketId)
    {
        try {
            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);

            $employeeId = isset($input['employee_id']) ? (int)$input['employee_id'] : 0;
            $note = isset($input['note']) ? trim($input['note']) : '';

            if (!$employeeId || !$note) {
                $this->_response(['success' => false, 'message' => 'Employee ID and note are required'], 400);
                return;
            }

            // Get ticket
            $ticket = $this->TicketModel->get_ticket($ticketId);
            
            if (!$ticket) {
                $this->_response(['success' => false, 'message' => 'Ticket not found'], 404);
                return;
            }

            $this->db->trans_start();

            // Insert note as a solution with special type or use comments table with is_internal flag
            // Using comments table with is_internal flag
            $noteData = [
                'ticket_id' => $ticketId,
                'employee_id' => $employeeId,
                'comment' => $note,
                'is_internal' => 1, // Mark as internal/private
                'is_solution' => 0,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $this->db->insert('it_ticket_comments', $noteData);
            $noteId = $this->db->insert_id();

            // Add history
            $this->TicketModel->add_history($ticketId, $employeeId, 'commented', [
                'comment' => 'Private note added',
                'comment_id' => $noteId
            ]);

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Failed to add note');
            }

            $this->_response([
                'success' => true,
                'message' => 'Note added successfully',
                'data' => [
                    'note_id' => $noteId,
                    'ticket_id' => $ticketId
                ]
            ]);

        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'Add note error: ' . $e->getMessage());
            $this->_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /api/tickets/notes/{noteId}
     * Delete a private note (comment with is_internal=1)
     */
    public function delete_note($noteId)
    {
        try {
            // Check if note exists (comment with is_internal=1)
            $note = $this->db
                ->where('id', $noteId)
                ->where('is_internal', 1)
                ->get('it_ticket_comments')
                ->row();
            
            if (!$note) {
                $this->_response(['success' => false, 'message' => 'Note not found'], 404);
                return;
            }

            $this->db->trans_start();

            // Delete note
            $this->db->where('id', $noteId);
            $this->db->delete('it_ticket_comments');

            // Add history
            $this->TicketModel->add_history($note->ticket_id, $note->employee_id, 'commented', [
                'comment' => 'Private note deleted',
                'comment_id' => $noteId
            ]);

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Failed to delete note');
            }

            $this->_response([
                'success' => true,
                'message' => 'Note deleted successfully'
            ]);

        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'Delete note error: ' . $e->getMessage());
            $this->_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/support-teams
     * Get all active support teams
     */
    public function get_support_teams()
    {
        try {
            $teams = $this->db
                ->select('id, name, code, description, support_level')
                ->from('it_ticket_support_teams')
                ->where('status', 'active')
                ->order_by('name', 'ASC')
                ->get()
                ->result();

            $this->_response([
                'success' => true,
                'data' => $teams
            ]);

        } catch (Exception $e) {
            log_message('error', 'Get support teams error: ' . $e->getMessage());
            $this->_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/employees/active
     * Get all active employees
     */
    public function get_active_employees()
    {
        try {
            $employees = [];

            $this->_response([
                'success' => true,
                'data' => $employees
            ]);

        } catch (Exception $e) {
            log_message('error', 'Get active employees error: ' . $e->getMessage());
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