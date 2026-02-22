<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CustomFieldController extends CI_Controller
{
    public function __construct() {
        parent::__construct();
        $this->load->model('it_ticket/RulesModel');
        header('Content-Type: application/json');
    }

    public function index()
    {
        try {
            $itServiceId = $this->input->get('it_service_id');
            $fields = $this->RulesModel->get_custom_fields($itServiceId);
            $this->_response(['success' => true, 'data' => $fields]);
        } catch (Exception $e) {
            $this->_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $field = $this->RulesModel->get_custom_field($id);
            if (!$field) {
                $this->_response(['success' => false, 'message' => 'Custom Field not found'], 404);
                return;
            }
            $this->_response(['success' => true, 'data' => $field]);
        } catch (Exception $e) {
            $this->_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function store()
    {
        try {
            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);

            if (empty($input['it_service_id'])) {
                $this->_response(['success' => false, 'message' => 'It Service is required'], 400);
                return;
            }

            if (empty($input['field_name'])) {
                $this->_response(['success' => false, 'message' => 'Field Name is required'], 400);
                return;
            }

            if (empty($input['field_label'])) {
                $this->_response(['success' => false, 'message' => 'Field Label is required'], 400);
                return;
            }

            if (empty($input['field_type'])) {
                $this->_response(['success' => false, 'message' => 'Field Type is required'], 400);
                return;
            }

            $validTypes = ['text', 'textarea', 'number', 'date', 'select', 'radio', 'checkbox', 'file', 'user_select'];
            if (!in_array($input['field_type'], $validTypes)) {
                $this->_response(['success' => false, 'message' => 'Invalid Field Type'], 400);
                return;
            }

            // Check duplicate field name
            if ($this->RulesModel->field_name_exists($input['it_service_id'], $input['field_name'])) {
                $this->_response(['success' => false, 'message' => 'Field name already exists for this It Service'], 400);
                return;
            }

            // Validate JSON field_options
            if (!empty($input['field_options'])) {
                $decoded = json_decode($input['field_options'], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->_response(['success' => false, 'message' => 'Invalid JSON format in field options'], 400);
                    return;
                }
            }

            // Validate JSON validation_rules
            if (!empty($input['validation_rules'])) {
                $decoded = json_decode($input['validation_rules'], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->_response(['success' => false, 'message' => 'Invalid JSON format in validation rules'], 400);
                    return;
                }
            }

            $data = [
                'it_service_id' => $input['it_service_id'],
                'field_name' => $input['field_name'],
                'field_label' => $input['field_label'],
                'field_type' => $input['field_type'],
                'field_options' => $input['field_options'] ?? null,
                'is_required' => $input['is_required'] ?? 0,
                'default_value' => $input['default_value'] ?? null,
                'validation_rules' => $input['validation_rules'] ?? null,
                'placeholder' => $input['placeholder'] ?? null,
                'help_text' => $input['help_text'] ?? null,
                'sort_order' => $input['sort_order'] ?? 0,
                'status' => $input['status'] ?? 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $insertId = $this->RulesModel->create_custom_field($data);

            if ($insertId) {
                $this->_response(['success' => true, 'message' => 'Custom Field created successfully', 'data' => ['id' => $insertId]], 201);
            } else {
                throw new Exception('Failed to create custom field');
            }

        } catch (Exception $e) {
            $this->_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update($id)
    {
        try {
            $existing = $this->RulesModel->get_custom_field($id);
            if (!$existing) {
                $this->_response(['success' => false, 'message' => 'Custom Field not found'], 404);
                return;
            }

            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);

            if (empty($input['field_label'])) {
                $this->_response(['success' => false, 'message' => 'Field Label is required'], 400);
                return;
            }

            $validTypes = ['text', 'textarea', 'number', 'date', 'select', 'radio', 'checkbox', 'file', 'user_select'];
            if (!in_array($input['field_type'], $validTypes)) {
                $this->_response(['success' => false, 'message' => 'Invalid Field Type'], 400);
                return;
            }

            // Check duplicate field name (exclude current)
            if ($this->RulesModel->field_name_exists($existing->it_service_id, $input['field_name'], $id)) {
                $this->_response(['success' => false, 'message' => 'Field name already exists'], 400);
                return;
            }

            // Validate JSON
            if (!empty($input['field_options'])) {
                $decoded = json_decode($input['field_options'], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->_response(['success' => false, 'message' => 'Invalid JSON in field options'], 400);
                    return;
                }
            }

            if (!empty($input['validation_rules'])) {
                $decoded = json_decode($input['validation_rules'], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->_response(['success' => false, 'message' => 'Invalid JSON in validation rules'], 400);
                    return;
                }
            }

            $data = [
                'field_name' => $input['field_name'],
                'field_label' => $input['field_label'],
                'field_type' => $input['field_type'],
                'field_options' => $input['field_options'] ?? null,
                'is_required' => $input['is_required'],
                'default_value' => $input['default_value'] ?? null,
                'validation_rules' => $input['validation_rules'] ?? null,
                'placeholder' => $input['placeholder'] ?? null,
                'help_text' => $input['help_text'] ?? null,
                'sort_order' => $input['sort_order'],
                'status' => $input['status'],
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $updated = $this->RulesModel->update_custom_field($id, $data);

            if ($updated) {
                $this->_response(['success' => true, 'message' => 'Custom Field updated successfully']);
            } else {
                throw new Exception('Failed to update custom field');
            }

        } catch (Exception $e) {
            $this->_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function delete($id)
    {
        try {
            $existing = $this->RulesModel->get_custom_field($id);
            if (!$existing) {
                $this->_response(['success' => false, 'message' => 'Custom Field not found'], 404);
                return;
            }

            $deleted = $this->RulesModel->delete_custom_field($id);

            if ($deleted) {
                $this->_response(['success' => true, 'message' => 'Custom Field deleted successfully']);
            } else {
                throw new Exception('Failed to delete custom field');
            }

        } catch (Exception $e) {
            $this->_response(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /custom-fields/by-it-service/{it_service_id}
     */
    public function get_by_it_service($itServiceId)
    {
        try {
            $fields = $this->RulesModel->get_custom_fields($itServiceId, 'active');
            $this->_response(['success' => true, 'data' => $fields]);
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