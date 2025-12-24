<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class RoutingRuleController extends CI_Controller
{
    public function __construct() {
        parent::__construct();
        $this->load->model('it_ticket/RulesModel');
        header('Content-Type: application/json');
    }

    /**
     * GET /routing-rules
     * Get routing rules with filters
     */
    public function index()
    {
        try {
            $itServiceId = $this->input->get('it_service_id');
            $status = $this->input->get('status');

            $rules = $this->RulesModel->get_routing_rules($itServiceId, $status);

            $this->_response([
                'success' => true,
                'data' => $rules
            ]);

        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /routing-rules/show/{id}
     */
    public function show($id)
    {
        try {
            $rule = $this->RulesModel->get_routing_rule($id);

            if (!$rule) {
                $this->_response([
                    'success' => false,
                    'message' => 'Routing Rule not found'
                ], 404);
                return;
            }

            $this->_response([
                'success' => true,
                'data' => $rule
            ]);

        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /routing-rules/store
     */
    public function store()
    {
        try {
            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);

            // Validation
            if (empty($input['it_service_id'])) {
                $this->_response(['success' => false, 'message' => 'It Service is required'], 400);
                return;
            }

            if (empty($input['rule_name'])) {
                $this->_response(['success' => false, 'message' => 'Rule Name is required'], 400);
                return;
            }

            if (empty($input['assignment_type'])) {
                $this->_response(['success' => false, 'message' => 'Assignment Type is required'], 400);
                return;
            }

            if (!in_array($input['assignment_type'], ['team', 'user', 'round_robin', 'load_balanced'])) {
                $this->_response(['success' => false, 'message' => 'Invalid Assignment Type'], 400);
                return;
            }

            // Validate JSON conditions if provided
            if (!empty($input['conditions'])) {
                $decoded = json_decode($input['conditions'], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->_response(['success' => false, 'message' => 'Invalid JSON format in conditions'], 400);
                    return;
                }
            }

            $data = [
                'service_id' => $input['it_service_id'],
                'rule_name' => $input['rule_name'],
                'priority' => $input['priority'] ?? 100,
                'conditions' => $input['conditions'] ?? null,
                'assignment_type' => $input['assignment_type'],
                'target_team_id' => $input['target_team_id'] ?? null,
                'target_user_id' => $input['target_user_id'] ?? null,
                'status' => $input['status'] ?? 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $insertId = $this->RulesModel->create_routing_rule($data);

            if ($insertId) {
                $this->_response([
                    'success' => true,
                    'message' => 'Routing Rule created successfully',
                    'data' => ['id' => $insertId]
                ], 201);
            } else {
                throw new Exception('Failed to create routing rule');
            }

        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /routing-rules/update/{id}
     */
    public function update($id)
    {
        try {
            $existing = $this->RulesModel->get_routing_rule($id);
            if (!$existing) {
                $this->_response(['success' => false, 'message' => 'Routing Rule not found'], 404);
                return;
            }

            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);

            // Validation
            if (empty($input['rule_name'])) {
                $this->_response(['success' => false, 'message' => 'Rule Name is required'], 400);
                return;
            }

            if (!in_array($input['assignment_type'], ['team', 'user', 'round_robin', 'load_balanced'])) {
                $this->_response(['success' => false, 'message' => 'Invalid Assignment Type'], 400);
                return;
            }

            // Validate JSON
            if (!empty($input['conditions'])) {
                $decoded = json_decode($input['conditions'], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->_response(['success' => false, 'message' => 'Invalid JSON format in conditions'], 400);
                    return;
                }
            }

            $data = [
                'rule_name' => $input['rule_name'],
                'priority' => $input['priority'] ?? 100,
                'conditions' => $input['conditions'] ?? null,
                'assignment_type' => $input['assignment_type'],
                'target_team_id' => $input['target_team_id'] ?? null,
                'target_user_id' => $input['target_user_id'] ?? null,
                'status' => $input['status'],
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $updated = $this->RulesModel->update_routing_rule($id, $data);

            if ($updated) {
                $this->_response(['success' => true, 'message' => 'Routing Rule updated successfully']);
            } else {
                throw new Exception('Failed to update routing rule');
            }

        } catch (Exception $e) {
            $this->_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /routing-rules/delete/{id}
     */
    public function delete($id)
    {
        try {
            $existing = $this->RulesModel->get_routing_rule($id);
            if (!$existing) {
                $this->_response(['success' => false, 'message' => 'Routing Rule not found'], 404);
                return;
            }

            $deleted = $this->RulesModel->delete_routing_rule($id);

            if ($deleted) {
                $this->_response(['success' => true, 'message' => 'Routing Rule deleted successfully']);
            } else {
                throw new Exception('Failed to delete routing rule');
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