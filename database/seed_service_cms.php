<?php
/**
 * Seed the Service CMS.
 *
 * Run once after importing database/migration_013_service_cms.sql:
 *
 *   php database/seed_service_cms.php
 *
 * Safe to re-run:
 *   - services are matched by slug (then by title) and updated, never duplicated
 *   - page sections/items are only created when a service has none yet, so
 *     admin edits are never overwritten
 */

if (PHP_SAPI !== 'cli') {
    echo 'This script must be run from the command line.';
    exit(1);
}

require __DIR__ . '/../db_connect.php';
require __DIR__ . '/../lib/Service.php';
require __DIR__ . '/../lib/ServiceDefaults.php';

$serviceObj = new Service();

$fieldMap = ['slug', 'title', 'card_title', 'description', 'short_description', 'full_description',
    'image', 'image_alt', 'hero_image', 'hero_subtitle', 'seo_title', 'seo_description', 'seo_keywords',
    'service_category', 'card_link', 'icon', 'list_items', 'sort_order', 'is_active', 'show_homepage', 'show_listing'];

echo "Seeding service CMS...\n";

$created = 0;
$updated = 0;
$sectionsSeeded = 0;

foreach (ServiceDefaults::all() as $slug => $d) {
    $existing = $serviceObj->getBySlug($slug);
    if (!$existing) {
        $stmt = $pdo->prepare("SELECT * FROM services WHERE title = ? LIMIT 1");
        $stmt->execute([$d['title']]);
        $existing = $stmt->fetch() ?: null;
    }

    $data = [
        'slug' => $slug,
        'title' => $d['title'],
        'card_title' => $d['card_title'] ?? null,
        'description' => $d['description'] ?? '',
        'short_description' => $d['short_description'] ?? null,
        'full_description' => $d['full_description'] ?? null,
        'image' => $d['image'] ?? null,
        'image_alt' => $d['image_alt'] ?? null,
        'hero_image' => $d['hero_image'] ?? null,
        'hero_subtitle' => $d['hero_subtitle'] ?? null,
        'seo_title' => $d['page_title'] ?? null,
        'seo_description' => $d['page_desc'] ?? null,
        'seo_keywords' => $d['page_keywords'] ?? null,
        'service_category' => $d['service_category'] ?? null,
        'card_link' => $d['card_link'] ?? null,
        'icon' => $d['icon'] ?? null,
        'list_items' => null,
        'sort_order' => $d['sort_order'] ?? 0,
        'is_active' => 1,
        'show_homepage' => (int)($d['show_homepage'] ?? 0),
        'show_listing' => 1,
    ];

    if ($existing) {
        // Preserve an image the admin already uploaded.
        if (!empty($existing['image'])) {
            $data['image'] = $existing['image'];
        }
        if (!empty($existing['hero_image'])) {
            $data['hero_image'] = $existing['hero_image'];
        }
        $serviceObj->update((int)$existing['id'], $data);
        $id = (int)$existing['id'];
        echo "  updated  {$slug}\n";
    } else {
        $id = (int)$serviceObj->create($data);
        echo "  created  {$slug}\n";
    }

    // Only seed sections when the service has none yet.
    $existingSections = $serviceObj->getSections($id, false);
    if (empty($existingSections) && !empty($d['sections'])) {
        $order = 0;
        foreach ($d['sections'] as $s) {
            $secId = (int)$serviceObj->createSection($id, [
                'section_type' => $s['type'] ?? 'content',
                'column_side' => $s['side'] ?? 'left',
                'heading' => $s['heading'] ?? null,
                'subheading' => $s['subheading'] ?? null,
                'body' => $s['body'] ?? null,
                'image' => $s['image'] ?? null,
                'image_alt' => $s['image_alt'] ?? null,
                'button_text' => $s['button_text'] ?? null,
                'button_url' => $s['button_url'] ?? null,
                'anchor' => $s['anchor'] ?? null,
                'note' => $s['note'] ?? null,
                'sort_order' => $order++,
                'is_active' => 1,
            ]);
            $io = 0;
            foreach (($s['items'] ?? []) as $item) {
                $serviceObj->createItem($secId, [
                    'item_title' => $item['title'] ?? null,
                    'item_text' => $item['text'] ?? null,
                    'item_icon' => $item['icon'] ?? null,
                    'item_link' => $item['link'] ?? null,
                    'sort_order' => $io++,
                    'is_active' => 1,
                ]);
            }
        }
        echo "           seeded " . count($d['sections']) . " sections\n";
    }
}

echo "\nDone. Services seeded. Open the admin > Manage Services to review.\n";
