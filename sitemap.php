<?php
require_once __DIR__ . '/db_connect.php';

$blogs = $pdo->query("SELECT slug, created_at FROM blogs ORDER BY created_at DESC")->fetchAll();
$courses = $pdo->query("SELECT slug, created_at FROM courses WHERE status = 'published' ORDER BY created_at DESC")->fetchAll();
$products = $pdo->query("SELECT id, created_at FROM products ORDER BY created_at DESC")->fetchAll();

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
    <url>
        <loc><?= SITE_URL ?>/web-development</loc>
        <priority>0.8</priority>
        <changefreq>monthly</changefreq>
    </url>
    <url>
        <loc><?= SITE_URL ?>/mobile-apps</loc>
        <priority>0.8</priority>
        <changefreq>monthly</changefreq>
    </url>
    <url>
        <loc><?= SITE_URL ?>/graphic-design</loc>
        <priority>0.8</priority>
        <changefreq>monthly</changefreq>
    </url>
    <url>
        <loc><?= SITE_URL ?>/seo-digital-marketing</loc>
        <priority>0.8</priority>
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
