<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class IssueTypeWorkflowController extends CI_Controller
{
    public function __construct() {
        parent::__construct();
        $this->load->model('it_ticket/WorkflowModel');
        header('Content-Type: application/json');
    }

    /**
     * GET /issue-type-workflows
     * Get workflow mappings with filters
     */
    public function index()
    {
        try {
            $issueTypeId = $this->input->get('issue_type_id');

            $mappings = $this->WorkflowModel->get_issue_type_workflow_mappings($issueTypeId);

            $this->_response([
                'success' => true,
                'data' => $mappings
            ]);

        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /issue-type-workflows/show/{id}
     */
    public function show($id)
    {
        try {
            $mapping = $this->WorkflowModel->get_issue_type_workflow($id);

            if (!$mapping) {
                $this->_response([
                    'success' => false,
                    'message' => 'Workflow Mapping not found'
                ], 404);
                return;
            }

            $this->_response([
                'success' => true,
                'data' => $mapping
            ]);

        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /issue-type-workflows/store
     */
    public function store()
    {
        try {
            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);

            // Validation
            if (empty($input['issue_type_id'])) {
                $this->_response(['success' => false, 'message' => 'Issue Type is required'], 400);
                return;
            }

            if (empty($input['workflow_id'])) {
                $this->_response(['success' => false, 'message' => 'Workflow is required'], 400);
                return;
            }

            // Check if mapping already exists
            $existing = $this->WorkflowModel->get_mapping_by_issue_and_workflow(
                $input['issue_type_id'], 
                $input['workflow_id']
            );

            if ($existing) {
                $this->_response([
                    'success' => false, 
                    'message' => 'This Issue Type is already mapped to this Workflow'
                ], 400);
                return;
            }

            // Check if issue type already has an active workflow
            $activeMapping = $this->WorkflowModel->get_active_workflow_by_issue_type($input['issue_type_id']);
            
            if ($activeMapping && $input['is_active'] == 1) {
                // Deactivate old mapping if new mapping is active
                $this->WorkflowModel->update_issue_type_workflow($activeMapping->id, ['is_active' => 0]);
            }

            $data = [
                'issue_type_id' => $input['issue_type_id'],
                'workflow_id' => $input['workflow_id'],
                'is_active' => $input['is_active'] ?? 1,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $insertId = $this->WorkflowModel->create_issue_type_workflow($data);

            if ($insertId) {
                $this->_response([
                    'success' => true,
                    'message' => 'Workflow Mapping created successfully',
                    'data' => ['id' => $insertId]
                ], 201);
            } else {
                throw new Exception('Failed to create workflow mapping');
            }

        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /issue-type-workflows/update/{id}
     */
    public function update($id)
    {
        try {
            $existing = $this->WorkflowModel->get_issue_type_workflow($id);
            if (!$existing) {
                $this->_response(['success' => false, 'message' => 'Workflow Mapping not found'], 404);
                return;
            }

            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);

            // Validation
            if (empty($input['workflow_id'])) {
                $this->_response(['success' => false, 'message' => 'Workflow is required'], 400);
                return;
            }

            // Check if changing to a different workflow that already exists for this issue type
            if ($input['workflow_id'] != $existing->workflow_id) {
                $duplicate = $this->WorkflowModel->get_mapping_by_issue_and_workflow(
                    $existing->issue_type_id, 
                    $input['workflow_id']
                );

                if ($duplicate) {
                    $this->_response([
                        'success' => false, 
                        'message' => 'This Issue Type is already mapped to the selected Workflow'
                    ], 400);
                    return;
                }
            }

            // If activating this mapping, deactivate others for same issue type
            if ($input['is_active'] == 1) {
                $otherActive = $this->WorkflowModel->get_active_workflow_by_issue_type($existing->issue_type_id);
                
                if ($otherActive && $otherActive->id != $id) {
                    $this->WorkflowModel->update_issue_type_workflow($otherActive->id, ['is_active' => 0]);
                }
            }

            $data = [
                'workflow_id' => $input['workflow_id'],
                'is_active' => $input['is_active']
            ];

            $updated = $this->WorkflowModel->update_issue_type_workflow($id, $data);

            if ($updated) {
                $this->_response(['success' => true, 'message' => 'Workflow Mapping updated successfully']);
            } else {
                throw new Exception('Failed to update workflow mapping');
            }

        } catch (Exception $e) {
            $this->_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /issue-type-workflows/delete/{id}
     */
    public function delete($id)
    {
        try {
            $existing = $this->WorkflowModel->get_issue_type_workflow($id);
            if (!$existing) {
                $this->_response(['success' => false, 'message' => 'Workflow Mapping not found'], 404);
                return;
            }

            // Optional: Check if workflow mapping is being used by active tickets
            $hasActiveTickets = $this->WorkflowModel->mapping_has_active_tickets($id);
            if ($hasActiveTickets) {
                $this->_response([
                    'success' => false, 
                    'message' => 'Cannot delete workflow mapping that is being used by active tickets'
                ], 400);
                return;
            }

            $deleted = $this->WorkflowModel->delete_issue_type_workflow($id);

            if ($deleted) {
                $this->_response(['success' => true, 'message' => 'Workflow Mapping deleted successfully']);
            } else {
                throw new Exception('Failed to delete workflow mapping');
            }

        } catch (Exception $e) {
            $this->_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /issue-type-workflows/by-issue-type/{issue_type_id}
     * Get active workflow for specific issue type
     */
    public function get_by_issue_type($issueTypeId)
    {
        try {
            $workflow = $this->WorkflowModel->get_active_workflow_by_issue_type($issueTypeId);

            if (!$workflow) {
                $this->_response([
                    'success' => false,
                    'message' => 'No active workflow found for this issue type'
                ], 404);
                return;
            }

            $this->_response([
                'success' => true,
                'data' => $workflow
            ]);

        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /issue-type-workflows/stats
     * Get workflow mapping statistics
     */
    public function stats()
    {
        try {
            $stats = [
                'total_mappings' => 0,
                'active_mappings' => 0,
                'inactive_mappings' => 0,
                'mapped_issue_types' => 0,
                'unmapped_issue_types' => 0
            ];

            $stats = $this->WorkflowModel->get_workflow_mapping_stats();

            $this->_response([
                'success' => true,
                'data' => $stats
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