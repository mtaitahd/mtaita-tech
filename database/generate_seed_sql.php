<?php
/**
 * Generates database/seed_service_cms.sql from lib/ServiceDefaults.php.
 *
 * The generated file is safe to import in phpMyAdmin and safe to run more than
 * once:
 *   - services are upserted by slug (legacy same-title rows are adopted first)
 *   - a service's sections/items are only inserted when that service has none,
 *     so later admin edits are never overwritten
 *
 * Run from the command line:  php database/generate_seed_sql.php
 */

if (PHP_SAPI !== 'cli') {
    exit("Run this script from the command line.\n");
}

require __DIR__ . '/../lib/ServiceDefaults.php';

function sql_str($v) {
    if ($v === null) {
        return 'NULL';
    }
    $v = str_replace('\\', '\\\\', (string)$v);
    $v = str_replace("'", "\\'", $v);
    return "'" . $v . "'";
}

$out = [];
$out[] = '-- Migration 013 companion: canonical service page content';
$out[] = '-- Generated from lib/ServiceDefaults.php — do not edit by hand.';
$out[] = '-- Safe to import more than once: services are upserted by slug and';
$out[] = '-- sections/items are only created when the service has none yet.';
$out[] = '';
$out[] = 'SET NAMES utf8mb4;';
$out[] = '';

foreach (ServiceDefaults::all() as $slug => $d) {
    $title = $d['title'] ?? '';
    $out[] = '-- ========================================================================';
    $out[] = "-- Service: {$title} ({$slug})";
    $out[] = '-- ========================================================================';

    $fields = [
        'slug' => $slug,
        'title' => $title,
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
        'list_items' => '',
        'sort_order' => (int)($d['sort_order'] ?? 0),
        'is_active' => 1,
        'show_homepage' => (int)($d['show_homepage'] ?? 0),
        'show_listing' => 1,
    ];

    // Adopt a legacy row that has the same title but no slug, so we update it
    // instead of creating a duplicate.
    $out[] = 'UPDATE services SET slug = ' . sql_str($slug)
        . ' WHERE (slug IS NULL OR slug = \'\') AND title = ' . sql_str($title) . ' LIMIT 1;';

    $cols = implode(', ', array_keys($fields));
    $vals = [];
    foreach ($fields as $v) {
        $vals[] = is_int($v) ? (string)$v : sql_str($v);
    }
    $updates = [];
    foreach ($fields as $k => $v) {
        if ($k === 'slug') {
            continue;
        }
        $updates[] = "$k = VALUES($k)";
    }

    $out[] = "INSERT INTO services ($cols) VALUES (" . implode(', ', $vals) . ')';
    $out[] = '    ON DUPLICATE KEY UPDATE ' . implode(', ', $updates) . ';';
    $out[] = 'SET @sid := (SELECT id FROM services WHERE slug = ' . sql_str($slug) . ');';
    $out[] = 'SET @seed := (SELECT COUNT(*) = 0 FROM service_sections WHERE service_id = @sid);';
    $out[] = '';

    $order = 0;
    foreach (($d['sections'] ?? []) as $sec) {
        $secFields = [
            'section_type' => $sec['type'] ?? 'content',
            'column_side' => $sec['side'] ?? 'left',
            'heading' => $sec['heading'] ?? null,
            'subheading' => $sec['subheading'] ?? null,
            'body' => $sec['body'] ?? null,
            'image' => $sec['image'] ?? null,
            'image_alt' => $sec['image_alt'] ?? null,
            'button_text' => $sec['button_text'] ?? null,
            'button_url' => $sec['button_url'] ?? null,
            'anchor' => $sec['anchor'] ?? null,
            'note' => $sec['note'] ?? null,
        ];
        $select = ['@sid'];
        foreach ($secFields as $k => $v) {
            $select[] = sql_str($v);
        }
        $select[] = (string)$order;
        $select[] = '1';

        $out[] = 'INSERT INTO service_sections '
            . '(service_id, section_type, column_side, heading, subheading, body, image, image_alt, button_text, button_url, anchor, note, sort_order, is_active) '
            . 'SELECT ' . implode(', ', $select) . ' FROM DUAL WHERE @seed;';

        $io = 0;
        foreach (($sec['items'] ?? []) as $item) {
            $isel = [
                sql_str($item['title'] ?? null),
                sql_str($item['text'] ?? null),
                sql_str($item['icon'] ?? null),
                sql_str($item['link'] ?? null),
                (string)$io,
                '1',
            ];
            $out[] = 'INSERT INTO service_section_items '
                . '(section_id, item_title, item_text, item_icon, item_link, sort_order, is_active) '
                . 'SELECT id, ' . implode(', ', $isel)
                . ' FROM service_sections WHERE service_id = @sid AND sort_order = ' . $order . ' AND @seed;';
            $io++;
        }
        $out[] = '';
        $order++;
    }
    $out[] = '';
}

$target = __DIR__ . '/seed_service_cms.sql';
file_put_contents($target, implode("\n", $out) . "\n");
echo 'Wrote ' . $target . ' (' . strlen(implode("\n", $out)) . " bytes)\n";
