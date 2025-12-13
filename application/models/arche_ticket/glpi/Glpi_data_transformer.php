<?php
defined('BASEPATH') or exit('No direct script access allowed');

class glpi_data_transformer extends CI_Model
{
    /**
     * Glpi status map
     */
    private $statusMap = [
        1 => 'new',
        2 => 'processing',
        3 => 'planned',
        4 => 'pending',
        5 => 'solved',
        6 => 'closed'
    ];

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        // Load libraries
        $this->load->library('glpi_api_validation');
        $this->load->library('glpi_api_helper');
    }

    /**
     * Transform entities
     */
    public function transformEntities($data)
    {
        if (!$this->glpi_api_validation->isValidArray($data)) {
            return [];
        }

        $entities = [];
        foreach ($data as $item) {
            if (!isset($item['id']) || !isset($item['name'])) {
                continue;
            }

            if (strtolower($item['name']) === 'root entity') {
                continue;
            }

            $entities[] = [
                'id'    => (int)$item['id'],
                'name'  => $this->glpi_api_helper->sanitizeString($item['name']),
                'key'   => $this->glpi_api_helper->slugify($this->glpi_api_helper->sanitizeString($item['name']))
            ];
        }

        return $entities;
    }

    /**
     * Transfrom tickets
     */
    public function transformTickets($data)
    {
        if (!$this->glpi_api_validation->isValidArray($data)) {
            return [];
        }

        return array_map(function($ticket) {
            $statusId = $ticket[12] ?? 1;
            $title    = $ticket[1] ?? 'No Title';
            
            return [
                'id'            => $ticket[2] ?? 0,
                'title'         => $title,
                'name'          => $title,
                'entity'        => $ticket[80] ?? '',
                'status'        => $this->statusMap[$statusId] ?? 'unknown',
                'status_id'     => $statusId,
                'created_date'  => $ticket[19] ?? '',
                'updated_date'  => $ticket[15] ?? '',
                'priority'      => $ticket[3] ?? null,
                'category'      => strpos($title, '>') !== false ? trim(explode('>', $title)[0]) : $title,
                'description'   => $ticket[7] ?? '',
                'solution'      => $ticket[18] ?? ''
            ];
        }, $data);
    }

    /**
     * Transform ticket detail
     */
    public function transformTicketDetail($data)
    {
        if (isset($data['status']) && is_numeric($data['status'])) {
            $data['status'] = $this->statusMap[$data['status']] ?? 'unknown';
        }

        return $data;
    }

    /**
     * Get status name
     */
    public function getStatusName($status_id)
    {
        return $this->statusMap[$status_id] ?? 'unknown';
    }

    /**
     * Get status id
     */
    public function getStatusId($status_name)
    {
        $flipped = array_flip($this->statusMap);
        return $flipped[strtolower($status_name)] ?? null;
    }
}
