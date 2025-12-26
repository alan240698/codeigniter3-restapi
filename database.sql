
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
  `service_group_id` INT UNSIGNED NOT NULL,
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
  UNIQUE KEY `uk_code_service_group` (`code`, `service_group_id`),
  INDEX `idx_service_group` (`service_group_id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_sort_order` (`sort_order`),
  CONSTRAINT `fk_ticket_types_service_group` FOREIGN KEY (`service_group_id`) 
    REFERENCES `it_ticket_service_groups` (`id`) ON DELETE CASCADE
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

-- 11. IT Services (depends on: it_ticket_types, SELF-REFERENCE)
CREATE TABLE `it_ticket_services` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_type_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `code` VARCHAR(50) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `parent_id` INT UNSIGNED DEFAULT NULL,
  `input_type` ENUM('radio', 'dropdown', 'tree') NOT NULL DEFAULT 'dropdown',
  `icon` VARCHAR(50) DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_code` (`code`),
  INDEX `idx_ticket_type` (`ticket_type_id`),
  INDEX `idx_parent` (`parent_id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_sort_order` (`sort_order`),
  CONSTRAINT `fk_services_ticket_type` FOREIGN KEY (`ticket_type_id`) 
    REFERENCES `it_ticket_types` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_services_parent` FOREIGN KEY (`parent_id`) 
    REFERENCES `it_ticket_services` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='IT Services';

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
-- PHASE 5: TICKETS TABLE
-- Bảng chính phụ thuộc nhiều bảng khác
-- ============================================

-- 19. Tickets (depends on: service_groups, ticket_types, services, workflows, workflow_states, support_teams)
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
  `requires_solution` TINYINT(1) NOT NULL DEFAULT 1,
  `solution` TEXT DEFAULT NULL,
  `solution_provided_at` DATETIME DEFAULT NULL,
  `solution_provided_by` INT UNSIGNED DEFAULT NULL,
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
    REFERENCES `it_ticket_support_teams` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Main tickets table';

-- ============================================
-- PHASE 6: TICKET RELATED TABLES
-- Bảng phụ thuộc vào tickets
-- ============================================

-- 20. Ticket History (depends on: it_ticket_tickets)
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

-- 21. Ticket Comments (depends on: it_ticket_tickets)
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

-- 22. Ticket Attachments (depends on: it_ticket_tickets, it_ticket_comments)
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

-- 23. Ticket Escalations (depends on: it_ticket_tickets, it_ticket_support_teams)
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

-- 24. Ticket Approvals (depends on: it_ticket_tickets)
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

-- 25. Ticket Notifications (depends on: it_ticket_tickets)
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

-- 26. Email Logs (depends on: it_ticket_tickets, it_ticket_email_templates)
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

CREATE TABLE IF NOT EXISTS `it_ticket_template_instances` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `template_id` INT(11) NOT NULL COMMENT 'ID của template',
  `token` VARCHAR(64) NOT NULL COMMENT 'Token để truy cập',
  `recipient_email` VARCHAR(255) DEFAULT NULL COMMENT 'Email người nhận',
  `rendered_subject` VARCHAR(500) NOT NULL COMMENT 'Subject đã thay thế biến',
  `rendered_body` TEXT NOT NULL COMMENT 'Body đã thay thế biến',
  `context_data` TEXT DEFAULT NULL COMMENT 'Dữ liệu JSON gốc',
  `expires_at` DATETIME NOT NULL COMMENT 'Ngày hết hạn',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_token` (`token`),
  KEY `idx_template_id` (`template_id`),
  KEY `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 2: Log các action (approve, reject, etc.)
CREATE TABLE IF NOT EXISTS `it_ticket_template_actions` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `instance_id` INT(11) NOT NULL COMMENT 'ID của instance',
  `action_type` VARCHAR(50) NOT NULL COMMENT 'Loại action: approve, reject',
  `action_data` TEXT DEFAULT NULL COMMENT 'Dữ liệu thêm (JSON)',
  `ip_address` VARCHAR(45) DEFAULT NULL COMMENT 'IP address',
  `user_agent` TEXT DEFAULT NULL COMMENT 'Browser user agent',
  `performed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_instance_id` (`instance_id`),
  KEY `idx_action_type` (`action_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;