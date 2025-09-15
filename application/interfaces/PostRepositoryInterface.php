<?php

defined('BASEPATH') OR exit('No direct script access allowed');

interface PostRepositoryInterface {
    /**
     * Find all post with optional pagination
     * 
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function findAll($limit = 0, $offset = 0);

    /**
     * Find post by ID
     * 
     * @param int $id
     * @return array|null
     */
    public function findById($id);

    /**
     * Create new post
     * 
     * @param array $data
     * @return int|false Post ID or false on failure
     */
    public function create(array $data);

    /**
     * Update post by ID
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, array $data);

    /**
     * Delete post by ID
     * 
     * @param int $id
     * @return bool
     */
    public function delete($id);

    /**
     * Count total posts
     * 
     * @return int
     */
    public function count();

    /**
     * Check if post exists by ID
     * 
     * @param int $id
     * @return bool
     */
    public function exists($id);
}