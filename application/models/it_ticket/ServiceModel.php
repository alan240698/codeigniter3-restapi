<?php
defined('BASEPATH') or exit('No direct script access allowed');

class ServiceModel extends CI_Model
{
    private $table_groups               = 'it_ticket_service_groups';
    private $table_types                = 'it_ticket_types';
    private $table_it_ticket_services   = 'it_ticket_services';

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
     * Check if service group has related it service
     * @param int $group_id Service group ID
     * @return bool
     */
    public function group_has_it_service($group_id)
    {
        $this->db->where('service_group_id', $group_id);
        $count = $this->db->count_all_results($this->table_it_ticket_services);
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

    public function get_ticket_types_paginated($page = 1, $perPage = 10, $search = null, $status = null)
    {
        $this->db->from($this->table_types . ' tt');

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('tt.name', $search);
            $this->db->or_like('tt.code', $search);
            $this->db->or_like('tt.description', $search);
            $this->db->group_end();
        }

        if (!empty($status)) {
            $this->db->where('tt.status', $status);
        }

        $total = $this->db->count_all_results();

        $this->db->select("
            tt.id,
            tt.code,
            tt.name,
            tt.icon,
            tt.color,
            tt.description,
            tt.sort_order,
            tt.status,
            tt.created_at,
            tt.updated_at
        ", false);

        $this->db->from($this->table_types . ' tt');

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('tt.name', $search);
            $this->db->or_like('tt.code', $search);
            $this->db->or_like('tt.description', $search);
            $this->db->group_end();
        }

        if (!empty($status)) {
            $this->db->where('tt.status', $status);
        }

        $offset = ($page - 1) * $perPage;
        $this->db->order_by('tt.sort_order', 'ASC');
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
        $this->db->select('tt.*');
        $this->db->from($this->table_types . ' tt');
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
     * Check if ticket type code exists in a service group
     *
     * @param string $code Code to check
     * @param int $service_group_id Service group ID
     * @param int|null $exclude_id Exclude this ID (for update)
     * @return bool
     */
    public function is_ticket_type_code_exists($code, $service_group_id, $exclude_id = null)
    {
        $this->db->where('code', strtoupper(trim($code)));

        if (!empty($exclude_id)) {
            $this->db->where('id !=', (int)$exclude_id);
        }

        return $this->db->count_all_results($this->table_types) > 0;
    }

    /*
    |--------------------------------------------------------------------------
    | IT SERVICE - CRUD OPERATIONS
    |--------------------------------------------------------------------------
    */

    /**
     * Get paginated it services with filters
     * @param int $page Current page
     * @param int $perPage Items per page
     * @param string|null $search Search query
     * @param int|null $serviceGroupId Filter by service group
     * @param string|null $status Filter by status
     * @return array ['data' => array, 'total' => int]
     */
    public function get_it_services_paginated($page = 1, $perPage = 10, $search = null, $serviceGroupId = null, $status = null)
    {
        /* ================= COUNT QUERY ================= */
        $this->db->from($this->table_it_ticket_services . ' it');
        $this->db->join($this->table_groups . ' sg', 'it.service_group_id = sg.id', 'left');

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('it.name', $search);
            $this->db->or_like('it.code', $search);
            $this->db->or_like('it.description', $search);
            $this->db->group_end();
        }

        if (!empty($serviceGroupId)) {
            $this->db->where('it.service_group_id', $serviceGroupId);
        }

        if (!empty($status)) {
            $this->db->where('it.status', $status);
        }

        $total = $this->db->count_all_results();

        /* ================= DATA QUERY ================= */
        $this->db->select('it.*, sg.name AS service_group_name, sg.code AS service_group_code, sg.icon AS service_group_icon, parent.name AS parent_name');
        $this->db->from($this->table_it_ticket_services . ' it');
        $this->db->join($this->table_groups . ' sg', 'it.service_group_id = sg.id', 'left');
        $this->db->join($this->table_it_ticket_services . ' parent', 'it.parent_id = parent.id', 'left');

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('it.name', $search);
            $this->db->or_like('it.code', $search);
            $this->db->or_like('it.description', $search);
            $this->db->group_end();
        }

        if (!empty($serviceGroupId)) {
            $this->db->where('it.service_group_id', $serviceGroupId);
        }

        if (!empty($status)) {
            $this->db->where('it.status', $status);
        }

        $offset = ($page - 1) * $perPage;

        $this->db->order_by('sg.sort_order', 'ASC');
        $this->db->order_by('it.parent_id IS NOT NULL', 'ASC', false);
        $this->db->order_by('it.parent_id', 'ASC');
        $this->db->order_by('it.sort_order', 'ASC');
        $this->db->order_by('it.name', 'ASC');

        $this->db->limit($perPage, $offset);

        $data = $this->db->get()->result();

