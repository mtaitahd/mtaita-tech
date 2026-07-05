-- LMS Integration Migration
-- Adds all LMS features from soma while keeping existing mtaita_tech_db structure
-- Compatible with MySQL 5.7+ and MariaDB

DELIMITER $$

DROP PROCEDURE IF EXISTS add_column_if_not_exists$$
CREATE PROCEDURE add_column_if_not_exists(
    IN p_table VARCHAR(64),
    IN p_column VARCHAR(64),
    IN p_definition TEXT
)
BEGIN
    DECLARE col_count INT;
    SET @db = (SELECT DATABASE());
    SELECT COUNT(*) INTO col_count
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @db AND TABLE_NAME = p_table AND COLUMN_NAME = p_column;
    IF col_count = 0 THEN
        SET @sql = CONCAT('ALTER TABLE ', p_table, ' ADD COLUMN ', p_column, ' ', p_definition);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$

DROP PROCEDURE IF EXISTS add_index_if_not_exists$$
CREATE PROCEDURE add_index_if_not_exists(
    IN p_table VARCHAR(64),
    IN p_index VARCHAR(64),
    IN p_definition TEXT
)
BEGIN
    DECLARE idx_count INT;
    SET @db = (SELECT DATABASE());
    SELECT COUNT(*) INTO idx_count
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = @db AND TABLE_NAME = p_table AND INDEX_NAME = p_index;
    IF idx_count = 0 THEN
        SET @sql = CONCAT('ALTER TABLE ', p_table, ' ADD INDEX ', p_index, ' ', p_definition);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$

DELIMITER ;

-- ============================================================
-- 1. Extend public_users with missing columns
-- ============================================================
CALL add_column_if_not_exists('public_users', 'phone', 'VARCHAR(50) DEFAULT NULL AFTER email');
CALL add_column_if_not_exists('public_users', 'profile_picture', 'VARCHAR(255) DEFAULT NULL AFTER password');
CALL add_column_if_not_exists('public_users', 'updated_at', 'TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER created_at');

