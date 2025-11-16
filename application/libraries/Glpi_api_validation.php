<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Glpi_api_validation
{
    private $errors = [];

    private $validation_rules = [
        'ticket' => [
            'name'      => ['required', 'string', 'min:3', 'max:255'],
            'content'   => ['required', 'string', 'min:10'],
            'type'      => ['integer',  'in:1,2'],
        ],
        'document' => [
            'file_path' => ['required', 'file_exists', 'file_size:10240'],
            'file_name' => ['required', 'string', 'max:255'],
        ],
        'entity' => [
            'id'        => ['required', 'integer', 'min:0'],
        ],
    ];

    /**
     * Validate ticket data
     * 
     * @param array $data
     * @return bool
     */
    public function validateTicketData($data)
    {
        if (!$this->isValidArray($data)) {
            return false;
        }

        $required = ['name', 'content'];

        foreach ($required as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                log_message('error', "GLPI: Missing required field: {$field}");
                return false;
            }
        }

        return true;
    }

    /**
     * Validate data against rules
     * 
     * @param array $data
     * @param string $type (ticket, document, entity)
     * @return bool
     */
    public function _validate_data($data, $type)
    {
        $this->errors = [];

        if (!isset($this->validation_rules[$type])) {
            return true;
        }

        $rules = $this->validation_rules[$type];

        foreach ($rules as $field => $field_rules) {
            $value = isset($data[$field]) ? $data[$field] : null;
            $this->_validate_field($field, $value, $field_rules, $data);
        }

        return empty($this->errors);
    }

    /**
     * Validate single field
     * 
     * @param string $field
     * @param mixed $value
     * @param array $rules
     * @param array $all_data
     * @return bool
     */
    public function _validate_field($field, $value, $rules, $all_data = [])
    {
        foreach ($rules as $rule) {
            // Parse rule and parameters
            $params = [];
            if (strpos($rule, ':') !== false) {
                list($rule, $param_string) = explode(':', $rule, 2);
                $params = explode(',', $param_string);
            }

            $method = '_rule_' . $rule;

            if (method_exists($this, $method)) {
                if (!$this->$method($field, $value, $params, $all_data)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Check if valid ID
     * 
     * @param mixed $id
     * @return bool
     */
    public function isValidId($id)
    {
        return is_numeric($id) && $id > 0;
    }

    /**
     * Check if valid array
     * 
     * @param mixed $data
     * @return bool
     */
    public function isValidArray($data)
    {
        return is_array($data) && !empty($data);
    }

    /**
     * Check if valid string
     * 
     * @param mixed $str
     * @return bool
     */
    public function isValidString($str)
    {
        return is_string($str) && !empty(trim($str));
    }

    /**
     * Check if valid file structure
     * 
     * @param array $file
     * @return bool
     */
    public function isValidFile($file)
    {
        return isset($file['full_path'])
            && isset($file['file_name'])
            && file_exists($file['full_path']);
    }

    /**
     * Check if has files to upload
     * 
     * @param array $files
     * @return bool
     */
    public function hasFiles($files)
    {
        return $this->isValidArray($files);
    }

    /**
     * Check if response is success
     * 
     * @param array $response
     * @return bool
     */
    public function isSuccessResponse($response)
    {
        return isset($response['success']) && $response['success'] === true;
    }

    /**
     * Sanitize content (cho description/textarea)
     * 
     * @param string $content
     * @return string
     */
    public function sanitizeContent($content)
    {
        // Allow basic HTML tags
        $allowed_tags = '<p><br><b><i><u><ul><ol><li>';
        $cleaned = strip_tags($content, $allowed_tags);
        return trim($cleaned);
    }

    /**
     * Get default form structure cho mỗi entity
     * 
     * @return array
     */
    public function getDefaultFormStructure()
    {
        return [
            'subcategory' => [
                'type' => 'select',
                'label' => 'Sub-Category',
                'required' => true,
                'options' => []
            ],
            'description' => [
                'type' => 'textarea',
                'label' => 'Description',
                'required' => true,
                'placeholder' => 'Describe your problem in detail...',
                'rows' => 5
            ],
            'attachments' => [
                'type' => 'file',
                'label' => 'Attach documents/images',
                'required' => false,
                'max_files' => 5,
                'max_size' => 10485760,
                'allowed_types' => 'jpg|jpeg|png|gif|pdf|doc|docx|xls|xlsx|txt'
            ]
        ];
    }

    /**
     * Success response format
     * 
     * @param array $data
     * @return array
     */
    public function successResponse($data = [])
    {
        return array_merge(['success' => true], $data);
    }

    /**
     * Error response format
     * 
     * @param string $message
     * @param array $additional_data
     * @return array
     */
    public function errorResponse($message, $additional_data = [])
    {
        return array_merge([
            'success' => false,
            'message' => $message
        ], $additional_data);
    }

    // =====================================================
    // VALIDATION RULES
    // =====================================================

    private function _rule_required($field, $value, $params, $all_data)
    {
        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            $this->_add_error($field, ucfirst(str_replace('_', ' ', $field)) . ' is required');
            return false;
        }
        return true;
    }

    private function _rule_string($field, $value, $params, $all_data)
    {
        if ($value !== null && !is_string($value)) {
            $this->_add_error($field, ucfirst(str_replace('_', ' ', $field)) . ' must be a character string');
            return false;
        }
        return true;
    }

    private function _rule_integer($field, $value, $params, $all_data)
    {
        if ($value !== null && !is_numeric($value) && !filter_var($value, FILTER_VALIDATE_INT)) {
            $this->_add_error($field, ucfirst(str_replace('_', ' ', $field)) . ' must be an integer');
            return false;
        }
        return true;
    }

    private function _rule_min($field, $value, $params, $all_data)
    {
        if ($value === null) return true;

        $min = $params[0];

        if (is_numeric($value)) {
            if ($value < $min) {
                $this->_add_error($field, ucfirst(str_replace('_', ' ', $field)) . " phải lớn hơn hoặc bằng {$min}");
                return false;
            }
        } else if (is_string($value)) {
            if (mb_strlen($value) < $min) {
                $this->_add_error($field, ucfirst(str_replace('_', ' ', $field)) . " phải có ít nhất {$min} ký tự");
                return false;
            }
        }

        return true;
    }

    private function _rule_max($field, $value, $params, $all_data)
    {
        if ($value === null) return true;

        $max = $params[0];

        if (is_numeric($value)) {
            if ($value > $max) {
                $this->_add_error($field, ucfirst(str_replace('_', ' ', $field)) . " phải nhỏ hơn hoặc bằng {$max}");
                return false;
            }
        } else if (is_string($value)) {
            if (mb_strlen($value) > $max) {
                $this->_add_error($field, ucfirst(str_replace('_', ' ', $field)) . " không được vượt quá {$max} ký tự");
                return false;
            }
        }

        return true;
    }

    private function _rule_in($field, $value, $params, $all_data)
    {
        if ($value === null) return true;

        if (!in_array($value, $params)) {
            $this->_add_error($field, ucfirst(str_replace('_', ' ', $field)) . ' không hợp lệ');
            return false;
        }

        return true;
    }

    private function _rule_email($field, $value, $params, $all_data)
    {
        if ($value === null) return true;

        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->_add_error($field, ucfirst(str_replace('_', ' ', $field)) . ' không phải là email hợp lệ');
            return false;
        }

        return true;
    }

    private function _rule_file_exists($field, $value, $params, $all_data)
    {
        if ($value === null) return true;

        if (!file_exists($value)) {
            $this->_add_error($field, 'File không tồn tại');
            return false;
        }

        return true;
    }

    private function _rule_file_size($field, $value, $params, $all_data)
    {
        if ($value === null || !file_exists($value)) return true;

        $max_size = $params[0]; // Size in KB
        $file_size = filesize($value) / 1024; // Convert to KB

        if ($file_size > $max_size) {
            $this->_add_error($field, 'Kích thước file không được vượt quá ' . round($max_size / 1024, 2) . 'MB');
            return false;
        }

        return true;
    }

    private function _rule_regex($field, $value, $params, $all_data)
    {
        if ($value === null) return true;

        $pattern = $params[0];

        if (!preg_match($pattern, $value)) {
            $this->_add_error($field, ucfirst(str_replace('_', ' ', $field)) . ' không đúng định dạng');
            return false;
        }

        return true;
    }

    // =====================================================
    // PUBLIC VALIDATION HELPER METHODS
    // =====================================================

    /**
     * Add custom validation rule dynamically
     * 
     * @param string $type (ticket, document, entity)
     * @param string $field
     * @param array $rules
     * @return void
     */
    public function add_validation_rule($type, $field, $rules)
    {
        if (!isset($this->validation_rules[$type])) {
            $this->validation_rules[$type] = [];
        }

        $this->validation_rules[$type][$field] = $rules;
    }

    /**
     * Get all validation errors
     * 
     * @return array
     */
    public function get_errors()
    {
        return $this->errors;
    }

    /**
     * Get first error message
     * 
     * @return string|null
     */
    public function get_first_error()
    {
        if (empty($this->errors)) {
            return null;
        }

        $first_field = array_key_first($this->errors);
        return $this->errors[$first_field][0];
    }

    /**
     * Check if has validation errors
     * 
     * @return bool
     */
    public function has_errors()
    {
        return !empty($this->errors);
    }

    /**
     * Add error message
     * 
     * @param string $field
     * @param string $message
     * @return void
     */
    private function _add_error($field, $message)
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }

        $this->errors[$field][] = $message;
    }
}
