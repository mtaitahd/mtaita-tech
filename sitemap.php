<?php
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/lib/ServiceDefaults.php';

$blogs = $pdo->query("SELECT slug, created_at FROM blogs ORDER BY created_at DESC")->fetchAll();
$courses = $pdo->query("SELECT slug, created_at FROM courses WHERE status = 'published' ORDER BY created_at DESC")->fetchAll();
$products = $pdo->query("SELECT id, created_at FROM products ORDER BY created_at DESC")->fetchAll();

// Service pages: always include the built-in services (clean URLs), plus every
// admin-managed service. New services created from the dashboard live at
// /service/{slug} and would otherwise never appear here.
$service_urls = [];
foreach (array_keys(ServiceDefaults::all()) as $slug) {
    $service_urls['/' . $slug] = '0.8';
}
try {
    $dbServices = $pdo->query("SELECT slug FROM services WHERE is_active = 1 AND slug IS NOT NULL AND slug <> '' ORDER BY sort_order ASC, title ASC")->fetchAll();
    $builtin = array_keys(ServiceDefaults::all());
    foreach ($dbServices as $s) {
        $slug = (string)$s['slug'];
        $path = in_array($slug, $builtin, true) ? '/' . $slug : '/service/' . $slug;
        if (!isset($service_urls[$path])) {
            $service_urls[$path] = '0.7';
        }
    }
} catch (Throwable $e) {
    // services table not available yet – keep the built-in URLs
}

header('Content-Type: application/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc><?= SITE_URL ?>/</loc>
        <priority>1.0</priority>
        <changefreq>weekly</changefreq>
    </url>
    <url>
        <loc><?= SITE_URL ?>/about</loc>
        <priority>0.9</priority>
        <changefreq>monthly</changefreq>
    </url>
    <url>
        <loc><?= SITE_URL ?>/services</loc>
        <priority>0.9</priority>
        <changefreq>monthly</changefreq>
    </url>
    <url>
        <loc><?= SITE_URL ?>/contact</loc>
        <priority>0.9</priority>
        <changefreq>monthly</changefreq>
    </url>
    <?php foreach ($service_urls as $path => $priority): ?>
    <url>
        <loc><?= SITE_URL . htmlspecialchars($path) ?></loc>
        <priority><?= $priority ?></priority>
        <changefreq>monthly</changefreq>
    </url>
    <?php endforeach; ?>
    <url>
        <loc><?= SITE_URL ?>/pricing</loc>
        <priority>0.9</priority>
        <changefreq>monthly</changefreq>
    </url>
    <url>
        <loc><?= SITE_URL ?>/portfolio</loc>
        <priority>0.8</priority>
        <changefreq>weekly</changefreq>
    </url>
    <url>
        <loc><?= SITE_URL ?>/all-projects</loc>
        <priority>0.8</priority>
        <changefreq>weekly</changefreq>
    </url>
    <url>
        <loc><?= SITE_URL ?>/courses</loc>
        <priority>0.7</priority>
        <changefreq>weekly</changefreq>
    </url>
    <url>
        <loc><?= SITE_URL ?>/login</loc>
        <priority>0.3</priority>
        <changefreq>monthly</changefreq>
    </url>
    <url>
        <loc><?= SITE_URL ?>/blog</loc>
        <priority>0.7</priority>
        <changefreq>weekly</changefreq>
    </url>
    <url>
        <loc><?= SITE_URL ?>/web-designer-dar-es-salaam</loc>
        <priority>0.7</priority>
        <changefreq>monthly</changefreq>
    </url>
    <url>
        <loc><?= SITE_URL ?>/web-designer-arusha</loc>
        <priority>0.7</priority>
        <changefreq>monthly</changefreq>
    </url>
    <url>
        <loc><?= SITE_URL ?>/web-designer-mwanza</loc>
        <priority>0.7</priority>
        <changefreq>monthly</changefreq>
    </url>
    <url>
        <loc><?= SITE_URL ?>/seo-moshi</loc>
        <priority>0.7</priority>
        <changefreq>monthly</changefreq>
    </url>
    <url>
        <loc><?= SITE_URL ?>/seo-arusha</loc>
        <priority>0.7</priority>
        <changefreq>monthly</changefreq>
    </url>
    <url>
        <loc><?= SITE_URL ?>/seo-dar-es-salaam</loc>
        <priority>0.7</priority>
        <changefreq>monthly</changefreq>
    </url>
    <url>
        <loc><?= SITE_URL ?>/mobile-apps-zanzibar</loc>
        <priority>0.7</priority>
        <changefreq>monthly</changefreq>
    </url>
    <?php foreach ($courses as $c): ?>
    <url>
        <loc><?= SITE_URL ?>/course/<?= htmlspecialchars($c['slug']) ?></loc>
        <priority>0.6</priority>
        <changefreq>monthly</changefreq>
        <lastmod><?= date('Y-m-d', strtotime($c['created_at'])) ?></lastmod>
    </url>
    <?php endforeach; ?>
    <?php foreach ($blogs as $b): ?>
    <url>
        <loc><?= SITE_URL ?>/blog/<?= htmlspecialchars($b['slug']) ?></loc>
        <priority>0.6</priority>
        <changefreq>monthly</changefreq>
        <lastmod><?= date('Y-m-d', strtotime($b['created_at'])) ?></lastmod>
    </url>
    <?php endforeach; ?>
    <?php foreach ($products as $p): ?>
    <url>
        <loc><?= SITE_URL ?>/product-detail?id=<?= (int)$p['id'] ?></loc>
        <priority>0.6</priority>
        <changefreq>monthly</changefreq>
        <lastmod><?= date('Y-m-d', strtotime($p['created_at'])) ?></lastmod>
    </url>
    <?php endforeach; ?>
</urlset>
