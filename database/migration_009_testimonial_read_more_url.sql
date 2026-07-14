-- Migration 009: Add read_more_url to testimonials table
ALTER TABLE testimonials ADD COLUMN read_more_url VARCHAR(500) DEFAULT NULL AFTER content;
