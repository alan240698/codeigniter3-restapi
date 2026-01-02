<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class WorkflowModel extends CI_Model
{
    private $table_workflows = 'it_ticket_workflows';
    private $table_states = 'it_ticket_workflow_states';
    private $table_transitions = 'it_ticket_workflow_transitions';
    private $table_tickets = 'it_ticket_tickets';
    private $table_it_service_workflows = 'it_ticket_service_workflows';

    /*
    |--------------------------------------------------------------------------
    | WORKFLOWS - CRUD OPERATIONS
    |--------------------------------------------------------------------------
    */

    /**
     * Get paginated workflows with search and filter
     */
    public function get_workflows_paginated($page = 1, $perPage = 10, $search = null, $status = null)
    {
        // Count query
        $this->db->from($this->table_workflows . ' w');
        
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('w.name', $search);
            $this->db->or_like('w.code', $search);
            $this->db->or_like('w.description', $search);
            $this->db->group_end();
        }

        if (!empty($status)) {
            $this->db->where('w.status', $status);
        }

        $total = $this->db->count_all_results();

        // Data query with states count
        $this->db->select('w.*, COUNT(ws.id) as states_count');
        $this->db->from($this->table_workflows . ' w');
        $this->db->join($this->table_states . ' ws', 'w.id = ws.workflow_id', 'left');

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('w.name', $search);
            $this->db->or_like('w.code', $search);
            $this->db->or_like('w.description', $search);
            $this->db->group_end();
        }

        if (!empty($status)) {
            $this->db->where('w.status', $status);
        }

        $this->db->group_by('w.id');
        $offset = ($page - 1) * $perPage;
        $this->db->order_by('w.created_at', 'DESC');
        $this->db->limit($perPage, $offset);

        $data = $this->db->get()->result();

        return [
            'data' => $data,
            'total' => $total
        ];
    }

    /**
     * Get all workflows (for dropdown)
     */
    public function get_all_workflows($status = 'active')
    {
        $this->db->select('id, name, code, status');
        $this->db->from($this->table_workflows);

        if (!empty($status)) {
            $this->db->where('status', $status);
        }

        $this->db->order_by('name', 'ASC');
        return $this->db->get()->result();
    }

    /**
     * Get single workflow by ID
     */
    public function get_workflow($id)
    {
        $this->db->select('w.*, COUNT(ws.id) as states_count');
        $this->db->from($this->table_workflows . ' w');
        $this->db->join($this->table_states . ' ws', 'w.id = ws.workflow_id', 'left');
        $this->db->where('w.id', $id);
        $this->db->group_by('w.id');
        
        return $this->db->get()->row();
    }

    /**
     * Create new workflow
     */
    public function create_workflow($data)
    {
        $this->db->insert($this->table_workflows, $data);
        return $this->db->insert_id();
    }

    /**
     * Update workflow
     */
    public function update_workflow($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->table_workflows, $data);
    }

    /**
     * Delete workflow
     */
    public function delete_workflow($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete($this->table_workflows);
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

        $count = $this->db->count_all_results($this->table_workflows);
        return $count > 0;
    }

    /**
     * Check if workflow has tickets
     */
    public function workflow_has_tickets($workflow_id)
    {
        $this->db->where('workflow_id', $workflow_id);
        $count = $this->db->count_all_results($this->table_tickets);
        return $count > 0;
    }

    /**
     * Check if workflow has it services
     */
    public function workflow_has_it_services($workflow_id)
    {
        $this->db->where('workflow_id', $workflow_id);
        $count = $this->db->count_all_results($this->table_it_service_workflows);
        return $count > 0;
    }

    /*
    |--------------------------------------------------------------------------
    | WORKFLOW STATES - CRUD OPERATIONS
    |--------------------------------------------------------------------------
    */

    /**
     * Get workflow states by workflow_id
     */
    public function get_workflow_states($workflow_id)
    {
        $this->db->where('workflow_id', $workflow_id);
        $this->db->order_by('sort_order', 'ASC');
        $this->db->order_by('id', 'ASC');
        
        return $this->db->get($this->table_states)->result();
    }

    /**
     * Get single state by ID
     */
    public function get_state($id)
    {
        $this->db->where('id', $id);
        return $this->db->get($this->table_states)->row();
    }

    /**
     * Create new state
     */
    public function create_state($data)
    {
        $this->db->insert($this->table_states, $data);
        return $this->db->insert_id();
    }

    /**
     * Update state
     */
    public function update_state($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->table_states, $data);
    }

    /**
     * Delete state
     */
    public function delete_state($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete($this->table_states);
    }

    /**
     * Check if state code exists in workflow
     */
    public function is_state_code_exists($workflow_id, $code, $exclude_id = null)
    {
        $this->db->where('workflow_id', $workflow_id);
        $this->db->where('code', strtolower($code));
        
        if ($exclude_id !== null) {
            $this->db->where('id !=', $exclude_id);
        }

        $count = $this->db->count_all_results($this->table_states);
        return $count > 0;
    }

    /**
     * Check if state has tickets
     */
    public function state_has_tickets($state_id)
    {
        $this->db->where('current_state_id', $state_id);
        $count = $this->db->count_all_results($this->table_tickets);
        return $count > 0;
    }

    /**
     * Get initial state of a workflow
     */
    public function get_initial_state($workflow_id)
    {
        $this->db->where('workflow_id', $workflow_id);
        $this->db->where('state_type', 'initial');
        
        return $this->db->get($this->table_states)->row();
    }

    /*
    |--------------------------------------------------------------------------
    | WORKFLOW TRANSITIONS - CRUD OPERATIONS
    |--------------------------------------------------------------------------
    */

    /**
     * Get workflow transitions by workflow_id with state names
     */
    public function get_workflow_transitions($workflow_id)
    {
        $this->db->select('
            t.*,
            fs.name as from_state_name,
            fs.code as from_state_code,
            fs.color as from_state_color,
            ts.name as to_state_name,
            ts.code as to_state_code,
            ts.color as to_state_color
        ');
        $this->db->from($this->table_transitions . ' t');
        $this->db->join($this->table_states . ' fs', 't.from_state_id = fs.id', 'left');
        $this->db->join($this->table_states . ' ts', 't.to_state_id = ts.id', 'left');
        $this->db->where('t.workflow_id', $workflow_id);
        $this->db->order_by('t.sort_order', 'ASC');
        $this->db->order_by('t.id', 'ASC');
        
        return $this->db->get()->result();
    }

    /**
     * Get single transition by ID
     */
    public function get_transition($id)
    {
        $this->db->where('id', $id);
        return $this->db->get($this->table_transitions)->row();
    }

    /**
     * Create new transition
     */
    public function create_transition($data)
    {
        $this->db->insert($this->table_transitions, $data);
        return $this->db->insert_id();
    }

    /**
     * Update transition
     */
    public function update_transition($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->table_transitions, $data);
    }

    /**
     * Delete transition
     */
    public function delete_transition($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete($this->table_transitions);
    }

    /**
     * Get available transitions for current state
     */
    public function get_available_transitions($workflow_id, $current_state_id, $user_role = null)
    {
        $this->db->select('t.*, ts.name as to_state_name, ts.color as to_state_color');
        $this->db->from($this->table_transitions . ' t');
        $this->db->join($this->table_states . ' ts', 't.to_state_id = ts.id', 'left');
        $this->db->where('t.workflow_id', $workflow_id);
        
        $this->db->group_start();
            $this->db->where('t.from_state_id', $current_state_id);
            $this->db->or_where('t.from_state_id IS NULL'); // Initial transitions
        $this->db->group_end();

        if ($user_role) {
            $this->db->group_start();
                $this->db->where('t.required_role', $user_role);
                $this->db->or_where('t.required_role IS NULL');
            $this->db->group_end();
        }

        $this->db->order_by('t.sort_order', 'ASC');
        
        return $this->db->get()->result();
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Toggle workflow status
     */
    public function toggle_status($id)
    {
        $current = $this->get_workflow($id);
        if (!$current) {
            return false;
        }

        $newStatus = $current->status === 'active' ? 'inactive' : 'active';
        
        return $this->update_workflow($id, [
            'status' => $newStatus,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get workflow statistics
     */
    public function get_workflow_stats($workflow_id)
    {
        $stats = [
            'total_states' => 0,
            'total_transitions' => 0,
            'total_tickets' => 0,
            'linked_it_services' => 0
        ];

        // Count states
        $this->db->where('workflow_id', $workflow_id);
        $stats['total_states'] = $this->db->count_all_results($this->table_states);

        // Count transitions
        $this->db->where('workflow_id', $workflow_id);
        $stats['total_transitions'] = $this->db->count_all_results($this->table_transitions);

        // Count tickets
        $this->db->where('workflow_id', $workflow_id);
        $stats['total_tickets'] = $this->db->count_all_results($this->table_tickets);

        // Count linked it services
        $this->db->where('workflow_id', $workflow_id);
        $stats['linked_it_services'] = $this->db->count_all_results($this->table_it_service_workflows);

        return $stats;
    }

    /*
|--------------------------------------------------------------------------
| IT SERVICE WORKFLOW MAPPING - CRUD OPERATIONS
|--------------------------------------------------------------------------
*/

/**
 * Get it service workflow mappings with details
 */
public function get_it_service_workflow_mappings($itServiceId = null)
{
    $this->db->select('
        itw.*,
        it.name as it_service_name,
        it.code as it_service_code,
        w.name as workflow_name,
        w.code as workflow_code,
        w.status as workflow_status,
        COUNT(ws.id) as states_count
    ');
    $this->db->from($this->table_it_service_workflows . ' itw');
    $this->db->join('it_ticket_services it', 'itw.it_service_id = it.id', 'left');
    $this->db->join($this->table_workflows . ' w', 'itw.workflow_id = w.id', 'left');
    $this->db->join($this->table_states . ' ws', 'w.id = ws.workflow_id', 'left');
    $this->db->join($this->table_transitions . ' wt', 'w.id = wt.workflow_id', 'left');

    if ($itServiceId) {
        $this->db->where('itw.it_service_id', $itServiceId);
    }

    $this->db->group_by('itw.id');
    $this->db->order_by('itw.created_at', 'DESC');

    return $this->db->get()->result();
}

/**
 * Get single it service workflow mapping by ID
 */
public function get_it_service_workflow($id)
{
    $this->db->where('id', $id);
    return $this->db->get($this->table_it_service_workflows)->row();
}

/**
 * Get active workflow for specific it service
 */
public function get_active_workflow_by_it_service($itServiceId)
{
    $this->db->select('
        itw.*,
        w.name as workflow_name,
        w.code as workflow_code,
        w.description as workflow_description,
        w.status as workflow_status,
        COUNT(ws.id) as states_count
    ');
    $this->db->from($this->table_it_service_workflows . ' itw');
    $this->db->join($this->table_workflows . ' w', 'itw.workflow_id = w.id', 'left');
    $this->db->join($this->table_states . ' ws', 'w.id = ws.workflow_id', 'left');
    $this->db->where('itw.it_service_id', $itServiceId);
    $this->db->where('itw.is_active', 1);
    $this->db->group_by('itw.id');

    return $this->db->get()->row();
}

/**
 * Get mapping by it service and workflow
 */
public function get_mapping_by_it_service_and_workflow($itServiceId, $workflowId)
{
    $this->db->where('it_service_id', $itServiceId);
    $this->db->where('workflow_id', $workflowId);
    return $this->db->get($this->table_it_service_workflows)->row();
}

/**
 * Create new it service workflow mapping
 */
public function create_it_service_workflow($data)
{
    $this->db->insert($this->table_it_service_workflows, $data);
    return $this->db->insert_id();
}

/**
 * Update it service workflow mapping
 */
public function update_it_service_workflow($id, $data)
{
    $this->db->where('id', $id);
    return $this->db->update($this->table_it_service_workflows, $data);
}

/**
 * Delete it service workflow mapping
 */
public function delete_it_service_workflow($id)
{
    $this->db->where('id', $id);
    return $this->db->delete($this->table_it_service_workflows);
}

/**
 * Deactivate all workflow mappings for an it service
 */
public function deactivate_it_service_workflows($itServiceId, $excludeId = null)
{
    $this->db->where('it_service_id', $itServiceId);
    $this->db->where('is_active', 1);

    if ($excludeId !== null) {
        $this->db->where('id !=', $excludeId);
    }

    return $this->db->update($this->table_it_service_workflows, ['is_active' => 0]);
}

/**
 * Check if mapping has active tickets
 */
public function mapping_has_active_tickets($mappingId)
{
    $mapping = $this->get_it_service_workflow($mappingId);
    if (!$mapping) {
        return false;
    }

    $this->db->where('it_service_id', $mapping->it_service_id);
    $this->db->where('workflow_id', $mapping->workflow_id);
    $this->db->where_in('status', ['open', 'in_progress', 'pending']); // Adjust status values as needed
    
    $count = $this->db->count_all_results($this->table_tickets);
    return $count > 0;
}

/**
 * Get workflow mapping statistics
 */
public function get_workflow_mapping_stats()
{
    $stats = [
        'total_mappings' => 0,
        'active_mappings' => 0,
        'inactive_mappings' => 0,
        'mapped_it_services' => 0,
        'unmapped_it_services' => 0
    ];

    // Total mappings
    $stats['total_mappings'] = $this->db->count_all($this->table_it_service_workflows);

    // Active mappings
    $this->db->where('is_active', 1);
    $stats['active_mappings'] = $this->db->count_all_results($this->table_it_service_workflows);

    // Inactive mappings
    $this->db->where('is_active', 0);
    $stats['inactive_mappings'] = $this->db->count_all_results($this->table_it_service_workflows);

    // Mapped it services (distinct)
    $this->db->select('DISTINCT it_service_id');
    $this->db->where('is_active', 1);
    $stats['mapped_it_services'] = $this->db->count_all_results($this->table_it_service_workflows);

    // Unmapped it services
    $this->db->select('COUNT(*) as count');
    $this->db->from('it_ticket_services it');
    $this->db->join($this->table_it_service_workflows . ' itw', 'it.id = itw.it_service_id AND itw.is_active = 1', 'left');
    $this->db->where('itw.it_service_id IS NULL');
    $result = $this->db->get()->row();
    $stats['unmapped_it_services'] = $result ? $result->count : 0;

    return $stats;
}

/**
 * Get all it services with their workflow mappings
 */
public function get_it_services_with_workflows()
{
    $this->db->select('
        it.id,
        it.name,
        it.code,
        itw.workflow_id,
        w.name as workflow_name,
        w.code as workflow_code,
        itw.is_active as has_workflow
    ');
    $this->db->from('it_ticket_services it');
    $this->db->join($this->table_it_service_workflows . ' itw', 'it.id = itw.it_service_id AND itw.is_active = 1', 'left');
    $this->db->join($this->table_workflows . ' w', 'itw.workflow_id = w.id', 'left');
    $this->db->order_by('it.name', 'ASC');

    return $this->db->get()->result();
}

/**
 * Bulk assign workflow to multiple it services
 */
public function bulk_assign_workflow($itServiceIds, $workflowId)
{
    if (empty($itServiceIds) || !$workflowId) {
        return false;
    }

    $this->db->trans_start();

    foreach ($itServiceIds as $itServiceId) {
        // Check if mapping already exists
        $existing = $this->get_mapping_by_it_service_and_workflow($itServiceId, $workflowId);
        
        if ($existing) {
            // Just activate it
            $this->update_it_service_workflow($existing->id, ['is_active' => 1]);
        } else {
            // Create new mapping
            $this->create_it_service_workflow([
                'it_service_id' => $itServiceId,
                'workflow_id' => $workflowId,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        // Deactivate other workflows for this it service
        $this->deactivate_it_service_workflows($itServiceId);
    }

    $this->db->trans_complete();

    return $this->db->trans_status();
}

/**
 * Get workflow with full details for it service
 */
public function get_workflow_details_for_it_service($itServiceId)
{
    $mapping = $this->get_active_workflow_by_it_service($itServiceId);
    
    if (!$mapping) {
        return null;
    }

    // Get workflow states
    $states = $this->get_workflow_states($mapping->workflow_id);

    // Get workflow transitions
    $transitions = $this->get_workflow_transitions($mapping->workflow_id);

    return [
        'mapping' => $mapping,
        'states' => $states,
        'transitions' => $transitions,
        'initial_state' => $this->get_initial_state($mapping->workflow_id)
    ];
}

}