<?php

defined('BASEPATH') or exit('No direct script access allowed');

class PostValidator
{
    const ALLOWED_FIELDS = ['title', 'description'];

    /** @var CI_Form_validation */
    protected $validator;

    /** @var array */
    protected $rules = [];

    /**
     * Constructor function
     */
    public function __construct()
    {
        $CI = &get_instance();
        if (!isset($CI->form_validation)) {
            $CI->load->library('form_validation');
        }

        $this->validator = $CI->form_validation;
        $this->initializeRules();
    }

    /**
     * Validate data for post creation
     * @param array $data
     * @return array{is_valid:bool,errors:array}
     */
    public function validateCreate(array $data)
    {
        // Check for invalid fields
        $invalidFields = $this->getInvalidFields($data);
        if (!empty($invalidFields)) {
            return [
                'is_valid'  => false,
                'errors'    => ['general' => 'Invalid fields: ' . implode(', ', $invalidFields)]
            ];
        }

        // Reset validator
        $this->validator->reset_validation();
        $this->validator->set_error_delimiters('', '');

        // Set data
        $this->validator->set_data($data);

        // Apply rules only for provided fields
        $fieldsToValidate = array_intersect(self::ALLOWED_FIELDS, array_keys($data));

        // Apply rules for required fields
        $this->applyRules($fieldsToValidate);

        $ok = $this->validator->run();
        return [
            'is_valid' => $ok === TRUE,
            'errors'   => $ok ? [] : $this->validator->error_array(),
        ];
    }

    /**
     * Validate data for post update
     * @param array $data
     * @return array{is_valid:bool,errors:array}
     */
    public function validateUpdate(array $data)
    { 
        // Check for invalid fields
        $invalidFields = $this->getInvalidFields($data);
        if (!empty($invalidFields)) {
            return [
                'is_valid'  => false,
                'errors'    => ['general' => 'Invalid fields: ' . implode(', ', $invalidFields)]
            ];
        }

        // Reset validator
        $this->validator->reset_validation();
        $this->validator->set_error_delimiters('', '');

        // Set data
        $this->validator->set_data($data);

        // Apply rules only for provided fields
        $fieldsToValidate = array_intersect(self::ALLOWED_FIELDS, array_keys($data));

        $this->applyRules($fieldsToValidate);

        $ok = $this->validator->run();
        return [
            'is_valid' => $ok === TRUE,
            'errors'   => $ok ? [] : $this->validator->error_array(),
        ];
    }

    /**
     * Apply rules from getRules() to form validation
     * @param array $fields
     */
    protected function applyRules(array $fields)
    {
        foreach ($fields as $field) {
            if (isset($this->rules[$field])) {
                $rule = $this->rules[$field];
                $ruleString = 'trim';

                if (!empty($rule['required'])) {
                    $ruleString .= '|required';
                }

                if (!empty($rule['min_length'])) {
                    $ruleString .= '|min_length[' . $rule['min_length'] . ']';
                }

                if (!empty($rule['max_length'])) {
                    $ruleString .= '|max_length[' . $rule['max_length'] . ']';
                }

                $this->validator->set_rules(
                    $field,
                    ucfirst($field),
                    $ruleString,
                    $this->messages()
                );
            }
        }
    }

    /**
     * Shared error message
     */
    protected function messages(): array
    {
        return [
            'required'   => '%s is required',
            'min_length' => '%s must be at least %s characters long',
            'max_length' => '%s cannot exceed %s characters',
        ];
    }

    /**
     * InitializeRules function
     * @return void
     */
    protected function initializeRules()
    {
        $this->rules = [
            'title' => [
                'required'    => true,
                'min_length'  => 2,
                'max_length'  => 255,
            ],
            'description' => [
                'required'    => true,
                'min_length'  => 10,
                'max_length'  => 65000,
            ],
        ];
    }

    /**
     * Get rules
     * @return array
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * Check for invalid fields
     * @param array $data
     * @return array
     */
    protected function getInvalidFields(array $data): array
    {
        $invalidFields = [];
        foreach (array_keys($data) as $field) {
            if (!in_array($field, self::ALLOWED_FIELDS)) {
                $invalidFields[] = $field;
            }
        }

        return $invalidFields;
    }
}
