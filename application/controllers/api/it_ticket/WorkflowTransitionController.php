<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class WorkflowTransitionController extends CI_Controller
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

            $transitions = $this->WorkflowModel->get_workflow_transitions($workflowId);
            $this->_response(['success' => true, 'data' => $transitions]);

        } catch (Exception $e) {
            $this->_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $transition = $this->WorkflowModel->get_transition($id);

            if (!$transition) {
                $this->_response(['success' => false, 'message' => 'Transition not found'], 404);
                return;
            }

            $this->_response(['success' => true, 'data' => $transition]);

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
                $this->_response(['success' => false, 'message' => 'Transition Name is required'], 400);
                return;
            }

            if (empty($input['to_state_id'])) {
                $this->_response(['success' => false, 'message' => 'To State is required'], 400);
                return;
            }

            if (!empty($input['conditions'])) {
                $decoded = json_decode($input['conditions'], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->_response(['success' => false, 'message' => 'Invalid JSON format in conditions'], 400);
                    return;
                }
            }

            $data = [
                'workflow_id' => $input['workflow_id'],
                'from_state_id' => $input['from_state_id'] ?? null,
                'to_state_id' => $input['to_state_id'],
                'name' => $input['name'],
                'description' => $input['description'] ?? null,
                'required_role' => $input['required_role'] ?? null,
                'conditions' => $input['conditions'] ?? null,
                'sort_order' => $input['sort_order'] ?? 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $insertId = $this->WorkflowModel->create_transition($data);

            if ($insertId) {
                $this->_response(['success' => true, 'message' => 'Transition created successfully', 'data' => ['id' => $insertId]], 201);
            } else {
                throw new Exception('Failed to create transition');
            }

        } catch (Exception $e) {
            $this->_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update($id)
    {
        try {
            $existing = $this->WorkflowModel->get_transition($id);
            if (!$existing) {
                $this->_response(['success' => false, 'message' => 'Transition not found'], 404);
                return;
            }

            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);

            if (empty($input['name'])) {
                $this->_response(['success' => false, 'message' => 'Transition Name is required'], 400);
                return;
            }

            if (empty($input['to_state_id'])) {
                $this->_response(['success' => false, 'message' => 'To State is required'], 400);
                return;
            }

            if (!empty($input['conditions'])) {
                $decoded = json_decode($input['conditions'], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->_response(['success' => false, 'message' => 'Invalid JSON format in conditions'], 400);
                    return;
                }
            }

            $data = [
                'from_state_id' => $input['from_state_id'] ?? null,
                'to_state_id' => $input['to_state_id'],
                'name' => $input['name'],
                'description' => $input['description'] ?? null,
                'required_role' => $input['required_role'] ?? null,
                'conditions' => $input['conditions'] ?? null,
                'sort_order' => $input['sort_order'] ?? 0,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $updated = $this->WorkflowModel->update_transition($id, $data);

            if ($updated) {
                $this->_response(['success' => true, 'message' => 'Transition updated successfully']);
            } else {
                throw new Exception('Failed to update transition');
            }

        } catch (Exception $e) {
            $this->_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function delete($id)
    {
        try {
            $existing = $this->WorkflowModel->get_transition($id);
            if (!$existing) {
                $this->_response(['success' => false, 'message' => 'Transition not found'], 404);
                return;
            }

            $deleted = $this->WorkflowModel->delete_transition($id);

            if ($deleted) {
                $this->_response(['success' => true, 'message' => 'Transition deleted successfully']);
            } else {
                throw new Exception('Failed to delete transition');
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