<?php
defined('BASEPATH') or exit('No direct script access allowed');

class glpi_http_client
{
    private $CI;
    private $api_url;
    private $app_token;
    private $session_token = null;
    private $response_handler;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->library('glpi/glpi_response_handler');

        $this->api_url          = $this->CI->config->item('glpi_api_url');
        $this->app_token        = $this->CI->config->item('glpi_app_token');
        $this->response_handler = $this->CI->glpi_response_handler;
    }

    /**
     * Request
     */
    public function request($endpoint, $method = 'GET', $data = null, $additional_headers = [], $raw_data = false, $is_multipart = false)
    {
        $url     = $this->api_url . '/' . $endpoint;
        $headers = $this->buildHeaders($additional_headers, $is_multipart);

        $ch = curl_init();
        $this->configureCurl($ch, $url, $method, $data, $headers, $raw_data, $is_multipart);

        $response   = curl_exec($ch);
        $http_code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        return $this->response_handler->handle($response, $http_code, $curl_error);
    }

    /**
     * Set session token
     */
    public function setSessionToken($token)
    {
        $this->session_token = $token;
    }

    /**
     * Build headers
     */
    private function buildHeaders($additional_headers = [], $is_multipart = false)
    {
        $headers = ['App-Token: ' . $this->app_token];

        if (!$is_multipart) {
            $headers[] = 'Content-Type: application/json';
        }

        if ($this->session_token) {
            $headers[] = 'Session-Token: ' . $this->session_token;
        }

        return array_merge($headers, $additional_headers);
    }

    /**
     * Configure curl
     */
    private function configureCurl($ch, $url, $method, $data, $headers, $raw_data, $is_multipart)
    {
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        switch ($method) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                if ($data) {
                    $postFields = $is_multipart ? $data : ($raw_data ? $data : json_encode($data));
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
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
    }
}