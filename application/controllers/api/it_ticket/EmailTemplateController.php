<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class EmailTemplateController extends CI_Controller
{
    public function __construct() {
        parent::__construct();
        $this->load->model('it_ticket/EmailTemplateModel');
        header('Content-Type: application/json');
    }

    /**
     * GET /email-templates
     * Get email templates with filters
     */
    public function index()
    {
        try {
            $search = $this->input->get('search');
            $status = $this->input->get('status');

            $templates = $this->EmailTemplateModel->get_templates($search, $status);

            $this->_response([
                'success' => true,
                'data' => $templates
            ]);

        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /email-templates/show/{id}
     */
    public function show($id)
    {
        try {
            $template = $this->EmailTemplateModel->get_template($id);

            if (!$template) {
                $this->_response([
                    'success' => false,
                    'message' => 'Template not found'
                ], 404);
                return;
            }

            $this->_response([
                'success' => true,
                'data' => $template
            ]);

        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /email-templates/store
     */
    public function store()
    {
        try {
            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);

            // Validation
            if (empty($input['template_name'])) {
                $this->_response(['success' => false, 'message' => 'Template Name is required'], 400);
                return;
            }

            if (empty($input['template_code'])) {
                $this->_response(['success' => false, 'message' => 'Template Code is required'], 400);
                return;
            }

            // Validate template code format (uppercase, underscores only)
            if (!preg_match('/^[A-Z_]+$/', $input['template_code'])) {
                $this->_response(['success' => false, 'message' => 'Template Code must be uppercase with underscores only'], 400);
                return;
            }

            if (empty($input['subject'])) {
                $this->_response(['success' => false, 'message' => 'Subject is required'], 400);
                return;
            }

            if (empty($input['body'])) {
                $this->_response(['success' => false, 'message' => 'Body is required'], 400);
                return;
            }

            // Check duplicate template code
            if ($this->EmailTemplateModel->template_code_exists($input['template_code'])) {
                $this->_response(['success' => false, 'message' => 'Template Code already exists'], 400);
                return;
            }

            // Validate JSON variables if provided
            if (!empty($input['variables'])) {
                $decoded = json_decode($input['variables']);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->_response(['success' => false, 'message' => 'Invalid JSON format in variables'], 400);
                    return;
                }
            }

            $data = [
                'template_name' => $input['template_name'],
                'template_code' => $input['template_code'],
                'subject' => $input['subject'],
                'body' => $input['body'],
                'variables' => $input['variables'] ?? null,
                'status' => $input['status'] ?? 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $insertId = $this->EmailTemplateModel->create_template($data);

            if ($insertId) {
                $this->_response([
                    'success' => true,
                    'message' => 'Template created successfully',
                    'data' => ['id' => $insertId]
                ], 201);
            } else {
                throw new Exception('Failed to create template');
            }

        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /email-templates/update/{id}
     */
    public function update($id)
    {
        try {
            $existing = $this->EmailTemplateModel->get_template($id);
            if (!$existing) {
                $this->_response(['success' => false, 'message' => 'Template not found'], 404);
                return;
            }

            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);

            // Validation
            if (empty($input['template_name'])) {
                $this->_response(['success' => false, 'message' => 'Template Name is required'], 400);
                return;
            }

            if (empty($input['template_code'])) {
                $this->_response(['success' => false, 'message' => 'Template Code is required'], 400);
                return;
            }

            // Validate template code format
            if (!preg_match('/^[A-Z_]+$/', $input['template_code'])) {
                $this->_response(['success' => false, 'message' => 'Template Code must be uppercase with underscores only'], 400);
                return;
            }

            if (empty($input['subject'])) {
                $this->_response(['success' => false, 'message' => 'Subject is required'], 400);
                return;
            }

            if (empty($input['body'])) {
                $this->_response(['success' => false, 'message' => 'Body is required'], 400);
                return;
            }

            // Check duplicate template code (exclude current)
            if ($this->EmailTemplateModel->template_code_exists($input['template_code'], $id)) {
                $this->_response(['success' => false, 'message' => 'Template Code already exists'], 400);
                return;
            }

            // Validate JSON variables if provided
            if (!empty($input['variables'])) {
                $decoded = json_decode($input['variables']);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->_response(['success' => false, 'message' => 'Invalid JSON format in variables'], 400);
                    return;
                }
            }

            $data = [
                'template_name' => $input['template_name'],
                'template_code' => $input['template_code'],
                'subject' => $input['subject'],
                'body' => $input['body'],
                'variables' => $input['variables'] ?? null,
                'status' => $input['status'],
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $updated = $this->EmailTemplateModel->update_template($id, $data);

            if ($updated) {
                $this->_response(['success' => true, 'message' => 'Template updated successfully']);
            } else {
                throw new Exception('Failed to update template');
            }

        } catch (Exception $e) {
            $this->_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /email-templates/delete/{id}
     */
    public function delete($id)
    {
        try {
            $existing = $this->EmailTemplateModel->get_template($id);
            if (!$existing) {
                $this->_response(['success' => false, 'message' => 'Template not found'], 404);
                return;
            }

            $deleted = $this->EmailTemplateModel->delete_template($id);

            if ($deleted) {
                $this->_response(['success' => true, 'message' => 'Template deleted successfully']);
            } else {
                throw new Exception('Failed to delete template');
            }

        } catch (Exception $e) {
            $this->_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /email-templates/render/{id}
     * Render template with sample data for preview
     */
    public function render($id)
    {
        try {
            $template = $this->EmailTemplateModel->get_template($id);
            
            if (!$template) {
                $this->_response(['success' => false, 'message' => 'Template not found'], 404);
                return;
            }

            // Get sample data
            $sampleData = $this->EmailTemplateModel->get_sample_data();

            // Render template
            $rendered = $this->EmailTemplateModel->render_template($id, $sampleData);

            $this->_response([
                'success' => true,
                'data' => $rendered
            ]);

        } catch (Exception $e) {
            $this->_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /email-templates/create-instance
     * Create a template instance for testing
     */
    public function create_instance()
    {
        try {
            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);

            if (empty($input['template_id'])) {
                $this->_response(['success' => false, 'message' => 'Template ID is required'], 400);
                return;
            }

            $template = $this->EmailTemplateModel->get_template($input['template_id']);
            if (!$template) {
                $this->_response(['success' => false, 'message' => 'Template not found'], 404);
                return;
            }

            // Use provided data or sample data
            $data = $input['data'] ?? $this->EmailTemplateModel->get_sample_data();
            $recipient = $input['recipient_email'] ?? null;
            $expires_days = $input['expires_days'] ?? 30;

            // Create instance
            $instance = $this->EmailTemplateModel->create_instance(
                $input['template_id'],
                $data,
                $recipient,
                $expires_days
            );

            if ($instance) {
                $this->_response([
                    'success' => true,
                    'message' => 'Template instance created successfully',
                    'data' => $instance
                ], 201);
            } else {
                throw new Exception('Failed to create template instance');
            }

        } catch (Exception $e) {
            $this->_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function _response($data, $statusCode = 200)
    {
        $this->output
            ->set_status_header($statusCode)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))
            ->_display();
        exit;
    }
}
