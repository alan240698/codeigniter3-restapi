<?php

defined('BASEPATH') OR exit('No direct script access allowed');

interface PostServiceInterface {
    /**
     * Get paginated post list
     * 
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function getPaginatedPosts($page = 1, $perPage = 10);

    /**
     * Get post by ID
     * 
     * @param int $id
     * @return array|null
     * @throws PostNotFoundException
     */
    public function getPostById($id);

    /**
     * Create new post with validation
     * 
     * @param array $data
     * @return array Created post data
     * @throws ValidationException
     * @throws PostAlreadyExistsException
     */
    public function createPost(array $data);

    /**
     * Update post with validation
     * 
     * @param int $id
     * @param array $data
     * @return array Updated post data
     * @throws PostNotFoundException
     * @throws ValidationException
     * @throws PostAlreadyExistsException
     */
    public function updatePost($id, array $data);

    /**
     * Delete post
     * 
     * @param int $id
     * @return bool
     * @throws PostNotFoundException
     */
    public function deletePost($id);

    /**
     * Check if post exists
     * 
     * @param int $id
     * @return bool
     */
    public function postExists($id);
}