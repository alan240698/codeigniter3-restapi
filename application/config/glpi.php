<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * GLPI domain
 */
$config['glpi_domain_url']    = 'http://localhost'; 

/**
 * GLPI API Configuration
 */
$config['glpi_api_url']    = 'http://localhost/api.php/v1'; 
$config['glpi_app_token']  = 'dqamt66cruGzkAggv3S0unmvPle5SOFpZ5yjiRue';
$config['glpi_user_token'] = 'user_token hg7Tu8QDKJ1H8aa4HtLbaxRdRMIQdMtdGY8JQYdF';

/**
 * Upload configuration
 */
$config['upload_path']          = './uploads/tickets/';
$config['upload_max_size']      = 10240; // 10MB
$config['upload_allowed_types'] = 'jpg|jpeg|png|gif|pdf|doc|docx|xls|xlsx|txt';