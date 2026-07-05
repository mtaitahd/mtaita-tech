ALTER TABLE portfolio
ADD COLUMN IF NOT EXISTS category VARCHAR(100) DEFAULT 'Websites' AFTER project_link,
ADD COLUMN IF NOT EXISTS tech_stack VARCHAR(500) DEFAULT '' AFTER category,
ADD COLUMN IF NOT EXISTS completion_year VARCHAR(4) DEFAULT '' AFTER tech_stack,
ADD COLUMN IF NOT EXISTS is_live TINYINT(1) NOT NULL DEFAULT 1 AFTER completion_year;

UPDATE portfolio SET category = 'Websites' WHERE category IS NULL OR category = '';
