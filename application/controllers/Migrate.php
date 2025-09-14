<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migrate extends CI_Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        if (!$this->input->is_cli_request()) {
            show_error('This controller can only be accessed via CLI', 403);
        }

    }

    /**
     * Index function
     *
     * @return void
     */
    public function index()
    {
        if ($this->migration->latest() === FALSE) {
            show_error($this->migration->error_string(), 500);
        }

        echo "Migrated to latest.\n";
    }

    /**
     * Version function
     *
     * @param string $ver
     * @return void
     */
    public function version($ver = null)
    {
        if ($ver === null || !ctype_digit((string)$ver) || (int)$ver < 0) {
            show_error("Usage: php index.php migrate version <number> (must be >= 0)", 400);
        }

        $version = (int)$ver;
        if ($this->migration->version($version) === FALSE) {
            show_error($this->migration->error_string(), 500);
        }

        echo "Migrated to version {$version}.\n";
    }

    /**
     * Reset function
     *
     * @return void
     */
    public function reset()
    {
        if ($this->migration->version(0) === FALSE) {
            show_error($this->migration->error_string(), 500);
        }

        echo "All migrations rolled back.\n";
    }
}
