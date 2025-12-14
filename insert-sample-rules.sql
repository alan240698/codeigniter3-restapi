-- Quick sample data for testing Rules tabs UI
-- Run this in phpMyAdmin SQL tab

-- First, check if you have Issue Types
SELECT id, name FROM it_ticket_issue_types LIMIT 5;

-- If you have Issue Types, insert sample Approval Rules
INSERT INTO it_ticket_approval_rules 
(issue_type_id, rule_name, requires_approval, approval_type, approver_user_id, approver_role, auto_assign_after_approval, reminder_hours, status, created_at, updated_at) 
VALUES
(1, 'Manager Approval for Requests', 1, 'manager', NULL, NULL, 1, 24, 'active', NOW(), NOW()),
(2, 'Department Head Approval', 1, 'department_head', NULL, 'Department Head', 1, 48, 'active', NOW(), NOW()),
(1, 'IT Director Approval for Critical', 1, 'specific_user', 1, NULL, 1, 12, 'active', NOW(), NOW());

-- Check if Support Teams exist
SELECT id, name FROM it_ticket_support_teams LIMIT 5;

-- If you have Support Teams, insert sample Level Rules
INSERT INTO it_ticket_level_rules 
(issue_type_id, level_number, level_name, support_team_id, auto_escalate_hours, requires_escalation_reason, can_reject_escalation, assignment_type, status, created_at, updated_at) 
VALUES
(1, 1, 'L1 - First Line Support', 1, 4, 1, 1, 'round_robin', 'active', NOW(), NOW()),
(1, 2, 'L2 - Advanced Support', 2, 8, 1, 1, 'team', 'active', NOW(), NOW()),
(1, 3, 'L3 - Expert Support', 3, NULL, 1, 0, 'team', 'active', NOW(), NOW()),
(2, 1, 'L1 - Initial Support', 1, 2, 1, 1, 'load_balanced', 'active', NOW(), NOW()),
(2, 2, 'L2 - Senior Support', 2, 6, 1, 1, 'team', 'active', NOW(), NOW());

-- Insert sample Custom Fields
INSERT INTO it_ticket_custom_fields 
(issue_type_id, field_name, field_label, field_type, field_options, is_required, sort_order, default_value, placeholder, help_text, status, created_at, updated_at) 
VALUES
(1, 'manager_email', 'Manager Email', 'text', NULL, 1, 1, NULL, 'manager@company.com', 'Enter your manager email for approval', 'active', NOW(), NOW()),
(1, 'department', 'Department', 'select', '["HR", "IT", "Finance", "Sales", "Marketing"]', 1, 2, NULL, NULL, 'Select your department', 'active', NOW(), NOW()),
(1, 'priority_level', 'Priority Level', 'radio', '["Low", "Medium", "High", "Critical"]', 1, 3, 'Medium', NULL, 'Select priority', 'active', NOW(), NOW()),
(2, 'business_impact', 'Business Impact', 'textarea', NULL, 1, 1, NULL, 'Describe impact...', 'Explain business impact', 'active', NOW(), NOW()),
(2, 'affected_users', 'Number of Affected Users', 'number', NULL, 0, 2, '1', '1', 'How many users affected?', 'active', NOW(), NOW());

-- Verify data
SELECT 'Sample data inserted!' AS Status;
SELECT COUNT(*) AS 'Approval Rules' FROM it_ticket_approval_rules;
SELECT COUNT(*) AS 'Level Rules' FROM it_ticket_level_rules;
SELECT COUNT(*) AS 'Custom Fields' FROM it_ticket_custom_fields;
