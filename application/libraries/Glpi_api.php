<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Glpi_api
{

    private $CI;
    private $api_url;
    private $app_token;
    private $user_token;
    private $session_token = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->config('glpi');
        $this->CI->load->library('glpi_api_validation');

        $this->api_url     = $this->CI->config->item('glpi_api_url');
        $this->app_token   = $this->CI->config->item('glpi_app_token');
        $this->user_token  = $this->CI->config->item('glpi_user_token');
    }

    /**
     * Initialize GLPI session
     * 
     * @return bool
     */
    public function initSession()
    {
        $response = $this->_curl_request(
            'initSession',
            'GET',
            null,
            ['Authorization: ' . $this->user_token]
        );

        if ($response['success'] && isset($response['data']['session_token'])) {
            $this->session_token = $response['data']['session_token'];
            return true;
        }

        return false;
    }

    /**
     * Kill GLPI session
     * 
     * @return bool
     */
    public function killSession()
    {
        if (!$this->session_token) {
            return false;
        }

        $response = $this->_curl_request('killSession', 'GET');
        $this->session_token = null;

        return $response['success'];
    }

    /**
     * Get glpi entities
     * 
     * @return array
     */
    public function getGlpiEntities()
    {
        $response = $this->_curl_request(
            'Entity',
            'GET',
            null,
            ['Authorization: user_token ' . $this->user_token]
        );

        return $response['data'];
    }

    /**
     * Get categories by entity
     * 
     * @param int $id
     * @return array|false
     */
    public function getCategoyByEntity($id)
    {
        if (!$this->CI->glpi_api_validation->_validate_field('id', $id, ['required', 'integer', 'min:0'])) {
            return false;
        }

        $payload = [
            'criteria' => [
                [
                    'field'      => 80,
                    'searchtype' => 'equals',
                    'value'      => $id
                ]
            ]
        ];

        $response = $this->_curl_request('search/ITILCategory', 'POST', $payload);

        if (isset($response['success']) && $response['success'] && isset($response['data']['data'])) {
            return $response['data']['data'];
        }

        log_message('error', 'GLPI API error: ' . print_r($response, true));
        return false;
    }

    /**
     * Create a new ticket
     * 
     * @param array $ticket_data
     * @param bool $auto_validate (default: true)
     * @return array
     */
    public function create_ticket($ticket_data, $auto_validate = true)
    {
        if (!$this->session_token) {
            return [
                'success' => false,
                'message' => 'Session not initialized'
            ];
        }

        if ($auto_validate) {
            $ticket_data = $this->_sanitize_data($ticket_data);


            if (!$this->CI->glpi_api_validation->_validate_data($ticket_data, 'ticket')) {
                return [
                    'success' => false,
                    'message' => $this->get_first_error(),
                    'errors' => $this->get_errors(),
                    'validation_failed' => true
                ];
            }
        }

        $payload = [
            'input' => $ticket_data
        ];

        $response = $this->_curl_request('Ticket', 'POST', $payload);

        return $response;
    }

    /**
     * Upload document to ticket
     * 
     * @param int $ticket_id
     * @param string $file_path
     * @param string $file_name
     * @param bool $auto_validate (default: true)
     * @return array
     */
    public function uploadDocument($ticket_id, $file_path, $file_name, $auto_validate = true)
    {
        if (!$this->session_token) {
            return [
                'success' => false,
                'message' => 'Session not initialized'
            ];
        }

        if ($auto_validate) {
            $document_data = [
                'file_path' => $file_path,
                'file_name' => $file_name
            ];

            if (!$this->CI->glpi_api_validation->_validate_data($document_data, 'document')) {
                return [
                    'success' => false,
                    'message' => $this->get_first_error(),
                    'errors' => $this->get_errors(),
                    'validation_failed' => true
                ];
            }

            if (!$this->CI->glpi_api_validation->_validate_field('ticket_id', $ticket_id, ['required', 'integer', 'min:1'])) {
                return [
                    'success' => false,
                    'message' => $this->get_first_error(),
                    'errors' => $this->get_errors(),
                    'validation_failed' => true
                ];
            }
        }

        $manifest = json_encode([
            'input' => [
                'name' => $file_name,
                '_filename' => [$file_name],
                '_tag_filename' => [$file_name]
            ]
        ]);

        $post_fields = [
            'uploadManifest' => $manifest,  // GLPI dùng 'uploadManifest', không phải 'input'
            'filename[0]' => new CURLFile($file_path, mime_content_type($file_path), $file_name)
        ];


        $response = $this->_curl_request(
            "Document",
            'POST',
            $post_fields,
            [],
            false,
            true
        );



        if (!$response['success'] || empty($response['data']['id'])) {
            return [
                'success' => false,
                'message' => 'Upload document failed',
                'data' => $response
            ];
        }

        $document_id = $response['data']['id'];

        // 🔹 2. Gắn document vào ticket
        $link_payload = [
            'input' => [
                [
                    'documents_id' => $document_id,
                    'itemtype' => 'Ticket',
                    'items_id' => $ticket_id
                ]
            ]
        ];

        $link_response = $this->_curl_request(
            "Document_Item",
            'POST',
            $link_payload
        );

        if (!$link_response['success']) {
            return [
                'success' => false,
                'message' => 'Document uploaded but linking to ticket failed',
                'upload' => $response,
                'link' => $link_response
            ];
        }

        return $response;
    }

    /**
     * Get ticket by ID
     * 
     * @param int $ticket_id
     * @return array
     */
    public function get_ticket($ticket_id)
    {
        if (!$this->session_token) {
            return [
                'success' => false,
                'message' => 'Session not initialized'
            ];
        }

        if (!$this->CI->glpi_api_validation->_validate_field('ticket_id', $ticket_id, ['required', 'integer', 'min:1'])) {
            return [
                'success'           => false,
                'message'           => $this->get_first_error(),
                'errors'            => $this->get_errors(),
                'validation_failed' => true
            ];
        }

        $response = $this->_curl_request("Ticket/{$ticket_id}", 'GET');

        return $response;
    }

    /**
     * Sanitize data
     * 
     * @param array $data
     * @return array
     */
    private function _sanitize_data($data)
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = trim(strip_tags($value));
            } else if (is_array($value)) {
                $sanitized[$key] = $this->_sanitize_data($value);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    // =====================================================
    // CURL REQUEST METHOD (Không thay đổi)
    // =====================================================

    /**
     * Private method to make CURL requests
     * 
     * @param string $endpoint
     * @param string $method
     * @param mixed $data
     * @param array $additional_headers
     * @param bool $raw_data
     * @return array
     */
    private function _curl_request($endpoint, $method = 'GET', $data = null, $additional_headers = [], $raw_data = false, $is_multipart = false)
    {
        $url = $this->api_url . '/' . $endpoint;

        // Chuẩn bị headers
        $headers = [
            'App-Token: ' . $this->app_token
        ];

        if (!$is_multipart) {
            $headers[] = 'Content-Type: application/json';
        }


        // Thêm Session-Token nếu đã có
        if ($this->session_token) {
            $headers[] = 'Session-Token: ' . $this->session_token;
        }

        // Merge additional headers
        $headers = array_merge($headers, $additional_headers);

        // Khởi tạo CURL
        $ch = curl_init();

        // Thiết lập options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Tắt verify SSL (chỉ dùng trong dev)
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        // Thiết lập method
        switch ($method) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                if ($data) {
                    // curl_setopt($ch, CURLOPT_POSTFIELDS, $raw_data ? $data : json_encode($data));
                    if ($is_multipart) {
                        // For multipart, pass data as-is (array with CURLFile)
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                    } else {
                        // For JSON, encode the data
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $raw_data ? $data : json_encode($data));
                    }
                }
                break;
            case 'PUT':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                if ($data) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
                break;
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
        }

        // Thực hiện request
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);

        curl_close($ch);

        // Xử lý response
        if ($curl_error) {
            return [
                'success' => false,
                'message' => 'CURL Error: ' . $curl_error,
                'http_code' => $http_code
            ];
        }

        $decoded_response = json_decode($response, true);

        if ($http_code >= 200 && $http_code < 300) {
            return [
                'success' => true,
                'data' => $decoded_response,
                'http_code' => $http_code
            ];
        } else {
            return [
                'success' => false,
                'message' => $decoded_response['message'] ?? 'Unknown error',
                'data' => $decoded_response,
                'http_code' => $http_code
            ];
        }
    }

    /**
     * Get category ID from config
     * 
     * @param string $category
     * @param string $subcategory
     * @return int
     */
    public function get_category_id($category, $subcategory)
    {
        $categories = $this->CI->config->item('glpi_categories');

        if (isset($categories[$category][$subcategory])) {
            return $categories[$category][$subcategory];
        }

        return 0;
    }
}
