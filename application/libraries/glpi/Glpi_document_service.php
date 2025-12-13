<?php
defined('BASEPATH') or exit('No direct script access allowed');

class glpi_document_service
{
    private $CI;
    private $http_client;
    private $session_manager;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->CI = &get_instance();

        $this->CI->load->library('glpi/glpi_http_client');
        $this->CI->load->library('glpi/glpi_session_manager');
        $this->CI->load->library('glpi_api_validation');

        $this->http_client      = $this->CI->glpi_http_client;
        $this->session_manager  = $this->CI->glpi_session_manager;
    }

    /**
     * Upload
     */
    public function upload($ticket_id, $file_path, $file_name, $auto_validate = true)
    {
        if (!$this->session_manager->hasActiveSession()) {
            return [
                'success' => false,
                'message' => 'Session not initialized'
            ];
        }

        if ($auto_validate && !$this->validateUpload($ticket_id, $file_path, $file_name)) {
            return [
                'success'           => false,
                'message'           => $this->CI->glpi_api_validation->get_first_error(),
                'errors'            => $this->CI->glpi_api_validation->get_errors(),
                'validation_failed' => true
            ];
        }

        $response = $this->uploadFile($file_path, $file_name);

        if (!$response['success'] || empty($response['data']['id'])) {
            return [
                'success'   => false,
                'message'   => 'Upload document failed',
                'data'      => $response
            ];
        }

        $link_response = $this->linkToTicket($response['data']['id'], $ticket_id);

        if (!$link_response['success']) {
            return [
                'success' => false,
                'message' => 'Document uploaded but linking to ticket failed',
                'upload'  => $response,
                'link'    => $link_response
            ];
        }

        return $response;
    }

    /**
     * Download
     */
    public function download($document_id)
    {
        if (!$this->session_manager->hasActiveSession()) {
            return [
                'success' => false,
                'message' => 'Session not initialized'
            ];
        }

        $meta = $this->http_client->request("Document/$document_id", "GET");

        if (!isset($meta['data']['filepath'])) {
            return [
                'success' => false,
                'message' => 'Document not found'
            ];
        }

        $filePath = $meta['data']['filepath'];
        
        if ($filePath) {
            $session_token = $this->session_manager->getSessionToken();

            $url = "http://172.21.86.29/front/sso.php?session_token=" . urlencode($session_token) . "&document_id=" . $document_id;

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
            $response = curl_exec($ch);
            curl_close($ch);
            echo $response;
        }
    }

    /**
     * Validate upload
     */
    private function validateUpload($ticket_id, $file_path, $file_name)
    {
        $document_data = [
            'file_path' => $file_path,
            'file_name' => $file_name
        ];

        if (!$this->CI->glpi_api_validation->_validate_data($document_data, 'document')) {
            return false;
        }

        if (!$this->CI->glpi_api_validation->_validate_field('ticket_id', $ticket_id, ['required', 'integer', 'min:1'])) {
            return false;
        }

        return true;
    }

    /**
     * Upload file
     */
    private function uploadFile($file_path, $file_name)
    {
        $manifest = json_encode([
            'input' => [
                'name'          => $file_name,
                '_filename'     => [$file_name],
                '_tag_filename' => [$file_name]
            ]
        ]);

        $post_fields = [
            'uploadManifest' => $manifest,
            'filename[0]'    => new CURLFile($file_path, mime_content_type($file_path), $file_name)
        ];

        return $this->http_client->request("Document", 'POST', $post_fields, [], false, true);
    }

    /**
     * Link to ticket
     */
    private function linkToTicket($document_id, $ticket_id)
    {
        $payload = [
            'input' => [
                [
                    'documents_id'  => $document_id,
                    'itemtype'      => 'Ticket',
                    'items_id'      => $ticket_id
                ]
            ]
        ];

        return $this->http_client->request("Document_Item", 'POST', $payload);
    }
}