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
        
        try {
            // Get form data
            $serviceGroupId = $this->input->post('service_group_id');
            $itServiceId = $this->input->post('it_service_id');
            $subServiceId = $this->input->post('sub_service_id'); // If has sub-service
            $description = $this->input->post('description');
            $employeeId = $this->session->userdata('employee_id') ?? 1;
            
            // Use sub-service if provided, otherwise use main service
            $finalServiceId = $subServiceId ? $subServiceId : $itServiceId;
            
            // Get IT service details to determine ticket type
            $itService = $this->ServiceModel->get_it_service_by_id($finalServiceId);
            
            if (!$itService) {
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode([
                        'success' => false,
                        'message' => 'Invalid IT service selected'
                    ]));
                return;
            }
            
            // Generate ticket number
            $ticketNumber = $this->TicketModel->generate_ticket_number();
            
            // Create subject from IT service name
            $subject = $itService['name'];
            
            // Prepare ticket data
            $ticketData = [
                'ticket_number' => $ticketNumber,
                'service_group_id' => $serviceGroupId,
                'ticket_type_id' => 1, // Default ticket type, you can make this dynamic
                'it_service_id' => $finalServiceId,
                'subject' => $subject,
                'description' => $description,
                'priority' => 'medium', // Default priority
                'status' => 'new',
                'requester_id' => $employeeId,
                'current_level' => 1,
                'max_level' => 1,
                'requires_approval' => 0,
                'approval_status' => 'not_required',
                'requires_solution' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Calculate SLA due date
            $slaDueDate = $this->TicketModel->calculate_sla_due_date($finalServiceId, 'medium');
            if ($slaDueDate) {
                $ticketData['sla_due_date'] = $slaDueDate;
            }
            
            // Insert ticket
            $ticketId = $this->TicketModel->create_ticket($ticketData);
            
            if (!$ticketId) {
                throw new Exception('Failed to create ticket');
            }
            
            // Handle file uploads
            if (!empty($_FILES['attachments']['name'][0])) {
                $this->handle_file_uploads($ticketId, $employeeId);
            }
            
            // Add history
            $this->TicketModel->add_history($ticketId, $employeeId, 'created', [
                'comment' => 'Ticket created',
                'metadata' => [
                    'service_group_id' => $serviceGroupId,
                    'it_service_id' => $finalServiceId
                ]
            ]);
            
            // Return success
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => true,
                    'message' => 'Ticket created successfully',
                    'data' => [
                        'ticket_id' => $ticketId,
                        'ticket_number' => $ticketNumber
                    ]
                ]));
                
        } catch (Exception $e) {
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
