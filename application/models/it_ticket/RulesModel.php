<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class RulesModel extends CI_Model
{
    private $table_routing = 'it_ticket_routing_rules';
    private $table_approval = 'it_ticket_approval_rules';
    private $table_level = 'it_ticket_level_rules';
    private $table_custom_fields = 'it_ticket_custom_fields';
    private $table_it_ticket_services = 'it_ticket_services';
    private $table_teams = 'it_ticket_support_teams';

    /*
    |--------------------------------------------------------------------------
    | ROUTING RULES
    |--------------------------------------------------------------------------
    */

    public function get_routing_rules($it_service_id = null, $status = null)
    {
        $this->db->select('
            rr.*,
            it.name as it_service_name,
            st.name as target_team_name
        ');
        $this->db->from($this->table_routing . ' rr');
        $this->db->join($this->table_it_ticket_services . ' it', 'rr.id = it.id', 'left');
        $this->db->join($this->table_teams . ' st', 'rr.target_team_id = st.id', 'left');

        if (!empty($it_service_id)) {
            $this->db->where('rr.id', $it_service_id);
        }

        if (!empty($status)) {
            $this->db->where('rr.status', $status);
        }

        $this->db->order_by('rr.priority', 'ASC');
        $this->db->order_by('rr.created_at', 'DESC');
        
        return $this->db->get()->result();
    }

    public function get_routing_rule($id)
    {
        $this->db->where('id', $id);
        return $this->db->get($this->table_routing)->row();
    }

    public function create_routing_rule($data)
    {
        $this->db->insert($this->table_routing, $data);
        return $this->db->insert_id();
    }

    public function update_routing_rule($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->table_routing, $data);
    }

    public function delete_routing_rule($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete($this->table_routing);
    }

    /**
     * Get applicable routing rule for a ticket
     */
    public function get_applicable_routing_rule($it_service_id, $conditions = [])
    {
        $this->db->where('id', $it_service_id);
        $this->db->where('status', 'active');
        $this->db->order_by('priority', 'ASC');
        
        $rules = $this->db->get($this->table_routing)->result();

        // Filter by conditions (JSON matching)
        foreach ($rules as $rule) {
            if (empty($rule->conditions)) {
                return $rule; // No conditions = match all
            }

            $ruleConditions = json_decode($rule->conditions, true);
            if ($this->match_conditions($ruleConditions, $conditions)) {
                return $rule;
            }
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | APPROVAL RULES
    |--------------------------------------------------------------------------
    */

    public function get_approval_rules($it_service_id = null)
    {
        $this->db->select('
            ar.*,
            it.name as it_service_name
        ');
        $this->db->from($this->table_approval . ' ar');
        $this->db->join($this->table_it_ticket_services . ' it', 'ar.id = it.id', 'left');

        if (!empty($it_service_id)) {
            $this->db->where('ar.id', $it_service_id);
        }

        $this->db->order_by('ar.created_at', 'DESC');
        
        return $this->db->get()->result();
    }

    public function get_approval_rule($id)
    {
        $this->db->where('id', $id);
        return $this->db->get($this->table_approval)->row();
    }

    public function create_approval_rule($data)
    {
        $this->db->insert($this->table_approval, $data);
        return $this->db->insert_id();
    }

    public function update_approval_rule($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->table_approval, $data);
    }

    public function delete_approval_rule($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete($this->table_approval);
    }

    /**
     * Get approval rule for it service
     */
    public function get_approval_rule_by_it_service($it_service_id)
    {
        $this->db->where('id', $it_service_id);
        $this->db->where('status', 'active');
        $this->db->where('requires_approval', 1);
        
        return $this->db->get($this->table_approval)->row();
    }

    /*
    |--------------------------------------------------------------------------
    | LEVEL RULES
    |--------------------------------------------------------------------------
    */

    public function get_level_rules($it_service_id = null)
    {
        $this->db->select('
            lr.*,
            it.name as it_service_name,
            st.name as support_team_name,
            st.support_level
        ');
        $this->db->from($this->table_level . ' lr');
        $this->db->join($this->table_it_ticket_services . ' it', 'lr.id = it.id', 'left');
        $this->db->join($this->table_teams . ' st', 'lr.support_team_id = st.id', 'left');

        if (!empty($it_service_id)) {
            $this->db->where('lr.id', $it_service_id);
        }

        $this->db->order_by('lr.id', 'ASC');
        $this->db->order_by('lr.level_number', 'ASC');
        
        return $this->db->get()->result();
    }

    public function get_level_rule($id)
    {
        $this->db->where('id', $id);
        return $this->db->get($this->table_level)->row();
    }

    public function create_level_rule($data)
    {
        $this->db->insert($this->table_level, $data);
        return $this->db->insert_id();
    }

    public function update_level_rule($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->table_level, $data);
    }

    public function delete_level_rule($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete($this->table_level);
    }

    /**
     * Check if level number exists for it service
     */
    public function level_number_exists($it_service_id, $level_number, $exclude_id = null)
    {
        $this->db->where('id', $it_service_id);
        $this->db->where('level_number', $level_number);
        
        if ($exclude_id !== null) {
            $this->db->where('id !=', $exclude_id);
        }

        $count = $this->db->count_all_results($this->table_level);
        return $count > 0;
    }

    /**
     * Get level rule by level number
     */
    public function get_level_by_number($it_service_id, $level_number)
    {
        $this->db->where('id', $it_service_id);
        $this->db->where('level_number', $level_number);
        $this->db->where('status', 'active');
        
        return $this->db->get($this->table_level)->row();
    }

    /**
     * Get max level for it service
     */
    public function get_max_level($it_service_id)
    {
        $this->db->select_max('level_number');
        $this->db->where('id', $it_service_id);
        $this->db->where('status', 'active');
        
        $result = $this->db->get($this->table_level)->row();
        return $result ? (int)$result->level_number : 1;
    }

    /*
    |--------------------------------------------------------------------------
    | CUSTOM FIELDS
    |--------------------------------------------------------------------------
    */

    public function get_custom_fields($it_service_id = null, $status = null)
    {
        $this->db->select('
            cf.*,
            it.name as it_service_name
        ');
        $this->db->from($this->table_custom_fields . ' cf');
        $this->db->join($this->table_it_ticket_services . ' it', 'cf.id = it.id', 'left');

        if (!empty($it_service_id)) {
            $this->db->where('cf.id', $it_service_id);
        }

        if (!empty($status)) {
            $this->db->where('cf.status', $status);
        }

        $this->db->order_by('cf.sort_order', 'ASC');
        $this->db->order_by('cf.created_at', 'ASC');
        
        return $this->db->get()->result();
    }

    public function get_custom_field($id)
    {
        $this->db->where('id', $id);
        return $this->db->get($this->table_custom_fields)->row();
    }

    public function create_custom_field($data)
    {
        $this->db->insert($this->table_custom_fields, $data);
        return $this->db->insert_id();
    }

    public function update_custom_field($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->table_custom_fields, $data);
    }

    public function delete_custom_field($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete($this->table_custom_fields);
    }

    /**
     * Check if field name exists for it service
     */
    public function field_name_exists($it_service_id, $field_name, $exclude_id = null)
    {
        $this->db->where('id', $it_service_id);
        $this->db->where('field_name', $field_name);
        
        if ($exclude_id !== null) {
            $this->db->where('id !=', $exclude_id);
        }

        $count = $this->db->count_all_results($this->table_custom_fields);
        return $count > 0;
    }

    /**
     * Get required custom fields for it service
     */
    public function get_required_fields($it_service_id)
    {
        $this->db->where('id', $it_service_id);
        $this->db->where('is_required', 1);
        $this->db->where('status', 'active');
        $this->db->order_by('sort_order', 'ASC');
        
        return $this->db->get($this->table_custom_fields)->result();
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Match conditions (JSON)
     */
    private function match_conditions($ruleConditions, $actualConditions)
    {
        if (empty($ruleConditions)) {
            return true;
        }

        foreach ($ruleConditions as $key => $value) {
            if (!isset($actualConditions[$key]) || $actualConditions[$key] != $value) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get all rules for an it service
     */
    public function get_all_rules_by_it_service($it_service_id)
    {
        return [
            'routing' => $this->get_routing_rules($it_service_id, 'active'),
            'approval' => $this->get_approval_rules($it_service_id),
            'levels' => $this->get_level_rules($it_service_id),
            'custom_fields' => $this->get_custom_fields($it_service_id, 'active')
        ];
    }

    /**
     * Validate custom field data against rules
     */
    public function validate_custom_field_data($it_service_id, $data)
    {
        $fields = $this->get_custom_fields($it_service_id, 'active');
        $errors = [];

        foreach ($fields as $field) {
            $fieldName = $field->field_name;
            $fieldValue = $data[$fieldName] ?? null;

            // Check required
            if ($field->is_required && empty($fieldValue)) {
                $errors[] = "{$field->field_label} is required";
                continue;
            }

            // Check validation rules (JSON)
            if (!empty($field->validation_rules)) {
                $rules = json_decode($field->validation_rules, true);
                
                if (isset($rules['min_length']) && strlen($fieldValue) < $rules['min_length']) {
                    $errors[] = "{$field->field_label} must be at least {$rules['min_length']} characters";
                }

                if (isset($rules['max_length']) && strlen($fieldValue) > $rules['max_length']) {
                    $errors[] = "{$field->field_label} must not exceed {$rules['max_length']} characters";
                }

                if (isset($rules['pattern']) && !preg_match($rules['pattern'], $fieldValue)) {
                    $errors[] = "{$field->field_label} format is invalid";
                }
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}