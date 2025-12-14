<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class SLAPolicyController extends CI_Controller
{
    public function __construct() {
        parent::__construct();
        $this->load->model('it_ticket/SLAModel');
        header('Content-Type: application/json');
    }

    /**
     * GET /sla-policies
     * Get SLA policies with filters
     */
    public function index()
    {
        try {
            $priority = $this->input->get('priority');
            $status = $this->input->get('status');

            $policies = $this->SLAModel->get_sla_policies($priority, $status);

            $this->_response([
                'success' => true,
                'data' => $policies
            ]);

        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /sla-policies/show/{id}
     */
    public function show($id)
    {
        try {
            $policy = $this->SLAModel->get_sla_policy($id);

            if (!$policy) {
                $this->_response([
                    'success' => false,
                    'message' => 'SLA Policy not found'
                ], 404);
                return;
            }

            $this->_response([
                'success' => true,
                'data' => $policy
            ]);

        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /sla-policies/store
     */
    public function store()
    {
        try {
            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);

            // Validation
            if (empty($input['name'])) {
                $this->_response(['success' => false, 'message' => 'Policy Name is required'], 400);
                return;
            }

            if (empty($input['priority'])) {
                $this->_response(['success' => false, 'message' => 'Priority is required'], 400);
                return;
            }

            if (!in_array($input['priority'], ['low', 'medium', 'high', 'critical'])) {
                $this->_response(['success' => false, 'message' => 'Invalid Priority'], 400);
                return;
            }

            if (empty($input['resolution_hours']) || $input['resolution_hours'] < 1) {
                $this->_response(['success' => false, 'message' => 'Resolution Hours must be at least 1'], 400);
                return;
            }

            // Check duplicate priority
            if ($this->SLAModel->priority_exists($input['priority'])) {
                $this->_response(['success' => false, 'message' => 'SLA Policy for this priority already exists'], 400);
                return;
            }
            
            // Validate first_response_hours < resolution_hours
            if (!empty($input['first_response_hours']) && $input['first_response_hours'] >= $input['resolution_hours']) {
                $this->_response(['success' => false, 'message' => 'First Response Hours must be less than Resolution Hours'], 400);
                return;
            }

            $data = [
                'name' => $input['name'],
                'description' => $input['description'] ?? null,
                'priority' => $input['priority'],
                'first_response_hours' => $input['first_response_hours'] ?? null,
                'resolution_hours' => $input['resolution_hours'],
                'business_hours_only' => $input['business_hours_only'] ?? 0,
                'status' => $input['status'] ?? 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $insertId = $this->SLAModel->create_sla_policy($data);

            if ($insertId) {
                $this->_response([
                    'success' => true,
                    'message' => 'SLA Policy created successfully',
                    'data' => ['id' => $insertId]
                ], 201);
            } else {
                throw new Exception('Failed to create SLA policy');
            }

        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /sla-policies/update/{id}
     */
    public function update($id)
    {
        try {
            $existing = $this->SLAModel->get_sla_policy($id);
            if (!$existing) {
                $this->_response(['success' => false, 'message' => 'SLA Policy not found'], 404);
                return;
            }

            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);

            // Validation
            if (empty($input['name'])) {
                $this->_response(['success' => false, 'message' => 'Policy Name is required'], 400);
                return;
            }

            if (!in_array($input['priority'], ['low', 'medium', 'high', 'critical'])) {
                $this->_response(['success' => false, 'message' => 'Invalid Priority'], 400);
                return;
            }

            if (empty($input['resolution_hours']) || $input['resolution_hours'] < 1) {
                $this->_response(['success' => false, 'message' => 'Resolution Hours must be at least 1'], 400);
                return;
            }

            // Check duplicate priority (exclude current)
            if ($this->SLAModel->priority_exists($input['priority'], $id)) {
                $this->_response(['success' => false, 'message' => 'SLA Policy for this priority already exists'], 400);
                return;
            }
            
            // Validate first_response_hours < resolution_hours
            if (!empty($input['first_response_hours']) && $input['first_response_hours'] >= $input['resolution_hours']) {
                $this->_response(['success' => false, 'message' => 'First Response Hours must be less than Resolution Hours'], 400);
                return;
            }

            $data = [
                'name' => $input['name'],
                'description' => $input['description'] ?? null,
                'priority' => $input['priority'],
                'first_response_hours' => $input['first_response_hours'] ?? null,
                'resolution_hours' => $input['resolution_hours'],
                'business_hours_only' => $input['business_hours_only'],
                'status' => $input['status'],
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $updated = $this->SLAModel->update_sla_policy($id, $data);

            if ($updated) {
                $this->_response(['success' => true, 'message' => 'SLA Policy updated successfully']);
            } else {
                throw new Exception('Failed to update SLA policy');
            }

        } catch (Exception $e) {
            $this->_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /sla-policies/delete/{id}
     */
    public function delete($id)
    {
        try {
            $existing = $this->SLAModel->get_sla_policy($id);
            if (!$existing) {
                $this->_response(['success' => false, 'message' => 'SLA Policy not found'], 404);
                return;
            }

            // Check if policy is being used
            if ($this->SLAModel->policy_has_mappings($id)) {
                $this->_response([
                    'success' => false,
                    'message' => 'Cannot delete SLA Policy that is mapped to issue types'
                ], 400);
                return;
            }

            $deleted = $this->SLAModel->delete_sla_policy($id);

            if ($deleted) {
                $this->_response(['success' => true, 'message' => 'SLA Policy deleted successfully']);
            } else {
                throw new Exception('Failed to delete SLA policy');
            }

        } catch (Exception $e) {
            $this->_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /sla-policies/all
     * Get all active SLA policies for dropdown
     */
    public function get_all()
    {
        try {
            $status = $this->input->get('status') ?: 'active';
            $policies = $this->SLAModel->get_all_sla_policies($status);

            $this->_response([
                'success' => true,
                'data' => $policies
            ]);

        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
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