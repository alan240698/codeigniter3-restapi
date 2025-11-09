<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Glpi_ticket_service
{
    private $CI;
    private $http_client;
    private $session_manager;
    private $data_sanitizer;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->library('glpi/glpi_http_client');
        $this->CI->load->library('glpi/glpi_session_manager');
        $this->CI->load->library('glpi/glpi_data_sanitizer');
        $this->CI->load->library('glpi_api_validation');
        
        $this->http_client = $this->CI->glpi_http_client;
        $this->session_manager = $this->CI->glpi_session_manager;
        $this->data_sanitizer = $this->CI->glpi_data_sanitizer;
    }

    public function create($ticket_data, $auto_validate = true)
    {
        if (!$this->session_manager->hasActiveSession()) {
            return ['success' => false, 'message' => 'Session not initialized'];
        }

        if ($auto_validate) {
            $ticket_data = $this->data_sanitizer->sanitize($ticket_data);

            if (!$this->CI->glpi_api_validation->_validate_data($ticket_data, 'ticket')) {
                return [
                    'success' => false,
                    'message' => $this->CI->glpi_api_validation->get_first_error(),
                    'errors' => $this->CI->glpi_api_validation->get_errors(),
                    'validation_failed' => true
                ];
            }
        }

        $payload = ['input' => $ticket_data];
        $response = $this->http_client->request('Ticket', 'POST', $payload);

        if (isset($response['data']['id'])) {
            $this->addRequester($response);
        }

        return $response;
    }

    public function reopen($id)
    {
        if (!$this->session_manager->hasActiveSession()) {
            return ['success' => false, 'message' => 'Session not initialized'];
        }

        $payload = ['input' => ['status' => 2]];
        return $this->http_client->request("Ticket/$id", 'PUT', $payload);
    }

    public function getByRequester()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $requester_email = $_SESSION['user_sso_glpi']['email'] ?? 'thong.dh@archetype-group.com';

        $payload = [
            'criteria' => [
                [
                    'field' => 34,
                    'searchtype' => 'contains',
                    'value' => $requester_email
                ]
            ]
        ];

        $response = $this->http_client->request('search/Ticket', 'POST', $payload);

        if (isset($response['success']) && $response['success'] && isset($response['data']['data'])) {
            return $response['data']['data'];
        }

        log_message('error', 'GLPI API error: ' . print_r($response, true));
        return false;
    }

    public function getWithAttachments($ticket_id)
    {
        if (!$this->session_manager->hasActiveSession()) {
            return ['success' => false, 'message' => 'Session not initialized'];
        }

        if (!$this->CI->glpi_api_validation->_validate_field('ticket_id', $ticket_id, ['required', 'integer', 'min:1'])) {
            return [
                'success' => false,
                'message' => $this->CI->glpi_api_validation->get_first_error(),
                'errors' => $this->CI->glpi_api_validation->get_errors(),
                'validation_failed' => true
            ];
        }

        $ticket_response = $this->http_client->request("Ticket/{$ticket_id}", 'GET');
        
        if (!$ticket_response['success']) {
            return $ticket_response;
        }

        $ticket_data = $ticket_response['data'];
        $ticket_data['attachments'] = $this->getAttachments($ticket_data);

        return [
            'success' => true,
            'data' => $ticket_data
        ];
    }

    public function addRequester($response)
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $ticket_id = $response['data']['id'];
        $requester_email = $_SESSION['user_sso_glpi']['email'] ?? 'thong.dh@archetype-group.com';

        if ($requester_email) {
            $this->http_client->request("Ticket/$ticket_id/Ticket_User", "POST", [
                "input" => [
                    "tickets_id" => $ticket_id,
                    "type" => 1,
                    "use_notification" => 1,
                    "alternative_email" => $requester_email
                ]
            ]);
        }
    }

    private function getAttachments($ticket_data)
    {
        $attachments = [];

        if (!isset($ticket_data['links']) || !is_array($ticket_data['links'])) {
            return $attachments;
        }

        foreach ($ticket_data['links'] as $link) {
            if (isset($link['rel']) && $link['rel'] === 'Document_Item' && isset($link['href'])) {
                $parsed_url = parse_url($link['href']);
                $path = $parsed_url['path'];
                
                if (preg_match('#/api\.php/(v\d+/)?(.+)$#', $path, $matches)) {
                    $endpoint = rtrim($matches[2], '/');
                    $doc_items_response = $this->http_client->request($endpoint, 'GET');
                    
                    if ($doc_items_response['success'] && isset($doc_items_response['data'])) {
                        foreach ($doc_items_response['data'] as $doc_item) {
                            if (isset($doc_item['documents_id'])) {
                                $attachments[] = $this->getDocumentDetails($doc_item['documents_id']);
                            }
                        }
                    }
                }
                break;
            }
        }

        return array_filter($attachments);
    }

    private function getDocumentDetails($doc_id)
    {
        $doc_response = $this->http_client->request("Document/{$doc_id}", 'GET');
        
        if ($doc_response['success'] && isset($doc_response['data'])) {
            $doc = $doc_response['data'];
            return [
                'id' => $doc['id'] ?? null,
                'name' => $doc['name'] ?? 'Unknown',
                'filename' => $doc['filename'] ?? '',
                'filepath' => $doc['filepath'] ?? '',
                'mime' => $doc['mime'] ?? 'application/octet-stream',
                'size' => $doc['size'] ?? 0,
                'date_creation' => $doc['date_creation'] ?? '',
                'comment' => $doc['comment'] ?? '',
                'tag' => $doc['tag'] ?? ''
            ];
        }

        return null;
    }
}