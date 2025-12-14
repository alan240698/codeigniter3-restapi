<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class SupportTeamModel extends CI_Model
{
    private $table_teams = 'it_ticket_support_teams';
    private $table_members = 'it_ticket_team_members';
    private $table_tickets = 'it_ticket_tickets';

    /*
    |--------------------------------------------------------------------------
    | SUPPORT TEAMS - CRUD OPERATIONS
    |--------------------------------------------------------------------------
    */

    /**
     * Get paginated support teams with filters and member count
     */
    public function get_teams_paginated($page = 1, $perPage = 10, $search = null, $supportLevel = null, $country = null, $status = null)
    {
        // Count query
        $this->db->from($this->table_teams . ' t');

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('t.name', $search);
            $this->db->or_like('t.code', $search);
            $this->db->or_like('t.description', $search);
            $this->db->group_end();
        }

        if (!empty($supportLevel)) {
            $this->db->where('t.support_level', $supportLevel);
        }

        if (!empty($country)) {
            $this->db->where('t.country', $country);
        }

        if (!empty($status)) {
            $this->db->where('t.status', $status);
        }

        $total = $this->db->count_all_results();

        // Data query with member count
        $this->db->select('t.*, COUNT(m.id) as members_count');
        $this->db->from($this->table_teams . ' t');
        $this->db->join($this->table_members . ' m', 't.id = m.team_id AND m.is_active = 1', 'left');

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('t.name', $search);
            $this->db->or_like('t.code', $search);
            $this->db->or_like('t.description', $search);
            $this->db->group_end();
        }

        if (!empty($supportLevel)) {
            $this->db->where('t.support_level', $supportLevel);
        }

        if (!empty($country)) {
            $this->db->where('t.country', $country);
        }

        if (!empty($status)) {
            $this->db->where('t.status', $status);
        }

        $this->db->group_by('t.id');
        $offset = ($page - 1) * $perPage;
        $this->db->order_by('t.created_at', 'DESC');
        $this->db->limit($perPage, $offset);

        $data = $this->db->get()->result();

        return [
            'data' => $data,
            'total' => $total
        ];
    }

    /**
     * Get all teams (for dropdown)
     */
    public function get_all_teams($status = 'active', $supportLevel = null)
    {
        $this->db->select('id, name, code, support_level, country, status');
        $this->db->from($this->table_teams);

        if (!empty($status)) {
            $this->db->where('status', $status);
        }

        if (!empty($supportLevel)) {
            $this->db->where('support_level', $supportLevel);
        }

        $this->db->order_by('name', 'ASC');
        return $this->db->get()->result();
    }

    /**
     * Get single team by ID
     */
    public function get_team($id)
    {
        $this->db->select('t.*, COUNT(m.id) as members_count');
        $this->db->from($this->table_teams . ' t');
        $this->db->join($this->table_members . ' m', 't.id = m.team_id AND m.is_active = 1', 'left');
        $this->db->where('t.id', $id);
        $this->db->group_by('t.id');
        
        return $this->db->get()->row();
    }

    /**
     * Create new support team
     */
    public function create_team($data)
    {
        $this->db->insert($this->table_teams, $data);
        return $this->db->insert_id();
    }

    /**
     * Update support team
     */
    public function update_team($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->table_teams, $data);
    }

    /**
     * Delete support team
     */
    public function delete_team($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete($this->table_teams);
    }

    /**
     * Check if code exists
     */
    public function is_code_exists($code, $exclude_id = null)
    {
        $this->db->where('code', strtoupper($code));
        
        if ($exclude_id !== null) {
            $this->db->where('id !=', $exclude_id);
        }

        $count = $this->db->count_all_results($this->table_teams);
        return $count > 0;
    }

    /**
     * Check if team has members
     */
    public function team_has_members($team_id)
    {
        $this->db->where('team_id', $team_id);
        $this->db->where('is_active', 1);
        $count = $this->db->count_all_results($this->table_members);
        return $count > 0;
    }

    /**
     * Check if team has assigned tickets
     */
    public function team_has_tickets($team_id)
    {
        $this->db->where('assigned_team_id', $team_id);
        $this->db->where_not_in('status', ['closed', 'cancelled']);
        $count = $this->db->count_all_results($this->table_tickets);
        return $count > 0;
    }

    /*
    |--------------------------------------------------------------------------
    | TEAM MEMBERS - CRUD OPERATIONS
    |--------------------------------------------------------------------------
    */

public function get_team_members($team_id)
{
    $this->db->select("m.*, 'Employee Name' AS employee_name", false);
    $this->db->from($this->table_members . ' m');
    $this->db->where('m.team_id', $team_id);
    $this->db->order_by('m.role', 'DESC');
    $this->db->order_by('m.joined_at', 'ASC');

    return $this->db->get()->result();
}

