-- Migration 012: Admin-managed service pricing for the public Pricing page
-- The public page reads these packages and the admin/manager page writes them.

CREATE TABLE IF NOT EXISTS service_pricing (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_slug VARCHAR(80) NOT NULL,
    package_name VARCHAR(150) NOT NULL,
    description VARCHAR(500) NOT NULL DEFAULT '',
    price_tzs INT NOT NULL DEFAULT 0,
    price_usd DECIMAL(12,2) DEFAULT NULL,
    billing ENUM('one-off','annual') NOT NULL DEFAULT 'one-off',
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_service (service_slug),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Ensure the settings keys used by the pricing feature exist without
-- overwriting any value an administrator has already saved.
INSERT INTO settings (setting_key, setting_value) VALUES ('usd_rate', '')
ON DUPLICATE KEY UPDATE setting_value = setting_value;

INSERT INTO settings (setting_key, setting_value) VALUES ('pricing_valid_year', '')
ON DUPLICATE KEY UPDATE setting_value = setting_value;
