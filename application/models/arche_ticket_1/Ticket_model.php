<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Ticket Model
 * 
 * Model để quản lý ticket logs trong database
 */
class Ticket_model extends CI_Model {
    
    private $table = 'tickets_log';
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Save ticket log
     * 
     * @param array $data
     * @return int|bool
     */
    public function save_log($data) {
        $insert_data = [
            'ticket_id' => $data['ticket_id'],
            'category' => $data['category'],
            'subcategory' => $data['subcategory'],
            'description' => $data['description'],
            'user_id' => $this->session->userdata('user_id'),
            'user_name' => $this->session->userdata('user_name'),
            'created_at' => date('Y-m-d H:i:s'),
            'status' => $data['status'] ?? 'success'
        ];
        
        if ($this->db->insert($this->table, $insert_data)) {
            return $this->db->insert_id();
        }
        
        return false;
    }
    
    /**
     * Get ticket logs
     * 
     * @param array $filters
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function get_logs($filters = [], $limit = 10, $offset = 0) {
        $this->db->select('*');
        $this->db->from($this->table);
        
        if (!empty($filters['category'])) {
            $this->db->where('category', $filters['category']);
        }
        
        if (!empty($filters['user_id'])) {
            $this->db->where('user_id', $filters['user_id']);
        }
        
        if (!empty($filters['status'])) {
            $this->db->where('status', $filters['status']);
        }
        
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($limit, $offset);
        
        $query = $this->db->get();
        
        return $query->result_array();
    }
}