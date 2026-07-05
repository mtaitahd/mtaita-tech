-- Migration 006: SMS Notifications & OTP Preferences
-- Adds column for OTP delivery preference and SMS notification log

ALTER TABLE public_users
ADD COLUMN IF NOT EXISTS otp_preference ENUM('email', 'sms') NOT NULL DEFAULT 'email' AFTER phone;

CREATE TABLE IF NOT EXISTS sms_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    batch_id VARCHAR(100) DEFAULT NULL,
    recipient VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('queued', 'sent', 'failed') DEFAULT 'queued',
    type VARCHAR(50) DEFAULT 'manual',
    reference_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type (type),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
