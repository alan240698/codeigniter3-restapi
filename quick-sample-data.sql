-- Quick sample data for Rules tabs
-- Make sure you have created Support Teams first via UI!

-- Get your Support Team IDs first by running:
-- SELECT id, name FROM it_ticket_support_teams;

-- Sample Approval Rules (adjust issue_type_id to match your existing Issue Types)
INSERT INTO it_ticket_approval_rules 
(issue_type_id, rule_name, requires_approval, approval_type, approver_user_id, approver_role, auto_assign_after_approval, reminder_hours, status, created_at, updated_at) 
VALUES
(1, 'Manager Approval Required', 1, 'manager', NULL, NULL, 1, 24, 'active', NOW(), NOW()),
(2, 'Department Head Approval', 1, 'department_head', NULL, 'Department Head', 1, 48, 'active', NOW(), NOW());

-- Sample Level Rules (IMPORTANT: Replace team_id 1, 2 with your actual Support Team IDs!)
-- To get your team IDs, check Teams tab or run: SELECT id, name FROM it_ticket_support_teams;
INSERT INTO it_ticket_level_rules 
(issue_type_id, level_number, level_name, support_team_id, auto_escalate_hours, requires_escalation_reason, can_reject_escalation, assignment_type, status, created_at, updated_at) 
VALUES
(1, 1, 'L1 - First Line Support', 1, 4, 1, 1, 'round_robin', 'active', NOW(), NOW()),
(1, 2, 'L2 - Advanced Support', 2, 8, 1, 1, 'team', 'active', NOW(), NOW()),
(2, 1, 'L1 - Initial Support', 1, 2, 1, 1, 'load_balanced', 'active', NOW(), NOW());

-- Sample Custom Fields
INSERT INTO it_ticket_custom_fields 
(issue_type_id, field_name, field_label, field_type, field_options, is_required, sort_order, default_value, placeholder, help_text, status, created_at, updated_at) 
VALUES
(1, 'manager_email', 'Manager Email', 'text', NULL, 1, 1, NULL, 'manager@company.com', 'Enter manager email for approval', 'active', NOW(), NOW()),
(1, 'department', 'Department', 'select', '["HR", "IT", "Finance", "Sales"]', 1, 2, NULL, NULL, 'Select your department', 'active', NOW(), NOW()),
(2, 'priority_level', 'Priority', 'select', '["Low", "Medium", "High", "Critical"]', 1, 1, 'Medium', NULL, 'Request priority', 'active', NOW(), NOW());

-- Verify data inserted
SELECT 'Data inserted!' AS Status;
SELECT COUNT(*) AS 'Approval Rules' FROM it_ticket_approval_rules;
SELECT COUNT(*) AS 'Level Rules' FROM it_ticket_level_rules;
SELECT COUNT(*) AS 'Custom Fields' FROM it_ticket_custom_fields;
