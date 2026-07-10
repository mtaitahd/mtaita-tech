<?php
$page_title = 'Our Services | Software & Web Development Tanzania';
$page_desc = 'Explore Mtaita Tech services: web development, mobile apps, POS systems, inventory management, CRM, ERP, eCommerce, SEO, hosting & ICT consultancy.';
$page_keywords = 'software services Tanzania, web development services, mobile app development, POS system, inventory system, CRM development, ERP development, ICT consultancy Tanzania';
require_once 'header.php';
require_once 'db_connect.php';
require_once 'lib/Settings.php';

$hero_bg = Settings::get('hero_bg_services', '');
$services = $pdo->query("SELECT * FROM services WHERE is_active = 1 ORDER BY sort_order ASC")->fetchAll();
?>

<section class="page-header<?= $hero_bg ? ' page-header-with-bg' : '' ?>"<?php if ($hero_bg): ?> style="background-image:url('/<?= htmlspecialchars($hero_bg) ?>')"<?php endif; ?>>
    <div class="container">
        <h1><?= __('services_title') ?></h1>
        <p><?= __('services_subtitle') ?></p>
    </div>
</section>

<section class="section-padding bg-light-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2><?= __('services_title') ?></h2>
            <p class="text-muted"><?= __('services_subtitle') ?></p>
        </div>
        <?php if (empty($services)): ?>
            <div class="text-center text-muted py-5"><p>No services available at the moment.</p></div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach ($services as $s): ?>
            <div class="col-lg-6">
                <div class="service-detail-card">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-wrap me-3"><i class="<?= htmlspecialchars($s['icon']) ?>"></i></div>
                        <h3 class="mb-0"><?= htmlspecialchars($s['title']) ?></h3>
                    </div>
                    <p><?= htmlspecialchars($s['description']) ?></p>
                    <?php if (!empty($s['list_items'])): ?>
                    <ul>
                        <?php foreach (explode("\n", $s['list_items']) as $item): ?>
                        <li><?= htmlspecialchars(trim($item)) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<section class="choose-best">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <h2><?= __('service_cta_heading') ?><br><span><?= __('service_cta_highlight') ?></span></h2>
                <p><?= __('service_cta_text') ?></p>
                <a href="/contact/" class="btn btn-gradient mt-3">
                    <i class="fa-solid fa-paper-plane"></i> <?= __('service_cta_btn') ?>
                </a>
            </div>
            <div class="col-lg-6">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="stat-box text-center">
                            <h3>50+</h3>
                            <p><?= __('about_stat_projects') ?></p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-box text-center">
                            <h3>30+</h3>
                            <p><?= __('about_stat_clients') ?></p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-box text-center">
                            <h3>4+</h3>
                            <p><?= __('about_stat_experience') ?></p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-box text-center">
                            <h3>24/7</h3>
                            <p><?= __('about_stat_support') ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
require_once 'footer.php';
?>
