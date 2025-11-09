<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * GLPI API Configuration
 */
$config['glpi_api_url']    = 'http://localhost/api.php/v1';
$config['glpi_app_token']  = 'BnPzerdFaRjsSsLhDy5AoPtCRDsInaSoDBDxKryv';
$config['glpi_user_token'] = 'user_token XVN426XCBtZ8qrHQkxYmaHViFR932eF6X570XLSg';

/**
 * Upload configuration
 */
$config['upload_path']          = './uploads/tickets/';
$config['upload_max_size']      = 10240; // 10MB
$config['upload_allowed_types'] = 'jpg|jpeg|png|gif|pdf|doc|docx|xls|xlsx|txt';