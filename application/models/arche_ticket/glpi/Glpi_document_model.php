<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Glpi_document_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('glpi/glpi_api');
        $this->load->library('glpi_api_validation');
    }

    public function download($id)
    {
        return $this->glpi_api->download_document($id);
    }

    public function uploadMultiple($ticket_id, $files)
    {
        $uploaded = [];

        foreach ($files as $file) {
            if (!$this->glpi_api_validation->isValidFile($file)) {
                log_message('error', 'GLPI: Invalid file structure');
                continue;
            }

            $result = $this->glpi_api->uploadDocument(
                $ticket_id,
                $file['full_path'],
                $file['file_name']
            );

            if ($this->glpi_api_validation->isSuccessResponse($result)) {
                $uploaded[] = $file['file_name'];
            } else {
                log_message('error', "GLPI: Failed to upload file: {$file['file_name']}");
            }
        }

        return $uploaded;
    }

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

