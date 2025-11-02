<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ticket extends CI_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->config('glpi');
        $this->load->library('form_validation');
        $this->load->library('upload');
        $this->load->model('arche_ticket/glpi_api_model');
        $this->load->model('arche_ticket/ticket_model');
    }

    public function index() {
        $cards          = $this->glpi_api_model->getCards();

        $networks       = $this->glpi_api_model->getCategories(1);
        $userComputer   = $this->glpi_api_model->getCategories(2);
        $groupApp       = $this->glpi_api_model->getCategories(3);
        $cypers         = $this->glpi_api_model->getCategories(4);

        $data = [
            'cards'         =>  $cards,
            'page_title'    => 'IT Support Dashboard',
            'networks'      => $networks,
            'userComputer'  => $userComputer,
            'groupApp'      => $groupApp,
            'cypers'        => $cypers,

        ];

        $this->load->view('arche_ticket/dashboard', $data);
    }
    
    public function create() {
        $this->output->set_content_type('application/json');
        // Get POST data
        $category = $this->input->post('category');
        $subcategory = $this->input->post('subcategory');
        $description = $this->input->post('description');

        
        // Validate category
        if (!in_array($category, ['network', 'user-computer', 'group-app', 'cyber-security'])) {
            $response = [
                'success' => false,
                'message' => 'Category không hợp lệ'
            ];
            echo json_encode($response);
            return;
        }
        
        // Validate required fields based on category
        // $validation_rules = $this->_get_validation_rules($category);
        // $this->form_validation->set_rules($validation_rules);
        // echo '<pre>';print_r($validation_rules);die;
        
        // if (!$this->form_validation->run()) {
        //     $response = [
        //         'success' => false,
        //         'message' => 'Dữ liệu không hợp lệ',
        //         'errors' => $this->form_validation->error_array()
        //     ];
        //     echo json_encode($response);
        //     return;
        // }
        
        // Handle file uploads
        $uploaded_files = [];
        if (!empty($_FILES['attachments']['name'][0])) {
            $upload_result = $this->_handle_file_upload($category);
            
            if (!$upload_result['success']) {
                echo json_encode($upload_result);
                return;
            }
            
            $uploaded_files = $upload_result['files'];
        }
        


        // Prepare ticket data
        $form_data = [
            'subcategory' => $subcategory,
            'description' => $description
        ];
        
        $ticket_data = $this->glpi_api_model->prepare_ticket_data($category, $form_data);
        
        // Create ticket with attachments
        $result = $this->glpi_api_model->create_ticket_with_attachments($ticket_data, $uploaded_files);
        
        // // Log ticket creation
        // if ($result['success']) {
        //     $log_data = [
        //         'ticket_id' => $result['ticket_id'],
        //         'category' => $category,
        //         'subcategory' => $subcategory,
        //         'description' => $description,
        //         'status' => 'success'
        //     ];
        //     // $this->ticket_model->save_log($log_data);
        // }
        
        echo json_encode($result);
    }
    
    /**
     * Get form fields for specific category
     * AJAX endpoint
     */
    public function get_form_fields() {
        $this->output->set_content_type('application/json');
        
        $category = $this->input->get('category');
        $form_fields = $this->config->item('form_fields');
        
        if (isset($form_fields[$category])) {
            echo json_encode([
                'success' => true,
                'fields' => $form_fields[$category]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Category không tồn tại'
            ]);
        }
    }
    
    /**
     * Get validation rules based on category
     * 
     * @param string $category
     * @return array
     */
    private function _get_validation_rules($category) {
        $form_fields = $this->config->item('form_fields');
        $rules = [];
        
        if (!isset($form_fields[$category])) {
            return $rules;
        }
        
        foreach ($form_fields[$category] as $field_name => $field_config) {
            if ($field_config['type'] === 'file') {
                continue; // File validation sẽ xử lý riêng
            }
            
            $rule = [];
            
            if ($field_config['required']) {
                $rule[] = 'required';
            }
            
            if ($field_config['type'] === 'select') {
                $allowed_values = array_keys($field_config['options']);
                $rule[] = 'in_list[' . implode(',', $allowed_values) . ']';
            }
            
            if (!empty($rule)) {
                $rules[] = [
                    'field' => $field_name,
                    'label' => $field_config['label'],
                    'rules' => implode('|', $rule)
                ];
            }
        }
        
        return $rules;
    }
    
    /**
     * Handle file upload
     * 
     * @param string $category
     * @return array
     */
    private function _handle_file_upload($category) {
        $form_fields = $this->config->item('form_fields');
        $file_config = $form_fields[$category]['attachments'] ?? [];
        
        $max_files = $file_config['max_files'] ?? 5;
        $max_size = ($file_config['max_size'] ?? 10485760) / 1024; // Convert to KB
        $allowed_types = $file_config['allowed_types'] ?? $this->config->item('upload_allowed_types');
        
        // Create upload directory if not exists
        $upload_path = $this->config->item('upload_path') . $category . '/';
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0755, true);
        }
        
        // Configure upload
        $config['upload_path'] = $upload_path;
        $config['allowed_types'] = $allowed_types;
        $config['max_size'] = $max_size;
        $config['encrypt_name'] = true;
        
        $this->upload->initialize($config);
        
        $uploaded_files = [];
        $files_count = count($_FILES['attachments']['name']);
        
        if ($files_count > $max_files) {
            return [
                'success' => false,
                'message' => "Chỉ được phép tải lên tối đa {$max_files} files"
            ];
        }
        
        // Upload each file
        for ($i = 0; $i < $files_count; $i++) {
            if ($_FILES['attachments']['error'][$i] == 0) {
                $_FILES['file']['name'] = $_FILES['attachments']['name'][$i];
                $_FILES['file']['type'] = $_FILES['attachments']['type'][$i];
                $_FILES['file']['tmp_name'] = $_FILES['attachments']['tmp_name'][$i];
                $_FILES['file']['error'] = $_FILES['attachments']['error'][$i];
                $_FILES['file']['size'] = $_FILES['attachments']['size'][$i];
                
                if ($this->upload->do_upload('file')) {
                    $uploaded_files[] = $this->upload->data();
                } else {
                    return [
                        'success' => false,
                        'message' => $this->upload->display_errors('', '')
                    ];
                }
            }
        }
        
        return [
            'success' => true,
            'files' => $uploaded_files
                ];
    }


     /**
     * Get ticket history
     * AJAX endpoint
     */
    public function history() {
        $this->output->set_content_type('application/json');
        
        // Check if user is logged in
        $user_id = $this->session->userdata('user_id');
        if (!$user_id) {
            echo json_encode([
                'success' => false,
                'message' => 'Unauthorized'
            ]);
            return;
        }
        
        // Get filters from query params
        $filters = [
            'category' => $this->input->get('category'),
            'status' => $this->input->get('status'),
            'user_id' => $user_id // Chỉ lấy ticket của user hiện tại
        ];
        
        $page = $this->input->get('page') ?? 1;
        $limit = $this->input->get('limit') ?? 10;
        $offset = ($page - 1) * $limit;
        
        // Get tickets from database
        $tickets = $this->ticket_model->get_logs($filters, $limit, $offset);
        
        echo json_encode([
            'success' => true,
            'data' => $tickets,
            'page' => $page,
            'limit' => $limit
        ]);
    }
}