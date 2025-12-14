<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ApprovalRuleController extends CI_Controller
{
    public function __construct() {
        parent::__construct();
        $this->load->model('it_ticket/RulesModel');
        header('Content-Type: application/json');
    }

    public function index()
    {
        try {
            $issueTypeId = $this->input->get('issue_type_id');
            $rules = $this->RulesModel->get_approval_rules($issueTypeId);
            $this->_response(['success' => true, 'data' => $rules]);
        } catch (Exception $e) {
            $this->_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $rule = $this->RulesModel->get_approval_rule($id);
            if (!$rule) {
                $this->_response(['success' => false, 'message' => 'Approval Rule not found'], 404);
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

            if (empty($input['issue_type_id'])) {
                $this->_response(['success' => false, 'message' => 'Issue Type is required'], 400);
                return;
            }

            if (empty($input['rule_name'])) {
                $this->_response(['success' => false, 'message' => 'Rule Name is required'], 400);
                return;
            }

            if (!in_array($input['approval_type'], ['manager', 'specific_user', 'department_head', 'custom'])) {
                $this->_response(['success' => false, 'message' => 'Invalid Approval Type'], 400);
                return;
            }

            // Validate JSON conditions
            if (!empty($input['conditions'])) {
                $decoded = json_decode($input['conditions'], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->_response(['success' => false, 'message' => 'Invalid JSON format in conditions'], 400);
                    return;
                }
            }

            $data = [
                'issue_type_id' => $input['issue_type_id'],
                'rule_name' => $input['rule_name'],
                'requires_approval' => $input['requires_approval'] ?? 1,
                'approval_type' => $input['approval_type'],
                'approver_user_id' => $input['approver_user_id'] ?? null,
                'approver_role' => $input['approver_role'] ?? null,
                'conditions' => $input['conditions'] ?? null,
                'auto_assign_after_approval' => $input['auto_assign_after_approval'] ?? 1,
                'reminder_hours' => $input['reminder_hours'] ?? 24,
                'status' => $input['status'] ?? 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $insertId = $this->RulesModel->create_approval_rule($data);

            if ($insertId) {
                $this->_response(['success' => true, 'message' => 'Approval Rule created successfully', 'data' => ['id' => $insertId]], 201);
            } else {
                throw new Exception('Failed to create approval rule');
            }

        } catch (Exception $e) {
            $this->_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update($id)
    {
        try {
            $existing = $this->RulesModel->get_approval_rule($id);
            if (!$existing) {
                $this->_response(['success' => false, 'message' => 'Approval Rule not found'], 404);
                return;
            }

            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);

            if (empty($input['rule_name'])) {
                $this->_response(['success' => false, 'message' => 'Rule Name is required'], 400);
                return;
            }

            if (!in_array($input['approval_type'], ['manager', 'specific_user', 'department_head', 'custom'])) {
                $this->_response(['success' => false, 'message' => 'Invalid Approval Type'], 400);
                return;
            }

            // Validate JSON
            if (!empty($input['conditions'])) {
                $decoded = json_decode($input['conditions'], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->_response(['success' => false, 'message' => 'Invalid JSON format'], 400);
                    return;
                }
            }

            $data = [
                'rule_name' => $input['rule_name'],
                'requires_approval' => $input['requires_approval'],
                'approval_type' => $input['approval_type'],
                'approver_user_id' => $input['approver_user_id'] ?? null,
                'approver_role' => $input['approver_role'] ?? null,
                'conditions' => $input['conditions'] ?? null,
                'auto_assign_after_approval' => $input['auto_assign_after_approval'],
                'reminder_hours' => $input['reminder_hours'],
                'status' => $input['status'],
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $updated = $this->RulesModel->update_approval_rule($id, $data);

            if ($updated) {
                $this->_response(['success' => true, 'message' => 'Approval Rule updated successfully']);
            } else {
                throw new Exception('Failed to update approval rule');
            }

        } catch (Exception $e) {
            $this->_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function delete($id)
    {
        try {
            $existing = $this->RulesModel->get_approval_rule($id);
            if (!$existing) {
                $this->_response(['success' => false, 'message' => 'Approval Rule not found'], 404);
                return;
            }

            $deleted = $this->RulesModel->delete_approval_rule($id);

            if ($deleted) {
                $this->_response(['success' => true, 'message' => 'Approval Rule deleted successfully']);
            } else {
                throw new Exception('Failed to delete approval rule');
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