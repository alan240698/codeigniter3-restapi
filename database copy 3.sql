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
  `is_system` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Flag to protect system roles (1=system, 0=custom)',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_name` (`name`),
  INDEX `idx_name` (`name`),
  INDEX `idx_is_system` (`is_system`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='System roles with protection for core roles';

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
-- IT TICKET SYSTEM - INITIAL DATA INSERT
-- Version: 1.0
-- Date: 2026-01-07
-- Description: Complete initial data for all tables
-- ============================================

-- ============================================
-- PHASE 1: FOUNDATION DATA
-- ============================================

-- 1. Roles (System roles)
INSERT INTO `it_ticket_roles` (`name`, `display_name`, `description`, `permissions`, `is_system`) VALUES
('admin', 'System Administrator', 'Full system access with all permissions', '{"all": true}', 1),
('support_agent', 'Support Agent', 'Can view and manage assigned tickets', '{"tickets.view": true, "tickets.update": true, "tickets.comment": true}', 1),
('manager', 'Support Manager', 'Can manage team and oversee all tickets', '{"tickets.view": true, "tickets.assign": true, "tickets.approve": true, "team.manage": true}', 1),
('team_lead', 'Team Lead', 'Can manage team members and their tickets', '{"tickets.view": true, "tickets.assign": true, "team.view": true}', 0),
('requester', 'End User', 'Can create and view own tickets', '{"tickets.create": true, "tickets.view_own": true}', 0);

-- 2. Service Groups
INSERT INTO `it_ticket_service_groups` (`name`, `code`, `description`, `icon`, `color`, `sort_order`, `status`) VALUES
('Hardware Support', 'HARDWARE', 'Hardware related services and support', 'laptop', '#3B82F6', 1, 'active'),
('Software Support', 'SOFTWARE', 'Software applications and licensing', 'code', '#10B981', 2, 'active'),
('Network & Infrastructure', 'NETWORK', 'Network connectivity and infrastructure', 'network-wired', '#8B5CF6', 3, 'active'),
('Security & Access', 'SECURITY', 'Security, access control and permissions', 'shield-alt', '#EF4444', 4, 'active'),
('Email & Communication', 'EMAIL', 'Email, messaging and communication tools', 'envelope', '#F59E0B', 5, 'active'),
('Cloud Services', 'CLOUD', 'Cloud platforms and services', 'cloud', '#06B6D4', 6, 'active'),
('Database Services', 'DATABASE', 'Database management and support', 'database', '#EC4899', 7, 'active'),
('General IT Support', 'GENERAL', 'General IT requests and inquiries', 'question-circle', '#6B7280', 8, 'active');

-- 3. Support Teams
INSERT INTO `it_ticket_support_teams` (`name`, `code`, `description`, `support_level`, `department_code`, `country`, `email`, `status`) VALUES
('L1 Helpdesk', 'L1-HELPDESK', 'First line support - general inquiries and basic troubleshooting', 'L1', 'IT', 'Vietnam', 'l1-helpdesk@company.com', 'active'),
('L2 Technical Support', 'L2-TECH', 'Second line support - advanced technical issues', 'L2', 'IT', 'Vietnam', 'l2-tech@company.com', 'active'),
('L3 Infrastructure', 'L3-INFRA', 'Third line support - infrastructure and network', 'L3', 'IT', 'Vietnam', 'l3-infra@company.com', 'active'),
('L3 Security Team', 'L3-SEC', 'Security and access control specialists', 'L3', 'IT', 'Vietnam', 'security@company.com', 'active'),
('L4 Engineering', 'L4-ENG', 'Engineering team - complex system issues', 'L4', 'IT', 'Vietnam', 'engineering@company.com', 'active');

-- 4. Workflows
INSERT INTO `it_ticket_workflows` (`name`, `code`, `description`, `status`) VALUES
('Standard Workflow', 'STANDARD', 'Standard ticket workflow for most services', 'active'),
('Approval Required Workflow', 'APPROVAL', 'Workflow requiring approval before assignment', 'active'),
('Emergency Workflow', 'EMERGENCY', 'Fast-track workflow for critical issues', 'active'),
('Change Request Workflow', 'CHANGE', 'Workflow for change management requests', 'active');

-- 5. SLA Policies
INSERT INTO `it_ticket_sla_policies` (`name`, `description`, `priority`, `first_response_hours`, `resolution_hours`, `business_hours_only`, `status`) VALUES
('Critical - 4 Hours', 'Critical priority - 4 hour resolution', 'critical', 1, 4, 0, 'active'),
('High - 8 Hours', 'High priority - 8 hour resolution', 'high', 2, 8, 0, 'active'),
('Medium - 24 Hours', 'Medium priority - 24 hour resolution', 'medium', 4, 24, 1, 'active'),
('Low - 48 Hours', 'Low priority - 48 hour resolution', 'low', 8, 48, 1, 'active');

-- 6. Email Templates
INSERT INTO `it_ticket_email_templates` (`template_code`, `template_name`, `subject`, `body`, `variables`, `status`) VALUES
('TICKET_CREATED', 'Ticket Created Notification', 'Ticket #{ticket_number} Created', 
'<p>Dear {requester_name},</p>
<p>Your ticket has been created successfully.</p>
<p><strong>Ticket Number:</strong> {ticket_number}</p>
<p><strong>Subject:</strong> {subject}</p>
<p><strong>Priority:</strong> {priority}</p>
<p>We will respond to your request soon.</p>',
'["ticket_number", "requester_name", "subject", "priority"]', 'active'),

('TICKET_ASSIGNED', 'Ticket Assignment Notification', 'Ticket #{ticket_number} Assigned to You',
'<p>Dear {assignee_name},</p>
<p>A ticket has been assigned to you.</p>
<p><strong>Ticket Number:</strong> {ticket_number}</p>
<p><strong>Subject:</strong> {subject}</p>
<p><strong>Priority:</strong> {priority}</p>
<p><strong>SLA Due:</strong> {sla_due_date}</p>',
'["ticket_number", "assignee_name", "subject", "priority", "sla_due_date"]', 'active'),

('TICKET_RESOLVED', 'Ticket Resolved Notification', 'Ticket #{ticket_number} Resolved',
'<p>Dear {requester_name},</p>
<p>Your ticket has been resolved.</p>
<p><strong>Ticket Number:</strong> {ticket_number}</p>
<p><strong>Resolution:</strong> {solution}</p>
<p>If you need further assistance, please reply to this email.</p>',
'["ticket_number", "requester_name", "solution"]', 'active'),

('APPROVAL_REQUEST', 'Approval Request Notification', 'Approval Required for Ticket #{ticket_number}',
'<p>Dear {approver_name},</p>
<p>Your approval is required for the following ticket:</p>
<p><strong>Ticket Number:</strong> {ticket_number}</p>
<p><strong>Subject:</strong> {subject}</p>
<p><strong>Requester:</strong> {requester_name}</p>
<p>Please review and approve/reject this request.</p>',
'["ticket_number", "approver_name", "subject", "requester_name"]', 'active');

-- ============================================
-- PHASE 2: LEVEL 1 DEPENDENCIES
-- ============================================

-- 7. User Roles (example data - adjust employee_id as needed)
INSERT INTO `it_ticket_user_roles` (`employee_id`, `role_id`, `is_active`, `assigned_by`) VALUES
(1, 1, 1, 1),  -- Employee 1 = Admin
(2, 3, 1, 1),  -- Employee 2 = Manager
(3, 2, 1, 1),  -- Employee 3 = Support Agent
(4, 2, 1, 1),  -- Employee 4 = Support Agent
(5, 4, 1, 1),  -- Employee 5 = Team Lead
(6, 2, 1, 1),  -- Employee 6 = Support Agent
(7, 2, 1, 1);  -- Employee 7 = Support Agent

-- 8. Ticket Types
INSERT INTO `it_ticket_types` (`name`, `code`, `description`, `icon`, `color`, `sort_order`, `status`) VALUES
('Incident', 'INCIDENT', 'Unplanned interruption or reduction in service quality', 'exclamation-triangle', '#EF4444', 1, 'active'),
('Service Request', 'SERVICE_REQ', 'Request for service or information', 'hand-paper', '#3B82F6', 2, 'active'),
('Change Request', 'CHANGE_REQ', 'Request for change in IT infrastructure or services', 'sync-alt', '#8B5CF6', 3, 'active'),
('Problem', 'PROBLEM', 'Root cause of one or more incidents', 'search', '#F59E0B', 4, 'active');

-- 9. Team Members
INSERT INTO `it_ticket_team_members` (`team_id`, `employee_id`, `role`, `is_active`) VALUES
-- L1 Helpdesk Team
(1, 3, 'member', 1),
(1, 4, 'member', 1),
(1, 5, 'lead', 1),
-- L2 Technical Support
(2, 6, 'member', 1),
(2, 7, 'lead', 1),
-- L3 Infrastructure
(3, 8, 'member', 1),
-- L3 Security
(4, 9, 'member', 1),
-- L4 Engineering
(5, 10, 'manager', 1);

-- 10. Workflow States
INSERT INTO `it_ticket_workflow_states` (`workflow_id`, `name`, `code`, `description`, `state_type`, `color`, `sort_order`) VALUES
-- Standard Workflow States
(1, 'New', 'NEW', 'Ticket just created', 'initial', '#6B7280', 1),
(1, 'Assigned', 'ASSIGNED', 'Ticket assigned to agent', 'intermediate', '#3B82F6', 2),
(1, 'In Progress', 'IN_PROGRESS', 'Agent working on ticket', 'intermediate', '#F59E0B', 3),
(1, 'Resolved', 'RESOLVED', 'Ticket resolved', 'final', '#10B981', 4),
(1, 'Closed', 'CLOSED', 'Ticket closed', 'final', '#6B7280', 5),
(1, 'Cancelled', 'CANCELLED', 'Ticket cancelled', 'cancelled', '#EF4444', 6),

-- Approval Workflow States
(2, 'New', 'NEW', 'Ticket just created', 'initial', '#6B7280', 1),
(2, 'Pending Approval', 'PENDING_APPROVAL', 'Waiting for approval', 'intermediate', '#F59E0B', 2),
(2, 'Approved', 'APPROVED', 'Request approved', 'intermediate', '#10B981', 3),
(2, 'Rejected', 'REJECTED', 'Request rejected', 'cancelled', '#EF4444', 4),
(2, 'Assigned', 'ASSIGNED', 'Assigned after approval', 'intermediate', '#3B82F6', 5),
(2, 'In Progress', 'IN_PROGRESS', 'Being worked on', 'intermediate', '#F59E0B', 6),
(2, 'Resolved', 'RESOLVED', 'Ticket resolved', 'final', '#10B981', 7),
(2, 'Closed', 'CLOSED', 'Ticket closed', 'final', '#6B7280', 8),

-- Emergency Workflow States
(3, 'Critical', 'CRITICAL', 'Critical issue', 'initial', '#EF4444', 1),
(3, 'Investigating', 'INVESTIGATING', 'Team investigating', 'intermediate', '#F59E0B', 2),
(3, 'Resolving', 'RESOLVING', 'Applying fix', 'intermediate', '#3B82F6', 3),
(3, 'Resolved', 'RESOLVED', 'Issue resolved', 'final', '#10B981', 4),
(3, 'Closed', 'CLOSED', 'Incident closed', 'final', '#6B7280', 5);

-- ============================================
-- PHASE 3: LEVEL 2 DEPENDENCIES
-- ============================================

-- 11. IT Services (Parent services first, then children)
INSERT INTO `it_ticket_services` (`service_group_id`, `name`, `code`, `description`, `parent_id`, `icon`, `sort_order`, `requires_solution`, `status`) VALUES
-- HARDWARE Services
(1, 'Desktop Support', 'HW-DESKTOP', 'Desktop computer support', NULL, 'desktop', 1, 'yes', 'active'),
(1, 'Laptop Support', 'HW-LAPTOP', 'Laptop computer support', NULL, 'laptop', 2, 'yes', 'active'),
(1, 'Printer Support', 'HW-PRINTER', 'Printer and scanner support', NULL, 'print', 3, 'yes', 'active'),
(1, 'Hardware Replacement', 'HW-REPLACE', 'Request new hardware', 1, 'exchange-alt', 4, 'no', 'active'),
(1, 'Hardware Repair', 'HW-REPAIR', 'Repair existing hardware', 1, 'wrench', 5, 'yes', 'active'),

-- SOFTWARE Services
(2, 'Application Installation', 'SW-INSTALL', 'Install new software', NULL, 'download', 1, 'no', 'active'),
(2, 'Application Support', 'SW-SUPPORT', 'Software troubleshooting', NULL, 'life-ring', 2, 'yes', 'active'),
(2, 'License Request', 'SW-LICENSE', 'Software license request', NULL, 'key', 3, 'no', 'active'),
(2, 'Software Update', 'SW-UPDATE', 'Update existing software', 2, 'sync', 4, 'no', 'active'),

-- NETWORK Services
(3, 'Network Connectivity', 'NET-CONNECT', 'Network connection issues', NULL, 'network-wired', 1, 'yes', 'active'),
(3, 'VPN Access', 'NET-VPN', 'VPN access request', NULL, 'shield-alt', 2, 'no', 'active'),
(3, 'WiFi Issues', 'NET-WIFI', 'Wireless network problems', 3, 'wifi', 3, 'yes', 'active'),

-- SECURITY Services
(4, 'Account Access', 'SEC-ACCESS', 'Account access requests', NULL, 'user-lock', 1, 'no', 'active'),
(4, 'Password Reset', 'SEC-PASSWORD', 'Password reset requests', NULL, 'key', 2, 'no', 'active'),
(4, 'Security Incident', 'SEC-INCIDENT', 'Security breach or threat', NULL, 'exclamation-triangle', 3, 'yes', 'active'),

-- EMAIL Services
(5, 'Email Issues', 'EMAIL-ISSUE', 'Email delivery or access problems', NULL, 'envelope', 1, 'yes', 'active'),
(5, 'Email Account Request', 'EMAIL-ACCOUNT', 'New email account request', NULL, 'user-plus', 2, 'no', 'active'),
(5, 'Distribution List', 'EMAIL-DL', 'Manage distribution lists', NULL, 'users', 3, 'no', 'active'),

-- CLOUD Services
(6, 'Cloud Storage', 'CLOUD-STORAGE', 'Cloud storage issues', NULL, 'cloud-upload-alt', 1, 'yes', 'active'),
(6, 'SaaS Application', 'CLOUD-SAAS', 'SaaS application support', NULL, 'cloud', 2, 'yes', 'active'),

-- DATABASE Services
(7, 'Database Access', 'DB-ACCESS', 'Database access request', NULL, 'database', 1, 'no', 'active'),
(7, 'Database Issues', 'DB-ISSUE', 'Database performance or errors', NULL, 'exclamation-circle', 2, 'yes', 'active'),

-- GENERAL Services
(8, 'General Inquiry', 'GEN-INQUIRY', 'General IT questions', NULL, 'question-circle', 1, 'yes', 'active'),
(8, 'Consultation', 'GEN-CONSULT', 'IT consultation request', NULL, 'comments', 2, 'yes', 'active');

-- 12. Workflow Transitions
INSERT INTO `it_ticket_workflow_transitions` (`workflow_id`, `from_state_id`, `to_state_id`, `name`, `description`, `sort_order`) VALUES
-- Standard Workflow Transitions
(1, NULL, 1, 'Create', 'Create new ticket', 1),
(1, 1, 2, 'Assign', 'Assign to agent', 2),
(1, 2, 3, 'Start Work', 'Begin working on ticket', 3),
(1, 3, 4, 'Resolve', 'Mark as resolved', 4),
(1, 4, 5, 'Close', 'Close ticket', 5),
(1, 1, 6, 'Cancel', 'Cancel ticket', 6),
(1, 2, 6, 'Cancel', 'Cancel ticket', 7),

-- Approval Workflow Transitions
(2, NULL, 7, 'Create', 'Create new ticket', 1),
(2, 7, 8, 'Request Approval', 'Submit for approval', 2),
(2, 8, 9, 'Approve', 'Approve request', 3),
(2, 8, 10, 'Reject', 'Reject request', 4),
(2, 9, 11, 'Assign', 'Assign after approval', 5),
(2, 11, 12, 'Start Work', 'Begin work', 6),
(2, 12, 13, 'Resolve', 'Mark as resolved', 7),
(2, 13, 14, 'Close', 'Close ticket', 8),

-- Emergency Workflow Transitions
(3, NULL, 15, 'Report Critical', 'Report critical issue', 1),
(3, 15, 16, 'Investigate', 'Start investigation', 2),
(3, 16, 17, 'Apply Fix', 'Apply resolution', 3),
(3, 17, 18, 'Resolve', 'Mark resolved', 4),
(3, 18, 19, 'Close', 'Close incident', 5);

-- ============================================
-- PHASE 4: LEVEL 3 DEPENDENCIES (RULES)
-- ============================================

-- 13. Service Workflows
INSERT INTO `it_ticket_service_workflows` (`it_service_id`, `workflow_id`, `is_active`) VALUES
-- Standard workflow for most services
(1, 1, 1), (2, 1, 1), (3, 1, 1), (5, 1, 1), (7, 1, 1), (9, 1, 1),
(10, 1, 1), (12, 1, 1), (16, 1, 1), (17, 1, 1), (20, 1, 1), (22, 1, 1), (23, 1, 1),
-- Approval workflow for requests
(4, 2, 1), (6, 2, 1), (8, 2, 1), (11, 2, 1), (13, 2, 1), (14, 2, 1), (18, 2, 1), (19, 2, 1), (21, 2, 1),
-- Emergency workflow for critical issues
(15, 3, 1);

-- 14. Routing Rules
INSERT INTO `it_ticket_routing_rules` (`it_service_id`, `rule_name`, `priority`, `assignment_type`, `target_team_id`, `status`) VALUES
(1, 'Desktop Support to L1', 100, 'team', 1, 'active'),
(2, 'Laptop Support to L1', 100, 'team', 1, 'active'),
(3, 'Printer Support to L1', 100, 'team', 1, 'active'),
(7, 'Application Support to L2', 100, 'team', 2, 'active'),
(10, 'Network Issues to L3 Infrastructure', 100, 'team', 3, 'active'),
(11, 'VPN to L3 Infrastructure', 100, 'team', 3, 'active'),
(13, 'Account Access to L3 Security', 100, 'team', 4, 'active'),
(14, 'Password Reset to L1', 100, 'team', 1, 'active'),
(15, 'Security Incident to L3 Security', 100, 'team', 4, 'active'),
(16, 'Email Issues to L2', 100, 'team', 2, 'active');

-- 15. Approval Rules
INSERT INTO `it_ticket_approval_rules` (`it_service_id`, `rule_name`, `requires_approval`, `approval_type`, `status`) VALUES
(4, 'Hardware Replacement Approval', 1, 'manager', 'active'),
(6, 'Software Installation Approval', 1, 'manager', 'active'),
(8, 'License Request Approval', 1, 'manager', 'active'),
(11, 'VPN Access Approval', 1, 'department_head', 'active'),
(13, 'Account Access Approval', 1, 'department_head', 'active'),
(18, 'Email Account Approval', 1, 'manager', 'active'),
(21, 'Database Access Approval', 1, 'department_head', 'active');

-- 16. Level Rules
INSERT INTO `it_ticket_level_rules` (`it_service_id`, `level_number`, `level_name`, `support_team_id`, `auto_escalate_hours`, `status`) VALUES
-- Desktop Support levels
(1, 1, 'L1 Support', 1, 4, 'active'),
(1, 2, 'L2 Technical', 2, 8, 'active'),
(1, 3, 'L3 Infrastructure', 3, NULL, 'active'),
-- Application Support levels
(7, 1, 'L1 Support', 1, 2, 'active'),
(7, 2, 'L2 Technical', 2, 4, 'active'),
(7, 3, 'L4 Engineering', 5, NULL, 'active'),
-- Network Connectivity levels
(10, 1, 'L2 Technical', 2, 2, 'active'),
(10, 2, 'L3 Infrastructure', 3, 4, 'active'),
(10, 3, 'L4 Engineering', 5, NULL, 'active');

-- 17. Custom Fields
INSERT INTO `it_ticket_custom_fields` (`it_service_id`, `field_name`, `field_label`, `field_type`, `field_options`, `is_required`, `sort_order`, `status`) VALUES
-- Hardware Replacement fields
(4, 'current_device', 'Current Device Model', 'text', NULL, 1, 1, 'active'),
(4, 'replacement_reason', 'Reason for Replacement', 'select', '["Broken", "Outdated", "Lost", "Upgrade needed"]', 1, 2, 'active'),
(4, 'preferred_model', 'Preferred New Model', 'text', NULL, 0, 3, 'active'),

-- Software Installation fields
(6, 'software_name', 'Software Name', 'text', NULL, 1, 1, 'active'),
(6, 'software_version', 'Version Required', 'text', NULL, 0, 2, 'active'),
(6, 'business_justification', 'Business Justification', 'textarea', NULL, 1, 3, 'active'),

-- VPN Access fields
(11, 'access_duration', 'Access Duration', 'select', '["1 month", "3 months", "6 months", "1 year", "Permanent"]', 1, 1, 'active'),
(11, 'access_reason', 'Reason for VPN Access', 'textarea', NULL, 1, 2, 'active'),

-- Account Access fields
(13, 'account_type', 'Account Type', 'select', '["Email", "Database", "Application", "Admin", "Service Account"]', 1, 1, 'active'),
(13, 'access_level', 'Access Level', 'select', '["Read Only", "Read/Write", "Admin", "Full Control"]', 1, 2, 'active'),
(13, 'manager_approval', 'Manager Name', 'text', NULL, 1, 3, 'active');

-- 18. Service SLA
INSERT INTO `it_ticket_service_sla` (`it_service_id`, `sla_policy_id`, `is_active`) VALUES
-- Critical services - 4 hour SLA
(15, 1, 1), -- Security Incident
(22, 1, 1), -- Database Issues

-- High priority services - 8 hour SLA
(1, 2, 1),  -- Desktop Support
(2, 2, 1),  -- Laptop Support
(10, 2, 1), -- Network Connectivity
(16, 2, 1), -- Email Issues

-- Medium priority services - 24 hour SLA
(3, 3, 1),  -- Printer Support
(7, 3, 1),  -- Application Support
(11, 3, 1), -- VPN Access
(17, 3, 1), -- Email Account Request

-- Low priority services - 48 hour SLA
(6, 4, 1),  -- Software Installation
(8, 4, 1),  -- License Request
(23, 4, 1), -- General Inquiry
(24, 4, 1); -- Consultation

-- ============================================
-- INITIAL DATA INSERT COMPLETE
-- ============================================

-- ============================================
-- END OF SCHEMA
-- Total Tables: 30
-- Total Initial Records: ~200+
-- ============================================