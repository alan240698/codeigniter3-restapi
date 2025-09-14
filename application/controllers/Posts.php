<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Posts extends CI_Controller
{
    /**
     * Constructor function
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Display posts list page
     */
    public function index()
    {
        $data['title'] = 'CRUD REST API in CodeIgniter3';

        $this->load->view('posts/header', $data);
        $this->load->view('posts/index');
        $this->load->view('posts/footer');
    }
}