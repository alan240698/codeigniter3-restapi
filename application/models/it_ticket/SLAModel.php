<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class SLAModel extends CI_Model
{
    private $table_policies = 'it_ticket_sla_policies';
    private $table_mappings = 'it_ticket_service_sla';
    private $table_it_ticket_services = 'it_ticket_services';

    /*
    |--------------------------------------------------------------------------
    | SLA POLICIES - CRUD OPERATIONS
    |--------------------------------------------------------------------------
    */

   public function get_sla_policies($priority = null, $status = null)
{
    $this->db->select("
        sp.*,
        COUNT(DISTINCT its.id) AS it_services_count,
        CASE sp.priority
            WHEN 'critical' THEN 1
            WHEN 'high' THEN 2
            WHEN 'medium' THEN 3
            WHEN 'low' THEN 4
            ELSE 5
        END AS priority_order
    ", false);

    $this->db->from($this->table_policies . ' sp');
    $this->db->join(
        $this->table_mappings . ' its',
        'sp.id = its.sla_policy_id AND its.is_active = 1',
        'left'
    );

    if (!empty($priority)) {
        $this->db->where('sp.priority', $priority);
    }

    if (!empty($status)) {
        $this->db->where('sp.status', $status);
    }

    $this->db->group_by('sp.id');

    // Sắp xếp chuẩn
    $this->db->order_by('priority_order', 'ASC');
    $this->db->order_by('sp.created_at', 'DESC');

    return $this->db->get()->result();
}


public function get_all_sla_policies($status = 'active')
{
    $this->db->select("
        id,
        name,
        priority,
        first_response_hours,
        resolution_hours,
        CASE priority
            WHEN 'critical' THEN 1
            WHEN 'high' THEN 2
            WHEN 'medium' THEN 3
            WHEN 'low' THEN 4
            ELSE 5
        END AS priority_order
    ", false);

    $this->db->from($this->table_policies);

    if (!empty($status)) {
        $this->db->where('status', $status);
    }

    // Order by alias — CI không escape
    $this->db->order_by('priority_order', 'ASC');

    return $this->db->get()->result();
}



    /**
     * Get single SLA policy by ID
     */
    public function get_sla_policy($id)
    {
        $this->db->where('id', $id);
        return $this->db->get($this->table_policies)->row();
    }

    /**
     * Get SLA policy by priority
     */
    public function get_policy_by_priority($priority)
    {
        $this->db->where('priority', $priority);
        $this->db->where('status', 'active');
        
        return $this->db->get($this->table_policies)->row();
    }

    /**
     * Create new SLA policy
     */
    public function create_sla_policy($data)
    {
        $this->db->insert($this->table_policies, $data);
        return $this->db->insert_id();
    }

    /**
     * Update SLA policy
     */
    public function update_sla_policy($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->table_policies, $data);
    }

    /**
     * Delete SLA policy
     */
    public function delete_sla_policy($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete($this->table_policies);
    }

    /**
     * Check if priority already exists
     */
    public function priority_exists($priority, $exclude_id = null)
    {
        $this->db->where('priority', $priority);
        
        if ($exclude_id !== null) {
            $this->db->where('id !=', $exclude_id);
        }

        $count = $this->db->count_all_results($this->table_policies);
        return $count > 0;
    }

    /**
     * Check if policy has it service mappings
     */
    public function policy_has_mappings($policy_id)
    {
        $this->db->where('sla_policy_id', $policy_id);
        $count = $this->db->count_all_results($this->table_mappings);
        return $count > 0;
    }

    /*
    |--------------------------------------------------------------------------
    | IT SERVICE SLA MAPPING - CRUD OPERATIONS
    |--------------------------------------------------------------------------
    */

    /**
     * Get SLA mappings with full details
     */
    public function get_it_service_sla_mappings($it_service_id = null)
    {
        $this->db->select('
            its.*,
            it.name as it_service_name,
            sp.name as sla_policy_name,
            sp.priority,
            sp.first_response_hours,
            sp.resolution_hours,
            sp.business_hours_only
        ');
        $this->db->from($this->table_mappings . ' its');
        $this->db->join($this->table_it_ticket_services . ' it', 'its.id = it.id', 'left');
        $this->db->join($this->table_policies . ' sp', 'its.sla_policy_id = sp.id', 'left');

        if (!empty($it_service_id)) {
            $this->db->where('its.id', $it_service_id);
        }

        $this->db->order_by('its.is_active', 'DESC');
        $this->db->order_by('its.created_at', 'DESC');
        
        return $this->db->get()->result();
    }

    /**
     * Get single SLA mapping by ID
     */
    public function get_it_service_sla($id)
    {
        $this->db->where('id', $id);
        return $this->db->get($this->table_mappings)->row();
    }

    /**
     * Get active SLA for specific it service
     */
    public function get_active_sla_by_it_service($it_service_id)
    {
        $this->db->select('
            its.*,
            sp.name as sla_policy_name,
            sp.priority,
            sp.first_response_hours,
            sp.resolution_hours,
            sp.business_hours_only
        ');
        $this->db->from($this->table_mappings . ' its');
        $this->db->join($this->table_policies . ' sp', 'its.sla_policy_id = sp.id');
        $this->db->where('its.id', $it_service_id);
        $this->db->where('its.is_active', 1);
        $this->db->where('sp.status', 'active');
        
        return $this->db->get()->row();
    }

    /**
     * Create new SLA mapping
     */
    public function create_it_service_sla($data)
    {
        $this->db->insert($this->table_mappings, $data);
        return $this->db->insert_id();
    }

    /**
     * Update SLA mapping
     */
    public function update_it_service_sla($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->table_mappings, $data);
    }

    /**
     * Delete SLA mapping
     */
    public function delete_it_service_sla($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete($this->table_mappings);
    }

    /**
     * Deactivate all SLA mappings for an it service
     */
    public function deactivate_all_for_it_service($it_service_id)
    {
        $this->db->where('id', $it_service_id);
        return $this->db->update($this->table_mappings, ['is_active' => 0]);
    }

    /*
    |--------------------------------------------------------------------------
    | SLA CALCULATION HELPERS
    |--------------------------------------------------------------------------
    */

    /**
     * Calculate SLA due date
     * 
     * @param int $it_service_id it service ID
     * @param string $created_at Ticket creation datetime
     * @return array ['first_response_due' => datetime, 'resolution_due' => datetime]
     */
    public function calculate_sla_due_dates($it_service_id, $created_at)
    {
        $sla = $this->get_active_sla_by_it_service($it_service_id);

        if (!$sla) {
            return null;
        }

        $start = new DateTime($created_at);
        $businessHoursOnly = $sla->business_hours_only == 1;

        $result = [
            'first_response_due' => null,
            'resolution_due' => null,
            'sla_policy_id' => $sla->sla_policy_id,
            'priority' => $sla->priority
        ];

        // Calculate first response due date
        if ($sla->first_response_hours) {
            $firstResponseDue = clone $start;
            if ($businessHoursOnly) {
                $this->add_business_hours($firstResponseDue, $sla->first_response_hours);
            } else {
                $firstResponseDue->modify('+' . $sla->first_response_hours . ' hours');
            }
            $result['first_response_due'] = $firstResponseDue->format('Y-m-d H:i:s');
        }

        // Calculate resolution due date
        $resolutionDue = clone $start;
        if ($businessHoursOnly) {
            $this->add_business_hours($resolutionDue, $sla->resolution_hours);
        } else {
            $resolutionDue->modify('+' . $sla->resolution_hours . ' hours');
        }
        $result['resolution_due'] = $resolutionDue->format('Y-m-d H:i:s');

        return $result;
    }

    /**
     * Add business hours to a datetime
     * Business hours: 8 AM - 6 PM (10 hours/day), Monday - Friday
     * 
     * @param DateTime $datetime Reference datetime object (modified in place)
     * @param int $hours Hours to add
     */
    private function add_business_hours(&$datetime, $hours)
    {
        $hoursRemaining = $hours;
        $businessHoursPerDay = 10; // 8 AM - 6 PM

        while ($hoursRemaining > 0) {
            // Skip weekends
            if ($datetime->format('N') >= 6) { // Saturday=6, Sunday=7
                $datetime->modify('+1 day');
                $datetime->setTime(8, 0); // Start at 8 AM
                continue;
            }

            $currentHour = (int)$datetime->format('H');
            $currentMinute = (int)$datetime->format('i');

            // If before 8 AM, move to 8 AM
            if ($currentHour < 8) {
                $datetime->setTime(8, 0);
                continue;
            }

            // If after 6 PM, move to next day 8 AM
            if ($currentHour >= 18) {
                $datetime->modify('+1 day');
                $datetime->setTime(8, 0);
                continue;
            }

            // Calculate hours remaining in current business day
            $hoursLeftToday = 18 - $currentHour - ($currentMinute / 60);

            if ($hoursRemaining <= $hoursLeftToday) {
                // Can complete within today
                $datetime->modify('+' . $hoursRemaining . ' hours');
                $hoursRemaining = 0;
            } else {
                // Need to continue to next day
                $hoursRemaining -= $hoursLeftToday;
                $datetime->modify('+1 day');
                $datetime->setTime(8, 0);
            }
        }
    }

    /**
     * Check SLA status
     * 
     * @param string $due_date SLA due date
     * @param string $warning_threshold Hours before due date to show warning (default: 2)
     * @return string 'on_time', 'warning', 'overdue'
     */
    public function check_sla_status($due_date, $warning_threshold = 2)
    {
        if (empty($due_date)) {
            return 'on_time';
        }

        $now = new DateTime();
        $due = new DateTime($due_date);
        $warning = clone $due;
        $warning->modify('-' . $warning_threshold . ' hours');

        if ($now > $due) {
            return 'overdue';
        } elseif ($now > $warning) {
            return 'warning';
        } else {
            return 'on_time';
        }
    }

    /**
     * Get SLA statistics
     */
    public function get_sla_statistics()
    {
        $stats = [
            'total_policies' => 0,
            'active_policies' => 0,
            'total_mappings' => 0,
            'by_priority' => [
                'critical' => 0,
                'high' => 0,
                'medium' => 0,
                'low' => 0
            ]
        ];

        // Count policies
        $this->db->where('status', 'active');
        $stats['active_policies'] = $this->db->count_all_results($this->table_policies);
        
        $stats['total_policies'] = $this->db->count_all_results($this->table_policies);

        // Count mappings
        $stats['total_mappings'] = $this->db->count_all_results($this->table_mappings);

        // Count by priority
        $this->db->select('sp.priority, COUNT(*) as count');
        $this->db->from($this->table_mappings . ' its');
        $this->db->join($this->table_policies . ' sp', 'its.sla_policy_id = sp.id');
        $this->db->where('its.is_active', 1);
        $this->db->group_by('sp.priority');
        
        $result = $this->db->get()->result();

        foreach ($result as $row) {
            $stats['by_priority'][$row->priority] = $row->count;
        }

        return $stats;
    }
}