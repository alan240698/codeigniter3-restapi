<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class LevelRuleController extends CI_Controller
{
    public function __construct() {
        parent::__construct();
        $this->load->model('it_ticket/RulesModel');
        header('Content-Type: application/json');
    }

    public function index()
    {
        try {
            $itServiceId = $this->input->get('it_service_id');
            $rules = $this->RulesModel->get_level_rules($itServiceId);
            $this->_response(['success' => true, 'data' => $rules]);
        } catch (Exception $e) {
            $this->_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $rule = $this->RulesModel->get_level_rule($id);
            if (!$rule) {
                $this->_response(['success' => false, 'message' => 'Level Rule not found'], 404);
                return;
            }
            $this->_response(['success' => true, 'data' => $rule]);
        } catch (Exception $e) {
            $this->_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function store()
    {
        try {
            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);

            if (empty($input['it_service_id'])) {
                $this->_response(['success' => false, 'message' => 'It Service is required'], 400);
                return;
            }

            if (empty($input['level_number'])) {
                $this->_response(['success' => false, 'message' => 'Level Number is required'], 400);
                return;
            }

            if (empty($input['level_name'])) {
                $this->_response(['success' => false, 'message' => 'Level Name is required'], 400);
                return;
            }

            // Check duplicate level number
            if ($this->RulesModel->level_number_exists($input['it_service_id'], $input['level_number'])) {
                $this->_response(['success' => false, 'message' => 'Level number already exists for this It Service'], 400);
                return;
            }

            $data = [
                'it_service_id' => $input['it_service_id'],
                'level_number' => $input['level_number'],
                'level_name' => $input['level_name'],
                'support_team_id' => $input['support_team_id'] ?? null,
                'auto_escalate_hours' => $input['auto_escalate_hours'] ?? null,
                'requires_escalation_reason' => $input['requires_escalation_reason'] ?? 1,
                'can_reject_escalation' => $input['can_reject_escalation'] ?? 1,
                'assignment_type' => $input['assignment_type'] ?? 'team',
                'status' => $input['status'] ?? 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $insertId = $this->RulesModel->create_level_rule($data);

            if ($insertId) {
                $this->_response(['success' => true, 'message' => 'Level Rule created successfully', 'data' => ['id' => $insertId]], 201);
            } else {
                throw new Exception('Failed to create level rule');
            }

        } catch (Exception $e) {
            $this->_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update($id)
    {
        try {
            $existing = $this->RulesModel->get_level_rule($id);
            if (!$existing) {
                $this->_response(['success' => false, 'message' => 'Level Rule not found'], 404);
                return;
            }

            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);

            if (empty($input['level_name'])) {
                $this->_response(['success' => false, 'message' => 'Level Name is required'], 400);
                return;
            }

            // Check duplicate level number (exclude current)
            if ($this->RulesModel->level_number_exists($existing->it_service_id, $input['level_number'], $id)) {
                $this->_response(['success' => false, 'message' => 'Level number already exists'], 400);
                return;
            }

            $data = [
                'level_number' => $input['level_number'],
                'level_name' => $input['level_name'],
                'support_team_id' => $input['support_team_id'] ?? null,
                'auto_escalate_hours' => $input['auto_escalate_hours'] ?? null,
                'requires_escalation_reason' => $input['requires_escalation_reason'],
                'can_reject_escalation' => $input['can_reject_escalation'],
                'assignment_type' => $input['assignment_type'],
                'status' => $input['status'],
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $updated = $this->RulesModel->update_level_rule($id, $data);

            if ($updated) {
                $this->_response(['success' => true, 'message' => 'Level Rule updated successfully']);
            } else {
                throw new Exception('Failed to update level rule');
            }

        } catch (Exception $e) {
            $this->_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function delete($id)
    {
        try {
            $existing = $this->RulesModel->get_level_rule($id);
            if (!$existing) {
                $this->_response(['success' => false, 'message' => 'Level Rule not found'], 404);
                return;
            }

            $deleted = $this->RulesModel->delete_level_rule($id);

            if ($deleted) {
                $this->_response(['success' => true, 'message' => 'Level Rule deleted successfully']);
            } else {
                throw new Exception('Failed to delete level rule');
            }

        } catch (Exception $e) {
            $this->_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /level-rules/by-it-service/{it_service_id}
     */
    public function get_by_it_service($itServiceId)
    {
        try {
            $rules = $this->RulesModel->get_level_rules($itServiceId);
            $this->_response(['success' => true, 'data' => $rules]);
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