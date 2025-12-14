<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class TicketModel extends CI_Model
{
    private $table_tickets = 'it_ticket_tickets';
    private $table_history = 'it_ticket_history';
    private $table_service_groups = 'it_ticket_service_groups';
    private $table_ticket_types = 'it_ticket_types';
    private $table_issue_types = 'it_ticket_issue_types';
    private $table_workflows = 'it_ticket_workflows';
    private $table_workflow_states = 'it_ticket_workflow_states';
    private $table_support_teams = 'it_ticket_support_teams';
    private $table_sla_policies = 'it_ticket_sla_policies';

    /**
     * Get paginated tickets with filters
     */
    public function get_tickets_paginated($page = 1, $perPage = 10, $filters = [])
    {
        $this->db->select('
            t.*,
            sg.name as service_group_name,
            tt.name as ticket_type_name,
            it.name as issue_type_name,
            ws.name as current_state_name,
            st.name as assigned_team_name
        ');
        $this->db->from($this->table_tickets . ' t');
        $this->db->join($this->table_service_groups . ' sg', 't.service_group_id = sg.id', 'left');
        $this->db->join($this->table_ticket_types . ' tt', 't.ticket_type_id = tt.id', 'left');
        $this->db->join($this->table_issue_types . ' it', 't.issue_type_id = it.id', 'left');
        $this->db->join($this->table_workflow_states . ' ws', 't.current_state_id = ws.id', 'left');
        $this->db->join($this->table_support_teams . ' st', 't.assigned_team_id = st.id', 'left');

        // Apply filters
        if (!empty($filters['status'])) {
            $this->db->where('t.status', $filters['status']);
        }
        if (!empty($filters['priority'])) {
            $this->db->where('t.priority', $filters['priority']);
        }
        if (!empty($filters['service_group_id'])) {
            $this->db->where('t.service_group_id', $filters['service_group_id']);
        }
        if (!empty($filters['assigned_to'])) {
            $this->db->where('t.assigned_to', $filters['assigned_to']);
        }
        if (!empty($filters['requester_id'])) {
            $this->db->where('t.requester_id', $filters['requester_id']);
        }
        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('t.ticket_number', $filters['search']);
            $this->db->or_like('t.subject', $filters['search']);
            $this->db->or_like('t.description', $filters['search']);
            $this->db->group_end();
        }

        // Count total
        $total = $this->db->count_all_results('', FALSE);

        // Pagination
        $offset = ($page - 1) * $perPage;
        $this->db->limit($perPage, $offset);
        $this->db->order_by('t.created_at', 'DESC');

        $data = $this->db->get()->result();

        return [
            'data' => $data,
            'total' => $total
        ];
    }

    /**
     * Get single ticket by ID
     */
    public function get_ticket($id)
    {
        $this->db->select('
            t.*,
            sg.name as service_group_name,
            tt.name as ticket_type_name,
            it.name as issue_type_name,
            w.name as workflow_name,
            ws.name as current_state_name,
            st.name as assigned_team_name
        ');
        $this->db->from($this->table_tickets . ' t');
        $this->db->join($this->table_service_groups . ' sg', 't.service_group_id = sg.id', 'left');
        $this->db->join($this->table_ticket_types . ' tt', 't.ticket_type_id = tt.id', 'left');
        $this->db->join($this->table_issue_types . ' it', 't.issue_type_id = it.id', 'left');
        $this->db->join($this->table_workflows . ' w', 't.workflow_id = w.id', 'left');
        $this->db->join($this->table_workflow_states . ' ws', 't.current_state_id = ws.id', 'left');
        $this->db->join($this->table_support_teams . ' st', 't.assigned_team_id = st.id', 'left');
        $this->db->where('t.id', $id);
        
        return $this->db->get()->row();
    }

    /**
     * Get ticket by ticket number
     */
    public function get_ticket_by_number($ticket_number)
    {
        $this->db->where('ticket_number', $ticket_number);
        return $this->db->get($this->table_tickets)->row();
    }

    /**
     * Create new ticket
     */
    public function create_ticket($data)
    {
        $this->db->insert($this->table_tickets, $data);
        return $this->db->insert_id();
    }

    /**
     * Update ticket
     */
    public function update_ticket($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->table_tickets, $data);
    }

    /**
     * Generate unique ticket number
     */
    public function generate_ticket_number()
    {
        $prefix = 'TKT';
        $year = date('Y');
        $month = date('m');
        
        // Get last ticket number for this month
        $this->db->select('ticket_number');
        $this->db->like('ticket_number', $prefix . $year . $month, 'after');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $last = $this->db->get($this->table_tickets)->row();
        
        if ($last) {
            // Extract sequence number and increment
            $lastNumber = intval(substr($last->ticket_number, -5));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . $year . $month . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate SLA due date
     */
    public function calculate_sla_due_date($issue_type_id, $priority)
    {
        // Get SLA policy for this issue type and priority
        $this->db->select('sp.resolution_hours, sp.business_hours_only');
        $this->db->from('it_ticket_issue_type_sla its');
        $this->db->join($this->table_sla_policies . ' sp', 'its.sla_policy_id = sp.id');
        $this->db->where('its.issue_type_id', $issue_type_id);
        $this->db->where('sp.priority', $priority);
        $this->db->where('its.is_active', 1);
        $this->db->where('sp.status', 'active');
        $sla = $this->db->get()->row();
        
        if (!$sla) {
            return null;
        }
        
        $hours = $sla->resolution_hours;
        
        if ($sla->business_hours_only) {
            // Calculate business hours (8 hours/day, Mon-Fri)
            $days = ceil($hours / 8);
            $dueDate = new DateTime();
            $dueDate->modify("+{$days} weekdays");
        } else {
            // Calculate calendar hours
            $dueDate = new DateTime();
            $dueDate->modify("+{$hours} hours");
        }
        
        return $dueDate->format('Y-m-d H:i:s');
    }

    /**
     * Add ticket history
     */
    public function add_history($ticketId, $employeeId, $actionType, $data = [])
    {
        $historyData = [
            'ticket_id' => $ticketId,
            'employee_id' => $employeeId,
            'action_type' => $actionType,
            'field_name' => $data['field_name'] ?? null,
            'old_value' => $data['old_value'] ?? null,
            'new_value' => $data['new_value'] ?? null,
            'comment' => $data['comment'] ?? null,
            'metadata' => isset($data['metadata']) ? json_encode($data['metadata']) : null,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert($this->table_history, $historyData);
        return $this->db->insert_id();
    }

    /**
     * Get ticket history
     */
    public function get_ticket_history($ticketId)
    {
        $this->db->where('ticket_id', $ticketId);
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get($this->table_history)->result();
    }

    /**
     * Check if ticket number exists
     */
    public function ticket_number_exists($ticketNumber)
    {
        $this->db->where('ticket_number', $ticketNumber);
        $count = $this->db->count_all_results($this->table_tickets);
        return $count > 0;
    }

    /**
     * Get tickets count by status
     */
    public function get_count_by_status($status)
    {
        $this->db->where('status', $status);
        return $this->db->count_all_results($this->table_tickets);
    }

    /**
     * Get overdue tickets
     */
    public function get_overdue_tickets()
    {
        $this->db->where('sla_due_date <', date('Y-m-d H:i:s'));
        $this->db->where_in('status', ['new', 'assigned', 'in_progress']);
        return $this->db->get($this->table_tickets)->result();
    }
}
