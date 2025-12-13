<?php
defined('BASEPATH') or exit('No direct script access allowed');

class glpi_document_model extends CI_Model
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->load->library('glpi/glpi_api');
        $this->load->library('glpi_api_validation');
    }

    /**
     * Download
     */
    public function download($id)
    {
        if (!$this->glpi_api->initSession()) {
            log_message('error', 'GLPI: Cannot initialize session for getList');
            return;
        }

        return $this->glpi_api->download_document($id);
    }

    /**
     * Upload multiple
     */
    public function uploadMultiple($ticket_id, $files)
    {
        $uploaded = [];

        foreach ($files as $file) {
            if (!$this->glpi_api_validation->isValidFile($file)) {
                log_message('error', 'GLPI: Invalid file structure - ' . json_encode($file));
                continue;
            }

            // Get the correct path and name (supports both formats)
            $filePath = $file['full_path'] ?? $file['tmp_name'];
            $fileName = $file['file_name'] ?? $file['name'];

            log_message('info', "GLPI: Uploading file {$fileName} to ticket {$ticket_id}");

            $result = $this->glpi_api->uploadDocument(
                $ticket_id,
                $filePath,
                $fileName
            );

            if ($this->glpi_api_validation->isSuccessResponse($result)) {
                $uploaded[] = $fileName;
                log_message('info', "GLPI: File uploaded successfully: {$fileName}");
            } else {
                log_message('error', "GLPI: Failed to upload file: {$fileName} - " . json_encode($result));
            }
        }

        return $uploaded;
    }

    /**
     * Upload single
     */
    public function uploadSingle($ticket_id, $file)
    {
        if (!$this->glpi_api_validation->isValidFile($file)) {
            return [
                'success' => false,
                'message' => 'Invalid file structure'
            ];
        }

        return $this->glpi_api->uploadDocument(
            $ticket_id,
            $file['full_path'],
            $file['file_name']
        );
    }
}

