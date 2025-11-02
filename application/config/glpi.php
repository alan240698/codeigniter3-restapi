<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * GLPI API Configuration
 */
$config['glpi_api_url']    = 'http://localhost/api.php/v1';
$config['glpi_app_token']  = 'zvfxavV1XXeDycNOYfwCU7bcNv1scmLjPHC2QcBQ';
$config['glpi_user_token'] = 'user_token 7Qc2WwBMlrttKmLODQo8Mjdq5wyG93N9RxkXdHgu';

/**
 * Upload configuration
 */
$config['upload_path']          = './uploads/tickets/';
$config['upload_max_size']      = 10240; // 10MB
$config['upload_allowed_types'] = 'jpg|jpeg|png|gif|pdf|doc|docx|xls|xlsx|txt';