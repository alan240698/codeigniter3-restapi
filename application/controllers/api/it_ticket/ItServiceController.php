<?php
defined('BASEPATH') or exit('No direct script access allowed');

class ItServiceController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library(['form_validation']);
        $this->load->model('it_ticket/ServiceModel');

        // Set JSON response header
        header('Content-Type: application/json');
    }

    /**
     * GET /api/it-services
     * Get paginated list with filters
     */
    public function index()
    {
        try {
            // Get query parameters
            $page = (int)$this->input->get('page') ?: 1;
            $perPage = (int)$this->input->get('per_page') ?: 10;
            $search = $this->input->get('search');
            $ticketTypeId = $this->input->get('ticket_type_id');

            // Get filtered data
            $result = $this->ServiceModel->get_it_services_paginated(
                $page,
                $perPage,
                $search,
                $ticketTypeId
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
     * GET /api/it-services/all
     * Get all it services (for dropdown)
     */
    public function get_all()
    {
        try {
            $itServices = $this->ServiceModel->get_all_group_type();

            $this->_response([
                'success' => true,
                'data' => $itServices
            ]);
        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/it-services/all-group-type
     * Get all it services with group info (alias for get_all)
     */
    public function get_all_with_groups()
    {
        return $this->get_all();
    }

    /**
     * GET /api/it-services/{id}
     * Get single it service
     */
    public function show($id)
    {
        try {
            $itService = $this->ServiceModel->get_it_service($id);

            if (!$itService) {
                $this->_response([
                    'success' => false,
                    'message' => 'It service not found'
                ], 404);
                return;
            }

            $this->_response([
                'success' => true,
                'data' => $itService
            ]);
        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/it-services/{id}/children
     * Get children of an it service
     */
    public function get_children($id)
    {
        try {
            $children = $this->ServiceModel->get_it_service_children($id);

            $this->_response([
                'success' => true,
                'data' => $children
            ]);
        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/it-services
     * Create new it service
     */
    public function store()
    {
        try {
            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);

            // Validate input
            $_POST = $input;
            $this->form_validation->set_rules('ticket_type_id', 'Ticket Type', 'required|integer|callback_check_ticket_type_exists');
            $this->form_validation->set_rules('parent_id', 'Parent It Service', 'callback_check_parent_exists');
            $this->form_validation->set_rules('name', 'Name', 'required|trim|max_length[100]');
            $this->form_validation->set_rules('code', 'Code', 'required|trim|max_length[50]|callback_check_unique_it_service_code');
            $this->form_validation->set_rules('input_type', 'Input Type', 'required|in_list[radio,dropdown,tree]');
            $this->form_validation->set_rules('icon', 'Icon', 'trim|max_length[50]');
            $this->form_validation->set_rules('sort_order', 'Sort Order', 'integer|greater_than_equal_to[0]');
            $this->form_validation->set_rules('status', 'Status', 'in_list[active,inactive]');
            $this->form_validation->set_rules('description', 'Description', 'trim');

            if (!$this->form_validation->run()) {
                $this->_response([
                    'success' => false,
                    'message' => validation_errors()
                ], 400);
                return;
            }

            // Prepare data
            $data = [
                'ticket_type_id' => $input['ticket_type_id'],
                'parent_id' => !empty($input['parent_id']) ? $input['parent_id'] : null,
                'name' => $input['name'],
                'code' => strtoupper($input['code']),
                'input_type' => $input['input_type'],
                'icon' => $input['icon'] ?? null,
                'sort_order' => $input['sort_order'] ?? 0,
                'status' => $input['status'] ?? 'active',
                'description' => $input['description'] ?? null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $insertId = $this->ServiceModel->create_it_service($data);

            if ($insertId) {
                $this->_response([
                    'success' => true,
                    'message' => 'It service created successfully',
                    'data' => ['id' => $insertId]
                ], 201);
            } else {
                throw new Exception('Failed to create it service');
            }
        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/it-services/{parent_id}/sub-it-service
     * Create sub-it-service (shortcut method)
     */
    public function create_sub_it_service($parent_id)
    {
        try {
            // Check if parent exists
            $parent = $this->ServiceModel->get_it_service($parent_id);
            if (!$parent) {
                $this->_response([
                    'success' => false,
                    'message' => 'Parent It Service not found'
                ], 404);
                return;
            }

            // Get JSON input
            $input = json_decode($this->input->raw_input_stream, true);

            // Force parent_id
            $input['parent_id'] = $parent_id;
            $input['ticket_type_id'] = $parent->ticket_type_id;

            // Validate input
            $this->form_validation->set_data($input);
            $this->form_validation->set_rules('name', 'Name', 'required|trim|max_length[100]');
            $this->form_validation->set_rules('code', 'Code', 'required|trim|max_length[50]|callback_check_unique_it_service_code');
            $this->form_validation->set_rules('input_type', 'Input Type', 'required|in_list[radio,dropdown,tree]');
            $this->form_validation->set_rules('icon', 'Icon', 'trim|max_length[50]');
            $this->form_validation->set_rules('sort_order', 'Sort Order', 'integer|greater_than_equal_to[0]');
            $this->form_validation->set_rules('status', 'Status', 'in_list[active,inactive]');
            $this->form_validation->set_rules('description', 'Description', 'trim');

            if (!$this->form_validation->run()) {
                $this->_response([
                    'success' => false,
                    'message' => validation_errors()
                ], 400);
                return;
            }

            // Prepare data
            $data = [
                'ticket_type_id' => $parent->ticket_type_id,
                'parent_id' => $parent_id,
                'name' => $input['name'],
                'code' => strtoupper($input['code']),
                'input_type' => $input['input_type'],
                'icon' => $input['icon'] ?? null,
                'sort_order' => $input['sort_order'] ?? 0,
                'status' => $input['status'] ?? 'active',
                'description' => $input['description'] ?? null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $insertId = $this->ServiceModel->create_it_service($data);

            if ($insertId) {
                $this->_response([
                    'success' => true,
                    'message' => 'Sub-It-Service created successfully',
                    'data' => ['id' => $insertId]
                ], 201);
            } else {
                throw new Exception('Failed to create sub-it-service');
            }
        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * PUT /api/it-services/{id}
     * Update it service
     */
    public function update($id)
    {
        try {
            // Check if exists
            $existing = $this->ServiceModel->get_it_service($id);
            if (!$existing) {
                $this->_response([
                    'success' => false,
                    'message' => 'It Service not found'
                ], 404);
                return;
            }

            // Get JSON input
            $input = json_decode($this->input->raw_input_stream, true);

            // Validate input
            $this->form_validation->set_data($input);
            $this->form_validation->set_rules('ticket_type_id', 'Ticket Type', 'required|integer|callback_check_ticket_type_exists');
            $this->form_validation->set_rules('parent_id', 'Parent It Service', 'callback_check_parent_exists|callback_check_not_self_parent[' . $id . ']');
            $this->form_validation->set_rules('name', 'Name', 'required|trim|max_length[100]');
            $this->form_validation->set_rules('code', 'Code', 'required|trim|max_length[50]|callback_check_unique_it_service_code_update[' . $id . ']');
            $this->form_validation->set_rules('input_type', 'Input Type', 'required|in_list[radio,dropdown,tree]');
            $this->form_validation->set_rules('icon', 'Icon', 'trim|max_length[50]');
            $this->form_validation->set_rules('sort_order', 'Sort Order', 'integer|greater_than_equal_to[0]');
            $this->form_validation->set_rules('status', 'Status', 'required|in_list[active,inactive]');
            $this->form_validation->set_rules('description', 'Description', 'trim');

            if (!$this->form_validation->run()) {
                $this->_response([
                    'success' => false,
                    'message' => validation_errors()
                ], 400);
                return;
            }

            // Prepare data
            $data = [
                'ticket_type_id' => $input['ticket_type_id'],
                'parent_id' => !empty($input['parent_id']) ? $input['parent_id'] : null,
                'name' => $input['name'],
                'code' => strtoupper($input['code']),
                'input_type' => $input['input_type'],
                'icon' => $input['icon'] ?? null,
                'status' => $input['status'],
                'description' => $input['description'] ?? null,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Update sort_order if provided
            if (isset($input['sort_order'])) {
                $data['sort_order'] = $input['sort_order'];
            }

            $updated = $this->ServiceModel->update_it_ticket($id, $data);

            if ($updated) {
                $this->_response([
                    'success' => true,
                    'message' => 'It Service updated successfully'
                ]);
            } else {
                throw new Exception('Failed to update it service');
            }
        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/it-services/{id}
     * Delete it service (cascade delete children)
     */
    public function delete($id)
    {
        try {
            // Check if exists
            $existing = $this->ServiceModel->get_it_service($id);
            if (!$existing) {
                $this->_response([
                    'success' => false,
                    'message' => 'It Service not found'
                ], 404);
                return;
            }

            // Check if has children
            $hasChildren = $this->ServiceModel->it_service_has_children($id);

            if ($hasChildren) {
                // Confirm cascade delete
                $forceDelete = $this->input->get('force');

                if ($forceDelete !== 'true') {
                    $this->_response([
                        'success' => false,
                        'message' => 'This it service has sub-it-service. Add ?force=true to delete all.',
                        'has_children' => true
                    ], 400);
                    return;
                }
            }

            // Delete (cascade)
            $deleted = $this->ServiceModel->delete_it_ticket($id);

            if ($deleted) {
                $this->_response([
                    'success' => true,
                    'message' => $hasChildren
                        ? 'It Service and all sub-it-service deleted successfully'
                        : 'It Service deleted successfully'
                ]);
            } else {
                throw new Exception('Failed to delete it service');
            }
        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Custom validation: Check if ticket type exists
     */
    public function check_ticket_type_exists($ticketTypeId)
    {
        $ticketType = $this->ServiceModel->get_ticket_type($ticketTypeId);
        if (!$ticketType) {
            $this->form_validation->set_message('check_ticket_type_exists', 'Ticket Type does not exist');
            return false;
        }
        return true;
    }

    /**
     * Custom validation: Check if parent it service exists
     */
    public function check_parent_exists($parentId)
    {
        if (empty($parentId)) {
            return true; // Optional field
        }

        $parent = $this->ServiceModel->get_it_service($parentId);
        if (!$parent) {
            $this->form_validation->set_message('check_parent_exists', 'Parent it service does not exist');
            return false;
        }
        return true;
    }

    /**
     * Custom validation: Check not setting self as parent
     */
    public function check_not_self_parent($parentId, $currentId)
    {
        if (empty($parentId)) {
            return true;
        }

        if ($parentId == $currentId) {
            $this->form_validation->set_message('check_not_self_parent', 'Cannot set itself as parent');
            return false;
        }
        return true;
    }

    /**
     * Custom validation: Check unique code on create
     */
    public function check_unique_it_service_code($code)
    {
        if ($this->ServiceModel->is_it_service_code_exists($code)) {
            $this->form_validation->set_message('check_unique_it_service_code', 'Code already exists');
            return false;
        }
        return true;
    }

    /**
     * Custom validation: Check unique code on update
     */
    public function check_unique_it_service_code_update($code, $id)
    {
        if ($this->ServiceModel->is_it_service_code_exists($code, $id)) {
            $this->form_validation->set_message('check_unique_it_service_code_update', 'Code already exists');
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
