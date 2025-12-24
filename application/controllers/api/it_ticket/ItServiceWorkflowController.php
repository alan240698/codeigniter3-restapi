<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ItServiceWorkflowController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('it_ticket/WorkflowModel');
        header('Content-Type: application/json');
    }

    /**
     * GET /it-service-workflows
     * Get workflow mappings with filters
     */
    public function index()
    {
        try {
            $itServiceId = $this->input->get('it_service_id');
            $mappings = $this->WorkflowModel->get_it_service_workflow_mappings($itServiceId);

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
     * GET /it-service-workflows/show/{id}
     */
    public function show($id)
    {
        try {
            $mapping = $this->WorkflowModel->get_it_service_workflow($id);

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
     * POST /it-service-workflows/store
     */
    public function store()
    {
        try {
            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);

            // Validation
            if (empty($input['it_service_id'])) {
                $this->_response(['success' => false, 'message' => 'It service is required'], 400);
                return;
            }

            if (empty($input['workflow_id'])) {
                $this->_response(['success' => false, 'message' => 'Workflow is required'], 400);
                return;
            }

            // Check if mapping already exists
            $existing = $this->WorkflowModel->get_mapping_by_it_service_and_workflow(
                $input['it_service_id'], 
                $input['workflow_id']
            );

            if ($existing) {
                $this->_response([
                    'success' => false, 
                    'message' => 'This it service is already mapped to this Workflow'
                ], 400);
                return;
            }

            // Check if it service already has an active workflow
            $activeMapping = $this->WorkflowModel->get_active_workflow_by_it_service($input['it_service_id']);
            
            if ($activeMapping && $input['is_active'] == 1) {
                // Deactivate old mapping if new mapping is active
                $this->WorkflowModel->update_it_service_workflow($activeMapping->id, ['is_active' => 0]);
            }

            $data = [
                'service_id' => $input['it_service_id'],
                'workflow_id' => $input['workflow_id'],
                'is_active' => $input['is_active'] ?? 1,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $insertId = $this->WorkflowModel->create_it_service_workflow($data);

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
     * POST /it-service-workflows/update/{id}
     */
    public function update($id)
    {
        try {
            $existing = $this->WorkflowModel->get_it_service_workflow($id);
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

            // Check if changing to a different workflow that already exists for this it service
            if ($input['workflow_id'] != $existing->workflow_id) {
                $duplicate = $this->WorkflowModel->get_mapping_by_it_service_and_workflow(
                    $existing->it_service_id, 
                    $input['workflow_id']
                );

                if ($duplicate) {
                    $this->_response([
                        'success' => false, 
                        'message' => 'This it service is already mapped to the selected Workflow'
                    ], 400);
                    return;
                }
            }

            // If activating this mapping, deactivate others for same it service
            if ($input['is_active'] == 1) {
                $otherActive = $this->WorkflowModel->get_active_workflow_by_it_service($existing->it_service_id);
                
                if ($otherActive && $otherActive->id != $id) {
                    $this->WorkflowModel->update_it_service_workflow($otherActive->id, ['is_active' => 0]);
                }
            }

            $data = [
                'workflow_id' => $input['workflow_id'],
                'is_active' => $input['is_active']
            ];

            $updated = $this->WorkflowModel->update_it_service_workflow($id, $data);

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
     * POST /it-service-workflows/delete/{id}
     */
    public function delete($id)
    {
        try {
            $existing = $this->WorkflowModel->get_it_service_workflow($id);
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

            $deleted = $this->WorkflowModel->delete_it_service_workflow($id);

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
     * GET /it-service-workflows/by-it-service/{it_service_id}
     * Get active workflow for specific it service
     */
    public function get_by_it_service($itServiceId)
    {
        try {
            $workflow = $this->WorkflowModel->get_active_workflow_by_it_service($itServiceId);

            if (!$workflow) {
                $this->_response([
                    'success' => false,
                    'message' => 'No active workflow found for this it service'
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
     * GET /it-service-workflows/stats
     * Get workflow mapping statistics
     */
    public function stats()
    {
        try {
            $stats = [
                'total_mappings' => 0,
                'active_mappings' => 0,
                'inactive_mappings' => 0,
                'mapped_it_services' => 0,
                'unmapped_it_services' => 0
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