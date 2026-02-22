<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class EmployeeController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('it_ticket/HrEmployeeModel');
    }

    /**
     * GET /api/it_ticket/employees/active
     * Get all active employees for dropdown
     */
    public function active()
    {
        try {
            $employees = $this->HrEmployeeModel->get_active_employees();

            $this->_response([
                'success' => true,
                'data' => $employees,
                'total' => count($employees)
            ]);
        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => 'Error fetching employees: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/it_ticket/employees/get/{id}
     * Get single employee by ID
     */
    public function get($id)
    {
        try {
            if (empty($id)) {
                $this->_response([
                    'success' => false,
                    'message' => 'Employee ID is required'
                ], 400);
                return;
            }

            $employee = $this->HrEmployeeModel->get_employee_by_id($id);

            if (!$employee) {
                $this->_response([
                    'success' => false,
                    'message' => 'Employee not found or not active'
                ], 404);
                return;
            }

            $this->_response([
                'success' => true,
                'data' => $employee
            ]);
        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => 'Error fetching employee: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/it_ticket/employees/search?q={query}&limit={limit}
     * Search employees for autocomplete
     */
    public function search()
    {
        try {
            $query = $this->input->get('q');
            $limit = $this->input->get('limit') ?: 20;

            if (empty($query)) {
                $this->_response([
                    'success' => false,
                    'message' => 'Search query is required'
                ], 400);
                return;
            }

            $employees = $this->HrEmployeeModel->search_employees($query, $limit);

            $this->_response([
                'success' => true,
                'data' => $employees,
                'total' => count($employees)
            ]);
        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => 'Error searching employees: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/it_ticket/employees/by-department/{department_code}
     * Get employees by department
     */
    public function by_department($department_code)
    {
        try {
            if (empty($department_code)) {
                $this->_response([
                    'success' => false,
                    'message' => 'Department code is required'
                ], 400);
                return;
            }

            $employees = $this->HrEmployeeModel->get_employees_by_department($department_code);

            $this->_response([
                'success' => true,
                'data' => $employees,
                'total' => count($employees)
            ]);
        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => 'Error fetching employees: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/it_ticket/employees/validate
     * Validate if employee exists and is active
     */
    public function validate()
    {
        try {
            $input = json_decode($this->input->raw_input_stream, true);

            if (empty($input['employee_id'])) {
                $this->_response([
                    'success' => false,
                    'message' => 'Employee ID is required'
                ], 400);
                return;
            }

            $isActive = $this->HrEmployeeModel->is_employee_active($input['employee_id']);

            $this->_response([
                'success' => true,
                'is_active' => $isActive,
                'message' => $isActive ? 'Employee is active' : 'Employee not found or not active'
            ]);
        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => 'Error validating employee: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/it_ticket/employees/offices
     * Get all offices for dropdown
     */
    public function offices()
    {
        try {
            $offices = $this->HrEmployeeModel->get_all_offices();

            $this->_response([
                'success' => true,
                'data' => $offices,
                'total' => count($offices)
            ]);
        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => 'Error fetching offices: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/it_ticket/employees/countries
     * Get all countries for dropdown
     */
    public function countries()
    {
        try {
            $countries = $this->HrEmployeeModel->get_all_countries();

            $this->_response([
                'success' => true,
                'data' => $countries,
                'total' => count($countries)
            ]);
        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => 'Error fetching countries: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/it_ticket/employees/managers
     * Get all managers for dropdown
     */
    public function managers()
    {
        try {
            $managers = $this->HrEmployeeModel->get_all_managers();

            $this->_response([
                'success' => true,
                'data' => $managers,
                'total' => count($managers)
            ]);
        } catch (Exception $e) {
            $this->_response([
                'success' => false,
                'message' => 'Error fetching managers: ' . $e->getMessage()
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
            ->set_output(json_encode($data))
            ->_display();
        exit;
    }
}