-- ============================================================
-- 2. OTP verification table
-- ============================================================
CREATE TABLE IF NOT EXISTS user_otps (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT(10) UNSIGNED NOT NULL,
  type ENUM('verify', 'login', 'reset') NOT NULL,
  otp VARCHAR(10) NOT NULL,
  expires_at DATETIME NOT NULL,
  used TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_user_type (user_id, type),
  INDEX idx_expires_used (expires_at, used),
  FOREIGN KEY (user_id) REFERENCES public_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- 3. Upgrade courses table with additional columns
-- ============================================================
CALL add_column_if_not_exists('courses', 'thumbnail', "VARCHAR(255) DEFAULT NULL AFTER price");
CALL add_column_if_not_exists('courses', 'status', "ENUM('draft','published','archived') NOT NULL DEFAULT 'published' AFTER type");
CALL add_column_if_not_exists('courses', 'featured', 'TINYINT(1) NOT NULL DEFAULT 0 AFTER status');
CALL add_column_if_not_exists('courses', 'updated_at', 'TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER created_at');

-- ============================================================
-- 4. Modules table (hierarchical course structure)
-- ============================================================
CREATE TABLE IF NOT EXISTS modules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT(10) UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_course_id (course_id),
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- 5. Extend lessons table
-- Note: lessons has no created_at column, so we add it first,
-- then add updated_at AFTER sort_order (which exists)
-- ============================================================
CALL add_column_if_not_exists('lessons', 'created_at', 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER sort_order');
CALL add_column_if_not_exists('lessons', 'module_id', 'INT DEFAULT NULL AFTER course_id');
CALL add_column_if_not_exists('lessons', 'video_url', 'VARCHAR(500) DEFAULT NULL AFTER youtube_url');
CALL add_column_if_not_exists('lessons', 'code_content', 'TEXT DEFAULT NULL AFTER thumbnail');
CALL add_column_if_not_exists('lessons', 'zip_file', 'VARCHAR(255) DEFAULT NULL AFTER code_content');
CALL add_column_if_not_exists('lessons', 'is_paid', 'TINYINT(1) NOT NULL DEFAULT 0 AFTER sort_order');
CALL add_column_if_not_exists('lessons', 'updated_at', 'TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER created_at');
CALL add_index_if_not_exists('lessons', 'idx_module_id', '(module_id)');
CALL add_index_if_not_exists('lessons', 'idx_module_course', '(module_id, course_id)');

-- ============================================================
-- 6. Lesson progress tracking
-- ============================================================
CREATE TABLE IF NOT EXISTS lesson_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT(10) UNSIGNED NOT NULL,
    lesson_id INT(10) UNSIGNED NOT NULL,
    completed TINYINT(1) NOT NULL DEFAULT 0,
    completed_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_lesson (user_id, lesson_id),
    INDEX idx_user_id (user_id),
    INDEX idx_lesson_id (lesson_id),
    FOREIGN KEY (user_id) REFERENCES public_users(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- 7. Upgrade enrollments table
-- ============================================================
CALL add_column_if_not_exists('enrollments', 'payment_id', 'INT DEFAULT NULL AFTER id');
CALL add_column_if_not_exists('enrollments', 'updated_at', 'TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER created_at');
CALL add_index_if_not_exists('enrollments', 'idx_payment_id', '(payment_id)');

-- ============================================================
-- 8. Course access control (time-limited access)
-- ============================================================
CREATE TABLE IF NOT EXISTS course_access (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT(10) UNSIGNED NOT NULL,
    course_id INT(10) UNSIGNED NOT NULL,
    granted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME DEFAULT NULL,
    revoked TINYINT(1) NOT NULL DEFAULT 0,
    INDEX idx_user_course (user_id, course_id),
    FOREIGN KEY (user_id) REFERENCES public_users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- 9. Digital products table
-- ============================================================
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    type ENUM('code', 'video', 'both') NOT NULL DEFAULT 'code',
    price INT NOT NULL DEFAULT 0,
    is_paid TINYINT(1) NOT NULL DEFAULT 0,
    is_visible TINYINT(1) NOT NULL DEFAULT 1,
    youtube_url VARCHAR(500) DEFAULT NULL,
    zip_file VARCHAR(255) DEFAULT NULL,
    thumbnail VARCHAR(255) DEFAULT NULL,
    download_count INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_visible (is_visible),
    INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- 10. Orders table (digital product purchases)
-- ============================================================
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT(10) UNSIGNED NOT NULL,
    product_id INT NOT NULL,
    transaction_id VARCHAR(255) DEFAULT NULL,
    amount INT NOT NULL DEFAULT 0,
    status ENUM('pending', 'completed', 'failed', 'refunded') NOT NULL DEFAULT 'pending',
    snippe_reference VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_product_id (product_id),
    INDEX idx_status (status),
    INDEX idx_transaction_id (transaction_id),
    FOREIGN KEY (user_id) REFERENCES public_users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- 11. Product reviews
-- ============================================================
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT(10) UNSIGNED DEFAULT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) DEFAULT NULL,
    rating TINYINT NOT NULL DEFAULT 5,
    comment TEXT,
    is_approved TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_product_id (product_id),
    INDEX idx_approved (is_approved),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- 12. Newsletter subscribers
-- ============================================================
CREATE TABLE IF NOT EXISTS subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) DEFAULT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- 13. Gallery system
-- ============================================================
CREATE TABLE IF NOT EXISTS gallery_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) DEFAULT NULL,
    image_path VARCHAR(255) NOT NULL,
    alt_text VARCHAR(255) DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_active (is_active),
    INDEX idx_sort (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- 14. Student showcase submissions
-- ============================================================
CREATE TABLE IF NOT EXISTS student_showcase (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT(10) UNSIGNED DEFAULT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    image_path VARCHAR(255) DEFAULT NULL,
    project_url VARCHAR(500) DEFAULT NULL,
    is_approved TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_approved (is_approved),
    INDEX idx_user_id (user_id),
    FOREIGN KEY (user_id) REFERENCES public_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Cleanup helper procedures
DROP PROCEDURE IF EXISTS add_column_if_not_exists;
DROP PROCEDURE IF EXISTS add_index_if_not_exists;
