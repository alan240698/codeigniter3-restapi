<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class RoleController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('it_ticket/RoleModel');
        header('Content-Type: application/json');
    }

    /**
     * GET /api/roles/all
     */
    public function get_all()
    {
        try {
            $roles = $this->RoleModel->get_all_roles();
            $this->_response(['success' => true, 'data' => $roles]);
        } catch (Exception $e) {
            $this->_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/roles
     */
    public function index()
    {
        try {
            $page = (int)$this->input->get('page') ?: 1;
            $perPage = (int)$this->input->get('per_page') ?: 10;
            $search = $this->input->get('search');

            $result = $this->RoleModel->get_roles_paginated($page, $perPage, $search);

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
     * POST /api/roles/create
     */
    public function store()
    {
        try {
            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);

            // Validate
            $this->load->library('form_validation');
            $_POST = $input;
            $this->form_validation->set_rules('name', 'Role Name', 'required|trim|max_length[50]|is_unique[it_ticket_roles.name]');
            $this->form_validation->set_rules('display_name', 'Display Name', 'required|trim|max_length[100]');

            if (!$this->form_validation->run()) {
                $this->_response(['success' => false, 'message' => strip_tags(validation_errors())], 400);
                return;
            }

            // Parse permissions
            $permissions = $this->parsePermissions($input);

            $data = [
                'name' => strtolower(trim($input['name'])),
                'display_name' => trim($input['display_name']),
                'description' => !empty($input['description']) ? trim($input['description']) : null,
                'permissions' => json_encode($permissions),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $insertId = $this->RoleModel->create_role($data);

            if ($insertId) {
                $this->_response(['success' => true, 'message' => 'Role created successfully', 'data' => ['id' => $insertId]], 201);
            } else {
                throw new Exception('Failed to create role');
            }
        } catch (Exception $e) {
            $this->_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * PUT /api/roles/{id}/update
     */
    public function update($id)
    {
        try {
            $existing = $this->RoleModel->get_role($id);
            if (!$existing) {
                $this->_response(['success' => false, 'message' => 'Role not found'], 404);
                return;
            }

            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);

            // Validate
            $this->load->library('form_validation');
            $_POST = $input;
            $this->form_validation->set_rules('name', 'Role Name', 'required|trim|max_length[50]|callback_check_unique_name_update[' . $id . ']');
            $this->form_validation->set_rules('display_name', 'Display Name', 'required|trim|max_length[100]');

            if (!$this->form_validation->run()) {
                $this->_response(['success' => false, 'message' => strip_tags(validation_errors())], 400);
                return;
            }

            // Parse permissions
            $permissions = $this->parsePermissions($input);

            $data = [
                'name' => strtolower(trim($input['name'])),
                'display_name' => trim($input['display_name']),
                'description' => !empty($input['description']) ? trim($input['description']) : null,
                'permissions' => json_encode($permissions),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $updated = $this->RoleModel->update_role($id, $data);

            if ($updated) {
                $this->_response(['success' => true, 'message' => 'Role updated successfully']);
            } else {
                throw new Exception('Failed to update role');
            }
        } catch (Exception $e) {
            $this->_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /api/roles/{id}/delete
     */
    public function delete($id)
    {
        try {
            $existing = $this->RoleModel->get_role($id);
            if (!$existing) {
                $this->_response(['success' => false, 'message' => 'Role not found'], 404);
                return;
            }

            if ($this->RoleModel->role_has_users($id)) {
                $this->_response(['success' => false, 'message' => 'Cannot delete role that is assigned to users'], 400);
                return;
            }

            $deleted = $this->RoleModel->delete_role($id);

            if ($deleted) {
                $this->_response(['success' => true, 'message' => 'Role deleted successfully']);
            } else {
                throw new Exception('Failed to delete role');
            }
        } catch (Exception $e) {
            $this->_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Parse permissions - returns default if empty
     */
    private function parsePermissions($input)
    {
        $default = [
            'tickets' => ['view' => false, 'create' => false, 'edit' => false, 'delete' => false, 'assign' => false, 'close' => false],
            'services' => ['view' => false, 'create' => false, 'edit' => false, 'delete' => false],
            'workflows' => ['view' => false, 'create' => false, 'edit' => false, 'delete' => false],
            'users' => ['view' => false, 'create' => false, 'edit' => false, 'delete' => false, 'assign_roles' => false],
            'reports' => ['view' => false, 'export' => false],
            'settings' => ['view' => false, 'edit' => false]
        ];

        if (!isset($input['permissions'])) return $default;

        if (is_string($input['permissions'])) {
            $perms = json_decode($input['permissions'], true);
            return (json_last_error() === JSON_ERROR_NONE) ? $perms : $default;
        }

        return is_array($input['permissions']) ? $input['permissions'] : $default;
    }

    /**
     * Validation callback
     */
    public function check_unique_name_update($name, $id)
    {
        $this->db->where('name', strtolower($name));
        $this->db->where('id !=', $id);
        $count = $this->db->count_all_results('it_ticket_roles');
        
        if ($count > 0) {
            $this->form_validation->set_message('check_unique_name_update', 'Role name already exists');
            return false;
        }
        return true;
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