<?php
require_once __DIR__ . '/db_connect.php';

try {
    $cols = $pdo->query("SHOW COLUMNS FROM blogs")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('description', $cols)) $pdo->exec("ALTER TABLE blogs ADD COLUMN description TEXT AFTER slug");
    if (!in_array('author', $cols)) $pdo->exec("ALTER TABLE blogs ADD COLUMN author VARCHAR(100) DEFAULT 'Admin' AFTER feature_image");
    if (!in_array('is_published', $cols)) $pdo->exec("ALTER TABLE blogs ADD COLUMN is_published TINYINT(1) DEFAULT 1 AFTER author");
    if (!in_array('updated_at', $cols)) $pdo->exec("ALTER TABLE blogs ADD COLUMN updated_at DATETIME DEFAULT NULL AFTER created_at");
} catch (Exception $e) {
    error_log('blog migration: ' . $e->getMessage());
}

$slug = $_GET['slug'] ?? '';

if ($slug) {
    $stmt = $pdo->prepare("SELECT * FROM blogs WHERE slug = ? AND is_published = 1");
    $stmt->execute([$slug]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$post && is_numeric($slug)) {
        $stmt = $pdo->prepare("SELECT * FROM blogs WHERE id = ? AND is_published = 1");
        $stmt->execute([(int)$slug]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    if (!$post) {
        header('HTTP/1.0 404 Not Found');
        $page_title = 'Blog Post Not Found — Mtaita Tech';
        $page_desc = 'The requested blog post could not be found on Mtaita Tech.';
        require_once 'header.php';
        echo '<section class="page-header"><div class="container"><h1>Post Not Found</h1><p>The blog post you are looking for does not exist.</p></div></section>';
        require_once 'footer.php';
        exit;
    }

    $page_title = htmlspecialchars($post['title']) . ' — Mtaita Tech Blog';
    $page_desc = htmlspecialchars(mb_strimwidth(strip_tags($post['content']), 0, 155, ''));
    $article_published = $post['created_at'];
    $og_image = $post['feature_image'] ? SITE_URL . '/' . $post['feature_image'] : (SITE_URL . '/assets/img/jj.png');
    require_once 'header.php';
?>
<section class="page-header">
    <div class="container">
        <h1><?= htmlspecialchars($post['title']) ?></h1>
        <p class="text-white-50"><small>Published <?= date('F j, Y', strtotime($post['created_at'])) ?></small></p>
    </div>
</section>
<section class="section-padding">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <?php if ($post['feature_image']): ?>
                <img src="/<?= htmlspecialchars(webp_url($post['feature_image'])) ?>" alt="<?= htmlspecialchars($post['title']) ?>" class="img-fluid rounded mb-4">
                <?php endif; ?>
                <div class="blog-content"><?= $post['content'] ?></div>
                <div class="mt-4">
                    <a href="/blog" class="btn btn-red"><i class="bi bi-arrow-left me-1"></i> Back to Blog</a>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
} else {
    require_once __DIR__ . '/lib/Settings.php';
    $hero_bg = Settings::get('hero_bg_blog', '');

    $posts = $pdo->query("SELECT id, title, slug, description, feature_image, created_at FROM blogs WHERE is_published = 1 ORDER BY created_at DESC")->fetchAll();
    $page_title = 'Blog — Mtaita Tech';
    $page_desc = 'Read the latest blog posts from Mtaita Tech about software development, web design, digital marketing, and tech insights for Tanzanian businesses.';
    require_once 'header.php';
?>
<section class="page-header<?= $hero_bg ? ' page-header-with-bg' : '' ?>"<?php if ($hero_bg): ?> style="background-image:url('/<?= htmlspecialchars(webp_url($hero_bg)) ?>')"<?php endif; ?>>
    <div class="container">
        <h1>Blog</h1>
        <p>Insights, tutorials, and updates from Mtaita Tech</p>
    </div>
</section>
<section class="section-padding bg-light-section">
    <div class="container">
        <?php if (empty($posts)): ?>
        <div class="text-center py-5">
            <i class="bi bi-journal-text" style="font-size:3rem;color:var(--red);"></i>
            <h3 class="mt-3">No posts yet</h3>
            <p class="text-muted">Check back soon for new content.</p>
        </div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach ($posts as $p): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                    <?php if ($p['feature_image']): ?>
                    <img src="/<?= htmlspecialchars(webp_url($p['feature_image'])) ?>" class="card-img-top" alt="<?= htmlspecialchars($p['title']) ?>" style="height:200px;object-fit:cover;">
                    <?php endif; ?>
                    <div class="card-body d-flex flex-column">
                        <small class="text-muted"><?= date('F j, Y', strtotime($p['created_at'])) ?></small>
                        <h5 class="card-title mt-1"><?= htmlspecialchars($p['title']) ?></h5>
                        <?php if (!empty($p['description'])): ?>
                        <p class="text-muted small flex-grow-1"><?= htmlspecialchars(mb_strimwidth($p['description'], 0, 120, '...')) ?></p>
                        <?php endif; ?>
                        <a href="/blog/<?= htmlspecialchars($p['slug'] ?: $p['id']) ?>" class="btn btn-outline-red mt-auto align-self-start">Read More <i class="bi bi-arrow-right ms-1"></i></a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php
}
require_once 'footer.php';
?>