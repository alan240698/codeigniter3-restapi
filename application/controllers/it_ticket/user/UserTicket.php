<?php
defined('BASEPATH') or exit('No direct script access allowed');

class UserTicket extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library(['form_validation', 'session']);
        $this->load->model('it_ticket/ServiceModel');
        $this->load->helper(['url', 'form']);
        
        // TODO: Add authentication check here
        // For now, we'll use a dummy user ID
        if (!$this->session->userdata('employee_id')) {
            $this->session->set_userdata('employee_id', 1); // Dummy user
        }
    }

    /**
     * Display user's ticket list
     */
    public function index()
    {
        // Get service groups for form
        $serviceGroups = $this->ServiceModel->get_all_groups();
        
        // Get ticket types
        $ticketTypes = $this->ServiceModel->get_all_ticket_types();
        
        // Get all IT services
        $itServices = $this->ServiceModel->get_all_group_type();
        
        // Organize IT services by service group
        $groupedServices = [];
        foreach ($itServices as $service) {
            $groupId = $service['service_group_id'];
            if (!isset($groupedServices[$groupId])) {
                $groupedServices[$groupId] = [];
            }
            $groupedServices[$groupId][] = $service;
        }
        
        // Convert objects to arrays for consistency
        $serviceGroupsArray = [];
        foreach ($serviceGroups as $group) {
            $serviceGroupsArray[] = (array) $group;
        }
        
        $data = [
            'page_title' => 'My Tickets',
            'employee_id' => $this->session->userdata('employee_id'),
            'cards' => $serviceGroupsArray,
            'formData' => [],
            'country' => 'Vietnam',
            'service_groups' => $serviceGroupsArray,
            'ticket_types' => $ticketTypes,
            'it_services' => $itServices,
            'grouped_services' => $groupedServices
        ];

        $this->load->view('it_ticket/user/ticket_list', $data);
    }

/**
 * Store new ticket
 */
