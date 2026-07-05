CREATE TABLE IF NOT EXISTS lessons (
    id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id INT(10) UNSIGNED NOT NULL,
    module_id INT DEFAULT NULL,
    title VARCHAR(200) NOT NULL,
    youtube_url VARCHAR(500) DEFAULT NULL,
    video_url VARCHAR(500) DEFAULT NULL,
    thumbnail VARCHAR(255) DEFAULT NULL,
    code_content TEXT DEFAULT NULL,
    zip_file VARCHAR(255) DEFAULT NULL,
    is_paid TINYINT(1) NOT NULL DEFAULT 0,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_course_id (course_id),
    INDEX idx_module_id (module_id),
    INDEX idx_module_course (module_id, course_id),
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
