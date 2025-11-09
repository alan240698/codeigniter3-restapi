<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Glpi_response_handler
{
    public function handle($response, $http_code, $curl_error)
    {
        if ($curl_error) {
            return $this->errorResponse('CURL Error: ' . $curl_error, $http_code);
        }

        $decoded = json_decode($response, true);

        if ($http_code >= 200 && $http_code < 300) {
            return $this->successResponse($decoded, $http_code);
        }

        return $this->errorResponse(
            $decoded['message'] ?? 'Unknown error',
            $http_code,
            $decoded
        );
    }

    private function successResponse($data, $http_code)
    {
        return [
            'success' => true,
            'data' => $data,
            'http_code' => $http_code
        ];
    }

    private function errorResponse($message, $http_code, $data = null)
    {
        return [
            'success' => false,
            'message' => $message,
            'data' => $data,
            'http_code' => $http_code
        ];
    }
}