public function store()
{
    $this->load->model('it_ticket/TicketModel');
    $this->load->model('it_ticket/RulesModel');
    $this->load->model('it_ticket/WorkflowModel');
    $this->load->model('it_ticket/NotificationModel');
    
    try {
        // ========================================
        // 1. GET FORM DATA & VALIDATE
        // ========================================
        $serviceGroupId = $this->input->post('service_group_id');
        $itServiceId = $this->input->post('it_service_id');
        $subServiceId = $this->input->post('sub_service_id');
        $description = $this->input->post('description');
        $subject = $this->input->post('subject') ?: ''; // Allow empty, will fill later
        $priority = $this->input->post('priority') ?: 'medium';
        $customFieldsData = $this->input->post('custom_fields') ?: [];
        $employeeId = $this->session->userdata('employee_id') ?? 1;
        
        // Use sub-service if provided, otherwise use main service
        $finalServiceId = $subServiceId ? $subServiceId : $itServiceId;
        
        if (!$serviceGroupId || !$finalServiceId || !$description) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'message' => 'Service Group, IT Service, and Description are required'
                ]));
            return;
        }
        
        // ========================================
        // 2. VERIFY SERVICE GROUP & IT SERVICE
        // ========================================
        $serviceGroup = $this->ServiceModel->get_group_by_id($serviceGroupId);
        if (!$serviceGroup) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'message' => 'Service Group not found'
                ]));
            return;
        }
        
        // Convert to object if it's an array
        if (is_array($serviceGroup)) {
            $serviceGroup = (object) $serviceGroup;
        }
        
        if ($serviceGroup->status !== 'active') {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'message' => 'Service Group is inactive'
                ]));
            return;
        }
        
        $itService = $this->ServiceModel->get_it_service_by_id($finalServiceId);
        if (!$itService) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'message' => 'IT Service not found'
                ]));
            return;
        }
        
        // Convert to object if it's an array
        if (is_array($itService)) {
            $itService = (object) $itService;
        }
        
        if ($itService->status !== 'active') {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'message' => 'IT Service is inactive'
                ]));
            return;
        }
        
        // ========================================
        // 3. VALIDATE CUSTOM FIELDS
        // ========================================
        // $customFields = $this->ServiceModel->get_custom_fields($finalServiceId);
        
        // if ($customFields) {
        //     foreach ($customFields as $field) {
        //         // Convert to object if it's an array
        //         if (is_array($field)) {
        //             $field = (object) $field;
        //         }
                
        //         if ($field->is_required && empty($customFieldsData[$field->field_name])) {
        //             $this->output
        //                 ->set_content_type('application/json')
        //                 ->set_output(json_encode([
        //                     'success' => false,
        //                     'message' => "{$field->field_label} is required"
        //                 ]));
        //             return;
        //         }
        //     }
        // }
        
        // ========================================
        // 4. GET WORKFLOW & INITIAL STATE
        // ========================================
        $workflow = $this->WorkflowModel->get_workflow_by_service($finalServiceId);
        $workflowId = null;
        $currentStateId = null;
        
        if ($workflow) {
            // Convert to object if it's an array
            if (is_array($workflow)) {
                $workflow = (object) $workflow;
            }
            
            $workflowId = $workflow->id;
            $initialState = $this->WorkflowModel->get_initial_state_v1($workflowId);
            
            if ($initialState) {
                // Convert to object if it's an array
                if (is_array($initialState)) {
                    $initialState = (object) $initialState;
                }
                $currentStateId = $initialState->id;
            }
        }
        
        // ========================================
        // 5. CALCULATE SLA DUE DATE
        // ========================================
        $slaDueDate = $this->TicketModel->calculate_sla_due_date($finalServiceId, $priority);
        
        // ========================================
        // 6. GET LEVEL RULES & MAX LEVEL
        // ========================================
        $levelRules = $this->RulesModel->get_level_rules_v1($finalServiceId);
        $maxLevel = is_array($levelRules) ? count($levelRules) : 0;
        
        // Get Level 1 team (default assignment)
        $level1Team = null;
        if ($levelRules) {
            foreach ($levelRules as $rule) {
                // Convert to object if it's an array
                if (is_array($rule)) {
                    $rule = (object) $rule;
                }
                
                if ($rule->level_number == 1 && $rule->status === 'active') {
                    $level1Team = $rule->support_team_id;
                    break;
                }
            }
        }
        
        // ========================================
        // 7. CHECK APPROVAL REQUIREMENTS
        // ========================================
        $approvalRule = $this->RulesModel->get_approval_rule_by_it_service($finalServiceId);
        $requiresApproval = 0;
        $approvalStatus = 'not_required';
        
        if ($approvalRule) {
            // Convert to object if it's an array
            if (is_array($approvalRule)) {
                $approvalRule = (object) $approvalRule;
            }
            
            $requiresApproval = $approvalRule->requires_approval ? 1 : 0;
            $approvalStatus = $requiresApproval ? 'pending' : 'not_required';
        }
        
        // ========================================
        // 8. APPLY ROUTING RULES
        // ========================================
        $assignedTo = null;
        $assignedTeamId = $level1Team; // Default to Level 1 team
        
        $routingRule = $this->RulesModel->get_routing_rule_by_service($finalServiceId);
        if ($routingRule) {
            // Convert to object if it's an array
            if (is_array($routingRule)) {
                $routingRule = (object) $routingRule;
            }
            
            if ($routingRule->status === 'active') {
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
        }
        
        // ========================================
        // 9. DETERMINE INITIAL STATUS
        // ========================================
        $initialStatus = 'new';
        if ($requiresApproval) {
            $initialStatus = 'pending_approval';
        } elseif ($assignedTo) {
            $initialStatus = 'assigned';
        }
        
        // ========================================
        // 10. GENERATE TICKET NUMBER & SUBJECT
        // ========================================
        $ticketNumber = $this->TicketModel->generate_ticket_number();
        
        // Create subject from IT service name if not provided
        if (empty($subject)) {
            $subject = $itService->name;
        }
        
        // ========================================
        // 11. PREPARE TICKET DATA
        // ========================================
        $ticketData = [
            'ticket_number' => $ticketNumber,
            'service_group_id' => $serviceGroupId,
            'it_service_id' => $finalServiceId,
            'workflow_id' => $workflowId,
            'current_state_id' => $currentStateId,
            'subject' => $subject,
            'description' => $description,
            'custom_fields_data' => !empty($customFieldsData) ? json_encode($customFieldsData) : null,
            'priority' => $priority,
            'status' => $initialStatus,
            'requester_id' => $employeeId,
            'assigned_to' => $assignedTo,
            'assigned_team_id' => $assignedTeamId,
            'current_level' => 1,
            'max_level' => $maxLevel > 0 ? $maxLevel : 1,
            'sla_due_date' => $slaDueDate,
            'sla_status' => 'on_time',
            'requires_approval' => $requiresApproval,
            'approval_status' => $approvalStatus,
            'requires_solution' => $itService->requires_solution,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // ========================================
        // 12. START TRANSACTION
        // ========================================
        $this->db->trans_start();
        
        // Create ticket
        $ticketId = $this->TicketModel->create_ticket($ticketData);
        
        if (!$ticketId) {
            throw new Exception('Failed to create ticket');
        }
        
        // ========================================
        // 13. HANDLE FILE UPLOADS
        // ========================================
        if (!empty($_FILES['attachments']['name'][0])) {
            $this->handle_file_uploads($ticketId, $employeeId);
        }
        
        // ========================================
        // 14. ADD TICKET HISTORY
        // ========================================
        $this->TicketModel->add_history($ticketId, $employeeId, 'created', [
            'comment' => 'Ticket created',
            'metadata' => [
                'service_group_id' => $serviceGroupId,
                'it_service_id' => $finalServiceId
            ]
        ]);
        
        // ========================================
        // 15. CREATE APPROVAL REQUEST IF NEEDED
        // ========================================
        // if ($requiresApproval && $approvalRule) {
        //     $approverId = $this->_determine_approver($approvalRule, $employeeId);
            
        //     if ($approverId) {
        //         $approvalData = [
        //             'ticket_id' => $ticketId,
        //             'approval_rule_id' => $approvalRule->id,
        //             'approver_id' => $approverId,
        //             'status' => 'pending',
        //             'requested_at' => date('Y-m-d H:i:s')
        //         ];
                
        //         $this->db->insert('it_ticket_approvals', $approvalData);
                
        //         // Add history
        //         $this->TicketModel->add_history($ticketId, $employeeId, 'approval_requested', [
        //             'comment' => 'Approval requested',
        //             'approver_id' => $approverId
        //         ]);
                
        //         // Send notification to approver
        //         $this->NotificationModel->create_notification([
        //             'employee_id' => $approverId,
        //             'ticket_id' => $ticketId,
        //             'type' => 'approval_request',
        //             'title' => 'Ticket Approval Request',
        //             'message' => "Ticket #{$ticketNumber} requires your approval",
        //             'link' => "/tickets/{$ticketId}/approve"
        //         ]);
        //     }
        // }
        
        // ========================================
        // 16. CREATE NOTIFICATIONS
        // ========================================
        
        // Notify requester
        $this->NotificationModel->create_notification([
            'employee_id' => $employeeId,
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
            
            $this->TicketModel->add_history($ticketId, $employeeId, 'assigned', [
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
        // 17. COMPLETE TRANSACTION
        // ========================================
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === FALSE) {
            throw new Exception('Transaction failed');
        }
        
        // ========================================
        // 18. RETURN SUCCESS RESPONSE
        // ========================================
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'message' => 'Ticket created successfully',
                'data' => [
                    'ticket_id' => $ticketId,
                    'ticket_number' => $ticketNumber,
                    'status' => $initialStatus,
                    'requires_approval' => $requiresApproval,
                    'assigned_to' => $assignedTo,
                    'assigned_team_id' => $assignedTeamId,
                    'sla_due_date' => $slaDueDate
                ]
            ]));
            
    } catch (Exception $e) {
        $this->db->trans_rollback();
        log_message('error', 'Ticket creation error: ' . $e->getMessage());
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => false,
                'message' => 'Failed to create ticket: ' . $e->getMessage()
            ]));
    }
}

