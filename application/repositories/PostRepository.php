<?php

defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'interfaces/PostRepositoryInterface.php';

class PostRepository implements PostRepositoryInterface
{
    protected $CI;
    protected $table = 'posts';

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->database();
    }

    /**
     * Find all post with optional pagination
     */
    public function findAll($limit = 0, $offset = 0)
    {
        $this->CI->db->where('deleted_at IS NULL');

        if ($limit > 0) {
            $this->CI->db->limit($limit, $offset);
        }

        $query = $this->CI->db
            ->order_by('created_at', 'DESC')
            ->get($this->table);

        return $query->result_array();
    }

    /**
     * Find post by ID
     */
    public function findById($id)
    {
        $query = $this->CI->db
            ->where('id', $id)
            ->where('deleted_at IS NULL')
            ->get($this->table);

        return $query->row_array();
    }

    /**
     * Create new post
     */
    public function create(array $data)
    {
        $insert_data = $this->prepareCreateData($data);

        if ($this->CI->db->insert($this->table, $insert_data)) {
            return $this->CI->db->insert_id();
        }

        return false;
    }

    /**
     * Update post by ID
     */
    public function update($id, array $data)
    {
        if (!$this->exists($id)) {
            return false;
        }

        $update_data = $this->prepareUpdateData($data);

        if (empty($update_data)) {
            return false;
        }

        return $this->CI->db
            ->where('id', $id)
            ->where('deleted_at IS NULL')
            ->update($this->table, $update_data);
    }

    /**
     * Soft delete post by ID
     */
    public function delete($id)
    {
        if (!$this->exists($id)) {
            return false;
        }

        return $this->CI->db
            ->where('id', $id)
            ->where('deleted_at IS NULL')
            ->update($this->table, [
                'deleted_at' => date('Y-m-d H:i:s')
            ]);
    }

    /**
     * Count total posts
     */
    public function count()
    {
        return $this->CI->db
            ->where('deleted_at IS NULL')
            ->count_all_results($this->table);
    }

    /**
     * Check if post exists by ID
     */
    public function exists($id)
    {
        $count = $this->CI->db
            ->where('id', $id)
            ->where('deleted_at IS NULL')
            ->count_all_results($this->table);

        return $count > 0;
    }

    /**
     * Prepare data for creation
     * 
     * @param array $data
     * @return array
     */
    protected function prepareCreateData(array $data)
    {
        $allowed_fields = ['title', 'description'];
        $insert_data = [];

        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                $insert_data[$field] = trim($data[$field]);
            }
        }

        return $insert_data;
    }

    /**
     * Prepare data for update
     * 
     * @param array $data
     * @return array
     */
    protected function prepareUpdateData(array $data)
    {
        $allowed_fields = ['title', 'description'];
        $update_data = [];

        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                $update_data[$field] = trim($data[$field]);
            }
        }

        return $update_data;
    }
}
