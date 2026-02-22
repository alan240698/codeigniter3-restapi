<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class WorkflowController extends CI_Controller
{
    public function __construct() {
        parent::__construct();
        $this->load->model('it_ticket/WorkflowModel');
        header('Content-Type: application/json');
    }

    public function index()
    {
        try {
            $page = (int)$this->input->get('page') ?: 1;
            $perPage = (int)$this->input->get('per_page') ?: 10;
            $search = $this->input->get('search');
            $status = $this->input->get('status');

            $result = $this->WorkflowModel->get_workflows_paginated($page, $perPage, $search, $status);

            $this->_response([
                'success' => true,
                'data' => $result['data'],
                'total' => $result['total'],
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($result['total'] / $perPage)
            ]);

        } catch (Exception $e) {
            $this->_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $workflow = $this->WorkflowModel->get_workflow($id);

            if (!$workflow) {
                $this->_response(['success' => false, 'message' => 'Workflow not found'], 404);
                return;
            }

            $this->_response(['success' => true, 'data' => $workflow]);

        } catch (Exception $e) {
            $this->_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function store()
    {
        try {
            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);

            if (empty($input['name'])) {
                $this->_response(['success' => false, 'message' => 'Workflow Name is required'], 400);
                return;
            }

            if (empty($input['code'])) {
                $this->_response(['success' => false, 'message' => 'Code is required'], 400);
                return;
            }

            if ($this->WorkflowModel->is_code_exists($input['code'])) {
                $this->_response(['success' => false, 'message' => 'Code already exists'], 400);
                return;
            }

            if (!in_array($input['status'] ?? 'active', ['active', 'inactive'])) {
                $this->_response(['success' => false, 'message' => 'Invalid status value'], 400);
                return;
            }

            $data = [
                'name' => $input['name'],
                'code' => strtoupper($input['code']),
                'description' => $input['description'] ?? null,
                'status' => $input['status'] ?? 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $insertId = $this->WorkflowModel->create_workflow($data);

            if ($insertId) {
                $this->_response([
                    'success' => true,
                    'message' => 'Workflow created successfully',
                    'data' => ['id' => $insertId]
                ], 201);
            } else {
                throw new Exception('Failed to create workflow');
            }

        } catch (Exception $e) {
            $this->_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update($id)
    {
        try {
            $existing = $this->WorkflowModel->get_workflow($id);
            if (!$existing) {
                $this->_response(['success' => false, 'message' => 'Workflow not found'], 404);
                return;
            }

            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);

            if (empty($input['name'])) {
                $this->_response(['success' => false, 'message' => 'Workflow Name is required'], 400);
                return;
            }

            if (empty($input['code'])) {
                $this->_response(['success' => false, 'message' => 'Code is required'], 400);
                return;
            }

            if ($this->WorkflowModel->is_code_exists($input['code'], $id)) {
                $this->_response(['success' => false, 'message' => 'Code already exists'], 400);
                return;
            }

            if (!in_array($input['status'] ?? 'active', ['active', 'inactive'])) {
                $this->_response(['success' => false, 'message' => 'Invalid status value'], 400);
                return;
            }

            $data = [
                'name' => $input['name'],
                'code' => strtoupper($input['code']),
                'description' => $input['description'] ?? null,
                'status' => $input['status'],
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $updated = $this->WorkflowModel->update_workflow($id, $data);

            if ($updated) {
                $this->_response(['success' => true, 'message' => 'Workflow updated successfully']);
            } else {
                throw new Exception('Failed to update workflow');
            }

        } catch (Exception $e) {
            $this->_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function delete($id)
    {
        try {
            $existing = $this->WorkflowModel->get_workflow($id);
            if (!$existing) {
                $this->_response(['success' => false, 'message' => 'Workflow not found'], 404);
                return;
            }

            if ($this->WorkflowModel->workflow_has_tickets($id)) {
                $this->_response(['success' => false, 'message' => 'Cannot delete workflow that is being used by tickets'], 400);
                return;
            }

            if ($this->WorkflowModel->workflow_has_it_services($id)) {
                $this->_response(['success' => false, 'message' => 'Cannot delete workflow that is mapped to it services'], 400);
                return;
            }

            $deleted = $this->WorkflowModel->delete_workflow($id);

            if ($deleted) {
                $this->_response(['success' => true, 'message' => 'Workflow deleted successfully']);
            } else {
                throw new Exception('Failed to delete workflow');
            }

        } catch (Exception $e) {
            $this->_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function get_all()
    {
        try {
            $workflows = $this->WorkflowModel->get_all_workflows('active');
            $this->_response(['success' => true, 'data' => $workflows]);
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