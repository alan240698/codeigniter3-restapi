<?php
defined('BASEPATH') or exit('No direct script access allowed');

class glpi_ticket_service
{
    private $CI;
    private $http_client;
    private $session_manager;
    private $data_sanitizer;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->library('glpi/glpi_http_client');
        $this->CI->load->library('glpi/glpi_session_manager');
        $this->CI->load->library('glpi/glpi_data_sanitizer');
        $this->CI->load->library('glpi_api_validation');

        $this->http_client      = $this->CI->glpi_http_client;
        $this->session_manager  = $this->CI->glpi_session_manager;
        $this->data_sanitizer   = $this->CI->glpi_data_sanitizer;
    }

    /**
     * Create ticket
     */
    public function create($ticket_data, $autoValidate = true)
    {
        if (!$this->session_manager->hasActiveSession()) {
            return [
                'success' => false,
                'message' => 'Session not initialized'
            ];
        }

        if ($autoValidate) {
            $ticket_data = $this->data_sanitizer->sanitize($ticket_data);

            if (!$this->CI->glpi_api_validation->_validate_data($ticket_data, 'ticket')) {
                return [
                    'success'           => false,
                    'message'           => $this->CI->glpi_api_validation->get_first_error(),
                    'errors'            => $this->CI->glpi_api_validation->get_errors(),
                    'validation_failed' => true
                ];
            }
        }

        $payload  = ['input' => $ticket_data];
        $response = $this->http_client->request('Ticket', 'POST', $payload);

        if (isset($response['data']['id'])) {
            $this->addRequester($response);
        }

        return $response;
    }

    /**
     * Reopen
     */
    public function reopen($id)
    {
        if (!$this->session_manager->hasActiveSession()) {
            return [
                'success' => false,
                'message' => 'Session not initialized'
            ];
        }

        $payload = ['input' => ['status' => 2]];
        return $this->http_client->request("Ticket/$id", 'PUT', $payload);
    }

    /**
     * Get by requester
     */
    public function getByRequester()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $requesterEmail = 'luonglecr15@gmail.com' ?? $_SESSION['auser'];

        $payload = [
            'criteria' => [
                [
                    'field'      => 34,
                    'searchtype' => 'contains',
                    'value'      => $requesterEmail
                ]
            ]
        ];

        $response = $this->http_client->request('search/Ticket?range=0-999&sort=2&order=DESC', 'POST', $payload);

        if (isset($response['success']) && $response['success'] && isset($response['data']['data'])) {
            return $response['data']['data'];
        }

        log_message('error', 'GLPI API error: ' . print_r($response, true));
        return false;
    }

    /**
     * Get with attachments
     */
    public function getWithAttachments($ticketId)
    {
        if (!$this->session_manager->hasActiveSession()) {
            return [
                'success' => false,
                'message' => 'Session not initialized'
            ];
        }

        if (!$this->CI->glpi_api_validation->_validate_field('ticket_id', $ticketId, ['required', 'integer', 'min:1'])) {
            return [
                'success'           => false,
                'message'           => $this->CI->glpi_api_validation->get_first_error(),
                'errors'            => $this->CI->glpi_api_validation->get_errors(),
                'validation_failed' => true
            ];
        }

        $ticketResponse = $this->http_client->request("Ticket/{$ticketId}", 'GET');
        if (!$ticketResponse['success']) {
            return $ticketResponse;
        }

        $ticketData = $ticketResponse['data'];
        $ticketData['attachments'] = $this->getAttachments($ticketData);

        return [
            'success' => true,
            'data'    => $ticketData
        ];
    }

    /**
     * Get supporter ticket
     */
    public function getSupporterTicket($ticketId)
    {
        if (!$this->session_manager->hasActiveSession()) {
            return [
                'success' => false,
                'message' => 'Session not initialized'
            ];
        }

        if (!$this->CI->glpi_api_validation->_validate_field('ticket_id', $ticketId, ['required', 'integer', 'min:1'])) {
            return [
                'success'           => false,
                'message'           => $this->CI->glpi_api_validation->get_first_error(),
                'errors'            => $this->CI->glpi_api_validation->get_errors(),
                'validation_failed' => true
            ];
        }

        $ticket_supporter_response = $this->http_client->request("Ticket/{$ticketId}/Ticket_User/", 'GET');
        
        if (!$ticket_supporter_response['success']) {
            return $ticket_supporter_response;
        }

        $ticket_data = $ticket_supporter_response['data'];

        $dataTmp = [];
        foreach($ticket_data as $index => $item)
        {
            $userId = $item['users_id'];
            if (empty($userId)) {
                continue;
            }

            $res = $this->http_client->request("User/{$userId}", 'GET');
            if($res['success']  == 1) {
                $dataTmp[] = $res['data'];
            }

        }

        return [
            'success' => true,
            'data'    => $dataTmp
        ];
    }

    /**
     * Add requester
     */
    public function addRequester($response)
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $ticketId        = $response['data']['id'];
        $requester_email = 'luonglecr15@gmail.com' ?? $_SESSION['auser'];

        if ($requester_email) {
            $this->http_client->request("Ticket/$ticketId/Ticket_User", "POST", [
                "input" => [
                    "tickets_id"        => $ticketId,
                    "type"              => 1,
                    "use_notification"  => 1,
                    "alternative_email" => $requester_email
                ]
            ]);
        }
    }

    /**
     * Get attachments
     */
    private function getAttachments($ticketData)
    {
        $attachments = [];

        if (!isset($ticketData['links']) || !is_array($ticketData['links'])) {
            return $attachments;
        }

        foreach ($ticketData['links'] as $link) {
            if (isset($link['rel']) && $link['rel'] === 'Document_Item' && isset($link['href'])) {
                $parsed_url = parse_url($link['href']);
                $path       = $parsed_url['path'];

                if (preg_match('#/api\.php/(v\d+/)?(.+)$#', $path, $matches)) {
                    $endpoint           = rtrim($matches[2], '/');
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

    /**
     * Get document details by id
     */
    private function getDocumentDetails($docId)
    {
        $doc_response = $this->http_client->request("Document/{$docId}", 'GET');

        if ($doc_response['success'] && isset($doc_response['data'])) {
            $doc = $doc_response['data'];
            return [
                'id'            => $doc['id']               ?? null,
                'name'          => $doc['name']             ?? 'Unknown',
                'filename'      => $doc['filename']         ?? '',
                'filepath'      => $doc['filepath']         ?? '',
                'mime'          => $doc['mime']             ?? 'application/octet-stream',
                'size'          => $doc['size']             ?? 0,
                'date_creation' => $doc['date_creation']    ?? '',
                'comment'       => $doc['comment']          ?? '',
                'tag'           => $doc['tag']              ?? ''
            ];
        }

        return null;
    }
}