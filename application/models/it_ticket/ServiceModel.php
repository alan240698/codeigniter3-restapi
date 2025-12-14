<?php
defined('BASEPATH') or exit('No direct script access allowed');

class ServiceModel extends CI_Model
{
    private $table_groups = 'it_ticket_service_groups';
    private $table_types = 'it_ticket_types';
    private $table_issues = 'it_ticket_issue_types';

    /*
    |--------------------------------------------------------------------------
    | SERVICE GROUPS - CRUD OPERATIONS
    |--------------------------------------------------------------------------
    */

    /**
     * Get paginated service groups with search and filter
     * @param int $page Current page
     * @param int $perPage Items per page
     * @param string|null $search Search query
     * @param string|null $status Filter by status
     * @return array ['data' => array, 'total' => int]
     */
    public function get_groups_paginated($page = 1, $perPage = 10, $search = null, $status = null)
    {
        $this->db->from($this->table_groups);

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('name', $search);
            $this->db->or_like('code', $search);
            $this->db->or_like('description', $search);
            $this->db->group_end();
        }

        if (!empty($status)) {
            $this->db->where('status', $status);
        }

        $total = $this->db->count_all_results(); 

        $this->db->from($this->table_groups);

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('name', $search);
            $this->db->or_like('code', $search);
            $this->db->or_like('description', $search);
            $this->db->group_end();
        }

        if (!empty($status)) {
            $this->db->where('status', $status);
        }

        $offset = ($page - 1) * $perPage;
        $this->db->order_by('sort_order', 'ASC');
        $this->db->order_by('id', 'DESC');
        $this->db->limit($perPage, $offset);

        $data = $this->db->get()->result();

        return [
            'data' => $data,
            'total' => $total
        ];
    }


    /**
     * Get all service groups (for dropdown, no pagination)
     * @param string|null $status Filter by status (default: 'active')
     * @param string $sortBy Sort order (ASC or DESC)
     * @return array
     */
    public function getAllServiceGroup($status = 'active', $sortBy = 'ASC')
    {
        $this->db->select('*');
        $this->db->from($this->table_groups);

        if (!empty($status)) {
            $this->db->where('status', $status);
        }

        $this->db->order_by('sort_order', $sortBy);
        $this->db->order_by('name', 'ASC');
        
        return $this->db->get()->result();
    }

    /**
     * Get single service group by ID
     * @param int $id Service group ID
     * @return object|null
     */
    public function get_group($id)
    {
        $this->db->where('id', $id);
        return $this->db->get($this->table_groups)->row();
    }

    /**
     * Create new service group
     * @param array $data Service group data
     * @return int Insert ID
     */
    public function create_group($data)
    {
        $this->db->insert($this->table_groups, $data);
        return $this->db->insert_id();
    }

    /**
     * Update service group
     * @param int $id Service group ID
     * @param array $data Update data
     * @return bool
     */
    public function update_group($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->table_groups, $data);
    }

    /**
     * Delete service group
     * @param int $id Service group ID
     * @return bool
     */
    public function delete_group($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete($this->table_groups);
    }

    /**
     * Check if service group has related ticket types
     * @param int $group_id Service group ID
     * @return bool
     */
    public function group_has_ticket_types($group_id)
    {
        $this->db->where('service_group_id', $group_id);
        $count = $this->db->count_all_results($this->table_types);
        return $count > 0;
    }

    /**
     * Check if code already exists (for validation)
     * @param string $code Code to check
     * @param int|null $exclude_id Exclude this ID (for update)
     * @return bool
     */
    public function is_code_exists($code, $exclude_id = null)
    {
        $this->db->where('code', strtoupper($code));
        
        if ($exclude_id !== null) {
            $this->db->where('id !=', $exclude_id);
        }

        $count = $this->db->count_all_results($this->table_groups);
        return $count > 0;
    }

    /**
     * Get maximum sort_order value
     * @return int
     */
    public function get_max_sort_order()
    {
        $this->db->select_max('sort_order');
        $result = $this->db->get($this->table_groups)->row();
        return $result->sort_order ?? 0;
    }

    /**
     * Update sort order for a service group
     * @param int $id Service group ID
     * @param int $sort_order New sort order
     * @return bool
     */
    public function update_sort_order($id, $sort_order)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->table_groups, [
            'sort_order' => $sort_order,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Toggle status (active <-> inactive)
     * @param int $id Service group ID
     * @return bool
     */
    public function toggle_status($id)
    {
        $current = $this->get_group($id);
        if (!$current) {
            return false;
        }

        $newStatus = $current->status === 'active' ? 'inactive' : 'active';
        
        return $this->update_group($id, [
            'status' => $newStatus,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | TICKET TYPES - CRUD OPERATIONS
    |--------------------------------------------------------------------------
    */

   public function get_ticket_types_paginated($page = 1, $perPage = 10, $search = null, $serviceGroupId = null, $status = null)
{
    // ---------------------
    // 1. Query đếm tổng
    // ---------------------
    $this->db->from($this->table_types . ' tt');
    $this->db->join($this->table_groups . ' sg', 'tt.service_group_id = sg.id', 'left');

    if (!empty($search)) {
        $this->db->group_start();
        $this->db->like('tt.name', $search);
        $this->db->or_like('tt.code', $search);
        $this->db->or_like('tt.description', $search);
        $this->db->group_end();
    }

    if (!empty($serviceGroupId)) {
        $this->db->where('tt.service_group_id', $serviceGroupId);
    }

    if (!empty($status)) {
        $this->db->where('tt.status', $status);
    }

    $total = $this->db->count_all_results();

    // ---------------------
    // 2. Query lấy dữ liệu
    // ---------------------
    $this->db->select('tt.*, sg.name as service_group_name, sg.icon as service_group_icon');
    $this->db->from($this->table_types . ' tt');
    $this->db->join($this->table_groups . ' sg', 'tt.service_group_id = sg.id', 'left');

    if (!empty($search)) {
        $this->db->group_start();
        $this->db->like('tt.name', $search);
        $this->db->or_like('tt.code', $search);
        $this->db->or_like('tt.description', $search);
        $this->db->group_end();
    }

    if (!empty($serviceGroupId)) {
        $this->db->where('tt.service_group_id', $serviceGroupId);
    }

    if (!empty($status)) {
        $this->db->where('tt.status', $status);
    }

    $offset = ($page - 1) * $perPage;
    $this->db->order_by('tt.created_at', 'DESC');
    $this->db->limit($perPage, $offset);

    $data = $this->db->get()->result();

    return [
        'data' => $data,
        'total' => $total
    ];
}

/**
 * Get all service groups (for dropdown)
 * @param string $status
 * @return array
 */
public function get_all_service_groups($status = 'active')
{
    $this->db->select('id, name, icon');
    $this->db->from($this->table_groups);

    if (!empty($status)) {
        $this->db->where('status', $status);
    }

    $this->db->order_by('sort_order', 'ASC');
    return $this->db->get()->result();
}

    /**
     * Get single ticket type by ID
     * @param int $id Ticket type ID
     * @return object|null
     */
    public function get_ticket_type($id)
    {
        $this->db->select('tt.*, sg.name as service_group_name, sg.icon as service_group_icon');
        $this->db->from($this->table_types . ' tt');
        $this->db->join($this->table_groups . ' sg', 'tt.service_group_id = sg.id', 'left');
        $this->db->where('tt.id', $id);
        
        return $this->db->get()->row();
    }

    /**
     * Create new ticket type
     * @param array $data Ticket type data
     * @return int Insert ID
     */
    public function create_ticket_type($data)
    {
        $this->db->insert($this->table_types, $data);
        return $this->db->insert_id();
    }

    /**
     * Update ticket type
     * @param int $id Ticket type ID
     * @param array $data Update data
     * @return bool
     */
    public function update_ticket_type($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->table_types, $data);
    }

    /**
     * Delete ticket type
     * @param int $id Ticket type ID
     * @return bool
     */
    public function delete_ticket_type($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete($this->table_types);
    }

    /**
     * Check if ticket type code exists
     * @param string $code Code to check
     * @param int|null $exclude_id Exclude this ID
     * @return bool
     */
    public function is_ticket_type_code_exists($code, $exclude_id = null)
    {
        $this->db->where('code', strtoupper($code));
        
        if ($exclude_id !== null) {
            $this->db->where('id !=', $exclude_id);
        }

        $count = $this->db->count_all_results($this->table_types);
        return $count > 0;
    }

    /**
     * Check if ticket type has issue types
     * @param int $ticket_type_id Ticket type ID
     * @return bool
     */
    public function ticket_type_has_issues($ticket_type_id)
    {
        $this->db->where('ticket_type_id', $ticket_type_id);
        $count = $this->db->count_all_results($this->table_issues);
        return $count > 0;
    }

    /*
    |--------------------------------------------------------------------------
    | ISSUE TYPES - CRUD OPERATIONS
    |--------------------------------------------------------------------------
    */

    /**
     * Get paginated issue types with filters
     * @param int $page Current page
     * @param int $perPage Items per page
     * @param string|null $search Search query
     * @param int|null $ticketTypeId Filter by ticket type
     * @return array ['data' => array, 'total' => int]
     */
public function get_issue_types_paginated($page = 1, $perPage = 10, $search = null, $ticketTypeId = null)
{
    /* ================= COUNT QUERY ================= */
    $this->db->from($this->table_issues . ' it');
    $this->db->join($this->table_types . ' tt', 'it.ticket_type_id = tt.id', 'left');

    if (!empty($search)) {
        $this->db->group_start();
        $this->db->like('it.name', $search);
        $this->db->or_like('it.code', $search);
        $this->db->or_like('it.description', $search);
        $this->db->group_end();
    }

    if (!empty($ticketTypeId)) {
        $this->db->where('it.ticket_type_id', $ticketTypeId);
    }

    $total = $this->db->count_all_results();



    /* ================= DATA QUERY ================= */
    $this->db->select('it.*, tt.name AS ticket_type_name, parent.name AS parent_name');
    $this->db->from($this->table_issues . ' it');
    $this->db->join($this->table_types . ' tt', 'it.ticket_type_id = tt.id', 'left');
    $this->db->join($this->table_issues . ' parent', 'it.parent_id = parent.id', 'left');

    if (!empty($search)) {
        $this->db->group_start();
        $this->db->like('it.name', $search);
        $this->db->or_like('it.code', $search);
        $this->db->or_like('it.description', $search);
        $this->db->group_end();
    }

    if (!empty($ticketTypeId)) {
        $this->db->where('it.ticket_type_id', $ticketTypeId);
    }

    $offset = ($page - 1) * $perPage;

    // 👉 ORDER: parent trước, child sau (KHÔNG dùng CASE)
    $this->db->order_by('it.parent_id IS NOT NULL', 'ASC', false);
    $this->db->order_by('it.parent_id', 'ASC');
    $this->db->order_by('it.name', 'ASC');

    $this->db->limit($perPage, $offset);

    $data = $this->db->get()->result();

    return [
        'data'  => $data,
        'total' => $total
    ];
}


    // /**
    //  * Get all issue types by ticket type (for dropdown)
    //  * @param int|null $ticketTypeId Filter by ticket type
    //  * @return array
    //  */
    // public function get_all_group_type($ticketTypeId = null)
    // {
    //     $this->db->select('it.*, tt.name as ticket_type_name, parent.name as parent_name');
    //     $this->db->from($this->table_issues . ' it');
    //     $this->db->join($this->table_types . ' tt', 'it.ticket_type_id = tt.id', 'left');
    //     $this->db->join($this->table_issues . ' parent', 'it.parent_id = parent.id', 'left');

    //     if (!empty($ticketTypeId)) {
    //         $this->db->where('it.ticket_type_id', $ticketTypeId);
    //     }

    //     $this->db->order_by('CASE WHEN it.parent_id IS NULL THEN 0 ELSE 1 END', 'ASC');
    //     $this->db->order_by('it.parent_id', 'ASC');
    //     $this->db->order_by('it.name', 'ASC');
        
    //     return $this->db->get()->result();
    // }

// public function get_all_group_type($groupId = null)
// {
//     $this->db->select("
//         CONCAT(g.id, '-', COALESCE(t.id, 0)) AS id,
//         CONCAT(g.name, 
//                CASE WHEN t.name IS NOT NULL THEN CONCAT(' - ', t.name) ELSE '' END
//         ) AS name
//     ", false);

//     $this->db->from($this->table_groups . ' g');
//     $this->db->join($this->table_types . ' t', 't.service_group_id = g.id', 'left');

//     if (!empty($groupId)) {
//         $this->db->where('g.id', $groupId);
//     }

//     $this->db->order_by('g.name', 'ASC');
//     $this->db->order_by('t.name', 'ASC');

//     return $this->db->get()->result();
// }

public function get_all_group_type($groupId = null)
{
    // Query from it_ticket_issue_types (NOT it_ticket_types!)
    $this->db->select("
        it.id,
        it.name,
        it.code,
        tt.name AS ticket_type_name
    ", false);

    $this->db->from($this->table_issues . ' it');
    $this->db->join(
        $this->table_types . ' tt',
        'it.ticket_type_id = tt.id',
        'left'
    );

    // Only active issue types
    $this->db->where('it.status', 'active');

    if (!empty($groupId)) {
        $this->db->where('tt.service_group_id', $groupId);
    }

    $this->db->order_by('tt.name', 'ASC');
    $this->db->order_by('it.name', 'ASC');

    return $this->db->get()->result();
}






    /**
     * Get single issue type by ID
     * @param int $id Issue type ID
     * @return object|null
     */
    public function get_issue_type($id)
    {
        $this->db->select('it.*, tt.name as ticket_type_name, parent.name as parent_name');
        $this->db->from($this->table_issues . ' it');
        $this->db->join($this->table_types . ' tt', 'it.ticket_type_id = tt.id', 'left');
        $this->db->join($this->table_issues . ' parent', 'it.parent_id = parent.id', 'left');
        $this->db->where('it.id', $id);
        
        return $this->db->get()->row();
    }

    /**
     * Create new issue type
     * @param array $data Issue type data
     * @return int Insert ID
     */
    public function create_issue_type($data)
    {
        $this->db->insert($this->table_issues, $data);
        return $this->db->insert_id();
    }

    /**
     * Update issue type
     * @param int $id Issue type ID
     * @param array $data Update data
     * @return bool
     */
    public function update_issue_type($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->table_issues, $data);
    }

    /**
     * Delete issue type (cascade delete children)
     * @param int $id Issue type ID
     * @return bool
     */
    public function delete_issue_type($id)
    {
        // First, delete all children
        $this->db->where('parent_id', $id);
        $this->db->delete($this->table_issues);

        // Then delete the parent
        $this->db->where('id', $id);
        return $this->db->delete($this->table_issues);
    }

    /**
     * Check if issue type code exists
     * @param string $code Code to check
     * @param int|null $exclude_id Exclude this ID
     * @return bool
     */
    public function is_issue_type_code_exists($code, $exclude_id = null)
    {
        $this->db->where('code', strtoupper($code));
        
        if ($exclude_id !== null) {
            $this->db->where('id !=', $exclude_id);
        }

        $count = $this->db->count_all_results($this->table_issues);
        return $count > 0;
    }

    /**
     * Check if issue type has children (sub-issues)
     * @param int $issue_type_id Issue type ID
     * @return bool
     */
    public function issue_type_has_children($issue_type_id)
    {
        $this->db->where('parent_id', $issue_type_id);
        $count = $this->db->count_all_results($this->table_issues);
        return $count > 0;
    }

    /**
     * Get children of an issue type
     * @param int $parent_id Parent issue type ID
     * @return array
     */
    public function get_issue_type_children($parent_id)
    {
        $this->db->where('parent_id', $parent_id);
        $this->db->order_by('name', 'ASC');
        return $this->db->get($this->table_issues)->result();
    }
}