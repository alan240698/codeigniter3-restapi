<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Create_posts extends CI_Migration
{
    /**
     * Up function
     *
     * @return void
     */
    public function up()
    {
        // Setter
        $this->db->query("SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'");
        $this->db->query("SET SESSION collation_connection = 'utf8mb4_unicode_ci'");

        // Add field
        $this->dbforge->add_field([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => TRUE,
                'auto_increment' => TRUE
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => FALSE,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => TRUE,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => TRUE,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => TRUE,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => TRUE,
            ],
        ]);

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('posts', TRUE, [
            'ENGINE'          => 'InnoDB',
            'DEFAULT CHARSET' => 'utf8mb4',
            'COLLATE'         => 'utf8mb4_unicode_ci',
        ]);

        $this->db->query('CREATE INDEX idx_posts_deleted_at ON posts(deleted_at)');
    }

    /**
     * Down function
     *
     * @return void
     */
    public function down()
    {
        $this->dbforge->drop_table('posts', TRUE);
    }
}
