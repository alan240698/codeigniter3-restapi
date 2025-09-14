<?php

defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'interfaces/PostServiceInterface.php';
require_once APPPATH . 'interfaces/PostRepositoryInterface.php';
require_once APPPATH . 'exceptions/PostExceptions.php';

class PostService implements PostServiceInterface
{
    /**
     * PostRepositoryInterface
     *
     * @var PostRepositoryInterface
     */
    protected $postRepository;

    /**
     * PostValidator
     *
     * @var PostValidator
     */
    protected $validator;

    /**
     * Constructor
     * 
     * @param PostRepositoryInterface $postRepository
     * @param PostValidator $validator
     */
    public function __construct(PostRepositoryInterface $postRepository, PostValidator $validator)
    {
        $this->postRepository   = $postRepository;
        $this->validator        = $validator;
    }

    /**
     * Get paginated post list
     */
    public function getPaginatedPosts($page = 1, $perPage = 10)
    {
        $page       = max(1, (int)$page);
        $perPage    = min(100, max(1, (int)$perPage));
        $offset     = ($page - 1) * $perPage;

        $posts      = $this->postRepository->findAll($perPage, $offset);
        $total      = $this->postRepository->count();

        return [
            'data'          => $posts,
            'pagination'    => [
                'current_page'  => $page,
                'per_page'      => $perPage,
                'total'         => $total,
                'total_pages'   => ceil($total / $perPage),
                'has_next'      => $page < ceil($total / $perPage),
                'has_prev'      => $page > 1
            ]
        ];
    }

    /**
     * Get post by ID
     */
    public function getPostById($id)
    {
        $this->validateId($id);

        $post = $this->postRepository->findById($id);

        if (!$post) {
            throw new PostNotFoundException("Post with ID {$id} not found");
        }

        return $post;
    }

    /**
     * Create new post with validation
     */
    public function createPost(array $data)
    {
        // Validate input data
        $validationResult = $this->validator->validateCreate($data);
        if (!$validationResult['is_valid']) {
            throw new ValidationException($validationResult['errors']);
        }

        // Create post
        $postId = $this->postRepository->create($data);

        if (!$postId) {
            throw new RuntimeException('Failed to create post');
        }

        return $this->postRepository->findById($postId);
    }

    /**
     * Update post with validation
     */
    public function updatePost($id, array $data)
    {
        $this->validateId($id);

        // Check if post exists
        if (!$this->postRepository->exists($id)) {
            throw new PostNotFoundException("Post with ID {$id} not found");
        }

        // Validate input data
        $validationResult = $this->validator->validateUpdate($data);
        if (!$validationResult['is_valid']) {
            throw new ValidationException($validationResult['errors']);
        }

        // Update post
        $success = $this->postRepository->update($id, $data);

        if (!$success) {
            throw new RuntimeException('Failed to update post');
        }

        return $this->postRepository->findById($id);
    }

    /**
     * Delete post
     */
    public function deletePost($id)
    {
        $this->validateId($id);

        if (!$this->postRepository->exists($id)) {
            throw new PostNotFoundException("Post with ID {$id} not found");
        }

        $success = $this->postRepository->delete($id);

        if (!$success) {
            throw new RuntimeException('Failed to delete post');
        }

        return true;
    }

    /**
     * Check if post exists
     */
    public function postExists($id)
    {
        $this->validateId($id);
        return $this->postRepository->exists($id);
    }

    /**
     * Validate ID parameter
     * 
     * @param mixed $id
     * @throws InvalidArgumentException
     */
    protected function validateId($id)
    {
        if (!is_numeric($id) || $id <= 0) {
            throw new InvalidArgumentException('Invalid post ID provided');
        }
    }
}
