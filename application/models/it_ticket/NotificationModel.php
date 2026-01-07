<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Notification Model
 * 
 * Quản lý notification system cho IT Ticket System
 * - Tạo và gửi notifications
 * - Quản lý read/unread status
 * - Notification preferences
 * - Statistics & reporting
 */
class NotificationModel extends CI_Model
{
    private $table = 'it_ticket_notifications';

    public function __construct()
    {
        parent::__construct();
    }

    // ========================================
    // CREATE NOTIFICATIONS
    // ========================================

    /**
     * Create a new notification
     * @param array $data
     * @return int|false Notification ID or false on failure
     */
    public function create_notification($data)
    {
        $notificationData = [
            'employee_id' => $data['employee_id'],
            'ticket_id' => isset($data['ticket_id']) ? $data['ticket_id'] : null,
            'type' => $data['type'],
            'title' => $data['title'],
            'message' => $data['message'],
            'link' => isset($data['link']) ? $data['link'] : null,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->db->insert($this->table, $notificationData);
        return $this->db->insert_id();
    }

    /**
     * Bulk create notifications
     * @param array $notifications Array of notification data
     * @return bool
     */
    public function bulk_create_notifications($notifications)
    {
        if (empty($notifications)) {
            return false;
        }

        $this->db->trans_start();

        foreach ($notifications as $data) {
            $this->create_notification($data);
        }

        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    /**
     * Notify team members
     * @param int $teamId
     * @param array $notificationData
     * @return bool
     */
    public function notify_team($teamId, $notificationData)
    {
        $members = $this->db
            ->select('employee_id')
            ->from('it_ticket_team_members')
            ->where('team_id', $teamId)
            ->where('is_active', 1)
            ->get()
            ->result();

        if (empty($members)) {
            return false;
        }

        $notifications = [];
        foreach ($members as $member) {
            $notifications[] = array_merge($notificationData, [
                'employee_id' => $member->employee_id
            ]);
        }

        return $this->bulk_create_notifications($notifications);
    }

    /**
     * Send notification with preferences check
     * @param int $employeeId
     * @param string $type
     * @param array $data
     * @return bool
     */
    public function send_notification_with_preferences($employeeId, $type, $data)
    {
        // Check if user has notification preferences
        $preferences = $this->db
            ->select('notification_preferences')
            ->from('employees')
            ->where('id', $employeeId)
            ->get()
            ->row();

        if ($preferences && $preferences->notification_preferences) {
            $prefs = json_decode($preferences->notification_preferences, true);
            
            // Check if this type of notification is enabled
            if (isset($prefs[$type]) && !$prefs[$type]) {
                return false; // User has disabled this notification type
            }
        }

        // Create notification
        return $this->create_notification(array_merge($data, [
            'employee_id' => $employeeId,
            'type' => $type
        ]));
    }

    // ========================================
    // READ NOTIFICATIONS
    // ========================================

    /**
     * Get notifications for a user
     * @param int $employeeId
     * @param array $filters
     * @return array
     */
    public function get_user_notifications($employeeId, $filters = [])
    {
        $this->db->select('n.*, t.ticket_number')
            ->from($this->table . ' n')
            ->join('it_ticket_tickets t', 't.id = n.ticket_id', 'left')
            ->where('n.employee_id', $employeeId);

        // Filter by read status
        if (isset($filters['is_read'])) {
            $this->db->where('n.is_read', $filters['is_read']);
        }

        // Filter by type
        if (isset($filters['type'])) {
            $this->db->where('n.type', $filters['type']);
        }

        // Filter by date range
        if (isset($filters['from_date'])) {
            $this->db->where('n.created_at >=', $filters['from_date']);
        }

        if (isset($filters['to_date'])) {
            $this->db->where('n.created_at <=', $filters['to_date']);
        }

        $this->db->order_by('n.created_at', 'DESC');

        // Pagination
        if (isset($filters['limit'])) {
            $limit = (int)$filters['limit'];
            $offset = isset($filters['offset']) ? (int)$filters['offset'] : 0;
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result();
    }

    /**
     * Get notification by ID
     * @param int $notificationId
     * @return object|null
     */
    public function get_notification($notificationId)
    {
        return $this->db
            ->select('n.*, t.ticket_number')
            ->from($this->table . ' n')
            ->join('it_ticket_tickets t', 't.id = n.ticket_id', 'left')
            ->where('n.id', $notificationId)
            ->get()
            ->row();
    }

    /**
     * Get recent notifications for dashboard
     * @param int $employeeId
     * @param int $limit
     * @return array
     */
    public function get_recent_notifications($employeeId, $limit = 5)
    {
        return $this->db
            ->select('n.*, t.ticket_number')
            ->from($this->table . ' n')
            ->join('it_ticket_tickets t', 't.id = n.ticket_id', 'left')
            ->where('n.employee_id', $employeeId)
            ->order_by('n.created_at', 'DESC')
            ->limit($limit)
            ->get()
            ->result();
    }

    /**
     * Get unread notification count
     * @param int $employeeId
     * @return int
     */
    public function get_unread_count($employeeId)
    {
        return $this->db
            ->where('employee_id', $employeeId)
            ->where('is_read', 0)
            ->count_all_results($this->table);
    }

    // ========================================
    // UPDATE NOTIFICATIONS
    // ========================================

    /**
     * Mark notification as read
     * @param int $notificationId
     * @param int $employeeId (for security check)
     * @return bool
     */
    public function mark_as_read($notificationId, $employeeId)
    {
        $this->db->where('id', $notificationId);
        $this->db->where('employee_id', $employeeId);
        $this->db->update($this->table, [
            'is_read' => 1,
            'read_at' => date('Y-m-d H:i:s')
        ]);

        return $this->db->affected_rows() > 0;
    }

    /**
     * Mark all notifications as read for a user
     * @param int $employeeId
     * @return bool
     */
    public function mark_all_as_read($employeeId)
    {
        $this->db->where('employee_id', $employeeId);
        $this->db->where('is_read', 0);
        $this->db->update($this->table, [
            'is_read' => 1,
            'read_at' => date('Y-m-d H:i:s')
        ]);

        return $this->db->affected_rows() > 0;
    }

    /**
     * Mark notifications as unread
     * @param int $notificationId
     * @param int $employeeId
     * @return bool
     */
    public function mark_as_unread($notificationId, $employeeId)
    {
        $this->db->where('id', $notificationId);
        $this->db->where('employee_id', $employeeId);
        $this->db->update($this->table, [
            'is_read' => 0,
            'read_at' => null
        ]);

        return $this->db->affected_rows() > 0;
    }

    // ========================================
    // DELETE NOTIFICATIONS
    // ========================================

    /**
     * Delete notification
     * @param int $notificationId
     * @param int $employeeId (for security check)
     * @return bool
     */
    public function delete_notification($notificationId, $employeeId)
    {
        $this->db->where('id', $notificationId);
        $this->db->where('employee_id', $employeeId);
        $this->db->delete($this->table);

        return $this->db->affected_rows() > 0;
    }

    /**
     * Delete old read notifications (cleanup)
     * @param int $days Delete notifications older than X days
     * @return bool
     */
    public function delete_old_notifications($days = 30)
    {
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        $this->db->where('is_read', 1);
        $this->db->where('created_at <', $cutoffDate);
        $this->db->delete($this->table);

        return $this->db->affected_rows() > 0;
    }

    /**
     * Delete all notifications for a user
     * @param int $employeeId
     * @return bool
     */
    public function delete_all_notifications($employeeId)
    {
        $this->db->where('employee_id', $employeeId);
        $this->db->delete($this->table);

        return $this->db->affected_rows() > 0;
    }

    // ========================================
    // SEARCH & FILTER
    // ========================================

    /**
     * Search notifications
     * @param int $employeeId
     * @param string $keyword
     * @param array $filters
     * @return array
     */
    public function search_notifications($employeeId, $keyword, $filters = [])
    {
        $this->db
            ->select('n.*, t.ticket_number')
            ->from($this->table . ' n')
            ->join('it_ticket_tickets t', 't.id = n.ticket_id', 'left')
            ->where('n.employee_id', $employeeId);

        // Search in title and message
        if (!empty($keyword)) {
            $this->db->group_start()
                ->like('n.title', $keyword)
                ->or_like('n.message', $keyword)
                ->or_like('t.ticket_number', $keyword)
                ->group_end();
        }

        // Apply additional filters
        if (isset($filters['type'])) {
            $this->db->where('n.type', $filters['type']);
        }

        if (isset($filters['is_read'])) {
            $this->db->where('n.is_read', $filters['is_read']);
        }

        if (isset($filters['from_date'])) {
            $this->db->where('n.created_at >=', $filters['from_date']);
        }

        if (isset($filters['to_date'])) {
            $this->db->where('n.created_at <=', $filters['to_date']);
        }

        $this->db->order_by('n.created_at', 'DESC');

        return $this->db->get()->result();
    }

    // ========================================
    // STATISTICS & REPORTING
    // ========================================

    /**
     * Get notification statistics
     * @param int $employeeId
     * @return object
     */
    public function get_statistics($employeeId)
    {
        $stats = new stdClass();

        // Total notifications
        $stats->total = $this->db
            ->where('employee_id', $employeeId)
            ->count_all_results($this->table);

        // Unread count
        $stats->unread = $this->get_unread_count($employeeId);

        // Read count
        $stats->read = $stats->total - $stats->unread;

        // By type
        $typeStats = $this->db
            ->select('type, COUNT(*) as count')
            ->from($this->table)
            ->where('employee_id', $employeeId)
            ->group_by('type')
            ->get()
            ->result();

        $stats->by_type = [];
        foreach ($typeStats as $typeStat) {
            $stats->by_type[$typeStat->type] = $typeStat->count;
        }

        // Today's notifications
        $stats->today = $this->db
            ->where('employee_id', $employeeId)
            ->where('DATE(created_at)', date('Y-m-d'))
            ->count_all_results($this->table);

        // This week's notifications
        $stats->this_week = $this->db
            ->where('employee_id', $employeeId)
            ->where('created_at >=', date('Y-m-d', strtotime('monday this week')))
            ->count_all_results($this->table);

        // This month's notifications
        $stats->this_month = $this->db
            ->where('employee_id', $employeeId)
            ->where('YEAR(created_at)', date('Y'))
            ->where('MONTH(created_at)', date('m'))
            ->count_all_results($this->table);

        return $stats;
    }

    /**
     * Get notification trend (last 7 days)
     * @param int $employeeId
     * @return array
     */
    public function get_notification_trend($employeeId)
    {
        $trend = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            
            $count = $this->db
                ->where('employee_id', $employeeId)
                ->where('DATE(created_at)', $date)
                ->count_all_results($this->table);
            
            $trend[] = [
                'date' => $date,
                'count' => $count
            ];
        }
        
        return $trend;
    }

    /**
     * Get most common notification types
     * @param int $employeeId
     * @param int $limit
     * @return array
     */
    public function get_top_notification_types($employeeId, $limit = 5)
    {
        return $this->db
            ->select('type, COUNT(*) as count')
            ->from($this->table)
            ->where('employee_id', $employeeId)
            ->group_by('type')
            ->order_by('count', 'DESC')
            ->limit($limit)
            ->get()
            ->result();
    }

    // ========================================
    // NOTIFICATION PREFERENCES
    // ========================================

    /**
     * Get user notification preferences
     * @param int $employeeId
     * @return array
     */
    public function get_preferences($employeeId)
    {
        $employee = $this->db
            ->select('notification_preferences')
            ->from('employees')
            ->where('id', $employeeId)
            ->get()
            ->row();

        if ($employee && $employee->notification_preferences) {
            return json_decode($employee->notification_preferences, true);
        }

        // Default preferences
        return [
            'ticket_created' => true,
            'ticket_assigned' => true,
            'ticket_comment' => true,
            'ticket_escalated' => true,
            'ticket_approved' => true,
            'ticket_rejected' => true,
            'approval_request' => true,
            'approval_reminder' => true
        ];
    }

    /**
     * Update user notification preferences
     * @param int $employeeId
     * @param array $preferences
     * @return bool
     */
    public function update_preferences($employeeId, $preferences)
    {
        $this->db->where('id', $employeeId);
        $this->db->update('employees', [
            'notification_preferences' => json_encode($preferences)
        ]);

        return $this->db->affected_rows() > 0;
    }

    // ========================================
    // NOTIFICATION TEMPLATES
    // ========================================

    /**
     * Get notification template by type
     * @param string $type
     * @return array Template data
     */
    public function get_notification_template($type)
    {
        $templates = [
            'ticket_created' => [
                'title' => 'Ticket Created',
                'message' => 'Your ticket #{ticket_number} has been created successfully'
            ],
            'ticket_assigned' => [
                'title' => 'Ticket Assigned',
                'message' => 'Ticket #{ticket_number} has been assigned to you'
            ],
            'ticket_comment' => [
                'title' => 'New Comment',
                'message' => 'New comment on ticket #{ticket_number}'
            ],
            'ticket_escalated' => [
                'title' => 'Ticket Escalated',
                'message' => 'Ticket #{ticket_number} escalated to Level {level}'
            ],
            'ticket_approved' => [
                'title' => 'Ticket Approved',
                'message' => 'Your ticket #{ticket_number} has been approved'
            ],
            'ticket_rejected' => [
                'title' => 'Ticket Rejected',
                'message' => 'Your ticket #{ticket_number} has been rejected'
            ],
            'approval_request' => [
                'title' => 'Approval Request',
                'message' => 'Ticket #{ticket_number} requires your approval'
            ],
            'approval_reminder' => [
                'title' => 'Approval Reminder',
                'message' => 'Reminder: Ticket #{ticket_number} is waiting for your approval'
            ]
        ];

        return isset($templates[$type]) ? $templates[$type] : [
            'title' => 'Notification',
            'message' => 'You have a new notification'
        ];
    }

    /**
     * Create notification from template
     * @param int $employeeId
     * @param string $type
     * @param array $variables Replacement variables
     * @return int|false
     */
    public function create_from_template($employeeId, $type, $variables = [])
    {
        $template = $this->get_notification_template($type);
        
        // Replace variables in title and message
        $title = $template['title'];
        $message = $template['message'];
        
        foreach ($variables as $key => $value) {
            $title = str_replace('{' . $key . '}', $value, $title);
            $message = str_replace('{' . $key . '}', $value, $message);
        }

        return $this->create_notification([
            'employee_id' => $employeeId,
            'ticket_id' => isset($variables['ticket_id']) ? $variables['ticket_id'] : null,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'link' => isset($variables['link']) ? $variables['link'] : null
        ]);
    }

    // ========================================
    // REAL-TIME NOTIFICATIONS (WebSocket/SSE)
    // ========================================

    /**
     * Get new notifications since last check
     * @param int $employeeId
     * @param string $lastCheckTime
     * @return array
     */
    public function get_new_since($employeeId, $lastCheckTime)
    {
        return $this->db
            ->select('n.*, t.ticket_number')
            ->from($this->table . ' n')
            ->join('it_ticket_tickets t', 't.id = n.ticket_id', 'left')
            ->where('n.employee_id', $employeeId)
            ->where('n.created_at >', $lastCheckTime)
            ->order_by('n.created_at', 'DESC')
            ->get()
            ->result();
    }

    /**
     * Get notification count by time range
     * @param int $employeeId
     * @param int $minutes
     * @return int
     */
    public function get_count_last_minutes($employeeId, $minutes = 5)
    {
        $cutoffTime = date('Y-m-d H:i:s', strtotime("-{$minutes} minutes"));
        
        return $this->db
            ->where('employee_id', $employeeId)
            ->where('created_at >=', $cutoffTime)
            ->count_all_results($this->table);
    }
}