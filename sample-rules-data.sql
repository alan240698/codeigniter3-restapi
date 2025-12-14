-- ============================================
-- Sample Data for IT Ticket Rules System
-- ============================================
-- Run this in phpMyAdmin or MySQL client
-- Database tables are empty, this will populate them with sample data

-- First, let's add Support Teams (needed for Level Rules)
INSERT INTO it_ticket_support_teams (name, team_code, support_level, description, status, created_at, updated_at) VALUES
('L1 Support Team Vietnam', 'L1-VN', 'L1', 'First level support team for Vietnam', 'active', NOW(), NOW()),
('L2 Support Team Vietnam', 'L2-VN', 'L2', 'Second level support team for Vietnam', 'active', NOW(), NOW()),
('L3 Support Team Global', 'L3-GLOBAL', 'L3', 'Third level global support team', 'active', NOW(), NOW());

-- Sample Approval Rules (using existing Issue Type IDs: 1, 2, 3)
INSERT INTO it_ticket_approval_rules (issue_type_id, rule_name, requires_approval, approval_type, approver_user_id, approver_role, auto_assign_after_approval, reminder_hours, status, created_at, updated_at) VALUES
(1, 'Manager Approval for HRM Tickets', 1, 'manager', NULL, NULL, 1, 24, 'active', NOW(), NOW()),
(2, 'Department Head Approval for HRM', 1, 'department_head', NULL, 'Department Head', 1, 48, 'active', NOW(), NOW()),
(3, 'IT Manager Approval', 1, 'specific_user', 1, NULL, 1, 24, 'active', NOW(), NOW());

-- Sample Level Rules (Multi-level support escalation)
INSERT INTO it_ticket_level_rules (issue_type_id, level_number, level_name, support_team_id, auto_escalate_hours, requires_escalation_reason, can_reject_escalation, assignment_type, status, created_at, updated_at) VALUES
-- For Issue Type 1 (HRM - Ticket HRM)
(1, 1, 'L1 - Initial Support', 1, 4, 1, 1, 'round_robin', 'active', NOW(), NOW()),
(1, 2, 'L2 - Advanced Support', 2, 8, 1, 1, 'team', 'active', NOW(), NOW()),
(1, 3, 'L3 - Expert Support', 3, NULL, 1, 0, 'team', 'active', NOW(), NOW()),

-- For Issue Type 2 (HRM - Ticket HRM)
(2, 1, 'L1 - HR First Line', 1, 2, 1, 1, 'load_balanced', 'active', NOW(), NOW()),
(2, 2, 'L2 - HR Specialist', 2, 6, 1, 1, 'team', 'active', NOW(), NOW()),

-- For Issue Type 3 (ff - sdfsd)
(3, 1, 'L1 - Technical Support', 1, 4, 0, 1, 'round_robin', 'active', NOW(), NOW()),
(3, 2, 'L2 - Senior Technical', 2, 12, 1, 1, 'team', 'active', NOW(), NOW());

-- Sample Custom Fields (Dynamic form fields for each issue type)
INSERT INTO it_ticket_custom_fields (issue_type_id, field_name, field_label, field_type, field_options, is_required, sort_order, default_value, placeholder, help_text, status, created_at, updated_at) VALUES
-- For Issue Type 1
(1, 'manager_email', 'Manager Email Address', 'text', NULL, 1, 1, NULL, 'manager@company.com', 'Enter your direct manager email for approval', 'active', NOW(), NOW()),
(1, 'employee_id', 'Employee ID', 'text', NULL, 1, 2, NULL, 'EMP-12345', 'Your company employee ID', 'active', NOW(), NOW()),
(1, 'department', 'Department', 'select', '["HR", "IT", "Finance", "Sales", "Marketing", "Operations"]', 1, 3, NULL, NULL, 'Select your department', 'active', NOW(), NOW()),
(1, 'request_type', 'Request Type', 'radio', '["Leave Request", "Salary Inquiry", "Benefits", "Other"]', 1, 4, 'Leave Request', NULL, 'Type of HR request', 'active', NOW(), NOW()),

-- For Issue Type 2
(2, 'priority_level', 'Priority Level', 'select', '["Low", "Medium", "High", "Critical"]', 1, 1, 'Medium', NULL, 'How urgent is this request?', 'active', NOW(), NOW()),
(2, 'affected_users', 'Number of Affected Users', 'number', NULL, 0, 2, '1', '1', 'How many users are affected?', 'active', NOW(), NOW()),
(2, 'business_impact', 'Business Impact', 'textarea', NULL, 1, 3, NULL, 'Describe the business impact...', 'Explain how this affects business operations', 'active', NOW(), NOW()),

-- For Issue Type 3
(3, 'system_name', 'System/Application Name', 'text', NULL, 1, 1, NULL, 'e.g., CRM, ERP, etc.', 'Which system is this related to?', 'active', NOW(), NOW()),
(3, 'error_screenshot', 'Error Screenshot', 'file', NULL, 0, 2, NULL, NULL, 'Upload screenshot of the error (optional)', 'active', NOW(), NOW()),
(3, 'preferred_contact', 'Preferred Contact Method', 'checkbox', '["Email", "Phone", "Teams", "Slack"]', 0, 3, NULL, NULL, 'How would you like to be contacted?', 'active', NOW(), NOW());

-- Display success message
SELECT 'Sample data inserted successfully!' AS Status,
       (SELECT COUNT(*) FROM it_ticket_support_teams) AS 'Support Teams',
       (SELECT COUNT(*) FROM it_ticket_approval_rules) AS 'Approval Rules',
       (SELECT COUNT(*) FROM it_ticket_level_rules) AS 'Level Rules',
       (SELECT COUNT(*) FROM it_ticket_custom_fields) AS 'Custom Fields';
