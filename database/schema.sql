-- Mtaita Tech - Database Schema
-- Run this SQL to set up the database

CREATE DATABASE IF NOT EXISTS mtaita_tech_db;
USE mtaita_tech_db;

-- Users table for admin authentication
CREATE TABLE IF NOT EXISTS users (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'editor') NOT NULL DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Portfolio table for projects
CREATE TABLE IF NOT EXISTS portfolio (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_title VARCHAR(255) NOT NULL,
    project_desc TEXT,
    project_link VARCHAR(500),
    category VARCHAR(100) DEFAULT 'Websites',
    tech_stack VARCHAR(500) DEFAULT '',
    completion_year VARCHAR(4) DEFAULT '',
    is_live TINYINT(1) NOT NULL DEFAULT 1,
    project_screenshot VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Announcements table for scrolling ticker bar
CREATE TABLE IF NOT EXISTS announcements (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    announcement_text TEXT NOT NULL,
    text_color VARCHAR(7) DEFAULT '#FFFFFF',
    badge VARCHAR(50) NOT NULL DEFAULT '',
    badge_bg VARCHAR(7) NOT NULL DEFAULT '#DC2626',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert a default admin user (password: admin123)
INSERT INTO users (username, password, role) VALUES
('admin', '$2y$10$1aBLJ8vqrocsPYSpVYHj2OzZtnZWROKiyAiG.UD/WXtD83snusPSe', 'admin');

-- Insert sample announcements
INSERT INTO announcements (announcement_text, text_color, is_active) VALUES
('Welcome to Mtaita Tech — Delivering Innovation in IT & Graphic Design.', '#FCD34D', 1),
('New: Web Development packages starting at KES 15,000!', '#6EE7B7', 1);

-- Posters table for dynamic homepage advertising banners
CREATE TABLE IF NOT EXISTS posters (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    poster_title VARCHAR(255) NOT NULL,
    poster_image_path VARCHAR(255) NOT NULL,
    redirect_link VARCHAR(500) NOT NULL DEFAULT '#',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Thumbnails table for YouTube thumbnail showcase
CREATE TABLE IF NOT EXISTS thumbnails (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    thumbnail_title VARCHAR(255) NOT NULL,
    thumbnail_image_path VARCHAR(255) NOT NULL,
    video_category VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
