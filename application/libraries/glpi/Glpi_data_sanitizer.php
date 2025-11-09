<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Glpi_data_sanitizer
{
    public function sanitize($data)
    {
        if (!is_array($data)) {
            return $this->sanitizeValue($data);
        }

        $sanitized = [];

        foreach ($data as $key => $value) {
            $sanitized[$key] = is_array($value) 
                ? $this->sanitize($value) 
                : $this->sanitizeValue($value);
        }

        return $sanitized;
    }

    private function sanitizeValue($value)
    {
        if (is_string($value)) {
            return trim(strip_tags($value));
        }

        return $value;
    }
}