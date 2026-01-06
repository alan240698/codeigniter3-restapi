<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class RoleModel extends CI_Model
{
    private $table = 'it_ticket_roles';

    /**
     * Get all roles for dropdown (no pagination)
     * Returns: id, name, display_name
     */
    public function get_all_roles()
    {
        $this->db->select('id, name, display_name, is_system');
        $this->db->order_by('is_system', 'DESC'); // System roles first
        $this->db->order_by('display_name', 'ASC');
        
        return $this->db->get($this->table)->result_array();
    }

    /**
     * Get paginated roles with search
     */
    public function get_roles_paginated($page = 1, $perPage = 10, $search = null)
    {
        // COUNT QUERY
        $this->db->from($this->table);

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('name', $search);
            $this->db->or_like('display_name', $search);
            $this->db->or_like('description', $search);
            $this->db->group_end();
        }

        $total = $this->db->count_all_results();

        // DATA QUERY
        $this->db->select('*');
        $this->db->from($this->table);

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('name', $search);
            $this->db->or_like('display_name', $search);
            $this->db->or_like('description', $search);
            $this->db->group_end();
        }

        $offset = ($page - 1) * $perPage;
        $this->db->order_by('is_system', 'DESC'); // System roles first
        $this->db->order_by('display_name', 'ASC');
        $this->db->limit($perPage, $offset);

        $data = $this->db->get()->result_array();

        // Parse permissions JSON & add dependency info
        foreach ($data as &$role) {
            if (isset($role['permissions'])) {
                $role['permissions'] = json_decode($role['permissions'], true);
            }
            
            // Add dependency count
            $role['dependencies'] = $this->check_role_dependencies($role['id']);
            $role['can_delete'] = ($role['is_system'] == 0 && $role['dependencies']['total'] == 0);
        }

        return [
            'data' => $data,
            'total' => $total
        ];
    }

    /**
     * Get single role by ID
     * @return array|null
     */
    public function get_role($id)
    {
        $result = $this->db->get_where($this->table, ['id' => $id])->row_array();
        
        if ($result) {
            // Parse permissions JSON
            if (isset($result['permissions'])) {
                $result['permissions'] = json_decode($result['permissions'], true);
            }
            
            // Add dependency info
            $result['dependencies'] = $this->check_role_dependencies($id);
            $result['can_delete'] = ($result['is_system'] == 0 && $result['dependencies']['total'] == 0);
        }
        
        return $result ?: null;
    }

    /**
     * Check if role is system role
     */
    public function is_system_role($role_id)
    {
        $this->db->select('is_system');
        $this->db->where('id', $role_id);
        $query = $this->db->get($this->table);
        
        if ($query->num_rows() > 0) {
            return $query->row()->is_system == 1;
        }
        return false;
    }

    /**
     * Create new role (always custom role, is_system = 0)
     */
    public function create_role($data)
    {
        // Force is_system = 0 for new roles
        $data['is_system'] = 0;
        
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    /**
     * Update role with system protection
     */
    public function update_role($id, $data)
    {
        $role = $this->get_role($id);
        
        if (!$role) {
            return false;
        }

        // PROTECTION: Cannot change is_system flag
        unset($data['is_system']);

        // PROTECTION: Cannot change name of system role
        if ($role['is_system'] == 1 && isset($data['name']) && $data['name'] != $role['name']) {
            throw new Exception('Cannot change system role name. This field is protected.');
        }

        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);
    }

    /**
     * Delete role with system protection
     */
    public function delete_role($id)
    {
        $role = $this->get_role($id);
        
        if (!$role) {
            throw new Exception('Role not found');
        }

        // PROTECTION: Cannot delete system role
        if ($role['is_system'] == 1) {
            throw new Exception('Cannot delete system role. This role is protected by the system.');
        }

        // Check dependencies
        $dependencies = $this->check_role_dependencies($id);
        
        if ($dependencies['user_roles'] > 0) {
            throw new Exception("Cannot delete role. {$dependencies['user_roles']} user(s) are assigned this role. Please remove all user assignments first.");
        }

        if ($dependencies['workflow_transitions'] > 0) {
            throw new Exception("Cannot delete role. Used in {$dependencies['workflow_transitions']} workflow transition(s). Please update workflows first.");
        }

        // Safe to delete
        $this->db->where('id', $id);
        return $this->db->delete($this->table);
    }

    /**
     * Check role dependencies
     */
    public function check_role_dependencies($role_id)
    {
        $role = $this->db->get_where($this->table, ['id' => $role_id])->row_array();
        
        if (!$role) {
            return ['user_roles' => 0, 'workflow_transitions' => 0, 'total' => 0];
        }

        // Count user role assignments (active only)
        $this->db->where('role_id', $role_id);
        $this->db->where('is_active', 1);
        $user_roles = $this->db->count_all_results('it_ticket_user_roles');

        // Count workflow transitions using this role
        $workflow_transitions = 0;
        if ($this->db->table_exists('it_ticket_workflow_transitions')) {
            $this->db->where('required_role', $role['name']);
            $workflow_transitions = $this->db->count_all_results('it_ticket_workflow_transitions');
        }

        return [
            'user_roles' => $user_roles,
            'workflow_transitions' => $workflow_transitions,
            'total' => $user_roles + $workflow_transitions
        ];
    }

    /**
     * Check if role is assigned to users (backward compatibility)
     */
    public function role_has_users($role_id)
    {
        $this->db->where('role_id', $role_id);
        $this->db->where('is_active', 1);
        $count = $this->db->count_all_results('it_ticket_user_roles');
        return $count > 0;
    }

    /**
     * Get role by name
     */
    public function get_role_by_name($name)
    {
        $result = $this->db->get_where($this->table, ['name' => $name])->row_array();
        
        if ($result && isset($result['permissions'])) {
            $result['permissions'] = json_decode($result['permissions'], true);
        }
        
        return $result ?: null;
    }
}