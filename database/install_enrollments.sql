CREATE TABLE IF NOT EXISTS enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    payment_id INT DEFAULT NULL,
    user_id INT(10) UNSIGNED NOT NULL,
    course_id INT(10) UNSIGNED NOT NULL,
    progress DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    status ENUM('pending', 'active', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_course_id (course_id),
    INDEX idx_payment_id (payment_id),
    INDEX idx_status (status),
    FOREIGN KEY (user_id) REFERENCES public_users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
