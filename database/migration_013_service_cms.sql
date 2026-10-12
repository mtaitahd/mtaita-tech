-- Migration 013: Service CMS
-- Extends the existing `services` table with card/SEO/hero fields and adds a
-- section-based content model so every individual service page (and the city
-- landing pages) can be managed from the admin dashboard.
--
-- Safe to run on an existing database:
--   * existing rows keep their id, title and content
--   * new columns are added without dropping or rewriting existing data
--   * run database/seed_service_cms.php afterwards to attach slugs and
--     canonical content to the built-in service pages

ALTER TABLE services
    ADD COLUMN IF NOT EXISTS slug VARCHAR(140) NULL AFTER id,
    ADD COLUMN IF NOT EXISTS card_title VARCHAR(255) NULL AFTER title,
    ADD COLUMN IF NOT EXISTS short_description TEXT NULL AFTER description,
    ADD COLUMN IF NOT EXISTS full_description LONGTEXT NULL AFTER short_description,
    ADD COLUMN IF NOT EXISTS image VARCHAR(255) NULL AFTER full_description,
    ADD COLUMN IF NOT EXISTS image_alt VARCHAR(255) NULL AFTER image,
    ADD COLUMN IF NOT EXISTS hero_image VARCHAR(255) NULL AFTER image_alt,
    ADD COLUMN IF NOT EXISTS hero_subtitle VARCHAR(500) NULL AFTER hero_image,
    ADD COLUMN IF NOT EXISTS seo_title VARCHAR(255) NULL AFTER hero_subtitle,
    ADD COLUMN IF NOT EXISTS seo_description TEXT NULL AFTER seo_title,
    ADD COLUMN IF NOT EXISTS seo_keywords VARCHAR(500) NULL AFTER seo_description,
    ADD COLUMN IF NOT EXISTS service_category VARCHAR(140) NULL AFTER seo_keywords,
    ADD COLUMN IF NOT EXISTS card_link VARCHAR(255) NULL AFTER service_category,
    ADD COLUMN IF NOT EXISTS show_homepage TINYINT(1) NOT NULL DEFAULT 0 AFTER is_active,
    ADD COLUMN IF NOT EXISTS show_listing TINYINT(1) NOT NULL DEFAULT 1 AFTER show_homepage,
    ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER show_listing;

-- Slug uniqueness (NULLs are allowed multiple times, so legacy rows without a
-- slug are unaffected until they are given one).
CREATE UNIQUE INDEX IF NOT EXISTS uniq_services_slug ON services (slug);

-- ---------------------------------------------------------------------------
-- Section model
-- ---------------------------------------------------------------------------
-- section_type values currently supported by the frontend renderer:
--   content     heading + subheading + body + optional image (overview block)
--   benefits    repeater of {title, link}      -> .wd-benefit-list rows
--   service_grid repeater of {title, link}     -> .wd-service-grid links
--   type_cards  repeater of {title, text}      -> .wd-types-grid cards
--   faq         repeater of {title, text}      -> bootstrap accordion + JSON-LD
--   cta         heading + body + button        -> .wd-contact-card
--   steps       repeater of {title, text}      -> numbered process list
--   tech        repeater of {title}            -> technology pills
--   projects    queries portfolio by service_category -> carousel
--   city_links  repeater of {title, link}      -> city pill buttons
CREATE TABLE IF NOT EXISTS service_sections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_id INT NOT NULL,
    section_type VARCHAR(40) NOT NULL DEFAULT 'content',
    column_side ENUM('left','right','full') NOT NULL DEFAULT 'left',
    heading VARCHAR(255) NULL,
    subheading VARCHAR(255) NULL,
    body TEXT NULL,
    image VARCHAR(255) NULL,
    image_alt VARCHAR(255) NULL,
    button_text VARCHAR(120) NULL,
    button_url VARCHAR(255) NULL,
    anchor VARCHAR(80) NULL,
    note VARCHAR(500) NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_ss_service (service_id, sort_order),
    INDEX idx_ss_active (is_active),
    CONSTRAINT fk_ss_service FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS service_section_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section_id INT NOT NULL,
    item_title VARCHAR(500) NULL,
    item_text TEXT NULL,
    item_icon VARCHAR(120) NULL,
    item_link VARCHAR(255) NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ssi_section (section_id, sort_order),
    CONSTRAINT fk_ssi_section FOREIGN KEY (section_id) REFERENCES service_sections(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
