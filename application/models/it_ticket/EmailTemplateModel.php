<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class EmailTemplateModel extends CI_Model
{
    private $table_templates = 'it_ticket_email_templates';
    private $table_instances = 'it_ticket_template_instances';
    private $table_actions = 'it_ticket_template_actions';

    /*
    |--------------------------------------------------------------------------
    | EMAIL TEMPLATES - CRUD OPERATIONS
    |--------------------------------------------------------------------------
    */

    /**
     * Get email templates with filters
     */
    public function get_templates($search = null, $status = null)
    {
        $this->db->select('*');
        $this->db->from($this->table_templates);

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('template_name', $search);
            $this->db->or_like('template_code', $search);
            $this->db->or_like('subject', $search);
            $this->db->group_end();
        }

        if (!empty($status)) {
            $this->db->where('status', $status);
        }

        $this->db->order_by('created_at', 'DESC');
        
        return $this->db->get()->result();
    }

    /**
     * Get single template by ID
     */
    public function get_template($id)
    {
        $this->db->where('id', $id);
        return $this->db->get($this->table_templates)->row();
    }

    /**
     * Get template by code
     */
    public function get_template_by_code($code)
    {
        $this->db->where('template_code', $code);
        $this->db->where('status', 'active');
        return $this->db->get($this->table_templates)->row();
    }

    /**
     * Create new template
     */
    public function create_template($data)
    {
        $this->db->insert($this->table_templates, $data);
        return $this->db->insert_id();
    }

    /**
     * Update template
     */
    public function update_template($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->table_templates, $data);
    }

    /**
     * Delete template
     */
    public function delete_template($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete($this->table_templates);
    }

    /**
     * Check if template code exists
     */
    public function template_code_exists($code, $exclude_id = null)
    {
        $this->db->where('template_code', $code);
        
        if ($exclude_id !== null) {
            $this->db->where('id !=', $exclude_id);
        }

        $count = $this->db->count_all_results($this->table_templates);
        return $count > 0;
    }

    /*
    |--------------------------------------------------------------------------
    | TEMPLATE RENDERING
    |--------------------------------------------------------------------------
    */

    /**
     * Render template with data
     * 
     * @param int $template_id Template ID
     * @param array $data Associative array of variable => value
     * @return array ['subject' => rendered_subject, 'body' => rendered_body]
     */
    public function render_template($template_id, $data = [])
    {
        $template = $this->get_template($template_id);
        
        if (!$template) {
            return null;
        }

        $subject = $template->subject;
        $body = $template->body;

        // Replace variables in format {variable_name}
        foreach ($data as $key => $value) {
            $placeholder = '{' . $key . '}';
            $subject = str_replace($placeholder, $value, $subject);
            $body = str_replace($placeholder, $value, $body);
        }

        return [
            'subject' => $subject,
            'body' => $body,
            'template_name' => $template->template_name,
            'template_code' => $template->template_code
        ];
    }

    /**
     * Get sample data for preview
     */
    public function get_sample_data()
    {
        return [
            'ticket_number' => 'TK-' . rand(10000, 99999),
            'requester_name' => 'John Doe',
            'subject' => 'Sample ticket subject',
            'priority' => 'High',
            'status' => 'Open',
            'assigned_to' => 'IT Support Team',
            'description' => 'This is a sample ticket description for preview purposes.',
            'comments' => 'Sample comment from support team.',
            'updated_at' => date('Y-m-d H:i:s'),
            'due_date' => date('Y-m-d H:i:s', strtotime('+2 days')),
            'approval_link' => base_url('template/action/SAMPLE_TOKEN?action=approve'),
            'rejection_link' => base_url('template/action/SAMPLE_TOKEN?action=reject'),
            'ticket_link' => base_url('tickets/view/123')
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | TEMPLATE INSTANCES
    |--------------------------------------------------------------------------
    */

    /**
     * Create template instance with unique token
     * 
     * @param int $template_id Template ID
     * @param array $data Data for rendering
     * @param string $recipient_email Recipient email
     * @param int $expires_days Days until expiration (default: 30)
     * @return array ['instance_id' => id, 'token' => token, 'url' => view_url]
     */
    public function create_instance($template_id, $data, $recipient_email = null, $expires_days = 30)
    {
        // Render template
        $rendered = $this->render_template($template_id, $data);
        
        if (!$rendered) {
            return null;
        }

        // Generate unique token
        $token = bin2hex(random_bytes(32));

        // Calculate expiration
        $expires_at = date('Y-m-d H:i:s', strtotime("+{$expires_days} days"));

        // Insert instance
        $instance_data = [
            'template_id' => $template_id,
            'token' => $token,
            'recipient_email' => $recipient_email,
            'rendered_subject' => $rendered['subject'],
            'rendered_body' => $rendered['body'],
            'context_data' => json_encode($data),
            'expires_at' => $expires_at,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->db->insert($this->table_instances, $instance_data);
        $instance_id = $this->db->insert_id();

        return [
            'instance_id' => $instance_id,
            'token' => $token,
            'url' => base_url('template/view/' . $token),
            'expires_at' => $expires_at
        ];
    }

    /**
     * Get instance by token
     */
    public function get_instance_by_token($token)
    {
        $this->db->select('ti.*, et.template_name, et.template_code');
        $this->db->from($this->table_instances . ' ti');
        $this->db->join($this->table_templates . ' et', 'ti.template_id = et.id', 'left');
        $this->db->where('ti.token', $token);
        
        return $this->db->get()->row();
    }

    /**
     * Check if token is valid (exists and not expired)
     */
    public function is_token_valid($token)
    {
        $instance = $this->get_instance_by_token($token);
        
        if (!$instance) {
            return false;
        }

        $now = new DateTime();
        $expires = new DateTime($instance->expires_at);

        return $now <= $expires;
    }

    /*
    |--------------------------------------------------------------------------
    | TEMPLATE ACTIONS
    |--------------------------------------------------------------------------
    */

    /**
     * Log action performed on template instance
     * 
     * @param int $instance_id Instance ID
     * @param string $action_type Action type (approve, reject, etc.)
     * @param array $action_data Additional action data
     * @return int Action ID
     */
    public function log_action($instance_id, $action_type, $action_data = [])
    {
        $log_data = [
            'instance_id' => $instance_id,
            'action_type' => $action_type,
            'action_data' => json_encode($action_data),
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
            'performed_at' => date('Y-m-d H:i:s')
        ];

        $this->db->insert($this->table_actions, $log_data);
        return $this->db->insert_id();
    }

    /**
     * Get actions for instance
     */
    public function get_instance_actions($instance_id)
    {
        $this->db->where('instance_id', $instance_id);
        $this->db->order_by('performed_at', 'DESC');
        
        return $this->db->get($this->table_actions)->result();
    }

    /**
     * Check if action already performed on instance
     */
    public function has_action($instance_id, $action_type)
    {
        $this->db->where('instance_id', $instance_id);
        $this->db->where('action_type', $action_type);
        
        $count = $this->db->count_all_results($this->table_actions);
        return $count > 0;
    }

    /**
     * Get latest action for instance
     */
    public function get_latest_action($instance_id)
    {
        $this->db->where('instance_id', $instance_id);
        $this->db->order_by('performed_at', 'DESC');
        $this->db->limit(1);
        
        return $this->db->get($this->table_actions)->row();
    }
}