/**
 * Determine approver based on approval rule
 */
private function _determine_approver($approvalRule, $requesterId)
{
    // Convert to object if it's an array
    if (is_array($approvalRule)) {
        $approvalRule = (object) $approvalRule;
    }
    
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
     * Handle file uploads for ticket
     */
    private function handle_file_uploads($ticketId, $employeeId)
    {
        $this->load->helper('string');
        
        $uploadPath = FCPATH . 'uploads/it_tickets/' . date('Y/m/');
        
        // Create directory if not exists
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
        
        $config['upload_path'] = $uploadPath;
        $config['allowed_types'] = 'jpg|jpeg|png|gif|pdf|doc|docx|xls|xlsx|txt';
        $config['max_size'] = 10240; // 10MB
        $config['encrypt_name'] = TRUE;
        
        $this->load->library('upload', $config);
        
        $filesCount = count($_FILES['attachments']['name']);
        
        for ($i = 0; $i < $filesCount; $i++) {
            if ($_FILES['attachments']['error'][$i] == 0) {
                $_FILES['file']['name'] = $_FILES['attachments']['name'][$i];
                $_FILES['file']['type'] = $_FILES['attachments']['type'][$i];
                $_FILES['file']['tmp_name'] = $_FILES['attachments']['tmp_name'][$i];
                $_FILES['file']['error'] = $_FILES['attachments']['error'][$i];
                $_FILES['file']['size'] = $_FILES['attachments']['size'][$i];
                
                if ($this->upload->do_upload('file')) {
                    $uploadData = $this->upload->data();
                    
                    // Save attachment to database
                    $attachmentData = [
                        'ticket_id' => $ticketId,
                        'employee_id' => $employeeId,
                        'filename' => $uploadData['file_name'],
                        'original_filename' => $_FILES['attachments']['name'][$i],
                        'file_path' => 'uploads/it_tickets/' . date('Y/m/') . $uploadData['file_name'],
                        'file_size' => $uploadData['file_size'] * 1024, // Convert to bytes
                        'mime_type' => $uploadData['file_type'],
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                    
                    $this->db->insert('it_ticket_attachments', $attachmentData);
                }
            }
        }
    }

    /**
     * Show create ticket form
     */
    public function create()
    {
        $data = [
            'page_title' => 'Create New Ticket',
            'employee_id' => $this->session->userdata('employee_id')
        ];

        $this->load->view('it_ticket/user/ticket_form', $data);
    }

    /**
     * Show ticket details
     */
    public function show($id)
    {
        $data = [
            'page_title' => 'Ticket Details',
            'ticket_id' => $id,
            'employee_id' => $this->session->userdata('employee_id')
        ];

        $this->load->view('it_ticket/user/ticket_detail', $data);
    }

    /**
     * AJAX: Get service groups, ticket types, and IT services
     */
    public function get_service_data()
    {
        $this->output->set_content_type('application/json');

        try {
            // Get all service groups
            $serviceGroups = $this->ServiceModel->get_all_groups();
            
            // Get all ticket types
            $ticketTypes = $this->ServiceModel->get_all_ticket_types();
            
            // Get all IT services with hierarchical structure
            $itServices = $this->ServiceModel->get_all_group_type();
            
            // Organize IT services by service group
            $groupedServices = [];
            foreach ($itServices as $service) {
                $groupId = $service['service_group_id'];
                if (!isset($groupedServices[$groupId])) {
                    $groupedServices[$groupId] = [];
                }
                $groupedServices[$groupId][] = $service;
            }

            $this->output->set_output(json_encode([
                'success' => true,
                'data' => [
                    'service_groups' => $serviceGroups,
                    'ticket_types' => $ticketTypes,
                    'it_services' => $itServices,
                    'grouped_services' => $groupedServices
                ]
            ], JSON_UNESCAPED_UNICODE));

        } catch (Exception $e) {
            $this->output->set_output(json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]));
        }
    }

    /**
     * AJAX: Get IT services by service group
     */
    public function get_services_by_group($groupId)
    {
        $this->output->set_content_type('application/json');

        try {
            $services = $this->ServiceModel->get_it_services_by_group($groupId);

            $this->output->set_output(json_encode([
                'success' => true,
                'data' => $services
            ], JSON_UNESCAPED_UNICODE));

        } catch (Exception $e) {
            $this->output->set_output(json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]));
        }
    }

    /**
     * AJAX: Get custom fields for IT service
     */
    public function get_custom_fields($itServiceId)
    {
        $this->output->set_content_type('application/json');

        try {
            // Call API to get custom fields
            $apiUrl = base_url('custom-fields/by-it-service/' . $itServiceId);
            
            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                $this->output->set_output($response);
            } else {
                throw new Exception('Failed to fetch custom fields');
            }

        } catch (Exception $e) {
            $this->output->set_output(json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]));
        }
    }
}