public function get_member($id)
{
    $this->db->select("m.*, 'Employee Name' AS employee_name", false);
    $this->db->from($this->table_members . ' m');
    $this->db->where('m.id', $id);

    return $this->db->get()->row();
}


    /**
     * Add member to team
     */
    public function add_member($data)
    {
        $this->db->insert($this->table_members, $data);
        return $this->db->insert_id();
    }

    /**
     * Update team member
     */
    public function update_member($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->table_members, $data);
    }

    /**
     * Hard delete team member (use with caution)
     */
    public function delete_member($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete($this->table_members);
    }

    /**
     * Check if employee is already in team
     */
    public function is_employee_in_team($team_id, $employee_id, $exclude_id = null)
    {
        $this->db->where('team_id', $team_id);
        $this->db->where('employee_id', $employee_id);
        $this->db->where('is_active', 1);
        
        if ($exclude_id !== null) {
            $this->db->where('id !=', $exclude_id);
        }

        $count = $this->db->count_all_results($this->table_members);
        return $count > 0;
    }

    /**
     * Get all teams for a specific employee
     */
    public function get_employee_teams($employee_id)
    {
        $this->db->select('t.*, m.role, m.joined_at, m.is_active');
        $this->db->from($this->table_members . ' m');
        $this->db->join($this->table_teams . ' t', 'm.team_id = t.id');
        $this->db->where('m.employee_id', $employee_id);
        $this->db->order_by('m.is_active', 'DESC');
        $this->db->order_by('m.joined_at', 'DESC');
        
        return $this->db->get()->result();
    }

    /**
     * Get active members by role
     */
    public function get_members_by_role($team_id, $role)
    {
        $this->db->where('team_id', $team_id);
        $this->db->where('role', $role);
        $this->db->where('is_active', 1);
        
        return $this->db->get($this->table_members)->result();
    }

    /**
     * Get team statistics
     */
    public function get_team_stats($team_id)
    {
        $stats = [
            'total_members' => 0,
            'active_members' => 0,
            'members_by_role' => [
                'member' => 0,
                'lead' => 0,
                'manager' => 0
            ]
        ];

        // Total members
        $this->db->where('team_id', $team_id);
        $stats['total_members'] = $this->db->count_all_results($this->table_members);

        // Active members
        $this->db->where('team_id', $team_id);
        $this->db->where('is_active', 1);
        $stats['active_members'] = $this->db->count_all_results($this->table_members);

        // Members by role
        $this->db->select('role, COUNT(*) as count');
        $this->db->where('team_id', $team_id);
        $this->db->where('is_active', 1);
        $this->db->group_by('role');
        $result = $this->db->get($this->table_members)->result();

        foreach ($result as $row) {
            $stats['members_by_role'][$row->role] = $row->count;
        }

        return $stats;
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Get teams by support level
     */
    public function get_teams_by_level($support_level, $status = 'active')
    {
        $this->db->where('support_level', $support_level);
        
        if (!empty($status)) {
            $this->db->where('status', $status);
        }

        $this->db->order_by('name', 'ASC');
        return $this->db->get($this->table_teams)->result();
    }

    /**
     * Get teams by country
     */
    public function get_teams_by_country($country, $status = 'active')
    {
        $this->db->where('country', $country);
        
        if (!empty($status)) {
            $this->db->where('status', $status);
        }

        $this->db->order_by('name', 'ASC');
        return $this->db->get($this->table_teams)->result();
    }

    /**
     * Toggle team status
     */
    public function toggle_status($id)
    {
        $current = $this->get_team($id);
        if (!$current) {
            return false;
        }

        $newStatus = $current->status === 'active' ? 'inactive' : 'active';
        
        return $this->update_team($id, [
            'status' => $newStatus,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get available team members for assignment
     * (Active members with specific role)
     */
    public function get_available_members($team_id, $min_role = null)
    {
        $this->db->select('m.*, "Employee Name" as employee_name');
        $this->db->from($this->table_members . ' m');
        $this->db->where('m.team_id', $team_id);
        $this->db->where('m.is_active', 1);

        if ($min_role) {
            // Role hierarchy: member < lead < manager
            $roles = [];
            if ($min_role === 'member') {
                $roles = ['member', 'lead', 'manager'];
            } elseif ($min_role === 'lead') {
                $roles = ['lead', 'manager'];
            } elseif ($min_role === 'manager') {
                $roles = ['manager'];
            }
            $this->db->where_in('m.role', $roles);
        }

        $this->db->order_by('m.role', 'DESC');
        
        return $this->db->get()->result();
    }
}