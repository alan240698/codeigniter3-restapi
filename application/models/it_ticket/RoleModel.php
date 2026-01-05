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
        $this->db->select('id, name, display_name');
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
        $this->db->order_by('display_name', 'ASC');
        $this->db->limit($perPage, $offset);

        $data = $this->db->get()->result_array();

        // Parse permissions JSON
        foreach ($data as &$role) {
            if (isset($role['permissions'])) {
                $role['permissions'] = json_decode($role['permissions'], true);
            }
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
        }
        
        return $result ?: null;
    }

    /**
     * Create new role
     */
    public function create_role($data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    /**
     * Update role
     */
    public function update_role($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);
    }

    /**
     * Delete role
     */
    public function delete_role($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete($this->table);
    }

    /**
     * Check if role is assigned to users
     */
    public function role_has_users($role_id)
    {
        $this->db->where('role_id', $role_id);
        $count = $this->db->count_all_results('it_ticket_user_roles');
        return $count > 0;
    }
}