<?php
$page_title = 'Live Projects | Web Development Portfolio Tanzania';
$page_desc = 'Browse all live systems, websites, and software projects built by Mtaita Tech for businesses across Kilimanjaro, Arusha and Tanzania.';
$page_keywords = 'live projects Tanzania, web development portfolio, software systems Kilimanjaro, business management systems Arusha, Mtaita Tech projects';
require_once 'header.php';
require_once 'db_connect.php';

$projects = $pdo->query("SELECT id, project_title, project_desc, project_link, project_screenshot, category, tech_stack, completion_year, is_live, created_at FROM portfolio ORDER BY is_live DESC, created_at DESC")->fetchAll();
?>

<section class="page-header">
    <div class="container">
        <h1>All Projects</h1>
        <p>Live systems and solutions we have built and deployed</p>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <?php if (empty($projects)): ?>
        <div class="text-center text-muted py-5">
            <i class="bi bi-folder2-open" style="font-size:3rem;"></i>
            <p class="mt-3">No projects yet. Check back soon.</p>
        </div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach ($projects as $p): ?>
            <div class="col-md-6 col-lg-4">
                <div class="portfolio-card">
                    <div class="portfolio-img-wrap">
                        <img src="<?= htmlspecialchars(webp_url($p['project_screenshot'])) ?>" alt="<?= htmlspecialchars($p['project_title']) ?>" class="portfolio-img" loading="lazy" onerror="this.src='https://via.placeholder.com/600x400/0F172A/DC2626?text=Mtaita+Tech'">
                        <?php if ($p['is_live']): ?>
                        <span style="position:absolute;top:12px;right:12px;background:#10B981;color:#fff;font-size:0.65rem;font-weight:700;letter-spacing:1px;padding:4px 10px;animation:livePulse 2s ease-in-out infinite;">LIVE</span>
                        <?php endif; ?>
                    </div>
                    <div class="portfolio-body">
                        <h5><?= htmlspecialchars($p['project_title']) ?></h5>
                        <?php if (!empty($p['tech_stack'])): ?>
                        <div style="display:flex;flex-wrap:wrap;gap:4px;margin-bottom:10px;">
                            <?php foreach (explode(',', $p['tech_stack']) as $tag): ?>
                            <span style="font-size:0.65rem;font-weight:600;background:var(--light-gray);color:var(--medium-gray);padding:2px 7px;border-radius:3px;"><?= htmlspecialchars(trim($tag)) ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($p['category'])): ?>
                        <span style="font-size:0.72rem;font-weight:600;color:var(--red);"><?= htmlspecialchars($p['category']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($p['completion_year'])): ?>
                        <span class="text-muted" style="font-size:0.72rem;"> | <?= htmlspecialchars($p['completion_year']) ?></span>
                        <?php endif; ?>
                        <p class="portfolio-desc-preview"><?= htmlspecialchars(substr($p['project_desc'] ?? '', 0, 150)) ?></p>
                        <a href="<?= htmlspecialchars($p['project_link']) ?>" target="_blank" class="portfolio-overlay-btn mt-3">
                            <i class="bi bi-box-arrow-up-right"></i> View Live
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'footer.php'; ?>
