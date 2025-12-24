<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ItServiceSLAController extends CI_Controller
{
    public function __construct() {
        parent::__construct();
        $this->load->model('it_ticket/SLAModel');
        header('Content-Type: application/json');
    }

    /**
     * GET /it-service-sla
     * Get SLA mappings with filters
     */
    public function index()
    {
        try {
            $itServiceId = $this->input->get('it_service_id');

            $mappings = $this->SLAModel->get_it_service_sla_mappings($itServiceId);

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
     * GET /it-service-sla/show/{id}
     */
    public function show($id)
    {
        try {
            $mapping = $this->SLAModel->get_it_service_sla($id);

            if (!$mapping) {
                $this->_response([
                    'success' => false,
                    'message' => 'SLA Mapping not found'
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
     * POST /it-service-sla/store
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

            if (empty($input['sla_policy_id'])) {
                $this->_response(['success' => false, 'message' => 'SLA Policy is required'], 400);
                return;
            }

            // Check if it service already has an active SLA
            $existing = $this->SLAModel->get_active_sla_by_it_service($input['it_service_id']);
            
            if ($existing && $input['is_active'] == 1) {
                // Deactivate old mapping if new mapping is active
                $this->SLAModel->update_it_service_sla($existing->id, ['is_active' => 0]);
            }

            $data = [
                'service_id' => $input['it_service_id'],
                'sla_policy_id' => $input['sla_policy_id'],
                'is_active' => $input['is_active'] ?? 1,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $insertId = $this->SLAModel->create_it_service_sla($data);

            if ($insertId) {
                $this->_response([
                    'success' => true,
                    'message' => 'SLA Mapping created successfully',
                    'data' => ['id' => $insertId]
                ], 201);
            } else {
                throw new Exception('Failed to create SLA mapping');
            }

        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /it-service-sla/update/{id}
     */
    public function update($id)
    {
        try {
            $existing = $this->SLAModel->get_it_service_sla($id);
            if (!$existing) {
                $this->_response(['success' => false, 'message' => 'SLA Mapping not found'], 404);
                return;
            }

            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);

            // Validation
            if (empty($input['sla_policy_id'])) {
                $this->_response(['success' => false, 'message' => 'SLA Policy is required'], 400);
                return;
            }

            // If activating this mapping, deactivate others for same it service
            if ($input['is_active'] == 1) {
                $otherActive = $this->SLAModel->get_active_sla_by_it_service($existing->it_service_id);
                
                if ($otherActive && $otherActive->id != $id) {
                    $this->SLAModel->update_it_service_sla($otherActive->id, ['is_active' => 0]);
                }
            }

            $data = [
                'sla_policy_id' => $input['sla_policy_id'],
                'is_active' => $input['is_active']
            ];

            $updated = $this->SLAModel->update_it_service_sla($id, $data);

            if ($updated) {
                $this->_response(['success' => true, 'message' => 'SLA Mapping updated successfully']);
            } else {
                throw new Exception('Failed to update SLA mapping');
            }

        } catch (Exception $e) {
            $this->_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /it-service-sla/delete/{id}
     */
    public function delete($id)
    {
        try {
            $existing = $this->SLAModel->get_it_service_sla($id);
            if (!$existing) {
                $this->_response(['success' => false, 'message' => 'SLA Mapping not found'], 404);
                return;
            }

            $deleted = $this->SLAModel->delete_it_service_sla($id);

            if ($deleted) {
                $this->_response(['success' => true, 'message' => 'SLA Mapping deleted successfully']);
            } else {
                throw new Exception('Failed to delete SLA mapping');
            }

        } catch (Exception $e) {
            $this->_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /it-service-sla/by-it-service/{it_service_id}
     * Get active SLA for specific it service
     */
    public function get_by_it_service($itServiceId)
    {
        try {
            $sla = $this->SLAModel->get_active_sla_by_it_service($itServiceId);

            if (!$sla) {
                $this->_response([
                    'success' => false,
                    'message' => 'No active SLA found for this it service'
                ], 404);
                return;
            }

            $this->_response([
                'success' => true,
                'data' => $sla
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