<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class WorkflowStateController extends CI_Controller
{
    public function __construct() {
        parent::__construct();

        $this->load->library(['form_validation']);
        $this->load->model('it_ticket/WorkflowModel');

        header('Content-Type: application/json');
    }

    /**
     * GET /workflow-states?workflow_id={id}
     * Get all states for a workflow
     */
    public function index()
    {
        try {
            $workflowId = $this->input->get('workflow_id');

            if (!$workflowId) {
                $this->_response([
                    'success' => false,
                    'message' => 'Workflow ID is required'
                ], 400);
                return;
            }

            // Validate workflow exists
            if (!$this->WorkflowModel->workflow_exists($workflowId)) {
                $this->_response([
                    'success' => false,
                    'message' => 'Workflow not found'
                ], 404);
                return;
            }

            $states = $this->WorkflowModel->get_workflow_states($workflowId);

            $this->_response([
                'success'   => true,
                'data'      => $states
            ]);

        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /workflow-states/{id}
     * Get single state
     */
    public function show($id)
    {
        try {
            $state = $this->WorkflowModel->get_state($id);

            if (!$state) {
                $this->_response([
                    'success' => false,
                    'message' => 'State not found'
                ], 404);
                return;
            }

            // Type safety: Convert object to array if needed
            if (is_object($state)) {
                $state = (array)$state;
            }

            $this->_response([
                'success'   => true,
                'data'      => $state
            ]);

        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /workflow-states
     * Create new state
     */
    public function store()
    {
        try {
            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);

            // Validate input
            $_POST = $input;
            $this->form_validation->set_rules('workflow_id', 'Workflow', 'required|integer|callback_check_workflow_exists');
            $this->form_validation->set_rules('name', 'State Name', 'required|trim|max_length[50]');
            $this->form_validation->set_rules('code', 'Code', 'required|trim|max_length[50]|callback_check_unique_state_code');
            $this->form_validation->set_rules('state_type', 'State Type', 'required|in_list[initial,intermediate,final,cancelled]');
            $this->form_validation->set_rules('color', 'Color', 'trim|max_length[7]|callback_check_color_format');
            $this->form_validation->set_rules('sla_hours', 'SLA Hours', 'integer|greater_than_equal_to[0]');
            $this->form_validation->set_rules('sort_order', 'Sort Order', 'integer|greater_than_equal_to[0]');
            $this->form_validation->set_rules('description', 'Description', 'trim');

            if (!$this->form_validation->run()) {
                $this->_response([
                    'success' => false,
                    'message' => validation_errors()
                ], 400);
                return;
            }

            // Determine sort_order
            $workflow_id = (int)$input['workflow_id'];
            
            if (isset($input['sort_order']) && is_numeric($input['sort_order']) && $input['sort_order'] >= 0) {
                $sort_order = (int)$input['sort_order'];
                // Shift existing states
                $this->WorkflowModel->shift_sort_order_state($workflow_id, $sort_order, 'up');
            } else {
                // Auto-assign to end
                $sort_order = $this->WorkflowModel->get_max_sort_order_state($workflow_id) + 1;
            }

            $data = [
                'workflow_id'   => $workflow_id,
                'name'          => trim($input['name']),
                'code'          => strtoupper(trim($input['code'])),
                'description'   => isset($input['description']) && !empty($input['description']) ? trim($input['description']) : null,
                'state_type'    => $input['state_type'],
                'color'         => isset($input['color']) && !empty($input['color']) ? trim($input['color']) : '#3b82f6',
                'sla_hours'     => isset($input['sla_hours']) && $input['sla_hours'] !== '' ? (int)$input['sla_hours'] : null,
                'sort_order'    => $sort_order,
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s')
            ];

            // Validate sort_order is not null
            if ($data['sort_order'] === null || $data['sort_order'] === '') {
                throw new Exception('sort_order cannot be null');
            }

            $insertId = $this->WorkflowModel->create_state($data);

            if ($insertId) {
                $this->_response([
                    'success'   => true,
                    'message'   => 'Workflow state created successfully',
                    'data'      => ['id' => $insertId]
                ], 201);
            } else {
                throw new Exception('Failed to create workflow state');
            }

        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * PUT /workflow-states/{id}
     * Update state
     */
    public function update($id)
    {
        try {
            // Check if exists
            $existing = $this->WorkflowModel->get_state($id);
            if (!$existing) {
                $this->_response([
                    'success' => false,
                    'message' => 'State not found'
                ], 404);
                return;
            }

            // Type safety: Convert object to array if needed
            if (is_object($existing)) {
                $existing = (array)$existing;
            }

            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);

            // Validate input
            $_POST = $input;
            $this->form_validation->set_rules('workflow_id', 'Workflow', 'required|integer|callback_check_workflow_exists');
            $this->form_validation->set_rules('name', 'State Name', 'required|trim|max_length[50]');
            $this->form_validation->set_rules('code', 'Code', 'required|trim|max_length[50]|callback_check_unique_state_code_update[' . $id . ']');
            $this->form_validation->set_rules('state_type', 'State Type', 'required|in_list[initial,intermediate,final,cancelled]');
            $this->form_validation->set_rules('color', 'Color', 'trim|max_length[7]|callback_check_color_format');
            $this->form_validation->set_rules('sla_hours', 'SLA Hours', 'integer|greater_than_equal_to[0]');
            $this->form_validation->set_rules('sort_order', 'Sort Order', 'integer|greater_than_equal_to[0]');
            $this->form_validation->set_rules('description', 'Description', 'trim');

            if (!$this->form_validation->run()) {
                $this->_response([
                    'success' => false,
                    'message' => validation_errors()
                ], 400);
                return;
            }

            // Handle sort_order change
            $workflow_id    = (int)$input['workflow_id'];
            $old_sort_order = (int)$existing['sort_order'];
            $new_sort_order = isset($input['sort_order']) ? (int)$input['sort_order'] : $old_sort_order;

            // If sort_order changed, reorder other records
            if ($new_sort_order !== $old_sort_order) {
                $this->WorkflowModel->reorder_on_update_state($id, $workflow_id, $old_sort_order, $new_sort_order);
            }

            $data = [
                'workflow_id'   => $workflow_id,
                'name'          => trim($input['name']),
                'code'          => strtoupper(trim($input['code'])),
                'description'   => isset($input['description']) && !empty($input['description']) ? trim($input['description']) : null,
                'state_type'    => $input['state_type'],
                'color'         => isset($input['color']) && !empty($input['color']) ? trim($input['color']) : '#3b82f6',
                'sla_hours'     => isset($input['sla_hours']) && $input['sla_hours'] !== '' ? (int)$input['sla_hours'] : null,
                'sort_order'    => $new_sort_order,
                'updated_at'    => date('Y-m-d H:i:s')
            ];

            $updated = $this->WorkflowModel->update_state($id, $data);

            if ($updated) {
                $this->_response([
                    'success' => true,
                    'message' => 'Workflow state updated successfully'
                ]);
            } else {
                throw new Exception('Failed to update workflow state');
            }

        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /workflow-states/{id}
     * Delete state
     */
    public function delete($id)
    {
        try {
            // Check if exists
            $existing = $this->WorkflowModel->get_state($id);
            if (!$existing) {
                $this->_response([
                    'success' => false,
                    'message' => 'State not found'
                ], 404);
                return;
            }

            // Type safety: Convert object to array if needed
            if (is_object($existing)) {
                $existing = (array)$existing;
            }

            // Check if has tickets
            if ($this->WorkflowModel->state_has_tickets($id)) {
                $this->_response([
                    'success' => false,
                    'message' => 'Cannot delete state that is being used by tickets'
                ], 400);
                return;
            }

            // Check if has transitions (from or to)
            if ($this->WorkflowModel->state_has_transitions($id)) {
                $this->_response([
                    'success' => false,
                    'message' => 'Cannot delete state that has transitions. Please remove transitions first.'
                ], 400);
                return;
            }

            // Get scope for reordering
            $workflow_id = (int)$existing['workflow_id'];
            $deleted_sort_order = (int)$existing['sort_order'];

            // Delete
            $deleted = $this->WorkflowModel->delete_state($id);

            if ($deleted) {
                // Reorder remaining states
                $this->WorkflowModel->reorder_after_delete_state($workflow_id, $deleted_sort_order);

                $this->_response([
                    'success' => true,
                    'message' => 'Workflow state deleted successfully'
                ]);
            } else {
                throw new Exception('Failed to delete workflow state');
            }

        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Custom validation: Check if workflow exists
     */
    public function check_workflow_exists($workflowId)
    {
        if (!$this->WorkflowModel->workflow_exists($workflowId)) {
            $this->form_validation->set_message('check_workflow_exists', 'Workflow does not exist');
            return false;
        }

        return true;
    }

    /**
     * Custom validation: Check unique code on create
     */
    public function check_unique_state_code($code)
    {
        $workflowId = $this->input->post('workflow_id');
        if ($this->WorkflowModel->is_state_code_exists($workflowId, $code)) {
            $this->form_validation->set_message('check_unique_state_code', 'State code already exists in this workflow');
            return false;
        }

        return true;
    }

    /**
     * Custom validation: Check unique code on update
     */
    public function check_unique_state_code_update($code, $id)
    {
        $workflowId = $this->input->post('workflow_id');
        if ($this->WorkflowModel->is_state_code_exists($workflowId, $code, $id)) {
            $this->form_validation->set_message('check_unique_state_code_update', 'State code already exists in this workflow');
            return false;
        }

        return true;
    }

    /**
     * Custom validation: Check color format (hex)
     */
    public function check_color_format($color)
    {
        if (empty($color)) {
            return true; // Optional field
        }

        if (!preg_match('/^#[0-9A-F]{6}$/i', $color)) {
            $this->form_validation->set_message('check_color_format', 'Color must be in hex format (#RRGGBB)');
            return false;
        }

        return true;
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