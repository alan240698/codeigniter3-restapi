<?php
defined('BASEPATH') or exit('No direct script access allowed');

class glpi_session_manager
{
    private $CI;
    private $http_client;
    private $user_token;
    private $session_token = null;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->library('glpi/glpi_http_client');

        $this->http_client = $this->CI->glpi_http_client;
        $this->user_token  = $this->CI->config->item('glpi_user_token');
    }

    /**
     * Init
     */
    public function init()
    {
        $response = $this->http_client->request(
            'initSession',
            'GET',
            null,
            ['Authorization: ' . $this->user_token]
        );

        if ($response['success'] && isset($response['data']['session_token'])) {
            $this->session_token = $response['data']['session_token'];
            $this->http_client->setSessionToken($this->session_token);
            return true;
        }

        return false;
    }

    /**
     * Kill
     */
    public function kill()
    {
        if (!$this->session_token) {
            return false;
        }

        $response = $this->http_client->request('killSession', 'GET');
        $this->session_token = null;
        $this->http_client->setSessionToken(null);

        return $response['success'];
    }

    /**
     * Get session token
     */
    public function getSessionToken()
    {
        return $this->session_token;
    }

    /**
     * Has active session
     */
    public function hasActiveSession()
    {
        return !is_null($this->session_token);
    }
}