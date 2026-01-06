<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class DashboardController extends CI_Controller
{
    // Tab config dashboard index page
    protected $tab_config = [
        'services'   => 'Service Structure',
        'workflows'  => 'Workflows & States',
        'sla'        => 'SLA Policies',
        'teams'      => 'Support Teams',
        'rules'      => 'Rules Configuration',
        'templates'  => 'Email Templates',
        'monitoring' => 'Test & Monitor',
        'roles'      => 'Roles',
        'user_role'  => 'User role'
    ];

    public function __construct() {
        parent::__construct();
    }

    public function index()
    {
        $tab = $this->input->get('tab') ?: 'services'; // Default first tab

        // AJAX REQUEST -> Returns only the HTML of the view tab
        if ($this->input->is_ajax_request()) {
            if (!isset($this->tab_config[$tab])) {
                show_404();
                return;
            }

            $data['tab'] = $tab;
            $data['tab_config'] = $this->tab_config;

            // ONLY load and return tab view, DO NOT load dashboard
            $this->load->view('it_ticket/tabs/' . $tab, $data);
            return;
        }

        $data['tab'] = $tab;
        $data['tab_config'] = $this->tab_config;
        $data['tab_view'] = 'it_ticket/tabs/' . $tab; // Pass tab view path

        $this->load->view('it_ticket/dashboard', $data);
    }
}