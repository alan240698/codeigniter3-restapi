<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class SupportTeamController extends CI_Controller
{
    public function __construct() {
        parent::__construct();
        $this->load->model('it_ticket/SupportTeamModel');
        header('Content-Type: application/json');
    }

    /**
     * GET /support-teams
     * Get paginated list with filters
     */
    public function index()
    {
        try {
            $page = (int)$this->input->get('page') ?: 1;
            $perPage = (int)$this->input->get('per_page') ?: 10;
            $search = $this->input->get('search');
            $supportLevel = $this->input->get('support_level');
            $country = $this->input->get('country');
            $status = $this->input->get('status');

            $result = $this->SupportTeamModel->get_teams_paginated(
                $page, 
                $perPage, 
                $search, 
                $supportLevel, 
                $country, 
                $status
            );

            $this->_response([
                'success' => true,
                'data' => $result['data'],
                'total' => $result['total'],
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($result['total'] / $perPage)
            ]);

        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /support-teams/show/{id}
     * Get single team with member count
     */
    public function show($id)
    {
        try {
            $team = $this->SupportTeamModel->get_team($id);

            if (!$team) {
                $this->_response([
                    'success' => false,
                    'message' => 'Support Team not found'
                ], 404);
                return;
            }

            $this->_response([
                'success' => true,
                'data' => $team
            ]);

        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /support-teams/store
     * Create new support team
     */
    public function store()
    {
        try {
            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);

            // Manual validation
            if (empty($input['name'])) {
                $this->_response(['success' => false, 'message' => 'Team Name is required'], 400);
                return;
            }

            if (empty($input['code'])) {
                $this->_response(['success' => false, 'message' => 'Code is required'], 400);
                return;
            }

            if ($this->SupportTeamModel->is_code_exists($input['code'])) {
                $this->_response(['success' => false, 'message' => 'Code already exists'], 400);
                return;
            }

            if (empty($input['support_level'])) {
                $this->_response(['success' => false, 'message' => 'Support Level is required'], 400);
                return;
            }

            if (!in_array($input['support_level'], ['L1', 'L2', 'L3', 'L4'])) {
                $this->_response(['success' => false, 'message' => 'Invalid Support Level'], 400);
                return;
            }

            $data = [
                'name' => $input['name'],
                'code' => strtoupper($input['code']),
                'description' => $input['description'] ?? null,
                'support_level' => $input['support_level'],
                'department_code' => $input['department_code'] ?? null,
                'office_id' => $input['office_id'] ?? null,
                'country' => $input['country'] ?? null,
                'manager_id' => $input['manager_id'] ?? null,
                'email' => $input['email'] ?? null,
                'status' => $input['status'] ?? 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $insertId = $this->SupportTeamModel->create_team($data);

            if ($insertId) {
                $this->_response([
                    'success' => true,
                    'message' => 'Support Team created successfully',
                    'data' => ['id' => $insertId]
                ], 201);
            } else {
                throw new Exception('Failed to create support team');
            }

        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /support-teams/update/{id}
     * Update support team
     */
    public function update($id)
    {
        try {
            $existing = $this->SupportTeamModel->get_team($id);
            if (!$existing) {
                $this->_response([
                    'success' => false,
                    'message' => 'Support Team not found'
                ], 404);
                return;
            }

            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);

            // Manual validation
            if (empty($input['name'])) {
                $this->_response(['success' => false, 'message' => 'Team Name is required'], 400);
                return;
            }

            if (empty($input['code'])) {
                $this->_response(['success' => false, 'message' => 'Code is required'], 400);
                return;
            }

            if ($this->SupportTeamModel->is_code_exists($input['code'], $id)) {
                $this->_response(['success' => false, 'message' => 'Code already exists'], 400);
                return;
            }

            if (!in_array($input['support_level'], ['L1', 'L2', 'L3', 'L4'])) {
                $this->_response(['success' => false, 'message' => 'Invalid Support Level'], 400);
                return;
            }

            $data = [
                'name' => $input['name'],
                'code' => strtoupper($input['code']),
                'description' => $input['description'] ?? null,
                'support_level' => $input['support_level'],
                'department_code' => $input['department_code'] ?? null,
                'office_id' => $input['office_id'] ?? null,
                'country' => $input['country'] ?? null,
                'manager_id' => $input['manager_id'] ?? null,
                'email' => $input['email'] ?? null,
                'status' => $input['status'],
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $updated = $this->SupportTeamModel->update_team($id, $data);

            if ($updated) {
                $this->_response([
                    'success' => true,
                    'message' => 'Support Team updated successfully'
                ]);
            } else {
                throw new Exception('Failed to update support team');
            }

        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /support-teams/delete/{id}
     * Delete support team
     */
    public function delete($id)
    {
        try {
            $existing = $this->SupportTeamModel->get_team($id);
            if (!$existing) {
                $this->_response([
                    'success' => false,
                    'message' => 'Support Team not found'
                ], 404);
                return;
            }

            // Check if team has members
            if ($this->SupportTeamModel->team_has_members($id)) {
                $this->_response([
                    'success' => false,
                    'message' => 'Cannot delete team with existing members. Please remove all members first.'
                ], 400);
                return;
            }

            // Check if team has assigned tickets
            if ($this->SupportTeamModel->team_has_tickets($id)) {
                $this->_response([
                    'success' => false,
                    'message' => 'Cannot delete team with assigned tickets'
                ], 400);
                return;
            }

            $deleted = $this->SupportTeamModel->delete_team($id);

            if ($deleted) {
                $this->_response([
                    'success' => true,
                    'message' => 'Support Team deleted successfully'
                ]);
            } else {
                throw new Exception('Failed to delete support team');
            }

        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /support-teams/all
     * Get all active teams for dropdown
     */
    public function get_all()
    {
        try {
            $status = $this->input->get('status') ?: 'active';
            $supportLevel = $this->input->get('support_level');

            $teams = $this->SupportTeamModel->get_all_teams($status, $supportLevel);

            $this->_response([
                'success' => true,
                'data' => $teams
            ]);

        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper: Send JSON response
     */
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