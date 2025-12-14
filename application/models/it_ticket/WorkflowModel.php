<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class WorkflowModel extends CI_Model
{
    private $table_workflows = 'it_ticket_workflows';
    private $table_states = 'it_ticket_workflow_states';
    private $table_transitions = 'it_ticket_workflow_transitions';
    private $table_tickets = 'it_ticket_tickets';
    private $table_issue_workflows = 'it_ticket_issue_type_workflows';

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
     * Check if workflow has issue types
     */
    public function workflow_has_issue_types($workflow_id)
    {
        $this->db->where('workflow_id', $workflow_id);
        $count = $this->db->count_all_results($this->table_issue_workflows);
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
            'linked_issue_types' => 0
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

        // Count linked issue types
        $this->db->where('workflow_id', $workflow_id);
        $stats['linked_issue_types'] = $this->db->count_all_results($this->table_issue_workflows);

        return $stats;
    }
}