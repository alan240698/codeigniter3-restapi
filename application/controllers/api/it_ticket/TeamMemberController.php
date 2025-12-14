<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class TeamMemberController extends CI_Controller
{
    public function __construct() {
        parent::__construct();
        $this->load->model('it_ticket/SupportTeamModel');
        header('Content-Type: application/json');
    }

    /**
     * GET /team-members
     * Get members by team_id
     */
    public function index()
    {
        try {
            $teamId = $this->input->get('team_id');

            if (!$teamId) {
                $this->_response([
                    'success' => false,
                    'message' => 'Team ID is required'
                ], 400);
                return;
            }

            $members = $this->SupportTeamModel->get_team_members($teamId);

            $this->_response([
                'success' => true,
                'data' => $members
            ]);

        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /team-members/show/{id}
     * Get single member
     */
    public function show($id)
    {
        try {
            $member = $this->SupportTeamModel->get_member($id);

            if (!$member) {
                $this->_response([
                    'success' => false,
                    'message' => 'Team Member not found'
                ], 404);
                return;
            }

            $this->_response([
                'success' => true,
                'data' => $member
            ]);

        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /team-members/store
     * Add member to team
     */
    public function store()
    {
        try {
            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);

            // Manual validation
            if (empty($input['team_id'])) {
                $this->_response(['success' => false, 'message' => 'Team ID is required'], 400);
                return;
            }

            if (empty($input['employee_id'])) {
                $this->_response(['success' => false, 'message' => 'Employee ID is required'], 400);
                return;
            }

            if (empty($input['role'])) {
                $this->_response(['success' => false, 'message' => 'Role is required'], 400);
                return;
            }

            if (!in_array($input['role'], ['member', 'lead', 'manager'])) {
                $this->_response(['success' => false, 'message' => 'Invalid role'], 400);
                return;
            }

            // Check if employee already in this team
            if ($this->SupportTeamModel->is_employee_in_team($input['team_id'], $input['employee_id'])) {
                $this->_response([
                    'success' => false,
                    'message' => 'Employee is already a member of this team'
                ], 400);
                return;
            }

            $data = [
                'team_id' => $input['team_id'],
                'employee_id' => $input['employee_id'],
                'role' => $input['role'],
                'is_active' => $input['is_active'] ?? 1,
                'joined_at' => $input['joined_at'] ?? date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $insertId = $this->SupportTeamModel->add_member($data);

            if ($insertId) {
                $this->_response([
                    'success' => true,
                    'message' => 'Team Member added successfully',
                    'data' => ['id' => $insertId]
                ], 201);
            } else {
                throw new Exception('Failed to add team member');
            }

        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /team-members/update/{id}
     * Update team member
     */
    public function update($id)
    {
        try {
            $existing = $this->SupportTeamModel->get_member($id);
            if (!$existing) {
                $this->_response([
                    'success' => false,
                    'message' => 'Team Member not found'
                ], 404);
                return;
            }

            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);

            // Manual validation
            if (empty($input['role'])) {
                $this->_response(['success' => false, 'message' => 'Role is required'], 400);
                return;
            }

            if (!in_array($input['role'], ['member', 'lead', 'manager'])) {
                $this->_response(['success' => false, 'message' => 'Invalid role'], 400);
                return;
            }

            $data = [
                'role' => $input['role'],
                'is_active' => $input['is_active'] ?? 1,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Update left_at if deactivating
            if (isset($input['is_active']) && $input['is_active'] == 0 && empty($existing->left_at)) {
                $data['left_at'] = date('Y-m-d H:i:s');
            }

            $updated = $this->SupportTeamModel->update_member($id, $data);

            if ($updated) {
                $this->_response([
                    'success' => true,
                    'message' => 'Team Member updated successfully'
                ]);
            } else {
                throw new Exception('Failed to update team member');
            }

        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /team-members/delete/{id}
     * Remove member from team
     */
    public function delete($id)
    {
        try {
            $existing = $this->SupportTeamModel->get_member($id);
            if (!$existing) {
                $this->_response([
                    'success' => false,
                    'message' => 'Team Member not found'
                ], 404);
                return;
            }

            // Soft delete: set left_at and is_active = 0
            $data = [
                'is_active' => 0,
                'left_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $updated = $this->SupportTeamModel->update_member($id, $data);

            if ($updated) {
                $this->_response([
                    'success' => true,
                    'message' => 'Team Member removed successfully'
                ]);
            } else {
                throw new Exception('Failed to remove team member');
            }

        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /team-members/by-employee/{employee_id}
     * Get all teams for a specific employee
     */
    public function get_by_employee($employeeId)
    {
        try {
            $teams = $this->SupportTeamModel->get_employee_teams($employeeId);

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