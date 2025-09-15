<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'interfaces/PostServiceInterface.php';
require_once APPPATH . 'exceptions/PostExceptions.php';
require_once APPPATH . 'libraries/ServiceContainer.php';

class Posts extends CI_Controller
{
    protected $postService;
    protected $responseService;
    protected $container;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        // Initialize service container
        $this->container        = ServiceContainer::getInstance();

        // Inject dependencies
        $this->postService      = $this->container->resolve('PostServiceInterface');
        $this->responseService  = $this->container->resolve('ApiResponseService');

        // Handle CORS preflight requests
        $this->responseService->handleCors();

        // EnforeCsrf
        $this->enforceCsrf();
    }

    /**
     * EnforeCSRF
     */
    protected function enforceCsrf(): void
    {
        $method = strtolower($this->input->method());
        if ($method === 'options' || $method === 'get') {
            return;
        }

        $this->verifyCsrf();
    }

    /**
     * VerifyCsrf
     */
    protected function verifyCsrf(): void
    {
        $token = $this->input->get_request_header('X-CSRF-TOKEN', true);

        // Check token
        if (!$token) {
            $json = $this->getJsonInput();
            $tokenName = $this->security->get_csrf_token_name();

            if (is_array($json) && isset($json[$tokenName])) {
                $token = $json[$tokenName];
            } else {
                $token = $this->input->post($tokenName, true);
            }
        }

        $cookieName = $this->config->item('csrf_cookie_name');
        $cookie     = $this->input->cookie($cookieName, true);
        $valid      = $token && $cookie && hash_equals((string)$cookie, (string)$token);

        if (!$valid) {
            $this->responseService->error('Invalid CSRF token', 403, 'CSRF_FAILED');
            exit;
        }
    }

    /**
     * GET /api/posts
     * List all posts with pagination
     */
    public function index()
    {
        try {
            // Validate support method
            if ($this->input->method() !== 'get') {
                $this->responseService->methodNotAllowed();
                return;
            }

            // Get query parameters
            $page       = (int)$this->input->get('page') ?: 1;
            $perPage    = (int)$this->input->get('per_page') ?: 10;

            // Get result
            $result     = $this->postService->getPaginatedPosts($page, $perPage);

            // Send paginated response
            $this->responseService->paginated(
                $result['data'],
                $result['pagination'],
                'Posts retrieved successfully'
            );
        } catch (Exception $e) {
            $this->responseService->handleException($e);
        }
    }

    /**
     * GET /api/posts/{id}
     * Get single post by ID
     */
    public function show($id = null)
    {
        try {
            // Validate support method
            if ($this->input->method() !== 'get') {
                $this->responseService->methodNotAllowed();
                return;
            }

            // Validate ID parameter
            if (!$id) {
                $this->responseService->error('Post ID is required', 400, 'BAD_REQUEST');
                return;
            }

            // Get posts
            $post = $this->postService->getPostById($id);

            // Send success response
            $this->responseService->success($post, 'Post retrieved successfully');
        } catch (Exception $e) {
            $this->responseService->handleException($e);
        }
    }

    /**
     * POST /api/posts
     * Create new posts
     */
    public function store()
    {
        try {
            // Validate support method
            if ($this->input->method() !== 'post') {
                $this->responseService->methodNotAllowed();
                return;
            }

            // Get and validate input data
            $input = $this->getJsonInput();
            if (!$input) {
                $this->responseService->error('Invalid JSON data provided', 400, 'BAD_REQUEST');
                return;
            }

            // Create post through service layer
            $post = $this->postService->createPost($input);

            // Send created response
            $this->responseService->created($post, 'Post created successfully');
            
        } catch (Exception $e) {
            $this->responseService->handleException($e);
        }
    }

    /**
     * PUT /api/posts/{id}
     * Update existing post
     */
    public function update($id = null)
    {
        try {
            // Validate support method
            if ($this->input->method() !== 'put') {
                $this->responseService->methodNotAllowed();
                return;
            }

            // Validate ID parameter
            if (!$id) {
                $this->responseService->error('Post ID is required', 400, 'BAD_REQUEST');
                return;
            }

            // Get and validate input data
            $input = $this->getJsonInput();
            if (!$input) {
                $this->responseService->error('Invalid JSON data provided', 400, 'BAD_REQUEST');
                return;
            }

            // Update post
            $post = $this->postService->updatePost($id, $input);

            // Send success response
            $this->responseService->success($post, 'Post updated successfully');
        } catch (Exception $e) {
            $this->responseService->handleException($e);
        }
    }

    /**
     * DELETE /api/posts/{id}
     * Delete post
     */
    public function delete($id = null)
    {
        try {
            // Validate support method
            if ($this->input->method() !== 'delete') {
                $this->responseService->methodNotAllowed();
                return;
            }

            // Validate ID parameter
            if (!$id) {
                $this->responseService->error('Post ID is required', 400, 'BAD_REQUEST');
                return;
            }

            // Delete post
            $this->postService->deletePost($id);

            // Send success response
            $this->responseService->success(null, 'Post deleted successfully');
            
        } catch (Exception $e) {
            $this->responseService->handleException($e);
        }
    }

    /**
     * GET /api/posts/{id}/exists
     * Check if post exists
     */
    public function exists($id = null)
    {
        try {
            // Validate support method
            if ($this->input->method() !== 'get') {
                $this->responseService->methodNotAllowed();
                return;
            }

            // Validate ID parameter
            if (!$id) {
                $this->responseService->error('Post ID is required', 400, 'BAD_REQUEST');
                return;
            }

            // Check existence
            $exists = $this->postService->postExists($id);

            // Send response
            $this->responseService->success(
                ['exists' => $exists], 
                $exists ? 'Post exists' : 'Post does not exist'
            );

        } catch (Exception $e) {
            $this->responseService->handleException($e);
        }
    }

    /**
     * Get JSON input from request body
     * 
     * @return array|null
     */
    protected function getJsonInput()
    {
        $input = $this->input->raw_input_stream;

        if (empty($input)) {
            return null;
        }

        $decoded = json_decode($input, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        return $decoded;
    }
}