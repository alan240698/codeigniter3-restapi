<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class WorkflowStateController extends CI_Controller
{
    public function __construct() {
        parent::__construct();
        $this->load->model('it_ticket/WorkflowModel');
        header('Content-Type: application/json');
    }

    public function index()
    {
        try {
            $workflowId = $this->input->get('workflow_id');

            if (!$workflowId) {
                $this->_response(['success' => false, 'message' => 'Workflow ID is required'], 400);
                return;
            }

            $states = $this->WorkflowModel->get_workflow_states($workflowId);
            $this->_response(['success' => true, 'data' => $states]);

        } catch (Exception $e) {
            $this->_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $state = $this->WorkflowModel->get_state($id);

            if (!$state) {
                $this->_response(['success' => false, 'message' => 'State not found'], 404);
                return;
            }

            $this->_response(['success' => true, 'data' => $state]);

        } catch (Exception $e) {
            $this->_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function store()
    {
        try {
            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);

            if (empty($input['workflow_id'])) {
                $this->_response(['success' => false, 'message' => 'Workflow is required'], 400);
                return;
            }

            if (empty($input['name'])) {
                $this->_response(['success' => false, 'message' => 'State Name is required'], 400);
                return;
            }

            if (empty($input['code'])) {
                $this->_response(['success' => false, 'message' => 'Code is required'], 400);
                return;
            }

            if ($this->WorkflowModel->is_state_code_exists($input['workflow_id'], $input['code'])) {
                $this->_response(['success' => false, 'message' => 'State code already exists in this workflow'], 400);
                return;
            }

            if (!in_array($input['state_type'], ['initial', 'intermediate', 'final', 'cancelled'])) {
                $this->_response(['success' => false, 'message' => 'Invalid state type'], 400);
                return;
            }
            
            // Validate color format
            if (!empty($input['color']) && !preg_match('/^#[0-9A-F]{6}$/i', $input['color'])) {
                $this->_response(['success' => false, 'message' => 'Color must be in hex format (#RRGGBB)'], 400);
                return;
            }
            
            // Validate sla_hours
            if (!empty($input['sla_hours']) && (!is_numeric($input['sla_hours']) || $input['sla_hours'] < 0)) {
                $this->_response(['success' => false, 'message' => 'SLA hours must be a positive number'], 400);
                return;
            }

            $data = [
                'workflow_id' => $input['workflow_id'],
                'name' => $input['name'],
                'code' => strtolower($input['code']),
                'description' => $input['description'] ?? null,
                'state_type' => $input['state_type'],
                'color' => $input['color'] ?? '#3b82f6',
                'sla_hours' => $input['sla_hours'] ?? null,
                'sort_order' => $input['sort_order'] ?? 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $insertId = $this->WorkflowModel->create_state($data);

            if ($insertId) {
                $this->_response(['success' => true, 'message' => 'State created successfully', 'data' => ['id' => $insertId]], 201);
            } else {
                throw new Exception('Failed to create state');
            }

        } catch (Exception $e) {
            $this->_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update($id)
    {
        try {
            $existing = $this->WorkflowModel->get_state($id);
            if (!$existing) {
                $this->_response(['success' => false, 'message' => 'State not found'], 404);
                return;
            }

            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);

            if (empty($input['name'])) {
                $this->_response(['success' => false, 'message' => 'State Name is required'], 400);
                return;
            }

            if (empty($input['code'])) {
                $this->_response(['success' => false, 'message' => 'Code is required'], 400);
                return;
            }

            if ($this->WorkflowModel->is_state_code_exists($input['workflow_id'], $input['code'], $id)) {
                $this->_response(['success' => false, 'message' => 'State code already exists in this workflow'], 400);
                return;
            }

            if (!in_array($input['state_type'], ['initial', 'intermediate', 'final', 'cancelled'])) {
                $this->_response(['success' => false, 'message' => 'Invalid state type'], 400);
                return;
            }
            
            // Validate color format
            if (!empty($input['color']) && !preg_match('/^#[0-9A-F]{6}$/i', $input['color'])) {
                $this->_response(['success' => false, 'message' => 'Color must be in hex format (#RRGGBB)'], 400);
                return;
            }
            
            // Validate sla_hours
            if (!empty($input['sla_hours']) && (!is_numeric($input['sla_hours']) || $input['sla_hours'] < 0)) {
                $this->_response(['success' => false, 'message' => 'SLA hours must be a positive number'], 400);
                return;
            }

            $data = [
                'name' => $input['name'],
                'code' => strtolower($input['code']),
                'description' => $input['description'] ?? null,
                'state_type' => $input['state_type'],
                'color' => $input['color'] ?? '#3b82f6',
                'sla_hours' => $input['sla_hours'] ?? null,
                'sort_order' => $input['sort_order'] ?? 0,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $updated = $this->WorkflowModel->update_state($id, $data);

            if ($updated) {
                $this->_response(['success' => true, 'message' => 'State updated successfully']);
            } else {
                throw new Exception('Failed to update state');
            }

        } catch (Exception $e) {
            $this->_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function delete($id)
    {
        try {
            $existing = $this->WorkflowModel->get_state($id);
            if (!$existing) {
                $this->_response(['success' => false, 'message' => 'State not found'], 404);
                return;
            }

            if ($this->WorkflowModel->state_has_tickets($id)) {
                $this->_response(['success' => false, 'message' => 'Cannot delete state that is being used by tickets'], 400);
                return;
            }

            $deleted = $this->WorkflowModel->delete_state($id);

            if ($deleted) {
                $this->_response(['success' => true, 'message' => 'State deleted successfully']);
            } else {
                throw new Exception('Failed to delete state');
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