-- ============================================================================
-- Tạo 2 bảng mới cho Template System (KHÔNG có foreign key)
-- ============================================================================

-- Bảng 1: Lưu các template instance đã render
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

-- Xong! Kiểm tra kết quả
SELECT 'Đã tạo 2 bảng thành công!' AS status;
SHOW TABLES LIKE 'it_ticket_template%';
