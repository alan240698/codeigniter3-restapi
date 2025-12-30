<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ServiceGroupController extends CI_Controller
{
    public function __construct() {
        parent::__construct();
        $this->load->library(['form_validation']);
        $this->load->model('it_ticket/ServiceModel');
        
        // Set JSON response header
        header('Content-Type: application/json');
    }

    /**
     * GET /it-ticket/service-groups
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

            // Calculate offset
            $offset = ($page - 1) * $perPage;
            // Get filtered data
            $result = $this->ServiceModel->get_groups_paginated($page, $perPage, $search, $status);

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
     * GET /it-ticket/service-groups/{id}
     * Get single service group
     */
    public function show($id)
    {
        try {
            $serviceGroup = $this->ServiceModel->get_group($id);

            if (!$serviceGroup) {
                $this->_response([
                    'success' => false,
                    'message' => 'Service Group not found'
                ], 404);
                return;
            }

            $this->_response([
                'success' => true,
                'data' => $serviceGroup
            ]);

        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /it-ticket/service-groups
     * Create new service group
     */
    public function store()
    {
        try {
            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);

            // Validate input
             $_POST = $input;
            $this->form_validation->set_rules('name', 'Name', 'required|trim|max_length[100]');
            $this->form_validation->set_rules('code', 'Code', 'required|trim|max_length[50]|callback_check_unique_code');
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
                'icon' => $input['icon'] ?? '📁',
                'color' => $input['color'] ?? null,
                'description' => $input['description'] ?? null,
                'status' => $input['status'],
                'sort_order' => $input['sort_order'] ?? ($this->ServiceModel->get_max_sort_order() + 1),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $insertId = $this->ServiceModel->create_group($data);

            if ($insertId) {
                $this->_response([
                    'success' => true,
                    'message' => 'Service Group created successfully',
                    'data' => ['id' => $insertId]
                ], 201);
            } else {
                throw new Exception('Failed to create service group');
            }

        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * PUT /it-ticket/service-groups/{id}
     * Update service group
     */
    public function update($id)
    {
        try {
            // Check if exists
            $existing = $this->ServiceModel->get_group($id);
            if (!$existing) {
                $this->_response([
                    'success' => false,
                    'message' => 'Service Group not found'
                ], 404);
                return;
            }

            // Get JSON input
                        $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);


            // Validate input
                $_POST = $input;

            $this->form_validation->set_rules('name', 'Name', 'required|trim|max_length[100]');
            $this->form_validation->set_rules('code', 'Code', 'required|trim|max_length[50]|callback_check_unique_code_update[' . $id . ']');
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
                'icon' => $input['icon'] ?? '📁',
                'color' => $input['color'] ?? null,
                'description' => $input['description'] ?? null,
                'status' => $input['status'],
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Update sort_order if provided
            if (isset($input['sort_order'])) {
                $data['sort_order'] = $input['sort_order'];
            }

            $updated = $this->ServiceModel->update_group($id, $data);

            if ($updated) {
                $this->_response([
                    'success' => true,
                    'message' => 'Service Group updated successfully'
                ]);
            } else {
                throw new Exception('Failed to update service group');
            }

        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /it-ticket/service-groups/{id}
     * Delete service group
     */
    public function delete($id)
    {
        try {
            // Check if exists
            $existing = $this->ServiceModel->get_group($id);
            if (!$existing) {
                $this->_response([
                    'success' => false,
                    'message' => 'Service Group not found'
                ], 404);
                return;
            }

            // Check if has ticket types
            if ($this->ServiceModel->group_has_it_service($id)) {
                $this->_response([
                    'success' => false,
                    'message' => 'Cannot delete service group with existing it service'
                ], 400);
                return;
            }

            $deleted = $this->ServiceModel->delete_group($id);

            if ($deleted) {
                $this->_response([
                    'success' => true,
                    'message' => 'Service Group deleted successfully'
                ]);
            } else {
                throw new Exception('Failed to delete service group');
            }

        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Custom validation: Check unique code on create
     */
    public function check_unique_code($code)
    {
        if ($this->ServiceModel->is_code_exists($code)) {
            $this->form_validation->set_message('check_unique_code', 'Code already exists');
            return false;
        }
        return true;
    }

    /**
     * Custom validation: Check unique code on update
     */
    public function check_unique_code_update($code, $id)
    {
        if ($this->ServiceModel->is_code_exists($code, $id)) {
            $this->form_validation->set_message('check_unique_code_update', 'Code already exists');
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