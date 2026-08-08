-- Coding Bootcamp / Online Coding Assessment Migration
-- Adds coding challenges attached to the existing LMS course/module/lesson structure.
-- Compatible with MySQL 5.7+ and MariaDB. Safe to re-run (IF NOT EXISTS).

CREATE TABLE IF NOT EXISTS coding_challenges (
    id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id INT(10) UNSIGNED NOT NULL,
    lesson_id INT(10) UNSIGNED DEFAULT NULL,
    module_id INT DEFAULT NULL,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) DEFAULT NULL,
    language ENUM('html','css','php','python','java','c','cpp') NOT NULL DEFAULT 'cpp',
    difficulty ENUM('easy','medium','hard') NOT NULL DEFAULT 'easy',
    marks INT NOT NULL DEFAULT 10,
    passing_score INT NOT NULL DEFAULT 50,
    time_limit INT NOT NULL DEFAULT 5,
    memory_limit INT NOT NULL DEFAULT 128,
    problem TEXT,
    input_desc TEXT,
    output_desc TEXT,
    constraints TEXT,
    sample_input TEXT,
    sample_output TEXT,
    starter_code MEDIUMTEXT,
    is_published TINYINT(1) NOT NULL DEFAULT 0,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_course (course_id),
    INDEX idx_module (module_id),
    INDEX idx_lesson (lesson_id),
    INDEX idx_published (is_published),
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE SET NULL,
    FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS coding_test_cases (
    id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    challenge_id INT(10) UNSIGNED NOT NULL,
    input_data TEXT,
    expected_output TEXT,
    is_visible TINYINT(1) NOT NULL DEFAULT 0,
    sort_order INT NOT NULL DEFAULT 0,
    INDEX idx_challenge (challenge_id),
    FOREIGN KEY (challenge_id) REFERENCES coding_challenges(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS coding_submissions (
    id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    challenge_id INT(10) UNSIGNED NOT NULL,
    user_id INT(10) UNSIGNED NOT NULL,
    language ENUM('html','css','php','python','java','c','cpp') NOT NULL,
    code MEDIUMTEXT NOT NULL,
    status ENUM('passed','failed','error','timeout','compilation_error','invalid') NOT NULL DEFAULT 'error',
    score INT NOT NULL DEFAULT 0,
    total_marks INT NOT NULL DEFAULT 0,
    tests_total INT NOT NULL DEFAULT 0,
    tests_passed INT NOT NULL DEFAULT 0,
    execution_time FLOAT NOT NULL DEFAULT 0,
    passed TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_challenge (challenge_id),
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_created (created_at),
    FOREIGN KEY (challenge_id) REFERENCES coding_challenges(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES public_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS coding_submission_results (
    id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    submission_id INT(10) UNSIGNED NOT NULL,
    test_case_id INT(10) UNSIGNED DEFAULT NULL,
    passed TINYINT(1) NOT NULL DEFAULT 0,
    status ENUM('passed','failed','error','timeout','skipped') NOT NULL DEFAULT 'failed',
    actual_output TEXT,
    execution_time FLOAT NOT NULL DEFAULT 0,
    INDEX idx_submission (submission_id),
    FOREIGN KEY (submission_id) REFERENCES coding_submissions(id) ON DELETE CASCADE,
    FOREIGN KEY (test_case_id) REFERENCES coding_test_cases(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