        return [
            'data'  => $data,
            'total' => $total
        ];
    }

    public function get_all_group_type($groupId = null)
    {
        $this->db->select("
            sg.id AS service_group_id,
            sg.name AS service_group_name,
            sg.sort_order AS service_group_sort,
            it.id,
            it.name,
            it.code,
            it.parent_id,
            it.input_type,
            it.sort_order
        ", false);

        $this->db->from($this->table_it_ticket_services . ' it');
        $this->db->join(
            $this->table_groups . ' sg',
            'it.service_group_id = sg.id',
            'left'
        );

        // Only active records
        $this->db->where('it.status', 'active');
        $this->db->where('sg.status', 'active');

        if (!empty($groupId)) {
            $this->db->where('it.service_group_id', $groupId);
        }

        // Order by service group first, then parent, then sort_order
        $this->db->order_by('sg.sort_order', 'ASC');
        $this->db->order_by('sg.name', 'ASC');
        $this->db->order_by('it.parent_id', 'ASC');
        $this->db->order_by('it.sort_order', 'ASC');
        $this->db->order_by('it.name', 'ASC');

        return $this->db->get()->result_array();
    }

    /**
     * Get single it service by ID
     * @param int $id it service ID
     * @return object|null
     */
    public function get_it_service($id)
    {
        $this->db->select('it.*, sg.name as service_group_name, sg.code as service_group_code, parent.name as parent_name');
        $this->db->from($this->table_it_ticket_services . ' it');
        $this->db->join($this->table_groups . ' sg', 'it.service_group_id = sg.id', 'left');
        $this->db->join($this->table_it_ticket_services . ' parent', 'it.parent_id = parent.id', 'left');
        $this->db->where('it.id', $id);
        
        return $this->db->get()->row();
    }

    /**
     * Create new it service
     * @param array $data it service data
     * @return int Insert ID
     */
    public function create_it_service($data)
    {
        $this->db->insert($this->table_it_ticket_services, $data);
        return $this->db->insert_id();
    }

    /**
     * Update it service
     * @param int $id it service ID
     * @param array $data Update data
     * @return bool
     */
    public function update_it_ticket($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->table_it_ticket_services, $data);
    }

    /**
     * Delete it service (cascade delete children)
     * @param int $id It service ID
     * @return bool
     */
    public function delete_it_ticket($id)
    {
        // First, delete all children
        $this->db->where('parent_id', $id);
        $this->db->delete($this->table_it_ticket_services);

        // Then delete the parent
        $this->db->where('id', $id);
        return $this->db->delete($this->table_it_ticket_services);
    }

    /**
     * Check if it service code exists
     * @param string $code Code to check
     * @param int|null $exclude_id Exclude this ID
     * @return bool
     */
    public function is_it_service_code_exists($code, $exclude_id = null)
    {
        $this->db->where('code', strtoupper($code));
        
        if ($exclude_id !== null) {
            $this->db->where('id !=', $exclude_id);
        }

        $count = $this->db->count_all_results($this->table_it_ticket_services);
        return $count > 0;
    }

    /**
     * Check if it service has children (sub-it-service)
     * @param int $it_service_id
     * @return bool
     */
    public function it_service_has_children($it_service_id)
    {
        $this->db->where('parent_id', $it_service_id);
        $count = $this->db->count_all_results($this->table_it_ticket_services);
        return $count > 0;
    }

    /**
     * Get children of an it service
     * @param int $parent_id Parent it service ID
     * @return array
     */
    public function get_it_service_children($parent_id)
    {
        $this->db->where('parent_id', $parent_id);
        $this->db->order_by('name', 'ASC');
        return $this->db->get($this->table_it_ticket_services)->result();
    }

    /*
    |--------------------------------------------------------------------------
    | ALIAS METHODS - For backward compatibility
    |--------------------------------------------------------------------------
    */

    /**
     * Alias for getAllServiceGroup - Get all service groups
     * @param string $status Filter by status
     * @return array
     */
    public function get_all_groups($status = 'active')
    {
        return $this->getAllServiceGroup($status);
    }

    /**
     * Get all ticket types (for dropdown)
     * @param string $status Filter by status
     * @return array
     */
    public function get_all_ticket_types($status = 'active')
    {
        $this->db->select('id, code, name, icon, color');
        $this->db->from($this->table_types);
        
        if (!empty($status)) {
            $this->db->where('status', $status);
        }
        
        $this->db->order_by('sort_order', 'ASC');
        $this->db->order_by('name', 'ASC');
        
        return $this->db->get()->result_array();
    }

    /**
     * Get IT services by service group ID
     * @param int $groupId Service group ID
     * @return array
     */
    public function get_it_services_by_group($groupId)
    {
        $this->db->select('
            it.id,
            it.name,
            it.code,
            it.parent_id,
            it.input_type,
            it.sort_order
        ', false);
        
        $this->db->from($this->table_it_ticket_services . ' it');
        $this->db->where('it.service_group_id', $groupId);
        $this->db->where('it.status', 'active');
        
        $this->db->order_by('it.parent_id', 'ASC');
        $this->db->order_by('it.sort_order', 'ASC');
        $this->db->order_by('it.name', 'ASC');
        
        return $this->db->get()->result_array();
    }

    /**
     * Get single IT service by ID
     * @param int $id IT service ID
     * @return array|null
     */
    public function get_it_service_by_id($id)
    {
        $this->db->where('id', $id);
        $this->db->where('status', 'active');
        return $this->db->get($this->table_it_ticket_services)->row_array();
    }
}