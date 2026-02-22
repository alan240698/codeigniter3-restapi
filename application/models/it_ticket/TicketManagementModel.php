<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Ticket Management Model
 * Handles database queries for ticket management system
 */
class TicketManagementModel extends CI_Model
{
    /**
     * Get tickets created by user
     */
    public function get_my_created_tickets($employee_id)
    {
        $this->db->select('
            t.id,
            t.title,
            t.description,
            t.priority,
            t.created_at,
            t.updated_at,
            s.name as service_name,
            st.status_name,
            it.name as issue_type_name,
            CONCAT(e.first_name, " ", e.last_name) as assigned_name,
            team.name as team_name,
            (SELECT COUNT(*) FROM it_ticket_comments WHERE ticket_id = t.id) as comments_count,
            (SELECT COUNT(*) FROM it_ticket_attachments WHERE ticket_id = t.id) as attachments_count,
            0 as links_count
        ');
        $this->db->from('it_tickets t');
        $this->db->join('it_services s', 's.id = t.it_service_id', 'left');
        $this->db->join('it_ticket_statuses st', 'st.id = t.status_id', 'left');
        $this->db->join('it_issue_types it', 'it.id = t.issue_type_id', 'left');
        $this->db->join('hr_employees e', 'e.employee_id = t.assigned_to', 'left');
        $this->db->join('it_support_teams team', 'team.id = t.assigned_team_id', 'left');
        $this->db->where('t.requester_id', $employee_id);
        $this->db->order_by('t.created_at', 'DESC');
        
        return $this->db->get()->result();
    }

    /**
     * Get tickets assigned to user
     */
    public function get_my_assigned_tickets($employee_id)
    {
        $this->db->select('
            t.id,
            t.title,
            t.description,
            t.priority,
            t.created_at,
            t.updated_at,
            t.assigned_team_id,
            t.assigned_to,
            t.status_id,
            s.name as service_name,
            st.status_name,
            it.name as issue_type_name,
            CONCAT(e.first_name, " ", e.last_name) as assigned_name,
            CONCAT(req.first_name, " ", req.last_name) as requester_name,
            team.name as team_name,
            (SELECT COUNT(*) FROM it_ticket_comments WHERE ticket_id = t.id) as comments_count,
            (SELECT COUNT(*) FROM it_ticket_attachments WHERE ticket_id = t.id) as attachments_count,
            0 as links_count
        ');
        $this->db->from('it_tickets t');
        $this->db->join('it_services s', 's.id = t.it_service_id', 'left');
        $this->db->join('it_ticket_statuses st', 'st.id = t.status_id', 'left');
        $this->db->join('it_issue_types it', 'it.id = t.issue_type_id', 'left');
        $this->db->join('hr_employees e', 'e.employee_id = t.assigned_to', 'left');
        $this->db->join('hr_employees req', 'req.employee_id = t.requester_id', 'left');
        $this->db->join('it_support_teams team', 'team.id = t.assigned_team_id', 'left');
        $this->db->where('t.assigned_to', $employee_id);
        $this->db->where('st.status_name !=', 'CLOSED');
        $this->db->order_by('t.created_at', 'DESC');
        
        return $this->db->get()->result();
    }

    /**
     * Get tickets assigned to user's team
     */
    public function get_my_team_tickets($employee_id)
    {
        // First, get user's team(s)
        $this->db->select('team_id');
        $this->db->from('it_team_members');
        $this->db->where('employee_id', $employee_id);
        $teams = $this->db->get()->result();
        
        if (empty($teams)) {
            return [];
        }
        
        $team_ids = array_column($teams, 'team_id');
        
        // Get tickets assigned to these teams
        $this->db->select('
            t.id,
            t.title,
            t.description,
            t.priority,
            t.created_at,
            t.updated_at,
            s.name as service_name,
            st.status_name,
            it.name as issue_type_name,
            CONCAT(e.first_name, " ", e.last_name) as assigned_name,
            team.name as team_name,
            (SELECT COUNT(*) FROM it_ticket_comments WHERE ticket_id = t.id) as comments_count,
            (SELECT COUNT(*) FROM it_ticket_attachments WHERE ticket_id = t.id) as attachments_count,
            0 as links_count
        ');
        $this->db->from('it_tickets t');
        $this->db->join('it_services s', 's.id = t.it_service_id', 'left');
        $this->db->join('it_ticket_statuses st', 'st.id = t.status_id', 'left');
        $this->db->join('it_issue_types it', 'it.id = t.issue_type_id', 'left');
        $this->db->join('hr_employees e', 'e.employee_id = t.assigned_to', 'left');
        $this->db->join('it_support_teams team', 'team.id = t.assigned_team_id', 'left');
        $this->db->where_in('t.assigned_team_id', $team_ids);
        $this->db->where('st.status_name !=', 'CLOSED');
        $this->db->order_by('t.created_at', 'DESC');
        
        return $this->db->get()->result();
    }

    /**
     * Get ticket counts for all tabs
     */
    public function get_ticket_counts($employee_id)
    {
        // Count created tickets
        $this->db->where('requester_id', $employee_id);
        $created = $this->db->count_all_results('it_tickets');
        
        // Count assigned tickets
        $this->db->select('t.id');
        $this->db->from('it_tickets t');
        $this->db->join('it_ticket_statuses st', 'st.id = t.status_id', 'left');
        $this->db->where('t.assigned_to', $employee_id);
        $this->db->where('st.status_name !=', 'CLOSED');
        $assigned = $this->db->count_all_results();
        
        // Count team tickets
        $this->db->select('team_id');
        $this->db->from('it_team_members');
        $this->db->where('employee_id', $employee_id);
        $teams = $this->db->get()->result();
        
        $team = 0;
        if (!empty($teams)) {
            $team_ids = array_column($teams, 'team_id');
            
            $this->db->select('t.id');
            $this->db->from('it_tickets t');
            $this->db->join('it_ticket_statuses st', 'st.id = t.status_id', 'left');
            $this->db->where_in('t.assigned_team_id', $team_ids);
            $this->db->where('st.status_name !=', 'CLOSED');
            $team = $this->db->count_all_results();
        }
        
        return [
            'created' => $created,
            'assigned' => $assigned,
            'team' => $team
        ];
    }
}
