<?php
$page_title = 'Free & Premium Courses | Web Development Tanzania';
$page_desc = 'Learn web development, graphic design, programming and IT skills with free and premium online courses from Mtaita Tech. Start learning today.';
$page_keywords = 'online courses Tanzania, web development course, programming course Tanzania, free courses Tanzania, premium courses Tanzania, learn coding Tanzania';
require_once 'header.php';

require_once 'lib/Settings.php';
$hero_bg = Settings::get('hero_bg_services', '');

$courses = $pdo->query("SELECT id, title, slug, description, type, price, thumbnail, featured, created_at FROM courses WHERE status = 'published' ORDER BY featured DESC, created_at DESC")->fetchAll();
?>

<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
        {"@type": "ListItem","position":1,"name":"Home","item":"<?= SITE_URL ?>"},
        {"@type": "ListItem","position":2,"name":"Courses","item":"<?= SITE_URL ?>/courses.php"}
    ]
}
</script>

<section class="page-header<?= $hero_bg ? ' page-header-with-bg' : '' ?>"<?php if ($hero_bg): ?> style="background-image:url('/<?= htmlspecialchars($hero_bg) ?>')"<?php endif; ?>>
    <div class="container">
        <h1>Courses</h1>
        <p>Learn new skills with our free and premium online courses</p>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <?php if (empty($courses)): ?>
            <div class="text-center py-5">
                <i class="bi bi-book" style="font-size:3rem;color:#CBD5E1;"></i>
                <p class="text-muted mt-3">No courses available yet. Check back soon!</p>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($courses as $course): 
                    $excerpt = mb_strimwidth(strip_tags($course['description']), 0, 120, '...');
                    $is_free = $course['type'] === 'free';
                    $thumb = $course['thumbnail'] ? '/' . $course['thumbnail'] : null;
                ?>
                <div class="col-md-4 col-lg-3">
                    <div class="portfolio-card h-100 d-flex flex-column">
                        <?php if ($thumb): ?>
                        <a href="single-course?slug=<?= htmlspecialchars($course['slug']) ?>" class="d-block" style="overflow:hidden;border-radius:12px 12px 0 0;">
                            <img src="<?= htmlspecialchars($thumb) ?>" alt="<?= htmlspecialchars($course['title']) ?>" style="width:100%;height:160px;object-fit:cover;">
                        </a>
                        <?php endif; ?>
                        <div class="p-4 pb-0 d-flex gap-2 flex-wrap">
                            <span class="badge <?= $is_free ? 'bg-success' : '' ?>" style="background: <?= $is_free ? '#10B981' : 'var(--red)' ?>;">
                                <?= strtoupper($course['type']) ?>
                            </span>
                            <?php if ($course['featured']): ?>
                                <span class="badge" style="background: #F59E0B;color:#000;">Featured</span>
                            <?php endif; ?>
                        </div>
                        <div class="portfolio-body flex-grow-1 d-flex flex-column px-4 pb-4">
                            <h5 class="mb-2"><?= htmlspecialchars($course['title']) ?></h5>
                            <p class="text-muted small mb-3 flex-grow-1"><?= htmlspecialchars($excerpt) ?></p>
                            <div class="d-flex align-items-center justify-content-between mt-auto">
                                <?php if ($is_free): ?>
                                    <span class="fw-bold" style="color: #10B981;">FREE</span>
                                    <a href="single-course?slug=<?= htmlspecialchars($course['slug']) ?>" class="btn btn-sm" style="background: #10B981; color: #fff; border-radius: 8px;">
                                        Enroll Free
                                    </a>
                                <?php else: ?>
                                    <span class="fw-bold" style="color: var(--red);">TZS <?= number_format($course['price']) ?></span>
                                    <a href="single-course?slug=<?= htmlspecialchars($course['slug']) ?>" class="btn btn-red btn-sm">
                                        Buy Now
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'footer.php'; ?>
