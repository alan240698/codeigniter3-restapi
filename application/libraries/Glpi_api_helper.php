<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Glpi_api_helper
{
    /**
     * Convert string thành slug (lowercase, no spaces)
     * 
     * @param string $str
     * @return string
     */
    public function slugify($str)
    {
        $str = strtolower(trim($str));
        $str = str_replace([' ', ',', '_'], '-', $str);
        $str = preg_replace('/[^a-z0-9-]/', '', $str);

        return preg_replace('/-+/', '-', $str);
    }

    /**
     * Sanitize string (remove HTML, trim)
     * 
     * @param string $str
     * @return string
     */
    public function sanitizeString($str)
    {
        return trim(strip_tags($str));
    }

    /**
     * Extract entity name
     * 
     * @param string $fullPath
     * @return string
     */
    public function extractEntityName($fullPath)
    {
        $cleaned = trim(str_replace('Root entity >', '', $fullPath));
        return strtolower($cleaned);
    }

}