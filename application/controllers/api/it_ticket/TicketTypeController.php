<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class TicketTypeController extends CI_Controller
{
    public function __construct() {
        parent::__construct();
        $this->load->library(['form_validation']);
        $this->load->model('it_ticket/ServiceModel');

        header('Content-Type: application/json');
    }

    /**
     * GET /api/ticket-types
     * Get paginated list with filters
     */
    public function index()
    {
        try {
            // Get query parameters
            $page = (int)$this->input->get('page') ?: 1;
            $perPage = (int)$this->input->get('per_page') ?: 10;
            $search = $this->input->get('search');
            $status = $this->input->get('status');

            // Get filtered data
            $result = $this->ServiceModel->get_ticket_types_paginated(
                $page, 
                $perPage, 
                $search, 
                $status
            );

            $this->_response([
                'success' => true,
                'data' => $result['data'],
                'total' => $result['total'],
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($result['total'] / $perPage)
            ]);

        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/ticket-types/all
     * Get all active ticket types (for dropdown)
     */
    public function get_all()
    {
        try {
            $status = $this->input->get('status') ?: 'active';
            $ticketTypes = $this->ServiceModel->get_all_service_groups($status);

            $this->_response([
                'success' => true,
                'data' => $ticketTypes
            ]);

        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/ticket-types/{id}
     * Get single ticket type
     */
    public function show($id)
    {
        try {
            $ticketType = $this->ServiceModel->get_ticket_type($id);

            if (!$ticketType) {
                $this->_response([
                    'success' => false,
                    'message' => 'Ticket Type not found'
                ], 404);
                return;
            }

            $this->_response([
                'success' => true,
                'data' => $ticketType
            ]);

        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/ticket-types
     * Create new ticket type
     */
    public function store()
    {
        try {
            // Get JSON input
                                    $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);

            // Validate input
                            $_POST = $input;

            $this->form_validation->set_rules('name', 'Name', 'required|trim|max_length[100]');
            $this->form_validation->set_rules('code', 'Code', 'required|trim|max_length[50]|callback_check_unique_ticket_code');
            $this->form_validation->set_rules('icon', 'Icon', 'trim|max_length[50]');
            $this->form_validation->set_rules('color', 'Color', 'trim|max_length[7]|callback_check_color_format');
            $this->form_validation->set_rules('description', 'Description', 'trim');
            $this->form_validation->set_rules('sort_order', 'Sort Order', 'integer|greater_than_equal_to[0]');
            $this->form_validation->set_rules('status', 'Status', 'required|in_list[active,inactive]');

            if (!$this->form_validation->run()) {
                $this->_response([
                    'success' => false,
                    'message' => validation_errors()
                ], 400);
                return;
            }

            // Prepare data
            $data = [
                'name' => $input['name'],
                'code' => strtoupper($input['code']),
                'icon' => $input['icon'] ?? null,
                'color' => $input['color'] ?? null,
                'description' => $input['description'] ?? null,
                'sort_order' => $input['sort_order'] ?? 0,
                'status' => $input['status'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $insertId = $this->ServiceModel->create_ticket_type($data);

            if ($insertId) {
                $this->_response([
                    'success' => true,
                    'message' => 'Ticket Type created successfully',
                    'data' => ['id' => $insertId]
                ], 201);
            } else {
                throw new Exception('Failed to create ticket type');
            }

        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * PUT /api/ticket-types/{id}
     * Update ticket type
     */
    public function update($id)
    {
        try {
            // Check if exists
            $existing = $this->ServiceModel->get_ticket_type($id);
            if (!$existing) {
                $this->_response([
                    'success' => false,
                    'message' => 'Ticket Type not found'
                ], 404);
                return;
            }

            // Get JSON input
                        // Get JSON input
                        $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);

            // Validate input
             $_POST = $input;
            $this->form_validation->set_rules('name', 'Name', 'required|trim|max_length[100]');
            $this->form_validation->set_rules('code', 'Code', 'required|trim|max_length[50]|callback_check_unique_ticket_code_update[' . $id . ']');
            $this->form_validation->set_rules('icon', 'Icon', 'trim|max_length[50]');
            $this->form_validation->set_rules('color', 'Color', 'trim|max_length[7]|callback_check_color_format');
            $this->form_validation->set_rules('description', 'Description', 'trim');
            $this->form_validation->set_rules('sort_order', 'Sort Order', 'integer|greater_than_equal_to[0]');
            $this->form_validation->set_rules('status', 'Status', 'required|in_list[active,inactive]');

            if (!$this->form_validation->run()) {
                $this->_response([
                    'success' => false,
                    'message' => validation_errors()
                ], 400);
                return;
            }

            // Prepare data
            $data = [
                'name' => $input['name'],
                'code' => strtoupper($input['code']),
                'icon' => $input['icon'] ?? null,
                'color' => $input['color'] ?? null,
                'description' => $input['description'] ?? null,
                'status' => $input['status'],
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Update sort_order if provided
            if (isset($input['sort_order'])) {
                $data['sort_order'] = $input['sort_order'];
            }

            $updated = $this->ServiceModel->update_ticket_type($id, $data);

            if ($updated) {
                $this->_response([
                    'success' => true,
                    'message' => 'Ticket Type updated successfully'
                ]);
            } else {
                throw new Exception('Failed to update ticket type');
            }

        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/ticket-types/{id}
     * Delete ticket type
     */
    public function delete($id)
    {
        try {
            // Check if exists
            $existing = $this->ServiceModel->get_ticket_type($id);
            if (!$existing) {
                $this->_response([
                    'success' => false,
                    'message' => 'Ticket Type not found'
                ], 404);
                return;
            }

            // Check if has it service
            if ($this->ServiceModel->ticket_type_has_it_service($id)) {
                $this->_response([
                    'success' => false,
                    'message' => 'Cannot delete ticket type with existing it service'
                ], 400);
                return;
            }

            $deleted = $this->ServiceModel->delete_ticket_type($id);

            if ($deleted) {
                $this->_response([
                    'success' => true,
                    'message' => 'Ticket Type deleted successfully'
                ]);
            } else {
                throw new Exception('Failed to delete ticket type');
            }

        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Custom validation: Check if service group exists
     */
    public function check_service_group_exists($serviceGroupId)
    {
        $serviceGroup = $this->ServiceModel->get_group($serviceGroupId);
        if (!$serviceGroup) {
            $this->form_validation->set_message('check_service_group_exists', 'Service Group does not exist');
            return false;
        }
        return true;
    }

    public function check_unique_ticket_code($code)
    {
        $excludeId      = $this->input->post('id');

        $exists = $this->ServiceModel->is_ticket_type_code_exists(
            $code,
            $excludeId
        );

        if ($exists) {
            $this->form_validation->set_message(
                'check_unique_ticket_code'
            );
            return false;
        }

        return true;
    }


    /**
     * Custom validation: Check unique code on update
     */
    public function check_unique_ticket_code_update($code, $id)
    {
        if ($this->ServiceModel->is_ticket_type_code_exists($code, $id)) {
            $this->form_validation->set_message('check_unique_ticket_code_update', 'Code already exists');
            return false;
        }
        return true;
    }

    /**
     * Custom validation: Check color format (hex)
     */
    public function check_color_format($color)
    {
        if (empty($color)) {
            return true; // Optional field
        }
        
        if (!preg_match('/^#[0-9A-F]{6}$/i', $color)) {
            $this->form_validation->set_message('check_color_format', 'Color must be in hex format (#RRGGBB)');
            return false;
        }
        return true;
    }

    /**
     * Helper: Send JSON response
     */
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