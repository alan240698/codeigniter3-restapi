<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class HrEmployeeModel extends CI_Model
{
    private $table_main = 'hr_table_main';
    private $table_contract = 'hr_table_contract';
    private $table_job_title = 'hr_menu_job_title';
    private $table_department = 'hr_menu_department';
    private $table_offices = 'hr_menu_offices';
    private $table_direct_manager = 'hr_table_direct_manager';

    /**
     * Get all active employees with full details
     * Based on the SQL query provided:
     * - Active employees (estatus = 7)
     * - With valid current contract (CURDATE() BETWEEN cfrom AND cto)
     * - Contract not terminated (tdate IS NULL)
     * - Excluding external department (code != 'EXT')
     */
    public function get_active_employees()
    {
        $this->db->select("
            htm.ide AS employee_id,
            htm.fname AS firstname,
            htm.lname AS lastname,
            CONCAT(htm.fname, ' ', htm.lname) AS fullname,
            htm.arche_email AS email,
            htm.phonework AS phone,
            htm.country,
            hmof.offices AS office,
            hmd.code AS department_code,
            hmd.id AS department_id,
            mgr.arche_email AS manager_email,
            CONCAT(mgr.fname, ' ', mgr.lname) AS manager_fullname,
            mgr.ide AS manager_id
        ", false);

        $this->db->from($this->table_main . ' AS htm');

        // Join contract table
        $this->db->join(
            $this->table_contract . ' AS htc',
            'htm.ide = htc.id_e',
            'inner'
        );

        // Join job title
        $this->db->join(
            $this->table_job_title . ' AS hmjt',
            'htm.job_title_id = hmjt.idjt',
            'left'
        );

        // Join department
        $this->db->join(
            $this->table_department . ' AS hmd',
            'hmjt.dept = hmd.id',
            'left'
        );

        // Join offices
        $this->db->join(
            $this->table_offices . ' AS hmof',
            'htm.working_location = hmof.idf',
            'left'
        );

        // Join direct manager
        $this->db->join(
            $this->table_direct_manager . ' AS htdm',
            'htm.ide = htdm.id_e AND htdm.is_main = 1',
            'left'
        );

        // Join manager details
        $this->db->join(
            $this->table_main . ' AS mgr',
            'htdm.id_m = mgr.ide',
            'left'
        );

        // Conditions
        $this->db->where('htm.estatus', 7); // Active employees
        $this->db->where('CURDATE() BETWEEN htc.cfrom AND htc.cto', null, false);
        $this->db->where('htc.tdate IS NULL', null, false);
        $this->db->where('hmd.code !=', 'EXT'); // Exclude external

        $this->db->order_by('htm.fname', 'ASC');

        return $this->db->get()->result();
    }

    /**
     * Get single employee by ID
     */
    public function get_employee_by_id($employee_id)
    {
        $this->db->select("
            htm.ide AS employee_id,
            htm.fname AS firstname,
            htm.lname AS lastname,
            CONCAT(htm.fname, ' ', htm.lname) AS fullname,
            htm.arche_email AS email,
            htm.phonework AS phone,
            htm.country,
            hmof.offices AS office,
            hmd.code AS department_code,
            hmd.id AS department_id,
            mgr.arche_email AS manager_email,
            CONCAT(mgr.fname, ' ', mgr.lname) AS manager_fullname,
            mgr.ide AS manager_id
        ", false);

        $this->db->from($this->table_main . ' AS htm');

        // Join contract table
        $this->db->join(
            $this->table_contract . ' AS htc',
            'htm.ide = htc.id_e',
            'inner'
        );

        // Join job title
        $this->db->join(
            $this->table_job_title . ' AS hmjt',
            'htm.job_title_id = hmjt.idjt',
            'left'
        );

        // Join department
        $this->db->join(
            $this->table_department . ' AS hmd',
            'hmjt.dept = hmd.id',
            'left'
        );

        // Join offices
        $this->db->join(
            $this->table_offices . ' AS hmof',
            'htm.working_location = hmof.idf',
            'left'
        );

        // Join direct manager
        $this->db->join(
            $this->table_direct_manager . ' AS htdm',
            'htm.ide = htdm.id_e AND htdm.is_main = 1',
            'left'
        );

        // Join manager details
        $this->db->join(
            $this->table_main . ' AS mgr',
            'htdm.id_m = mgr.ide',
            'left'
        );

        // Conditions
        $this->db->where('htm.ide', $employee_id);
        $this->db->where('htm.estatus', 7);
        $this->db->where('CURDATE() BETWEEN htc.cfrom AND htc.cto', null, false);
        $this->db->where('htc.tdate IS NULL', null, false);
        $this->db->where('hmd.code !=', 'EXT');

        return $this->db->get()->row();
    }

    /**
     * Search employees by name or email
     */
    public function search_employees($query, $limit = 20)
    {
        $this->db->select("
            htm.ide AS employee_id,
            htm.fname AS firstname,
            htm.lname AS lastname,
            CONCAT(htm.fname, ' ', htm.lname) AS fullname,
            htm.arche_email AS email,
            htm.phonework AS phone,
            htm.country,
            hmof.offices AS office,
            hmd.code AS department_code
        ", false);

        $this->db->from($this->table_main . ' AS htm');

        // Join contract table
        $this->db->join(
            $this->table_contract . ' AS htc',
            'htm.ide = htc.id_e',
            'inner'
        );

        // Join job title
        $this->db->join(
            $this->table_job_title . ' AS hmjt',
            'htm.job_title_id = hmjt.idjt',
            'left'
        );

        // Join department
        $this->db->join(
            $this->table_department . ' AS hmd',
            'hmjt.dept = hmd.id',
            'left'
        );

        // Join offices
        $this->db->join(
            $this->table_offices . ' AS hmof',
            'htm.working_location = hmof.idf',
            'left'
        );

        // Conditions
        $this->db->where('htm.estatus', 7);
        $this->db->where('CURDATE() BETWEEN htc.cfrom AND htc.cto', null, false);
        $this->db->where('htc.tdate IS NULL', null, false);
        $this->db->where('hmd.code !=', 'EXT');

        // Search conditions
        if (!empty($query)) {
            $this->db->group_start();
            $this->db->like('htm.fname', $query);
            $this->db->or_like('htm.lname', $query);
            $this->db->or_like('htm.arche_email', $query);
            $this->db->or_like("CONCAT(htm.fname, ' ', htm.lname)", $query);
            $this->db->group_end();
        }

        $this->db->order_by('htm.fname', 'ASC');
        $this->db->limit($limit);

        return $this->db->get()->result();
    }

    /**
     * Check if employee exists and is active
     */
    public function is_employee_active($employee_id)
    {
        $employee = $this->get_employee_by_id($employee_id);
        return !empty($employee);
    }

    /**
     * Get employees by department
     */
    public function get_employees_by_department($department_code)
    {
        $this->db->select("
            htm.ide AS employee_id,
            htm.fname AS firstname,
            htm.lname AS lastname,
            CONCAT(htm.fname, ' ', htm.lname) AS fullname,
            htm.arche_email AS email,
            hmd.code AS department_code
        ", false);

        $this->db->from($this->table_main . ' AS htm');

        $this->db->join(
            $this->table_contract . ' AS htc',
            'htm.ide = htc.id_e',
            'inner'
        );

        $this->db->join(
            $this->table_job_title . ' AS hmjt',
            'htm.job_title_id = hmjt.idjt',
            'left'
        );

        $this->db->join(
            $this->table_department . ' AS hmd',
            'hmjt.dept = hmd.id',
            'left'
        );

        $this->db->where('htm.estatus', 7);
        $this->db->where('CURDATE() BETWEEN htc.cfrom AND htc.cto', null, false);
        $this->db->where('htc.tdate IS NULL', null, false);
        $this->db->where('hmd.code', $department_code);

        $this->db->order_by('htm.fname', 'ASC');

        return $this->db->get()->result();
    }

    /**
     * Get all offices for dropdown
     */
    public function get_all_offices()
    {
        $this->db->select('idf AS id, offices AS name');
        $this->db->from($this->table_offices);
        $this->db->order_by('offices', 'ASC');

        return $this->db->get()->result();
    }

    /**
     * Get all countries from active employees
     */
    public function get_all_countries()
    {
        $this->db->select('DISTINCT htm.country AS country', false);
        $this->db->from($this->table_main . ' AS htm');
        $this->db->join(
            $this->table_contract . ' AS htc',
            'htm.ide = htc.id_e',
            'inner'
        );
        $this->db->where('htm.estatus', 7);
        $this->db->where('CURDATE() BETWEEN htc.cfrom AND htc.cto', null, false);
        $this->db->where('htc.tdate IS NULL', null, false);
        $this->db->where('htm.country IS NOT NULL', null, false);
        $this->db->where('htm.country !=', '');
        $this->db->order_by('htm.country', 'ASC');

        return $this->db->get()->result();
    }

    /**
     * Get all managers (employees who are managers)
     */
    public function get_all_managers()
    {
        $this->db->select("
            DISTINCT htm.ide AS employee_id,
            CONCAT(htm.fname, ' ', htm.lname) AS fullname,
            htm.arche_email AS email,
            hmd.code AS department_code
        ", false);

        $this->db->from($this->table_direct_manager . ' AS htdm');
        $this->db->join($this->table_main . ' AS htm', 'htdm.id_m = htm.ide', 'inner');
        $this->db->join($this->table_contract . ' AS htc', 'htm.ide = htc.id_e', 'inner');
        $this->db->join($this->table_job_title . ' AS hmjt', 'htm.job_title_id = hmjt.idjt', 'left');
        $this->db->join($this->table_department . ' AS hmd', 'hmjt.dept = hmd.id', 'left');

        $this->db->where('htm.estatus', 7);
        $this->db->where('CURDATE() BETWEEN htc.cfrom AND htc.cto', null, false);
        $this->db->where('htc.tdate IS NULL', null, false);
        $this->db->where('htdm.is_main', 1);

        $this->db->order_by('htm.fname', 'ASC');

        return $this->db->get()->result();
    }
}
