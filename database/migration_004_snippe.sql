-- Snippe Payments System
-- Migration 004: Payments table + enrollments update

CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id VARCHAR(100) NOT NULL,
    payment_reference VARCHAR(100) NOT NULL UNIQUE COMMENT 'Our internal reference (PAY-XXX)',
    snippe_reference VARCHAR(100) DEFAULT NULL COMMENT 'Snippe UUID reference',
    payment_type ENUM('mobile','card','dynamic-qr') NOT NULL,
    amount INT NOT NULL COMMENT 'Amount in TZS (smallest unit)',
    currency VARCHAR(3) NOT NULL DEFAULT 'TZS',
    customer_name VARCHAR(255) DEFAULT NULL,
    customer_email VARCHAR(255) DEFAULT NULL,
    customer_phone VARCHAR(50) DEFAULT NULL,
    status ENUM('pending','completed','failed','voided','expired') NOT NULL DEFAULT 'pending',
    transaction_data JSON DEFAULT NULL COMMENT 'Full API response on creation',
    webhook_payload JSON DEFAULT NULL COMMENT 'Latest webhook payload',
    idempotency_key VARCHAR(30) DEFAULT NULL,
    payment_url VARCHAR(500) DEFAULT NULL COMMENT 'Card redirect URL',
    payment_qr_code TEXT DEFAULT NULL COMMENT 'QR code data string',
    metadata JSON DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Update enrollments: add cancelled to status enum + add missing columns
ALTER TABLE enrollments
    MODIFY COLUMN status ENUM('pending','active','cancelled') NOT NULL DEFAULT 'pending',
    ADD COLUMN payment_id INT DEFAULT NULL AFTER id,
    ADD COLUMN updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER created_at,
    ADD INDEX idx_payment_id (payment_id);

-- Indexes for lookups
CREATE INDEX idx_payment_reference ON payments(payment_reference);
CREATE INDEX idx_snippe_reference ON payments(snippe_reference);
CREATE INDEX idx_order_id ON payments(order_id);
CREATE INDEX idx_status ON payments(status);
