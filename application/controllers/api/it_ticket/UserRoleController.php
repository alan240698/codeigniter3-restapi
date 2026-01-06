<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class UserRoleController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('it_ticket/UserRoleModel');
        $this->load->model('it_ticket/RoleModel');
        $this->load->library('session');
        header('Content-Type: application/json');
    }

    /**
     * GET /api/user-roles
     * Get user role assignments with pagination
     */
    public function index()
    {
        try {
            $page = (int)$this->input->get('page') ?: 1;
            $perPage = (int)$this->input->get('per_page') ?: 20;
            $search = $this->input->get('search');
            
            $filters = array(
                'role_id' => $this->input->get('role_id'),
                'is_active' => $this->input->get('is_active'),
                'employee_id' => $this->input->get('employee_id')
            );
            
            // Remove null filters
            $filters = array_filter($filters, function($v) {
                return $v !== null && $v !== '';
            });
            
            $result = $this->UserRoleModel->get_user_roles_paginated($page, $perPage, $search, $filters);

            $this->_response(array(
                'success' => true,
                'data' => $result['data'],
                'total' => $result['total'],
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($result['total'] / $perPage)
            ));
        } catch (Exception $e) {
            $this->_response(array('success' => false, 'message' => $e->getMessage()), 500);
        }
    }
    
    /**
     * GET /api/user-roles/by-users
     * Get users with their roles grouped
     */
    public function get_users_with_roles()
    {
        try {
            $page = (int)$this->input->get('page') ?: 1;
            $perPage = (int)$this->input->get('per_page') ?: 20;
            $search = $this->input->get('search');
            
            $result = $this->UserRoleModel->get_users_with_roles($page, $perPage, $search);

            $this->_response(array(
                'success' => true,
                'data' => $result['data'],
                'total' => $result['total'],
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($result['total'] / $perPage)
            ));
        } catch (Exception $e) {
            $this->_response(array('success' => false, 'message' => $e->getMessage()), 500);
        }
    }
    
    /**
     * GET /api/user-roles/employee/{employee_id}
     * Get roles for specific employee
     */
    public function get_employee_roles($employee_id)
    {
        try {
            $roles = $this->UserRoleModel->get_employee_roles($employee_id);
            
            $this->_response(array(
                'success' => true,
                'data' => $roles,
                'employee_id' => $employee_id
            ));
        } catch (Exception $e) {
            $this->_response(array('success' => false, 'message' => $e->getMessage()), 500);
        }
    }
    
    /**
     * GET /api/user-roles/role/{role_id}/users
     * Get users with specific role
     */
    public function get_role_users($role_id)
    {
        try {
            $activeOnly = $this->input->get('active_only') === '1';
            
            $users = $this->UserRoleModel->get_role_users($role_id, $activeOnly);
            
            $this->_response(array(
                'success' => true,
                'data' => $users,
                'role_id' => $role_id,
                'count' => count($users)
            ));
        } catch (Exception $e) {
            $this->_response(array('success' => false, 'message' => $e->getMessage()), 500);
        }
    }
    
    /**
     * POST /api/user-roles/assign
     * Assign role to employee
     */
    public function assign()
    {
        try {
            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);

            // Validate
            $this->load->library('form_validation');
            $_POST = $input;
            $this->form_validation->set_rules('employee_id', 'Employee ID', 'required|trim');
            $this->form_validation->set_rules('role_id', 'Role ID', 'required|integer');

            if (!$this->form_validation->run()) {
                $this->_response(array('success' => false, 'message' => strip_tags(validation_errors())), 400);
                return;
            }
            
            // Validate assignment
            $validation = $this->UserRoleModel->validate_assignment($input['employee_id'], $input['role_id']);
            if (!$validation['valid']) {
                $this->_response(array('success' => false, 'message' => implode(', ', $validation['errors'])), 400);
                return;
            }

            $data = array(
                'employee_id' => trim($input['employee_id']),
                'role_id' => (int)$input['role_id'],
                'assigned_by' => $this->session->userdata('employee_id')
            );
            
            if (empty($data['assigned_by'])) {
                $data['assigned_by'] = null;
            }

            $result = $this->UserRoleModel->assign_role($data);

            if ($result) {
                $this->_response(array('success' => true, 'message' => 'Role assigned successfully'), 201);
            } else {
                throw new Exception('Failed to assign role');
            }
        } catch (Exception $e) {
            $this->_response(array('success' => false, 'message' => $e->getMessage()), 500);
        }
    }
    
    /**
     * POST /api/user-roles/bulk-assign
     * Bulk assign roles to employee
     */
    public function bulk_assign()
    {
        try {
            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);

            // Validate
            if (empty($input['employee_id'])) {
                $this->_response(array('success' => false, 'message' => 'Employee ID is required'), 400);
                return;
            }
            
            if (!isset($input['role_ids']) || !is_array($input['role_ids'])) {
                $this->_response(array('success' => false, 'message' => 'Role IDs must be an array'), 400);
                return;
            }
$assignedBy = $this->session->userdata('employee_id');
if (empty($assignedBy)) {
    $assignedBy = isset($input['employee_id']) ? $input['employee_id'] : null;
}
            
            $result = $this->UserRoleModel->bulk_assign_roles(
                trim($input['employee_id']), 
                $input['role_ids'],
                $assignedBy
            );

            if ($result) {
                $this->_response(array('success' => true, 'message' => 'Roles assigned successfully'));
            } else {
                throw new Exception('Failed to assign roles');
            }
        } catch (Exception $e) {
            $this->_response(array('success' => false, 'message' => $e->getMessage()), 500);
        }
    }
    
    /**
     * PUT /api/user-roles/{id}/toggle
     * Toggle role assignment status
     */
    public function toggle($id)
    {
        try {
            $result = $this->UserRoleModel->toggle_status($id);

            if ($result) {
                $this->_response(array('success' => true, 'message' => 'Status toggled successfully'));
            } else {
                $this->_response(array('success' => false, 'message' => 'Assignment not found'), 404);
            }
        } catch (Exception $e) {
            $this->_response(array('success' => false, 'message' => $e->getMessage()), 500);
        }
    }
    
    /**
     * DELETE /api/user-roles/{id}/remove
     * Soft delete (deactivate) role assignment
     */
    public function remove($id)
    {
        try {
            $result = $this->UserRoleModel->remove_role($id);

            if ($result) {
                $this->_response(array('success' => true, 'message' => 'Role removed successfully'));
            } else {
                $this->_response(array('success' => false, 'message' => 'Assignment not found'), 404);
            }
        } catch (Exception $e) {
            $this->_response(array('success' => false, 'message' => $e->getMessage()), 500);
        }
    }
    
    /**
     * DELETE /api/user-roles/{id}/delete
     * Hard delete role assignment
     */
    public function delete($id)
    {
        try {
            $result = $this->UserRoleModel->delete_assignment($id);

            if ($result) {
                $this->_response(array('success' => true, 'message' => 'Assignment deleted permanently'));
            } else {
                $this->_response(array('success' => false, 'message' => 'Assignment not found'), 404);
            }
        } catch (Exception $e) {
            $this->_response(array('success' => false, 'message' => $e->getMessage()), 500);
        }
    }
    
    /**
     * GET /api/user-roles/statistics
     * Get assignment statistics
     */
    public function statistics()
    {
        try {
            $stats = $this->UserRoleModel->get_statistics();
            
            $this->_response(array(
                'success' => true,
                'data' => $stats
            ));
        } catch (Exception $e) {
            $this->_response(array('success' => false, 'message' => $e->getMessage()), 500);
        }
    }
    
    /**
     * GET /api/user-roles/available-roles
     * Get all available roles for assignment
     */
    public function available_roles()
    {
        try {
            $roles = $this->RoleModel->get_all_roles();
            
            $this->_response(array(
                'success' => true,
                'data' => $roles
            ));
        } catch (Exception $e) {
            $this->_response(array('success' => false, 'message' => $e->getMessage()), 500);
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