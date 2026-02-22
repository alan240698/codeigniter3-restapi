<?php
defined('BASEPATH') or exit('No direct script access allowed');

class ItServiceController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->library(['form_validation']);
        $this->load->model('it_ticket/ServiceModel');

        // Set JSON response header
        header('Content-Type: application/json');
    }

    /**
     * GET /api/it-services
     * Get paginated list with filters
     */
    public function index()
    {
        try {
            // Get query parameters
            $page           = (int)$this->input->get('page') ?: 1;
            $perPage        = (int)$this->input->get('per_page') ?: 10;
            $search         = $this->input->get('search');
            $serviceGroupId = $this->input->get('service_group_id');
            $status         = $this->input->get('status');

            // Get filtered data
            $result = $this->ServiceModel->get_it_services_paginated(
                $page,
                $perPage,
                $search,
                $serviceGroupId,
                $status
            );

            $this->_response([
                'success'       => true,
                'data'          => $result['data'],
                'total'         => $result['total'],
                'page'          => $page,
                'per_page'      => $perPage,
                'total_pages'   => ceil($result['total'] / $perPage)
            ]);
        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/it-services/all
     * Get all it services (for dropdown)
     */
    public function get_all()
    {
        try {
            $itServices = $this->ServiceModel->get_all_group_type();

            $this->_response([
                'success'   => true,
                'data'      => $itServices
            ]);
        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/it-services/all-group-type
     * Get all it services with group info (alias for get_all)
     */
    public function get_all_with_groups()
    {
        return $this->get_all_dropdown();
    }

    /**
     * GET /api/it-services/all-dropdown
     * Get hierarchical structure for dropdown
     */
    public function get_all_dropdown()
    {
        try {
            $itServices = $this->ServiceModel->get_all_group_type();

            // Organize by service group
            $groupedData = [];

            foreach ($itServices as $service) {
                $groupId    = $service['service_group_id'];
                $groupName  = $service['service_group_name'];

                if (!isset($groupedData[$groupId])) {
                    $groupedData[$groupId] = [
                        'id'            => $groupId,
                        'name'          => $groupName,
                        'sort_order'    => $service['service_group_sort'],
                        'services'      => []
                    ];
                }

                // Add service to group
                $groupedData[$groupId]['services'][] = $service;
            }

            // Build hierarchical structure
            $hierarchicalData = [];

            foreach ($groupedData as $group) {
                // Separate parents and children within each group
                $parents    = [];
                $children   = [];

                foreach ($group['services'] as $service) {
                    if (empty($service['parent_id'])) {
                        $parents[] = $service;
                    } else {
                        if (!isset($children[$service['parent_id']])) {
                            $children[$service['parent_id']] = [];
                        }
                        $children[$service['parent_id']][] = $service;
                    }
                }

                // Build the structure for this group
                $groupData = [
                    'id'        => $group['id'],
                    'name'      => $group['name'],
                    'type'      => 'group',
                    'level'     => 0,
                    'services'  => []
                ];

                foreach ($parents as $parent) {
                    $parentData = [
                        'id'                => $parent['id'],
                        'name'              => $parent['name'],
                        'code'              => $parent['code'],
                        'requires_solution' => $parent['requires_solution'],
                        'parent_id'         => $parent['parent_id'],
                        'type'              => 'service',
                        'level'             => 1,
                        'children'          => []
                    ];

                    // Add children if exist
                    if (isset($children[$parent['id']])) {
                        foreach ($children[$parent['id']] as $child) {
                            $parentData['children'][] = [
                                'id'                => $child['id'],
                                'name'              => $child['name'],
                                'code'              => $child['code'],
                                'requires_solution' => $child['requires_solution'],
                                'parent_id'         => $child['parent_id'],
                                'type'              => 'sub_service',
                                'level'             => 2
                            ];
                        }
                    }

                    $groupData['services'][] = $parentData;
                }

                $hierarchicalData[] = $groupData;
            }

            $this->_response([
                'success'   => true,
                'data'      => $hierarchicalData
            ]);
        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/it-services/{id}
     * Get single it service
     */
    public function show($id)
    {
        try {
            $itService = $this->ServiceModel->get_it_service($id);

            if (!$itService) {
                $this->_response([
                    'success' => false,
                    'message' => 'IT Service not found'
                ], 404);
                return;
            }

            // Type safety: Convert object to array if needed
            if (is_object($itService)) {
                $itService = (array)$itService;
            }

            $this->_response([
                'success' => true,
                'data' => $itService
            ]);
        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/it-services/{id}/children
     * Get children of an it service
     */
    public function get_children($id)
    {
        try {
            $children = $this->ServiceModel->get_it_service_children($id);

            $this->_response([
                'success'   => true,
                'data'      => $children
            ]);
        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/it-services
     * Create new it service
     */
    public function store()
    {
        try {
            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);

            // Validate input
            $_POST = $input;
            $this->form_validation->set_rules('service_group_id', 'Service Group', 'required|integer|callback_check_service_group_exists');
            $this->form_validation->set_rules('parent_id', 'Parent IT Service', 'callback_check_parent_exists');
            $this->form_validation->set_rules('name', 'Name', 'required|trim|max_length[100]');
            $this->form_validation->set_rules('code', 'Code', 'required|trim|max_length[50]|callback_check_unique_it_service_code');
            $this->form_validation->set_rules('requires_solution', 'Requires solution', 'in_list[yes,no]');
            $this->form_validation->set_rules('icon', 'Icon', 'trim|max_length[50]');
            $this->form_validation->set_rules('sort_order', 'Sort Order', 'integer|greater_than_equal_to[0]');
            $this->form_validation->set_rules('status', 'Status', 'in_list[active,inactive]');
            $this->form_validation->set_rules('description', 'Description', 'trim');

            if (!$this->form_validation->run()) {
                $this->_response([
                    'success' => false,
                    'message' => validation_errors()
                ], 400);
                return;
            }

            // Determine sort_order - IMPORTANT: scope by service_group_id AND parent_id
            $service_group_id = (int)$input['service_group_id'];
            $parent_id = !empty($input['parent_id']) ? (int)$input['parent_id'] : null;

            if (isset($input['sort_order']) && is_numeric($input['sort_order']) && $input['sort_order'] >= 0) {
                // User specified sort_order
                $sort_order = (int)$input['sort_order'];
                // Shift existing items in same scope
                $this->ServiceModel->shift_sort_order_service($service_group_id, $parent_id, $sort_order, 'up');
            } else {
                // Auto-assign to end of list in this scope
                $sort_order = $this->ServiceModel->get_max_sort_order_service($service_group_id, $parent_id) + 1;
            }

            // Prepare data
            $data = [
                'service_group_id'  => $service_group_id,
                'parent_id'         => $parent_id,
                'name'              => trim($input['name']),
                'code'              => strtoupper(trim($input['code'])),
                'requires_solution' => $input['requires_solution'],
                'icon'              => isset($input['icon']) && !empty($input['icon']) ? trim($input['icon']) : null,
                'sort_order'        => $sort_order,
                'status'            => $input['status'] ?? 'active',
                'description'       => isset($input['description']) && !empty($input['description']) ? trim($input['description']) : null,
                'created_at'        => date('Y-m-d H:i:s'),
                'updated_at'        => date('Y-m-d H:i:s')
            ];

            // Validate sort_order is not null
            if ($data['sort_order'] === null || $data['sort_order'] === '') {
                throw new Exception('sort_order cannot be null');
            }

            $insertId = $this->ServiceModel->create_it_service($data);

            if ($insertId) {
                $this->_response([
                    'success'   => true,
                    'message'   => 'IT Service created successfully',
                    'data'      => ['id' => $insertId]
                ], 201);
            } else {
                throw new Exception('Failed to create IT service');
            }
        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * PUT /api/it-services/{id}
     * Update it service
     */
    public function update($id)
    {
        try {
            // Check if exists
            $existing = $this->ServiceModel->get_it_service($id);
            if (!$existing) {
                $this->_response([
                    'success' => false,
                    'message' => 'IT Service not found'
                ], 404);
                return;
            }

            // Type safety: Convert object to array if needed
            if (is_object($existing)) {
                $existing = (array)$existing;
            }

            // Get JSON input
            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);

            // Validate input
            $_POST = $input;
            $this->form_validation->set_rules('service_group_id', 'Service Group', 'required|integer|callback_check_service_group_exists');
            $this->form_validation->set_rules('parent_id', 'Parent IT Service', 'callback_check_parent_exists|callback_check_not_self_parent[' . $id . ']');
            $this->form_validation->set_rules('name', 'Name', 'required|trim|max_length[100]');
            $this->form_validation->set_rules('code', 'Code', 'required|trim|max_length[50]|callback_check_unique_it_service_code_update[' . $id . ']');
            $this->form_validation->set_rules('requires_solution', 'Requires solution', 'required|in_list[yes,no]');
            $this->form_validation->set_rules('icon', 'Icon', 'trim|max_length[50]');
            $this->form_validation->set_rules('sort_order', 'Sort Order', 'integer|greater_than_equal_to[0]');
            $this->form_validation->set_rules('status', 'Status', 'required|in_list[active,inactive]');
            $this->form_validation->set_rules('description', 'Description', 'trim');

            if (!$this->form_validation->run()) {
                $this->_response([
                    'success' => false,
                    'message' => validation_errors()
                ], 400);
                return;
            }

            // Handle sort_order change - scope by service_group_id AND parent_id
            $old_service_group_id   = (int)$existing['service_group_id'];
            $old_parent_id          = $existing['parent_id'];
            $old_sort_order         = (int)$existing['sort_order'];

            $new_service_group_id   = (int)$input['service_group_id'];
            $new_parent_id          = !empty($input['parent_id']) ? (int)$input['parent_id'] : null;
            $new_sort_order         = isset($input['sort_order']) ? (int)$input['sort_order'] : $old_sort_order;

            // Check if scope changed (service_group_id or parent_id)
            $scope_changed = ($old_service_group_id !== $new_service_group_id) || ($old_parent_id !== $new_parent_id);

            if ($scope_changed) {
                // Moving to different scope
                // 1. Reorder old scope (remove gap)
                $this->ServiceModel->reorder_after_delete_service($old_service_group_id, $old_parent_id, $old_sort_order);

                // 2. Get max in new scope and append (or use specified sort_order)
                if (isset($input['sort_order'])) {
                    $new_sort_order = (int)$input['sort_order'];
                    $this->ServiceModel->shift_sort_order_service($new_service_group_id, $new_parent_id, $new_sort_order, 'up');
                } else {
                    $new_sort_order = $this->ServiceModel->get_max_sort_order_service($new_service_group_id, $new_parent_id) + 1;
                }
            } elseif ($new_sort_order !== $old_sort_order) {
                // Same scope, different order
                $this->ServiceModel->reorder_on_update_service($id, $new_service_group_id, $new_parent_id, $old_sort_order, $new_sort_order);
            }

            // Prepare data
            $data = [
                'service_group_id'  => $new_service_group_id,
                'parent_id'         => $new_parent_id,
                'name'              => trim($input['name']),
                'code'              => strtoupper(trim($input['code'])),
                'requires_solution' => $input['requires_solution'],
                'icon'              => isset($input['icon']) && !empty($input['icon']) ? trim($input['icon']) : null,
                'status'            => $input['status'],
                'description'       => isset($input['description']) && !empty($input['description']) ? trim($input['description']) : null,
                'sort_order'        => $new_sort_order,
                'updated_at'        => date('Y-m-d H:i:s')
            ];

            $updated = $this->ServiceModel->update_it_service($id, $data);

            if ($updated) {
                $this->_response([
                    'success' => true,
                    'message' => 'IT Service updated successfully'
                ]);
            } else {
                throw new Exception('Failed to update IT service');
            }
        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/it-services/{id}
     * Delete it service (cascade delete children)
     */
    public function delete($id)
    {
        try {
            // Check if exists
            $existing = $this->ServiceModel->get_it_service($id);
            if (!$existing) {
                $this->_response([
                    'success' => false,
                    'message' => 'IT Service not found'
                ], 404);
                return;
            }

            // Type safety: Convert object to array if needed
            if (is_object($existing)) {
                $existing = (array)$existing;
            }

            // Check if has children
            $hasChildren = $this->ServiceModel->it_service_has_children($id);

            if ($hasChildren) {
                // Confirm cascade delete
                $forceDelete = $this->input->get('force');

                if ($forceDelete !== 'true') {
                    $this->_response([
                        'success'       => false,
                        'message'       => 'This IT service has sub-services. Add ?force=true to delete all.',
                        'has_children'  => true
                    ], 400);
                    return;
                }
            }

            // Get scope for reordering
            $service_group_id   = (int)$existing['service_group_id'];
            $parent_id          = $existing['parent_id'];
            $deleted_sort_order = (int)$existing['sort_order'];

            // Delete (cascade)
            $deleted = $this->ServiceModel->delete_it_service($id);

            if ($deleted) {
                // Reorder remaining items in same scope
                $this->ServiceModel->reorder_after_delete_service($service_group_id, $parent_id, $deleted_sort_order);

                $this->_response([
                    'success' => true,
                    'message' => $hasChildren
                        ? 'IT Service and all sub-services deleted successfully'
                        : 'IT Service deleted successfully'
                ]);
            } else {
                throw new Exception('Failed to delete IT service');
            }
        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if service group exists
     */
    public function check_service_group_exists($serviceGroupId)
    {
        $serviceGroup = $this->ServiceModel->get_group($serviceGroupId);
        if (!$serviceGroup) {
            $this->form_validation->set_message('check_service_group_exists', 'Service group does not exist');
            return false;
        }

        return true;
    }

    /**
     * Check if parent it service exists
     */
    public function check_parent_exists($parentId)
    {
        if (empty($parentId)) {
            return true;
        }

        $parent = $this->ServiceModel->get_it_service($parentId);
        if (!$parent) {
            $this->form_validation->set_message('check_parent_exists', 'Parent IT service does not exist');
            return false;
        }

        return true;
    }

    /**
     * Check not setting self as parent
     */
    public function check_not_self_parent($parentId, $currentId)
    {
        if (empty($parentId)) {
            return true;
        }

        if ($parentId == $currentId) {
            $this->form_validation->set_message('check_not_self_parent', 'Cannot set itself as parent');
            return false;
        }
        return true;
    }

    /**
     * Check unique code on create
     */
    public function check_unique_it_service_code($code)
    {
        if ($this->ServiceModel->is_it_service_code_exists($code)) {
            $this->form_validation->set_message('check_unique_it_service_code', 'Code already exists');
            return false;
        }

        return true;
    }

    /**
     * Check unique code on update
     */
    public function check_unique_it_service_code_update($code, $id)
    {
        if ($this->ServiceModel->is_it_service_code_exists($code, $id)) {
            $this->form_validation->set_message('check_unique_it_service_code_update', 'Code already exists');
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
