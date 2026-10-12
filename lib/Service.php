<?php
/**
 * Service model.
 *
 * Backs the admin "Manage Services" screens and the public service pages.
 * Card fields (title, description, image, link, visibility) live on the
 * `services` table. The long-form content of each service page is stored as
 * ordered sections (service_sections) with repeatable items
 * (service_section_items).
 */
class Service {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    // ---------------------------------------------------------------------
    // Services
    // ---------------------------------------------------------------------
    public function getAll() {
        return $this->pdo->query("SELECT * FROM services ORDER BY sort_order ASC, id ASC")->fetchAll();
    }

    public function getActive() {
        return $this->pdo->query("SELECT * FROM services WHERE is_active = 1 ORDER BY sort_order ASC, id ASC")->fetchAll();
    }

    public function getHomepage() {
        return $this->pdo->query("SELECT * FROM services WHERE is_active = 1 AND show_homepage = 1 AND slug IS NOT NULL AND slug <> '' ORDER BY sort_order ASC, id ASC")->fetchAll();
    }

    public function getListing() {
        return $this->pdo->query("SELECT * FROM services WHERE is_active = 1 AND show_listing = 1 ORDER BY sort_order ASC, id ASC")->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM services WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch();
    }

    public function getBySlug($slug) {
        $stmt = $this->pdo->prepare("SELECT * FROM services WHERE slug = ?");
        $stmt->execute([$slug]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO services
                (slug, title, card_title, description, short_description, full_description, image, image_alt,
                 hero_image, hero_subtitle, seo_title, seo_description, seo_keywords, service_category,
                 card_link, icon, list_items, sort_order, is_active, show_homepage, show_listing)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute($this->bind($data));
        return $this->pdo->lastInsertId();
    }

    public function update($id, $data) {
        $stmt = $this->pdo->prepare(
            "UPDATE services SET
                slug = ?, title = ?, card_title = ?, description = ?, short_description = ?, full_description = ?,
                image = ?, image_alt = ?, hero_image = ?, hero_subtitle = ?,
                seo_title = ?, seo_description = ?, seo_keywords = ?, service_category = ?,
                card_link = ?, icon = ?, list_items = ?, sort_order = ?, is_active = ?,
                show_homepage = ?, show_listing = ?
             WHERE id = ?"
        );
        $params = $this->bind($data);
        $params[] = (int)$id;
        return $stmt->execute($params);
    }

    private function bind($data) {
        return [
            $data['slug'] ?? null,
            $data['title'] ?? '',
            $data['card_title'] ?? null,
            $data['description'] ?? '',
            $data['short_description'] ?? null,
            $data['full_description'] ?? null,
            $data['image'] ?? null,
            $data['image_alt'] ?? null,
            $data['hero_image'] ?? null,
            $data['hero_subtitle'] ?? null,
            $data['seo_title'] ?? null,
            $data['seo_description'] ?? null,
            $data['seo_keywords'] ?? null,
            $data['service_category'] ?? null,
            $data['card_link'] ?? null,
            $data['icon'] ?? null,
            $data['list_items'] ?? null,
            $data['sort_order'] ?? 0,
            $data['is_active'] ?? 1,
            $data['show_homepage'] ?? 1,
            $data['show_listing'] ?? 1,
        ];
    }

    public function delete($id) {
        $row = $this->getById($id);
        if ($row) {
            foreach (['image', 'hero_image'] as $field) {
                if (!empty($row[$field])) {
                    $path = __DIR__ . '/../' . ltrim($row[$field], '/');
                    if (is_file($path)) @unlink($path);
                }
            }
        }
        // service_sections / service_section_items cascade via foreign keys.
        $stmt = $this->pdo->prepare("DELETE FROM services WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }

    public function toggleActive($id) {
        return $this->pdo->prepare("UPDATE services SET is_active = NOT is_active WHERE id = ?")->execute([(int)$id]);
    }

    public function toggleHomepage($id) {
        return $this->pdo->prepare("UPDATE services SET show_homepage = NOT show_homepage WHERE id = ?")->execute([(int)$id]);
    }

    public function toggleListing($id) {
        $stmt = $this->pdo->prepare("UPDATE services SET show_listing = NOT show_listing WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }

    /**
     * Returns a slug that is unique in the services table (ignoring $ignoreId).
     */
    public function uniqueSlug($slug, $ignoreId = 0) {
        $base = self::slugify($slug);
        if ($base === '') {
            $base = 'service';
        }
        $slug = $base;
        $i = 2;
        while (true) {
            $stmt = $this->pdo->prepare("SELECT id FROM services WHERE slug = ? AND id <> ? LIMIT 1");
            $stmt->execute([$slug, (int)$ignoreId]);
            if (!$stmt->fetchColumn()) {
                return $slug;
            }
            $slug = $base . '-' . $i;
            $i++;
        }
    }

    public static function slugify($text) {
        $text = strtolower(trim((string)$text));
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        return trim($text, '-');
    }

    // ---------------------------------------------------------------------
    // Sections
    // ---------------------------------------------------------------------
    public function getSections($serviceId, $onlyActive = false) {
        $sql = "SELECT * FROM service_sections WHERE service_id = ?";
        if ($onlyActive) {
            $sql .= " AND is_active = 1";
        }
        $sql .= " ORDER BY sort_order ASC, id ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([(int)$serviceId]);
        return $stmt->fetchAll();
    }

    public function getSection($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM service_sections WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch();
    }

    public function createSection($serviceId, $data) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO service_sections
                (service_id, section_type, column_side, heading, subheading, body, image, image_alt,
                 button_text, button_url, anchor, note, sort_order, is_active)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            (int)$serviceId,
            $data['section_type'] ?? 'content',
            $data['column_side'] ?? 'left',
            $data['heading'] ?? null,
            $data['subheading'] ?? null,
            $data['body'] ?? null,
            $data['image'] ?? null,
            $data['image_alt'] ?? null,
            $data['button_text'] ?? null,
            $data['button_url'] ?? null,
            $data['anchor'] ?? null,
            $data['note'] ?? null,
            $data['sort_order'] ?? 0,
            $data['is_active'] ?? 1,
        ]);
        return $this->pdo->lastInsertId();
    }

    public function updateSection($id, $data) {
        $stmt = $this->pdo->prepare(
            "UPDATE service_sections SET
                section_type = ?, column_side = ?, heading = ?, subheading = ?, body = ?,
                image = ?, image_alt = ?, button_text = ?, button_url = ?, anchor = ?,
                note = ?, sort_order = ?, is_active = ?
             WHERE id = ?"
        );
        $stmt->execute([
            $data['section_type'] ?? 'content',
            $data['column_side'] ?? 'left',
            $data['heading'] ?? null,
            $data['subheading'] ?? null,
            $data['body'] ?? null,
            $data['image'] ?? null,
            $data['image_alt'] ?? null,
            $data['button_text'] ?? null,
            $data['button_url'] ?? null,
            $data['anchor'] ?? null,
            $data['note'] ?? null,
            $data['sort_order'] ?? 0,
            $data['is_active'] ?? 1,
            (int)$id,
        ]);
        return true;
    }

    public function deleteSection($id) {
        $row = $this->getSection($id);
        if ($row && !empty($row['image'])) {
            $path = __DIR__ . '/../' . ltrim($row['image'], '/');
            if (is_file($path)) @unlink($path);
        }
        // items cascade.
        $stmt = $this->pdo->prepare("DELETE FROM service_sections WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }

    public function toggleSection($id) {
        $stmt = $this->pdo->prepare("UPDATE service_sections SET is_active = NOT is_active WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }

    /** $order = array of section ids in the desired order. */
    public function reorderSections($serviceId, $order) {
        $stmt = $this->pdo->prepare("UPDATE service_sections SET sort_order = ? WHERE id = ? AND service_id = ?");
        $i = 0;
        foreach ($order as $sectionId) {
            $stmt->execute([$i++, (int)$sectionId, (int)$serviceId]);
        }
        return true;
    }

    public function nextSectionOrder($serviceId) {
        $stmt = $this->pdo->prepare("SELECT COALESCE(MAX(sort_order), -1) + 1 FROM service_sections WHERE service_id = ?");
        $stmt->execute([(int)$serviceId]);
        return (int)$stmt->fetchColumn();
    }

    // ---------------------------------------------------------------------
    // Section items
    // ---------------------------------------------------------------------
    public function getItems($sectionId, $onlyActive = false) {
        $sql = "SELECT * FROM service_section_items WHERE section_id = ?";
        if ($onlyActive) {
            $sql .= " AND is_active = 1";
        }
        $sql .= " ORDER BY sort_order ASC, id ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([(int)$sectionId]);
        return $stmt->fetchAll();
    }

    /** Returns [section_id => [items...]] for a whole service. */
    public function getItemsByService($serviceId, $onlyActive = false) {
        $sql = "SELECT i.* FROM service_section_items i
                JOIN service_sections s ON s.id = i.section_id
                WHERE s.service_id = ?";
        if ($onlyActive) {
            $sql .= " AND i.is_active = 1";
        }
        $sql .= " ORDER BY i.sort_order ASC, i.id ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([(int)$serviceId]);
        $grouped = [];
        foreach ($stmt->fetchAll() as $row) {
            $grouped[$row['section_id']][] = $row;
        }
        return $grouped;
    }

    public function getItem($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM service_section_items WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch();
    }

    public function createItem($sectionId, $data) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO service_section_items
                (section_id, item_title, item_text, item_icon, item_link, sort_order, is_active)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            (int)$sectionId,
            $data['item_title'] ?? null,
            $data['item_text'] ?? null,
            $data['item_icon'] ?? null,
            $data['item_link'] ?? null,
            $data['sort_order'] ?? 0,
            $data['is_active'] ?? 1,
        ]);
        return $this->pdo->lastInsertId();
    }

    public function updateItem($id, $data) {
        $stmt = $this->pdo->prepare(
            "UPDATE service_section_items
                SET item_title = ?, item_text = ?, item_icon = ?, item_link = ?, sort_order = ?, is_active = ?
             WHERE id = ?"
        );
        $stmt->execute([
            $data['item_title'] ?? null,
            $data['item_text'] ?? null,
            $data['item_icon'] ?? null,
            $data['item_link'] ?? null,
            $data['sort_order'] ?? 0,
            $data['is_active'] ?? 1,
            (int)$id,
        ]);
        return true;
    }

    public function deleteItem($id) {
        $stmt = $this->pdo->prepare("DELETE FROM service_section_items WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }

    public function toggleItem($id) {
        $stmt = $this->pdo->prepare("UPDATE service_section_items SET is_active = NOT is_active WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }

    public function nextItemOrder($sectionId) {
        $stmt = $this->pdo->prepare("SELECT COALESCE(MAX(sort_order), -1) + 1 FROM service_section_items WHERE section_id = ?");
        $stmt->execute([(int)$sectionId]);
        return (int)$stmt->fetchColumn();
    }

    // ---------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------
    public static function sectionTypes() {
        return [
            'content'      => 'Content block (heading + text + optional image)',
            'benefits'     => 'Benefits list (rows with links)',
            'service_grid' => 'Service grid (2-column links)',
            'type_cards'   => 'Types / cards grid (title + text)',
            'faq'          => 'FAQ accordion (question + answer)',
            'cta'          => 'Call to action / contact card',
            'steps'        => 'Process steps (numbered)',
            'tech'         => 'Technology list (pills)',
            'projects'     => 'Related projects carousel (by category)',
            'city_links'   => 'City / related page links',
        ];
    }

    public static function slugifyTitle($title) {
        $slug = strtolower(trim($title));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        return trim($slug, '-');
    }
}
