-- ============================================
-- IT TICKET SYSTEM - COMPLETE DATABASE SCHEMA
-- Version: 1.0
-- Date: 2026-01-05
-- Description: Complete schema with initial data
-- ============================================

-- ============================================
-- PHASE 1: FOUNDATION TABLES
-- Bảng không phụ thuộc vào bảng khác
-- ============================================

-- 1. Roles
CREATE TABLE `it_ticket_roles` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL,
  `display_name` VARCHAR(100) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `permissions` JSON DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_name` (`name`),
  INDEX `idx_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='System roles';

-- 2. Service Groups
CREATE TABLE `it_ticket_service_groups` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `code` VARCHAR(50) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `icon` VARCHAR(50) DEFAULT NULL,
  `color` VARCHAR(7) DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_code` (`code`),
  INDEX `idx_status` (`status`),
  INDEX `idx_sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Service Groups';

-- 3. Support Teams
CREATE TABLE `it_ticket_support_teams` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `code` VARCHAR(50) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `support_level` ENUM('L1', 'L2', 'L3', 'L4') NOT NULL,
  `department_code` VARCHAR(20) DEFAULT NULL,
  `office_id` INT UNSIGNED DEFAULT NULL,
  `country` VARCHAR(50) DEFAULT NULL,
  `manager_id` INT UNSIGNED DEFAULT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_code` (`code`),
  INDEX `idx_support_level` (`support_level`),
  INDEX `idx_department_code` (`department_code`),
  INDEX `idx_country` (`country`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Support teams';

-- 4. Workflows
CREATE TABLE `it_ticket_workflows` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `code` VARCHAR(50) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_code` (`code`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Workflow definitions';

-- 5. SLA Policies
CREATE TABLE `it_ticket_sla_policies` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `priority` ENUM('low', 'medium', 'high', 'critical') NOT NULL,
  `first_response_hours` INT DEFAULT NULL,
  `resolution_hours` INT NOT NULL,
  `business_hours_only` TINYINT(1) NOT NULL DEFAULT 0,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_priority` (`priority`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='SLA policies';

-- 6. Email Templates
CREATE TABLE `it_ticket_email_templates` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `template_code` VARCHAR(50) NOT NULL,
  `template_name` VARCHAR(100) NOT NULL,
  `subject` VARCHAR(255) NOT NULL,
  `body` TEXT NOT NULL,
  `variables` JSON DEFAULT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_template_code` (`template_code`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Email templates';

-- ============================================
-- PHASE 2: LEVEL 1 DEPENDENCIES
-- Bảng phụ thuộc vào bảng ở Phase 1
-- ============================================

-- 7. User Roles (depends on: it_ticket_roles)
CREATE TABLE `it_ticket_user_roles` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_id` INT UNSIGNED NOT NULL,
  `role_id` INT UNSIGNED NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `assigned_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `assigned_by` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_employee_role` (`employee_id`, `role_id`),
  INDEX `idx_employee` (`employee_id`),
  INDEX `idx_role` (`role_id`),
  INDEX `idx_is_active` (`is_active`),
  CONSTRAINT `fk_user_roles_role` FOREIGN KEY (`role_id`) 
    REFERENCES `it_ticket_roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='User role mapping';

-- 8. Ticket Types (depends on: it_ticket_service_groups)
CREATE TABLE `it_ticket_types` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `code` VARCHAR(50) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `icon` VARCHAR(50) DEFAULT NULL,
  `color` VARCHAR(7) DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_code` (`code`),
  INDEX `idx_status` (`status`),
  INDEX `idx_sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Ticket types';

-- 9. Team Members (depends on: it_ticket_support_teams)
CREATE TABLE `it_ticket_team_members` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `team_id` INT UNSIGNED NOT NULL,
  `employee_id` INT UNSIGNED NOT NULL,
  `role` ENUM('member', 'lead', 'manager') NOT NULL DEFAULT 'member',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `joined_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `left_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_team_employee` (`team_id`, `employee_id`),
  INDEX `idx_team` (`team_id`),
  INDEX `idx_employee` (`employee_id`),
  INDEX `idx_is_active` (`is_active`),
  CONSTRAINT `fk_team_members_team` FOREIGN KEY (`team_id`) 
    REFERENCES `it_ticket_support_teams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Team members';

-- 10. Workflow States (depends on: it_ticket_workflows)
CREATE TABLE `it_ticket_workflow_states` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `workflow_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(50) NOT NULL,
  `code` VARCHAR(50) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `state_type` ENUM('initial', 'intermediate', 'final', 'cancelled') NOT NULL,
  `color` VARCHAR(7) DEFAULT NULL,
  `sla_hours` INT DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_workflow_code` (`workflow_id`, `code`),
  INDEX `idx_workflow` (`workflow_id`),
  INDEX `idx_state_type` (`state_type`),
  CONSTRAINT `fk_workflow_states_workflow` FOREIGN KEY (`workflow_id`) 
    REFERENCES `it_ticket_workflows` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Workflow states';

-- ============================================
-- PHASE 3: LEVEL 2 DEPENDENCIES
-- Bảng phụ thuộc vào bảng ở Phase 2
-- ============================================

-- 11. IT Services (depends on: it_ticket_service_groups, SELF-REFERENCE)
CREATE TABLE `it_ticket_services` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `service_group_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `code` VARCHAR(50) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `parent_id` INT UNSIGNED DEFAULT NULL,
  `icon` VARCHAR(50) DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `requires_solution` ENUM('yes', 'no') NOT NULL DEFAULT 'yes',
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_code` (`code`),
  INDEX `idx_service_group` (`service_group_id`),
  INDEX `idx_parent` (`parent_id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_sort_order` (`sort_order`),
  CONSTRAINT `fk_services_service_group` FOREIGN KEY (`service_group_id`) 
    REFERENCES `it_ticket_service_groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_services_parent` FOREIGN KEY (`parent_id`) 
    REFERENCES `it_ticket_services` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='IT Services - depends on service_groups';

-- 12. Workflow Transitions (depends on: it_ticket_workflows, it_ticket_workflow_states)
CREATE TABLE `it_ticket_workflow_transitions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `workflow_id` INT UNSIGNED NOT NULL,
  `from_state_id` INT UNSIGNED DEFAULT NULL,
  `to_state_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `required_role` VARCHAR(50) DEFAULT NULL,
  `conditions` JSON DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_workflow` (`workflow_id`),
  INDEX `idx_from_state` (`from_state_id`),
  INDEX `idx_to_state` (`to_state_id`),
  CONSTRAINT `fk_transitions_workflow` FOREIGN KEY (`workflow_id`) 
    REFERENCES `it_ticket_workflows` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_transitions_from_state` FOREIGN KEY (`from_state_id`) 
    REFERENCES `it_ticket_workflow_states` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_transitions_to_state` FOREIGN KEY (`to_state_id`) 
    REFERENCES `it_ticket_workflow_states` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Workflow transitions';

-- ============================================
-- PHASE 4: LEVEL 3 DEPENDENCIES (RULES)
-- Bảng rules phụ thuộc vào services
-- ============================================

-- 13. Service Workflows (depends on: it_ticket_services, it_ticket_workflows)
CREATE TABLE `it_ticket_service_workflows` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `it_service_id` INT UNSIGNED NOT NULL,
  `workflow_id` INT UNSIGNED NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_service_workflow` (`it_service_id`, `workflow_id`),
  INDEX `idx_service` (`it_service_id`),
  INDEX `idx_workflow` (`workflow_id`),
  CONSTRAINT `fk_service_workflows_service` FOREIGN KEY (`it_service_id`) 
    REFERENCES `it_ticket_services` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_service_workflows_workflow` FOREIGN KEY (`workflow_id`) 
    REFERENCES `it_ticket_workflows` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Service workflow mapping';

-- 14. Routing Rules (depends on: it_ticket_services)
CREATE TABLE `it_ticket_routing_rules` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `it_service_id` INT UNSIGNED NOT NULL,
  `rule_name` VARCHAR(100) NOT NULL,
  `priority` INT NOT NULL DEFAULT 100,
  `conditions` JSON DEFAULT NULL,
  `assignment_type` ENUM('team', 'user', 'round_robin', 'load_balanced') NOT NULL DEFAULT 'team',
  `target_team_id` INT UNSIGNED DEFAULT NULL,
  `target_user_id` INT UNSIGNED DEFAULT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_service` (`it_service_id`),
  INDEX `idx_priority` (`priority`),
  INDEX `idx_status` (`status`),
  CONSTRAINT `fk_routing_rules_service` FOREIGN KEY (`it_service_id`) 
    REFERENCES `it_ticket_services` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Routing rules';

-- 15. Approval Rules (depends on: it_ticket_services)
CREATE TABLE `it_ticket_approval_rules` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `it_service_id` INT UNSIGNED NOT NULL,
  `rule_name` VARCHAR(100) NOT NULL,
  `requires_approval` TINYINT(1) NOT NULL DEFAULT 1,
  `approval_type` ENUM('manager', 'specific_user', 'department_head', 'custom') NOT NULL,
  `approver_user_id` INT UNSIGNED DEFAULT NULL,
  `approver_role` VARCHAR(50) DEFAULT NULL,
  `conditions` JSON DEFAULT NULL,
  `auto_assign_after_approval` TINYINT(1) NOT NULL DEFAULT 1,
  `reminder_hours` INT DEFAULT 24,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_service` (`it_service_id`),
  INDEX `idx_status` (`status`),
  CONSTRAINT `fk_approval_rules_service` FOREIGN KEY (`it_service_id`) 
    REFERENCES `it_ticket_services` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Approval rules';

-- 16. Level Rules (depends on: it_ticket_services, it_ticket_support_teams)
CREATE TABLE `it_ticket_level_rules` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `it_service_id` INT UNSIGNED NOT NULL,
  `level_number` INT NOT NULL,
  `level_name` VARCHAR(50) NOT NULL,
  `support_team_id` INT UNSIGNED DEFAULT NULL,
  `auto_escalate_hours` INT DEFAULT NULL,
  `requires_escalation_reason` TINYINT(1) NOT NULL DEFAULT 1,
  `can_reject_escalation` TINYINT(1) NOT NULL DEFAULT 1,
  `assignment_type` ENUM('team', 'round_robin', 'load_balanced') NOT NULL DEFAULT 'team',
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_service_level` (`it_service_id`, `level_number`),
  INDEX `idx_service` (`it_service_id`),
  INDEX `idx_level_number` (`level_number`),
  INDEX `idx_support_team` (`support_team_id`),
  CONSTRAINT `fk_level_rules_service` FOREIGN KEY (`it_service_id`) 
    REFERENCES `it_ticket_services` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Multi-level support rules';

-- 17. Custom Fields (depends on: it_ticket_services)
CREATE TABLE `it_ticket_custom_fields` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `it_service_id` INT UNSIGNED NOT NULL,
  `field_name` VARCHAR(100) NOT NULL,
  `field_label` VARCHAR(100) NOT NULL,
  `field_type` ENUM('text', 'textarea', 'number', 'date', 'select', 'radio', 'checkbox', 'file', 'user_select') NOT NULL,
  `field_options` JSON DEFAULT NULL,
  `is_required` TINYINT(1) NOT NULL DEFAULT 0,
  `default_value` VARCHAR(255) DEFAULT NULL,
  `validation_rules` JSON DEFAULT NULL,
  `placeholder` VARCHAR(255) DEFAULT NULL,
  `help_text` TEXT DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_service_field_name` (`it_service_id`, `field_name`),
  INDEX `idx_service` (`it_service_id`),
  INDEX `idx_sort_order` (`sort_order`),
  CONSTRAINT `fk_custom_fields_service` FOREIGN KEY (`it_service_id`) 
    REFERENCES `it_ticket_services` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Custom fields';

-- 18. Service SLA (depends on: it_ticket_services, it_ticket_sla_policies)
CREATE TABLE `it_ticket_service_sla` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `it_service_id` INT UNSIGNED NOT NULL,
  `sla_policy_id` INT UNSIGNED NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_service_sla` (`it_service_id`, `sla_policy_id`),
  INDEX `idx_service` (`it_service_id`),
  INDEX `idx_sla_policy` (`sla_policy_id`),
  CONSTRAINT `fk_service_sla_service` FOREIGN KEY (`it_service_id`) 
    REFERENCES `it_ticket_services` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_service_sla_policy` FOREIGN KEY (`sla_policy_id`) 
    REFERENCES `it_ticket_sla_policies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Service SLA mapping';

-- ============================================
-- PHASE 5: SOLUTION TABLES
-- Bảng quản lý solutions (tạo trước tickets để tránh circular dependency)
-- ============================================

-- 19. Ticket Solutions (will reference tickets later)
CREATE TABLE `it_ticket_solutions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_id` INT UNSIGNED NOT NULL,
  `solution_text` TEXT NOT NULL,
  `solution_type` ENUM('temporary', 'permanent', 'workaround') NOT NULL DEFAULT 'permanent',
  `is_approved` TINYINT(1) NOT NULL DEFAULT 0,
  `approved_by` INT UNSIGNED DEFAULT NULL,
  `approved_at` DATETIME DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `version` INT NOT NULL DEFAULT 1,
  `parent_solution_id` INT UNSIGNED DEFAULT NULL COMMENT 'For revisions',
  `provided_by` INT UNSIGNED NOT NULL,
  `provided_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_ticket` (`ticket_id`),
  INDEX `idx_is_active` (`is_active`),
  INDEX `idx_is_approved` (`is_approved`),
  INDEX `idx_provided_by` (`provided_by`),
  INDEX `idx_parent_solution` (`parent_solution_id`),
  INDEX `idx_version` (`ticket_id`, `version`),
  CONSTRAINT `fk_solutions_parent` FOREIGN KEY (`parent_solution_id`) 
    REFERENCES `it_ticket_solutions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Ticket solutions with history and versioning';

-- 20. Solution Attachments (depends on: it_ticket_solutions)
CREATE TABLE `it_ticket_solution_attachments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `solution_id` INT UNSIGNED NOT NULL,
  `filename` VARCHAR(255) NOT NULL,
  `original_filename` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(500) NOT NULL,
  `file_size` INT UNSIGNED NOT NULL,
  `mime_type` VARCHAR(100) NOT NULL,
  `uploaded_by` INT UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_solution` (`solution_id`),
  INDEX `idx_uploaded_by` (`uploaded_by`),
  CONSTRAINT `fk_solution_attachments_solution` FOREIGN KEY (`solution_id`) 
    REFERENCES `it_ticket_solutions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Solution attachments (screenshots, docs, configs)';

-- ============================================
-- PHASE 6: TICKETS TABLE
-- Bảng chính phụ thuộc nhiều bảng khác
-- ============================================

-- 21. Tickets (depends on: service_groups, ticket_types, services, workflows, workflow_states, support_teams)
CREATE TABLE `it_ticket_tickets` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_number` VARCHAR(20) NOT NULL,
  `service_group_id` INT UNSIGNED NOT NULL,
  `ticket_type_id` INT UNSIGNED NOT NULL,
  `it_service_id` INT UNSIGNED NOT NULL,
  `workflow_id` INT UNSIGNED DEFAULT NULL,
  `current_state_id` INT UNSIGNED DEFAULT NULL,
  `subject` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `custom_fields_data` JSON DEFAULT NULL,
  `priority` ENUM('low', 'medium', 'high', 'critical') NOT NULL DEFAULT 'medium',
  `status` ENUM('new', 'assigned', 'in_progress', 'pending_approval', 'approved', 'rejected', 'resolved', 'closed', 'cancelled', 'reopened') NOT NULL DEFAULT 'new',
  `requester_id` INT UNSIGNED NOT NULL,
  `assigned_to` INT UNSIGNED DEFAULT NULL,
  `assigned_team_id` INT UNSIGNED DEFAULT NULL,
  `current_level` INT NOT NULL DEFAULT 1,
  `max_level` INT NOT NULL DEFAULT 1,
  `sla_due_date` DATETIME DEFAULT NULL,
  `sla_status` ENUM('on_time', 'warning', 'overdue') NOT NULL DEFAULT 'on_time',
  `first_response_at` DATETIME DEFAULT NULL,
  `first_response_by` INT UNSIGNED DEFAULT NULL,
  `requires_approval` TINYINT(1) NOT NULL DEFAULT 0,
  `approval_status` ENUM('not_required', 'pending', 'approved', 'rejected') NOT NULL DEFAULT 'not_required',
  `requires_solution` ENUM('yes', 'no') NOT NULL DEFAULT 'yes',
  `solution` TEXT DEFAULT NULL,
  `solution_provided_at` DATETIME DEFAULT NULL,
  `solution_provided_by` INT UNSIGNED DEFAULT NULL,
  `active_solution_id` INT UNSIGNED DEFAULT NULL COMMENT 'Reference to current active solution',
  `resolved_at` DATETIME DEFAULT NULL,
  `resolved_by` INT UNSIGNED DEFAULT NULL,
  `closed_at` DATETIME DEFAULT NULL,
  `closed_by` INT UNSIGNED DEFAULT NULL,
  `cancelled_at` DATETIME DEFAULT NULL,
  `cancelled_by` INT UNSIGNED DEFAULT NULL,
  `cancel_reason` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_ticket_number` (`ticket_number`),
  INDEX `idx_service_group` (`service_group_id`),
  INDEX `idx_ticket_type` (`ticket_type_id`),
  INDEX `idx_service` (`it_service_id`),
  INDEX `idx_workflow` (`workflow_id`),
  INDEX `idx_current_state` (`current_state_id`),
  INDEX `idx_priority` (`priority`),
  INDEX `idx_status` (`status`),
  INDEX `idx_requester` (`requester_id`),
  INDEX `idx_assigned_to` (`assigned_to`),
  INDEX `idx_assigned_team` (`assigned_team_id`),
  INDEX `idx_current_level` (`current_level`),
  INDEX `idx_sla_due` (`sla_due_date`),
  INDEX `idx_sla_status` (`sla_status`),
  INDEX `idx_approval_status` (`approval_status`),
  INDEX `idx_created_at` (`created_at`),
  INDEX `idx_active_solution` (`active_solution_id`),
  CONSTRAINT `fk_tickets_service_group` FOREIGN KEY (`service_group_id`) 
    REFERENCES `it_ticket_service_groups` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_tickets_ticket_type` FOREIGN KEY (`ticket_type_id`) 
    REFERENCES `it_ticket_types` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_tickets_service` FOREIGN KEY (`it_service_id`) 
    REFERENCES `it_ticket_services` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_tickets_workflow` FOREIGN KEY (`workflow_id`) 
    REFERENCES `it_ticket_workflows` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_tickets_current_state` FOREIGN KEY (`current_state_id`) 
    REFERENCES `it_ticket_workflow_states` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_tickets_assigned_team` FOREIGN KEY (`assigned_team_id`) 
    REFERENCES `it_ticket_support_teams` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_tickets_active_solution` FOREIGN KEY (`active_solution_id`) 
    REFERENCES `it_ticket_solutions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Main tickets table';

-- Now add the FK from solutions to tickets
ALTER TABLE `it_ticket_solutions`
ADD CONSTRAINT `fk_solutions_ticket` FOREIGN KEY (`ticket_id`) 
  REFERENCES `it_ticket_tickets` (`id`) ON DELETE CASCADE;

-- ============================================
-- PHASE 7: TICKET RELATED TABLES
-- Bảng phụ thuộc vào tickets
-- ============================================

-- 22. Ticket History (depends on: it_ticket_tickets)
CREATE TABLE `it_ticket_history` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_id` INT UNSIGNED NOT NULL,
  `employee_id` INT UNSIGNED NOT NULL,
  `action_type` ENUM('created', 'assigned', 'status_changed', 'state_changed', 'priority_changed', 'escalated', 'deescalated', 'approval_requested', 'approved', 'rejected', 'commented', 'resolved', 'closed', 'cancelled', 'reopened') NOT NULL,
  `field_name` VARCHAR(100) DEFAULT NULL,
  `old_value` TEXT DEFAULT NULL,
  `new_value` TEXT DEFAULT NULL,
  `comment` TEXT DEFAULT NULL,
  `metadata` JSON DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_ticket` (`ticket_id`),
  INDEX `idx_employee` (`employee_id`),
  INDEX `idx_action_type` (`action_type`),
  INDEX `idx_created_at` (`created_at`),
  CONSTRAINT `fk_ticket_history_ticket` FOREIGN KEY (`ticket_id`) 
    REFERENCES `it_ticket_tickets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Ticket history';

-- 23. Ticket Comments (depends on: it_ticket_tickets)
CREATE TABLE `it_ticket_comments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_id` INT UNSIGNED NOT NULL,
  `employee_id` INT UNSIGNED NOT NULL,
  `comment` TEXT NOT NULL,
  `is_internal` TINYINT(1) NOT NULL DEFAULT 0,
  `is_solution` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_ticket` (`ticket_id`),
  INDEX `idx_employee` (`employee_id`),
  INDEX `idx_is_internal` (`is_internal`),
  INDEX `idx_created_at` (`created_at`),
  CONSTRAINT `fk_ticket_comments_ticket` FOREIGN KEY (`ticket_id`) 
    REFERENCES `it_ticket_tickets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Ticket comments';

-- 24. Ticket Attachments (depends on: it_ticket_tickets, it_ticket_comments)
CREATE TABLE `it_ticket_attachments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_id` INT UNSIGNED NOT NULL,
  `comment_id` INT UNSIGNED DEFAULT NULL,
  `employee_id` INT UNSIGNED NOT NULL,
  `filename` VARCHAR(255) NOT NULL,
  `original_filename` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(500) NOT NULL,
  `file_size` INT UNSIGNED NOT NULL,
  `mime_type` VARCHAR(100) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_ticket` (`ticket_id`),
  INDEX `idx_comment` (`comment_id`),
  INDEX `idx_employee` (`employee_id`),
  CONSTRAINT `fk_ticket_attachments_ticket` FOREIGN KEY (`ticket_id`) 
    REFERENCES `it_ticket_tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ticket_attachments_comment` FOREIGN KEY (`comment_id`) 
    REFERENCES `it_ticket_comments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Ticket attachments';

-- 25. Ticket Escalations (depends on: it_ticket_tickets, it_ticket_support_teams)
CREATE TABLE `it_ticket_escalations` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_id` INT UNSIGNED NOT NULL,
  `from_level` INT NOT NULL,
  `to_level` INT NOT NULL,
  `from_employee_id` INT UNSIGNED NOT NULL,
  `to_employee_id` INT UNSIGNED DEFAULT NULL,
  `to_team_id` INT UNSIGNED DEFAULT NULL,
  `reason` TEXT NOT NULL,
  `status` ENUM('pending', 'accepted', 'rejected') NOT NULL DEFAULT 'pending',
  `escalated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `responded_at` DATETIME DEFAULT NULL,
  `responded_by` INT UNSIGNED DEFAULT NULL,
  `response_note` TEXT DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_ticket` (`ticket_id`),
  INDEX `idx_from_employee` (`from_employee_id`),
  INDEX `idx_to_employee` (`to_employee_id`),
  INDEX `idx_to_team` (`to_team_id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_escalated_at` (`escalated_at`),
  CONSTRAINT `fk_ticket_escalations_ticket` FOREIGN KEY (`ticket_id`) 
    REFERENCES `it_ticket_tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ticket_escalations_to_team` FOREIGN KEY (`to_team_id`) 
    REFERENCES `it_ticket_support_teams` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Ticket escalations';

-- 26. Ticket Approvals (depends on: it_ticket_tickets)
CREATE TABLE `it_ticket_approvals` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_id` INT UNSIGNED NOT NULL,
  `approval_rule_id` INT UNSIGNED DEFAULT NULL,
  `approver_id` INT UNSIGNED NOT NULL,
  `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  `comments` TEXT DEFAULT NULL,
  `requested_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `responded_at` DATETIME DEFAULT NULL,
  `reminder_sent_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_ticket` (`ticket_id`),
  INDEX `idx_approver` (`approver_id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_requested_at` (`requested_at`),
  CONSTRAINT `fk_ticket_approvals_ticket` FOREIGN KEY (`ticket_id`) 
    REFERENCES `it_ticket_tickets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Ticket approvals';

-- 27. Ticket Notifications (depends on: it_ticket_tickets)
CREATE TABLE `it_ticket_notifications` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_id` INT UNSIGNED NOT NULL,
  `ticket_id` INT UNSIGNED DEFAULT NULL,
  `type` VARCHAR(50) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `link` VARCHAR(500) DEFAULT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `read_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_employee` (`employee_id`),
  INDEX `idx_ticket` (`ticket_id`),
  INDEX `idx_is_read` (`is_read`),
  INDEX `idx_created_at` (`created_at`),
  CONSTRAINT `fk_notifications_ticket` FOREIGN KEY (`ticket_id`) 
    REFERENCES `it_ticket_tickets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='User notifications';

-- 28. Email Logs (depends on: it_ticket_tickets, it_ticket_email_templates)
CREATE TABLE `it_ticket_email_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_id` INT UNSIGNED DEFAULT NULL,
  `template_id` INT UNSIGNED DEFAULT NULL,
  `to_email` VARCHAR(255) NOT NULL,
  `cc_email` VARCHAR(255) DEFAULT NULL,
  `subject` VARCHAR(255) NOT NULL,
  `body` TEXT NOT NULL,
  `status` ENUM('pending', 'sent', 'failed') NOT NULL DEFAULT 'pending',
  `sent_at` DATETIME DEFAULT NULL,
  `error_message` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_ticket` (`ticket_id`),
  INDEX `idx_template` (`template_id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_created_at` (`created_at`),
  CONSTRAINT `fk_email_logs_ticket` FOREIGN KEY (`ticket_id`) 
    REFERENCES `it_ticket_tickets` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_email_logs_template` FOREIGN KEY (`template_id`) 
    REFERENCES `it_ticket_email_templates` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Email logs';

-- 29. Template Instances (depends on: it_ticket_email_templates)
CREATE TABLE `it_ticket_template_instances` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `template_id` INT UNSIGNED NOT NULL COMMENT 'ID của template',
  `token` VARCHAR(64) NOT NULL COMMENT 'Token để truy cập',
  `recipient_email` VARCHAR(255) DEFAULT NULL COMMENT 'Email người nhận',
  `rendered_subject` VARCHAR(500) NOT NULL COMMENT 'Subject đã thay thế biến',
  `rendered_body` TEXT NOT NULL COMMENT 'Body đã thay thế biến',
  `context_data` TEXT DEFAULT NULL COMMENT 'Dữ liệu JSON gốc',
  `expires_at` DATETIME NOT NULL COMMENT 'Ngày hết hạn',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_token` (`token`),
  INDEX `idx_template_id` (`template_id`),
  INDEX `idx_expires_at` (`expires_at`),
  CONSTRAINT `fk_template_instances_template` FOREIGN KEY (`template_id`) 
    REFERENCES `it_ticket_email_templates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Email template instances';

-- 30. Template Actions (depends on: it_ticket_template_instances)
CREATE TABLE `it_ticket_template_actions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `instance_id` INT UNSIGNED NOT NULL COMMENT 'ID của instance',
  `action_type` VARCHAR(50) NOT NULL COMMENT 'Loại action: approve, reject',
  `action_data` TEXT DEFAULT NULL COMMENT 'Dữ liệu thêm (JSON)',
  `ip_address` VARCHAR(45) DEFAULT NULL COMMENT 'IP address',
  `user_agent` TEXT DEFAULT NULL COMMENT 'Browser user agent',
  `performed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_instance_id` (`instance_id`),
  INDEX `idx_action_type` (`action_type`),
  CONSTRAINT `fk_template_actions_instance` FOREIGN KEY (`instance_id`) 
    REFERENCES `it_ticket_template_instances` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Email template actions log';

-- ============================================
-- PHASE 8: INITIAL DATA
-- Insert essential data for system to work
-- ============================================

-- ============================================
-- 1. ROLES (Essential for user management)
-- ============================================
INSERT INTO `it_ticket_roles` (`id`, `name`, `display_name`, `description`, `permissions`) VALUES
(1, 'admin', 'System Administrator', 'Full system access with all permissions', 
 JSON_OBJECT(
   'manage_users', true,
   'manage_teams', true,
   'manage_services', true,
   'manage_workflows', true,
   'manage_sla', true,
   'manage_settings', true,
   'view_all_tickets', true,
   'manage_all_tickets', true,
   'view_reports', true,
   'manage_reports', true
 )),
(2, 'user', 'End User', 'Can create and view own tickets', 
 JSON_OBJECT(
   'create_ticket', true,
   'view_own_tickets', true,
   'comment_own_tickets', true,
   'cancel_own_tickets', true
 )),
(3, 'support_agent', 'Support Agent', 'Can view and resolve assigned tickets', 
 JSON_OBJECT(
   'view_assigned_tickets', true,
   'view_team_tickets', true,
   'update_tickets', true,
   'add_comments', true,
   'add_solutions', true,
   'escalate_tickets', true,
   'view_reports', true
 )),
(4, 'team_lead', 'Team Lead', 'Can manage team tickets and assign work', 
 JSON_OBJECT(
   'view_team_tickets', true,
   'view_all_tickets', true,
   'assign_tickets', true,
   'reassign_tickets', true,
   'approve_escalations', true,
   'view_team_reports', true,
   'manage_team_members', true
 )),
(5, 'manager', 'Manager', 'Can approve requests and oversee all tickets', 
 JSON_OBJECT(
   'view_all_tickets', true,
   'approve_requests', true,
   'reject_requests', true,
   'view_all_reports', true,
   'manage_sla', true,
   'manage_priorities', true
 ));

-- ============================================
-- 2. SERVICE GROUPS
-- ============================================
INSERT INTO `it_ticket_service_groups` (`id`, `name`, `code`, `description`, `icon`, `color`, `sort_order`, `status`) VALUES
(1, 'Network & Connectivity', 'NETWORK', 'Network infrastructure and connectivity issues', 'fa-network-wired', '#3498db', 1, 'active'),
(2, 'Human Resources', 'HRM', 'HR systems and employee services', 'fa-users', '#e74c3c', 2, 'active'),
(3, 'User Computer & Devices', 'USER_COMPUTER', 'Desktop, laptop, and peripheral support', 'fa-desktop', '#2ecc71', 3, 'active'),
(4, 'Cyber Security', 'CYBER_SECURITY', 'Security incidents and access management', 'fa-shield-alt', '#f39c12', 4, 'active'),
(5, 'Business Applications', 'BUSINESS_APPS', 'Enterprise application support', 'fa-briefcase', '#9b59b6', 5, 'active'),
(6, 'Infrastructure', 'INFRASTRUCTURE', 'Server and data center operations', 'fa-server', '#34495e', 6, 'active');

-- ============================================
-- 3. TICKET TYPES
-- ============================================
INSERT INTO `it_ticket_types` (`id`, `name`, `code`, `description`, `icon`, `color`, `sort_order`, `status`) VALUES
(1, 'Incident', 'INCIDENT', 'Unplanned interruption or reduction in service quality', 'fa-exclamation-triangle', '#e74c3c', 1, 'active'),
(2, 'Service Request', 'REQUEST', 'Request for service or information', 'fa-hand-paper', '#3498db', 2, 'active'),
(3, 'Change Request', 'CHANGE', 'Request to modify IT infrastructure or services', 'fa-exchange-alt', '#f39c12', 3, 'active'),
(4, 'Problem', 'PROBLEM', 'Root cause investigation for recurring incidents', 'fa-bug', '#9b59b6', 4, 'active');

-- ============================================
-- 4. SUPPORT TEAMS
-- ============================================
INSERT INTO `it_ticket_support_teams` (`id`, `name`, `code`, `description`, `support_level`, `country`, `email`, `status`) VALUES
(1, 'Service Desk - Level 1', 'IT_L1', 'First line support and ticket triage', 'L1', 'Vietnam', 'servicedesk@company.com', 'active'),
(2, 'IT Support - Level 2', 'IT_L2', 'Advanced technical support', 'L2', 'Vietnam', 'itsupport@company.com', 'active'),
(3, 'Network Operations', 'NETWORK_TEAM', 'Network infrastructure and connectivity', 'L2', 'Vietnam', 'netops@company.com', 'active'),
(4, 'Security Operations', 'SECURITY_TEAM', 'Cyber security incident response', 'L3', 'Vietnam', 'secops@company.com', 'active'),
(5, 'Application Support', 'APP_SUPPORT', 'Business application specialists', 'L2', 'Vietnam', 'appsupport@company.com', 'active'),
(6, 'Infrastructure Team', 'INFRA_TEAM', 'Server and infrastructure specialists', 'L3', 'Vietnam', 'infrastructure@company.com', 'active');

-- ============================================
-- 5. WORKFLOWS
-- ============================================
INSERT INTO `it_ticket_workflows` (`id`, `name`, `code`, `description`, `status`) VALUES
(1, 'Standard Workflow', 'STANDARD', 'Default workflow for most tickets without approval', 'active'),
(2, 'Approval Workflow', 'APPROVAL', 'Workflow requiring manager approval before assignment', 'active'),
(3, 'Emergency Workflow', 'EMERGENCY', 'Fast-track workflow for critical incidents', 'active'),
(4, 'Change Management', 'CHANGE_MGMT', 'Workflow for change requests with CAB approval', 'active');

-- ============================================
-- 6. WORKFLOW STATES
-- ============================================

-- Standard Workflow States
INSERT INTO `it_ticket_workflow_states` (`workflow_id`, `name`, `code`, `description`, `state_type`, `color`, `sla_hours`, `sort_order`) VALUES
-- Standard Workflow (ID=1)
(1, 'New', 'NEW', 'Ticket just created, awaiting assignment', 'initial', '#3498db', NULL, 1),
(1, 'Assigned', 'ASSIGNED', 'Ticket assigned to agent or team', 'intermediate', '#f39c12', 2, 2),
(1, 'In Progress', 'IN_PROGRESS', 'Agent is actively working on the ticket', 'intermediate', '#e67e22', 24, 3),
(1, 'Pending Customer', 'PENDING_CUSTOMER', 'Waiting for customer response', 'intermediate', '#95a5a6', NULL, 4),
(1, 'Resolved', 'RESOLVED', 'Solution provided, awaiting closure', 'intermediate', '#27ae60', 48, 5),
(1, 'Closed', 'CLOSED', 'Ticket completed and closed', 'final', '#2c3e50', NULL, 6),
(1, 'Cancelled', 'CANCELLED', 'Ticket cancelled by requester', 'cancelled', '#e74c3c', NULL, 7),

-- Approval Workflow (ID=2)
(2, 'New', 'NEW', 'Ticket created, pending approval', 'initial', '#3498db', NULL, 1),
(2, 'Pending Approval', 'PENDING_APPROVAL', 'Waiting for manager approval', 'intermediate', '#f39c12', 8, 2),
(2, 'Approved', 'APPROVED', 'Request approved, ready for assignment', 'intermediate', '#27ae60', 2, 3),
(2, 'Rejected', 'REJECTED', 'Request rejected by approver', 'final', '#e74c3c', NULL, 4),
(2, 'Assigned', 'ASSIGNED', 'Assigned to support team', 'intermediate', '#3498db', 2, 5),
(2, 'In Progress', 'IN_PROGRESS', 'Work in progress', 'intermediate', '#e67e22', 24, 6),
(2, 'Resolved', 'RESOLVED', 'Solution provided', 'intermediate', '#27ae60', 48, 7),
(2, 'Closed', 'CLOSED', 'Ticket closed', 'final', '#2c3e50', NULL, 8),
(2, 'Cancelled', 'CANCELLED', 'Ticket cancelled', 'cancelled', '#95a5a6', NULL, 9),

-- Emergency Workflow (ID=3)
(3, 'Critical', 'CRITICAL', 'Critical incident reported', 'initial', '#c0392b', NULL, 1),
(3, 'Investigating', 'INVESTIGATING', 'Team investigating the incident', 'intermediate', '#e67e22', 1, 2),
(3, 'Mitigating', 'MITIGATING', 'Applying temporary fix', 'intermediate', '#f39c12', 2, 3),
(3, 'Resolved', 'RESOLVED', 'Service restored', 'intermediate', '#27ae60', 4, 4),
(3, 'Closed', 'CLOSED', 'Incident closed with RCA', 'final', '#2c3e50', NULL, 5),

-- Change Management Workflow (ID=4)
(4, 'Draft', 'DRAFT', 'Change request in draft', 'initial', '#95a5a6', NULL, 1),
(4, 'Review', 'REVIEW', 'Under technical review', 'intermediate', '#3498db', 24, 2),
(4, 'CAB Approval', 'CAB_APPROVAL', 'Pending CAB approval', 'intermediate', '#f39c12', 48, 3),
(4, 'Approved', 'APPROVED', 'Change approved for implementation', 'intermediate', '#27ae60', NULL, 4),
(4, 'Scheduled', 'SCHEDULED', 'Change scheduled', 'intermediate', '#9b59b6', NULL, 5),
(4, 'Implementing', 'IMPLEMENTING', 'Change being implemented', 'intermediate', '#e67e22', NULL, 6),
(4, 'Completed', 'COMPLETED', 'Change successfully completed', 'final', '#27ae60', NULL, 7),
(4, 'Failed', 'FAILED', 'Change failed, rolled back', 'final', '#e74c3c', NULL, 8),
(4, 'Cancelled', 'CANCELLED', 'Change cancelled', 'cancelled', '#95a5a6', NULL, 9);

-- ============================================
-- 7. SLA POLICIES
-- ============================================
INSERT INTO `it_ticket_sla_policies` (`id`, `name`, `description`, `priority`, `first_response_hours`, `resolution_hours`, `business_hours_only`, `status`) VALUES
(1, 'Low Priority SLA', 'Non-urgent issues - 5 business days resolution', 'low', 24, 120, 1, 'active'),
(2, 'Medium Priority SLA', 'Standard business impact - 3 business days', 'medium', 8, 72, 1, 'active'),
(3, 'High Priority SLA', 'Significant business impact - 1 business day', 'high', 4, 24, 1, 'active'),
(4, 'Critical Priority SLA', 'Critical business impact - 4 hours resolution', 'critical', 1, 4, 0, 'active');

-- ============================================
-- 8. EMAIL TEMPLATES
-- ============================================
INSERT INTO `it_ticket_email_templates` (`id`, `template_code`, `template_name`, `subject`, `body`, `variables`, `status`) VALUES
(1, 'TICKET_CREATED', 'Ticket Created Confirmation', 
 'Ticket #{ticket_number} - {subject}', 
 '<h2>Ticket Created Successfully</h2>
<p>Dear {requester_name},</p>
<p>Your support ticket has been created with the following details:</p>
<ul>
  <li><strong>Ticket Number:</strong> #{ticket_number}</li>
  <li><strong>Subject:</strong> {subject}</li>
  <li><strong>Priority:</strong> {priority}</li>
  <li><strong>Service:</strong> {service_name}</li>
  <li><strong>Status:</strong> {status}</li>
</ul>
<p>You can track your ticket status at: <a href="{ticket_url}">{ticket_url}</a></p>
<p>Our team will respond within {sla_response_time}.</p>
<p>Best regards,<br>IT Support Team</p>', 
 '["ticket_number", "subject", "priority", "service_name", "status", "requester_name", "ticket_url", "sla_response_time"]',
 'active'),

(2, 'TICKET_ASSIGNED', 'Ticket Assigned to Agent',
 'Assigned: Ticket #{ticket_number}',
 '<h2>New Ticket Assigned</h2>
<p>Dear {assigned_to_name},</p>
<p>A ticket has been assigned to you:</p>
<ul>
  <li><strong>Ticket Number:</strong> #{ticket_number}</li>
  <li><strong>Subject:</strong> {subject}</li>
  <li><strong>Priority:</strong> {priority}</li>
  <li><strong>Requester:</strong> {requester_name}</li>
  <li><strong>SLA Due:</strong> {sla_due_date}</li>
</ul>
<p><strong>Description:</strong></p>
<p>{description}</p>
<p>Please review and respond: <a href="{ticket_url}">{ticket_url}</a></p>',
 '["ticket_number", "subject", "priority", "requester_name", "assigned_to_name", "description", "ticket_url", "sla_due_date"]',
 'active'),

(3, 'TICKET_RESOLVED', 'Ticket Resolved',
 'Resolved: Ticket #{ticket_number}',
 '<h2>Ticket Resolved</h2>
<p>Dear {requester_name},</p>
<p>Your ticket has been resolved:</p>
<ul>
  <li><strong>Ticket Number:</strong> #{ticket_number}</li>
  <li><strong>Subject:</strong> {subject}</li>
  <li><strong>Resolved By:</strong> {resolved_by_name}</li>
  <li><strong>Resolved At:</strong> {resolved_at}</li>
</ul>
<p><strong>Solution:</strong></p>
<p>{solution}</p>
<p>If you are satisfied with the solution, the ticket will be automatically closed in 24 hours.</p>
<p>If you need further assistance, please reply to reopen this ticket.</p>
<p>View ticket: <a href="{ticket_url}">{ticket_url}</a></p>',
 '["ticket_number", "subject", "requester_name", "resolved_by_name", "resolved_at", "solution", "ticket_url"]',
 'active'),

(4, 'TICKET_CLOSED', 'Ticket Closed',
 'Closed: Ticket #{ticket_number}',
 '<h2>Ticket Closed</h2>
<p>Dear {requester_name},</p>
<p>Your ticket has been closed:</p>
<ul>
  <li><strong>Ticket Number:</strong> #{ticket_number}</li>
  <li><strong>Subject:</strong> {subject}</li>
  <li><strong>Closed At:</strong> {closed_at}</li>
</ul>
<p>Thank you for using our IT support services.</p>
<p>If you need to reopen this ticket, please contact support.</p>',
 '["ticket_number", "subject", "requester_name", "closed_at"]',
 'active'),

(5, 'APPROVAL_REQUEST', 'Approval Required',
 'Approval Required: Ticket #{ticket_number}',
 '<h2>Approval Required</h2>
<p>Dear {approver_name},</p>
<p>A ticket requires your approval:</p>
<ul>
  <li><strong>Ticket Number:</strong> #{ticket_number}</li>
  <li><strong>Subject:</strong> {subject}</li>
  <li><strong>Requester:</strong> {requester_name}</li>
  <li><strong>Priority:</strong> {priority}</li>
</ul>
<p><strong>Description:</strong></p>
<p>{description}</p>
<p>Please review and approve/reject: <a href="{approval_url}">{approval_url}</a></p>',
 '["ticket_number", "subject", "requester_name", "approver_name", "priority", "description", "approval_url"]',
 'active'),

(6, 'TICKET_ESCALATED', 'Ticket Escalated',
 'Escalated: Ticket #{ticket_number}',
 '<h2>Ticket Escalated</h2>
<p>Dear {escalated_to_name},</p>
<p>A ticket has been escalated to you:</p>
<ul>
  <li><strong>Ticket Number:</strong> #{ticket_number}</li>
  <li><strong>Subject:</strong> {subject}</li>
  <li><strong>From Level:</strong> {from_level}</li>
  <li><strong>To Level:</strong> {to_level}</li>
  <li><strong>Escalated By:</strong> {escalated_by_name}</li>
</ul>
<p><strong>Escalation Reason:</strong></p>
<p>{escalation_reason}</p>
<p>View ticket: <a href="{ticket_url}">{ticket_url}</a></p>',
 '["ticket_number", "subject", "escalated_to_name", "from_level", "to_level", "escalated_by_name", "escalation_reason", "ticket_url"]',
 'active'),

(7, 'SLA_WARNING', 'SLA Warning',
 'SLA Warning: Ticket #{ticket_number}',
 '<h2>SLA Warning</h2>
<p>Dear {assigned_to_name},</p>
<p>This ticket is approaching SLA breach:</p>
<ul>
  <li><strong>Ticket Number:</strong> #{ticket_number}</li>
  <li><strong>Subject:</strong> {subject}</li>
  <li><strong>Priority:</strong> {priority}</li>
  <li><strong>SLA Due:</strong> {sla_due_date}</li>
  <li><strong>Time Remaining:</strong> {time_remaining}</li>
</ul>
<p>Please prioritize this ticket: <a href="{ticket_url}">{ticket_url}</a></p>',
 '["ticket_number", "subject", "assigned_to_name", "priority", "sla_due_date", "time_remaining", "ticket_url"]',
 'active'),

(8, 'SLA_BREACH', 'SLA Breached',
 'SLA BREACH: Ticket #{ticket_number}',
 '<h2 style="color: red;">SLA Breach Alert</h2>
<p>Dear Team Lead,</p>
<p>The following ticket has breached its SLA:</p>
<ul>
  <li><strong>Ticket Number:</strong> #{ticket_number}</li>
  <li><strong>Subject:</strong> {subject}</li>
  <li><strong>Priority:</strong> {priority}</li>
  <li><strong>Assigned To:</strong> {assigned_to_name}</li>
  <li><strong>SLA Due:</strong> {sla_due_date}</li>
  <li><strong>Breach Time:</strong> {breach_time}</li>
</ul>
<p>Immediate action required: <a href="{ticket_url}">{ticket_url}</a></p>',
 '["ticket_number", "subject", "priority", "assigned_to_name", "sla_due_date", "breach_time", "ticket_url"]',
 'active');

-- ============================================
-- 9. IT SERVICES
-- ============================================

-- HRM Services
INSERT INTO `it_ticket_services` (`id`, `service_group_id`, `name`, `code`, `parent_id`, `sort_order`, `requires_solution`, `status`) VALUES
-- Parent services
(1, 2, 'Attendance & Timekeeping', 'HRM_ATTENDANCE', NULL, 1, 1, 'active'),
(2, 2, 'Leave Management', 'HRM_LEAVE', NULL, 2, 1, 'active'),
(3, 2, 'Payroll & Compensation', 'HRM_PAYROLL', NULL, 3, 1, 'active'),
(4, 2, 'Employee Profile', 'HRM_PROFILE', NULL, 4, 1, 'active'),

-- Sub-services for Attendance
(5, 2, 'Check-in/Check-out Issue', 'HRM_ATT_CHECKIN', 1, 1, 1, 'active'),
(6, 2, 'Timesheet Correction', 'HRM_ATT_TIMESHEET', 1, 2, 1, 'active'),
(7, 2, 'Overtime Request', 'HRM_ATT_OVERTIME', 1, 3, 1, 'active'),

-- Sub-services for Leave
(8, 2, 'Annual Leave Request', 'HRM_LEAVE_ANNUAL', 2, 1, 0, 'active'),
(9, 2, 'Sick Leave', 'HRM_LEAVE_SICK', 2, 2, 0, 'active'),
(10, 2, 'Unpaid Leave', 'HRM_LEAVE_UNPAID', 2, 3, 0, 'active'),

-- Sub-services for Payroll
(11, 2, 'Salary Inquiry', 'HRM_PAY_INQUIRY', 3, 1, 1, 'active'),
(12, 2, 'Tax Certificate Request', 'HRM_PAY_TAX', 3, 2, 1, 'active'),
(13, 2, 'Bonus Inquiry', 'HRM_PAY_BONUS', 3, 3, 1, 'active');

-- Network Services
INSERT INTO `it_ticket_services` (`id`, `service_group_id`, `name`, `code`, `parent_id`, `sort_order`, `requires_solution`, `status`) VALUES
(20, 1, 'Internet Connection', 'NET_INTERNET', NULL, 1, 1, 'active'),
(21, 1, 'VPN Access', 'NET_VPN', NULL, 2, 1, 'active'),
(22, 1, 'WiFi Issues', 'NET_WIFI', NULL, 3, 1, 'active'),
(23, 1, 'Email Issues', 'NET_EMAIL', NULL, 4, 1, 'active'),

-- Sub-services for Network
(24, 1, 'No Internet Connection', 'NET_INT_NO_CONN', 20, 1, 1, 'active'),
(25, 1, 'Slow Connection', 'NET_INT_SLOW', 20, 2, 1, 'active'),
(26, 1, 'Cannot Connect VPN', 'NET_VPN_CONN', 21, 1, 1, 'active'),
(27, 1, 'VPN Disconnects Frequently', 'NET_VPN_DISC', 21, 2, 1, 'active'),
(28, 1, 'WiFi Not Available', 'NET_WIFI_NO_CONN', 22, 1, 1, 'active'),
(29, 1, 'Weak WiFi Signal', 'NET_WIFI_WEAK', 22, 2, 1, 'active');

-- User Computer Services
INSERT INTO `it_ticket_services` (`id`, `service_group_id`, `name`, `code`, `parent_id`, `sort_order`, `requires_solution`, `status`) VALUES
(30, 3, 'Hardware Issues', 'UC_HARDWARE', NULL, 1, 1, 'active'),
(31, 3, 'Software Installation', 'UC_SOFTWARE', NULL, 2, 1, 'active'),
(32, 3, 'Printer Issues', 'UC_PRINTER', NULL, 3, 1, 'active'),
(33, 3, 'Account & Access', 'UC_ACCESS', NULL, 4, 1, 'active'),

-- Sub-services
(34, 3, 'Computer Not Starting', 'UC_HW_NO_START', 30, 1, 1, 'active'),
(35, 3, 'Monitor Issue', 'UC_HW_MONITOR', 30, 2, 1, 'active'),
(36, 3, 'Keyboard/Mouse Problem', 'UC_HW_INPUT', 30, 3, 1, 'active'),
(37, 3, 'Install MS Office', 'UC_SW_OFFICE', 31, 1, 1, 'active'),
(38, 3, 'Install Other Software', 'UC_SW_OTHER', 31, 2, 1, 'active'),
(39, 3, 'Printer Not Printing', 'UC_PR_NO_PRINT', 32, 1, 1, 'active'),
(40, 3, 'Printer Paper Jam', 'UC_PR_JAM', 32, 2, 1, 'active');

-- Cyber Security Services
INSERT INTO `it_ticket_services` (`id`, `service_group_id`, `name`, `code`, `parent_id`, `sort_order`, `requires_solution`, `status`) VALUES
(41, 4, 'Phishing & Spam', 'SEC_PHISHING', NULL, 1, 1, 'active'),
(42, 4, 'Malware/Virus', 'SEC_MALWARE', NULL, 2, 1, 'active'),
(43, 4, 'Account Security', 'SEC_ACCOUNT', NULL, 3, 1, 'active'),
(44, 4, 'Data Security', 'SEC_DATA', NULL, 4, 1, 'active'),

-- Sub-services
(45, 4, 'Suspicious Email Received', 'SEC_PHISH_EMAIL', 41, 1, 1, 'active'),
(46, 4, 'Clicked Phishing Link', 'SEC_PHISH_CLICK', 41, 2, 1, 'active'),
(47, 4, 'Virus Detected', 'SEC_MAL_VIRUS', 42, 1, 1, 'active'),
(48, 4, 'Computer Infected', 'SEC_MAL_INFECT', 42, 2, 1, 'active'),
(49, 4, 'Password Reset', 'SEC_ACC_PASS', 43, 1, 1, 'active'),
(50, 4, 'Account Locked', 'SEC_ACC_LOCK', 43, 2, 1, 'active'),
(51, 4, 'Unauthorized Access Attempt', 'SEC_ACC_UNAUTH', 43, 3, 1, 'active');

-- Business Application Services
INSERT INTO `it_ticket_services` (`id`, `service_group_id`, `name`, `code`, `parent_id`, `sort_order`, `requires_solution`, `status`) VALUES
(52, 5, 'ERP System', 'APP_ERP', NULL, 1, 1, 'active'),
(53, 5, 'CRM System', 'APP_CRM', NULL, 2, 1, 'active'),
(54, 5, 'Document Management', 'APP_DMS', NULL, 3, 1, 'active'),
(55, 5, 'Other Applications', 'APP_OTHER', NULL, 4, 1, 'active');

-- Infrastructure Services
INSERT INTO `it_ticket_services` (`id`, `service_group_id`, `name`, `code`, `parent_id`, `sort_order`, `requires_solution`, `status`) VALUES
(56, 6, 'Server Issues', 'INFRA_SERVER', NULL, 1, 1, 'active'),
(57, 6, 'Database Issues', 'INFRA_DATABASE', NULL, 2, 1, 'active'),
(58, 6, 'Backup & Recovery', 'INFRA_BACKUP', NULL, 3, 1, 'active'),
(59, 6, 'Storage Issues', 'INFRA_STORAGE', NULL, 4, 1, 'active');

-- ============================================
-- END OF SCHEMA
-- Total Tables: 30
-- Total Initial Records: ~200+
-- ============================================