<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class UserRoleModel extends CI_Model
{
    private $table = 'it_ticket_user_roles';
    private $roles_table = 'it_ticket_roles';
    
    /**
     * Get all user role assignments with pagination
     */
    public function get_user_roles_paginated($page = 1, $perPage = 20, $search = null, $filters = array())
    {
        $offset = ($page - 1) * $perPage;
        
        // Build query
        $this->db->select('
            ur.id,
            ur.employee_id,
            ur.role_id,
            ur.is_active,
            ur.assigned_at,
            ur.assigned_by,
            r.name as role_name,
            r.display_name as role_display_name,
            r.description as role_description
        ');
        $this->db->from($this->table . ' ur');
        $this->db->join($this->roles_table . ' r', 'ur.role_id = r.id', 'left');
        
        // Search
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('ur.employee_id', $search);
            $this->db->or_like('r.name', $search);
            $this->db->or_like('r.display_name', $search);
            $this->db->group_end();
        }
        
        // Filters
        if (isset($filters['role_id'])) {
            $this->db->where('ur.role_id', $filters['role_id']);
        }
        
        if (isset($filters['is_active'])) {
            $this->db->where('ur.is_active', $filters['is_active']);
        }
        
        if (isset($filters['employee_id'])) {
            $this->db->where('ur.employee_id', $filters['employee_id']);
        }
        
        // Count total
        $total = $this->db->count_all_results('', false);
        
        // Get data
        $this->db->order_by('ur.assigned_at', 'DESC');
        $this->db->limit($perPage, $offset);
        $data = $this->db->get()->result();
        
        return array(
            'data' => $data,
            'total' => $total
        );
    }
    
    /**
     * Get user's roles grouped by user - OPTIMIZED VERSION v2
     * Fixed: No LIMIT in subquery (MySQL compatibility)
     */
    public function get_users_with_roles($page = 1, $perPage = 20, $search = null)
    {
        $offset = ($page - 1) * $perPage;
        
        // ✅ Step 1: Count total unique employees
        $this->db->select('COUNT(DISTINCT employee_id) as total_count', FALSE);
        $this->db->from($this->table);
        
        if (!empty($search)) {
            $this->db->like('employee_id', $search);
        }
        
        $count_result = $this->db->get()->row();
        $total = $count_result ? $count_result->total_count : 0;
        
        // ✅ Step 2: Get paginated employee IDs using derived table
        $subquery_sql = "(
            SELECT DISTINCT employee_id
            FROM {$this->table}
        ";
        
        if (!empty($search)) {
            $search_escaped = $this->db->escape_like_str($search);
            $subquery_sql .= " WHERE employee_id LIKE '%{$search_escaped}%'";
        }
        
        $subquery_sql .= "
            ORDER BY employee_id ASC
            LIMIT {$perPage} OFFSET {$offset}
        ) as emp_list";
        
        // ✅ Step 3: Get all roles for these employees in ONE query
        $sql = "
            SELECT 
                ur.employee_id,
                ur.id,
                ur.role_id,
                ur.is_active,
                ur.assigned_at,
                ur.assigned_by,
                r.name as role_name,
                r.display_name as role_display_name,
                r.description as role_description,
                r.is_system
            FROM {$this->table} ur
            INNER JOIN {$subquery_sql} ON ur.employee_id = emp_list.employee_id
            LEFT JOIN {$this->roles_table} r ON ur.role_id = r.id
            ORDER BY ur.employee_id ASC, ur.assigned_at DESC
        ";
        
        $query = $this->db->query($sql);
        $all_roles = $query->result();
        
        // ✅ Step 4: Group by employee_id
        $result = array();
        $current_employee = null;
        $employee_data = null;
        
        foreach ($all_roles as $role) {
            if ($current_employee !== $role->employee_id) {
                // Save previous employee data
                if ($employee_data !== null) {
                    $result[] = $employee_data;
                }
                
                // Start new employee
                $current_employee = $role->employee_id;
                $employee_data = array(
                    'employee_id' => $role->employee_id,
                    'roles' => array(),
                    'active_roles_count' => 0,
                    'total_roles_count' => 0
                );
            }
            
            // Add role to employee
            $employee_data['roles'][] = array(
                'id' => $role->id,
                'role_id' => $role->role_id,
                'role_name' => $role->role_name,
                'role_display_name' => $role->role_display_name,
                'role_description' => $role->role_description,
                'is_active' => $role->is_active,
                'is_system' => $role->is_system,
                'assigned_at' => $role->assigned_at,
                'assigned_by' => $role->assigned_by
            );
            
            $employee_data['total_roles_count']++;
            if ($role->is_active) {
                $employee_data['active_roles_count']++;
            }
        }
        
        // Don't forget last employee
        if ($employee_data !== null) {
            $result[] = $employee_data;
        }
        
        return array(
            'data' => $result,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage)
        );
    }
    
    /**
     * Get roles for specific employee
     */
    public function get_employee_roles($employee_id)
    {
        $this->db->select('
            ur.id,
            ur.role_id,
            ur.is_active,
            ur.assigned_at,
            ur.assigned_by,
            r.name as role_name,
            r.display_name as role_display_name,
            r.description as role_description,
            r.permissions,
            r.is_system
        ');
        $this->db->from($this->table . ' ur');
        $this->db->join($this->roles_table . ' r', 'ur.role_id = r.id', 'left');
        $this->db->where('ur.employee_id', $employee_id);
        $this->db->order_by('ur.assigned_at', 'DESC');
        
        return $this->db->get()->result();
    }
    
    /**
     * Get users assigned to a specific role
     */
    public function get_role_users($role_id, $active_only = false)
    {
        $this->db->select('
            ur.id,
            ur.employee_id,
            ur.is_active,
            ur.assigned_at,
            ur.assigned_by
        ');
        $this->db->from($this->table . ' ur');
        $this->db->where('ur.role_id', $role_id);
        
        if ($active_only) {
            $this->db->where('ur.is_active', 1);
        }
        
        $this->db->order_by('ur.assigned_at', 'DESC');
        
        return $this->db->get()->result();
    }
    
    /**
     * Assign role to employee
     */
    public function assign_role($data)
    {
        // Check if already exists
        $existing = $this->db->get_where($this->table, array(
            'employee_id' => $data['employee_id'],
            'role_id' => $data['role_id']
        ))->row();
        
        if ($existing) {
            // Update existing
            $assigned_by = isset($data['assigned_by']) ? $data['assigned_by'] : null;
            
            return $this->db->update($this->table, array(
                'is_active' => 1,
                'assigned_at' => date('Y-m-d H:i:s'),
                'assigned_by' => $assigned_by,
                'updated_at' => date('Y-m-d H:i:s')
            ), array(
                'id' => $existing->id
            ));
        } else {
            // Insert new
            $assigned_by = isset($data['assigned_by']) ? $data['assigned_by'] : null;
            
            $insert_data = array(
                'employee_id' => $data['employee_id'],
                'role_id' => $data['role_id'],
                'is_active' => 1,
                'assigned_at' => date('Y-m-d H:i:s'),
                'assigned_by' => $assigned_by,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            );
            
            return $this->db->insert($this->table, $insert_data);
        }
    }
    
    /**
     * Remove role from employee (soft delete - set is_active = 0)
     */
    public function remove_role($id)
    {
        return $this->db->update($this->table, array(
            'is_active' => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ), array('id' => $id));
    }
    
    /**
     * Hard delete role assignment
     */
    public function delete_assignment($id)
    {
        return $this->db->delete($this->table, array('id' => $id));
    }
    
    /**
     * Toggle role status
     */
    public function toggle_status($id)
    {
        $current = $this->db->get_where($this->table, array('id' => $id))->row();
        
        if (!$current) {
            return false;
        }
        
        return $this->db->update($this->table, array(
            'is_active' => !$current->is_active,
            'updated_at' => date('Y-m-d H:i:s')
        ), array('id' => $id));
    }
    
    /**
     * Bulk assign roles to employee
     */
    public function bulk_assign_roles($employee_id, $role_ids, $assigned_by = null)
    {
        $this->db->trans_start();
        
        // Deactivate all current roles
        $this->db->update($this->table, array(
            'is_active' => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ), array('employee_id' => $employee_id));
        
        // Assign new roles
        foreach ($role_ids as $role_id) {
            $this->assign_role(array(
                'employee_id' => $employee_id,
                'role_id' => $role_id,
                'assigned_by' => $assigned_by
            ));
        }
        
        $this->db->trans_complete();
        
        return $this->db->trans_status();
    }
    
    /**
     * Get assignment statistics
     */
    public function get_statistics()
    {
        // Total assignments
        $total_assignments = $this->db->count_all($this->table);
        
        // Active assignments
        $active_assignments = $this->db->where('is_active', 1)->count_all_results($this->table);
        
        // Unique employees with roles
        $this->db->select('COUNT(DISTINCT employee_id) as count');
        $this->db->from($this->table);
        $this->db->where('is_active', 1);
        $unique_employees = $this->db->get()->row()->count;
        
        // Roles usage
        $this->db->select('
            r.id,
            r.name,
            r.display_name,
            COUNT(ur.id) as assignment_count
        ');
        $this->db->from($this->roles_table . ' r');
        $this->db->join($this->table . ' ur', 'r.id = ur.role_id AND ur.is_active = 1', 'left');
        $this->db->group_by('r.id');
        $this->db->order_by('assignment_count', 'DESC');
        $roles_usage = $this->db->get()->result();
        
        return array(
            'total_assignments' => $total_assignments,
            'active_assignments' => $active_assignments,
            'unique_employees' => $unique_employees,
            'roles_usage' => $roles_usage
        );
    }
    
    /**
     * Check if employee has specific role
     */
    public function has_role($employee_id, $role_name)
    {
        $this->db->select('ur.id');
        $this->db->from($this->table . ' ur');
        $this->db->join($this->roles_table . ' r', 'ur.role_id = r.id');
        $this->db->where('ur.employee_id', $employee_id);
        $this->db->where('r.name', $role_name);
        $this->db->where('ur.is_active', 1);
        
        return $this->db->count_all_results() > 0;
    }
    
    /**
     * Get assignment history for employee
     */
    public function get_employee_history($employee_id, $limit = 50)
    {
        $this->db->select('
            ur.id,
            ur.role_id,
            ur.is_active,
            ur.assigned_at,
            ur.assigned_by,
            ur.updated_at,
            r.name as role_name,
            r.display_name as role_display_name
        ');
        $this->db->from($this->table . ' ur');
        $this->db->join($this->roles_table . ' r', 'ur.role_id = r.id', 'left');
        $this->db->where('ur.employee_id', $employee_id);
        $this->db->order_by('ur.updated_at', 'DESC');
        $this->db->limit($limit);
        
        return $this->db->get()->result();
    }
    
    /**
     * Validate assignment data
     */
    public function validate_assignment($employee_id, $role_id)
    {
        $errors = array();
        
        // Check if role exists
        $role = $this->db->get_where($this->roles_table, array('id' => $role_id))->row();
        if (!$role) {
            $errors[] = 'Role does not exist';
        }
        
        // Check if employee exists (you might need to join with employees table)
        // For now, just basic validation
        if (empty($employee_id)) {
            $errors[] = 'Employee ID is required';
        }
        
        return array(
            'valid' => empty($errors),
            'errors' => $errors
        );
    }
}