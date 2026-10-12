<?php
$page_title = 'Our Services | Software & Web Development Tanzania';
$page_desc = 'Explore Mtaita Tech services: web development, mobile apps, POS systems, inventory management, CRM, ERP, eCommerce, SEO, hosting & ICT consultancy.';
$page_keywords = 'software services Tanzania, web development services, mobile app development, POS system, inventory system, CRM development, ERP development, ICT consultancy Tanzania';
require_once 'header.php';
require_once 'db_connect.php';
require_once 'lib/Settings.php';
require_once 'lib/Service.php';

$hero_bg = Settings::get('hero_bg_services', '');
$serviceObj = new Service();
$services = $serviceObj->getListing();
?>

<section class="page-header<?= $hero_bg ? ' page-header-with-bg' : '' ?>"<?php if ($hero_bg): ?> style="background-image:url('/<?= htmlspecialchars(webp_url($hero_bg)) ?>')"<?php endif; ?>>
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
        <div class="row g-4 justify-content-center">
            <?php foreach ($services as $s): ?>
            <?php
                $svc_title = $s['card_title'] ?: $s['title'];
                $svc_link  = $s['card_link'] ?: (!empty($s['slug']) ? '/service/' . $s['slug'] : '/contact');
                $svc_img   = !empty($s['image']) ? webp_url('/' . ltrim($s['image'], '/')) : '';
                $svc_alt   = !empty($s['image_alt']) ? $s['image_alt'] : $svc_title;
                $svc_desc  = $s['short_description'] ?: $s['description'];
            ?>
            <div class="col-12 col-md-6 col-lg-4">
                <article class="home-service-card">
                    <h3 class="home-service-title"><?= htmlspecialchars($svc_title) ?></h3>
                    <p class="home-service-desc"><?= htmlspecialchars($svc_desc) ?></p>
                    <div class="home-service-media">
                        <div class="home-service-img-wrap">
                            <?php if ($svc_img): ?>
                            <img src="<?= htmlspecialchars($svc_img) ?>" alt="<?= htmlspecialchars($svc_alt) ?>" loading="lazy">
                            <?php endif; ?>
                        </div>
                        <a href="<?= htmlspecialchars($svc_link) ?>" class="home-service-link"><?= __('read_more') ?> <i class="bi bi-arrow-right"></i></a>
                    </div>
                </article>
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
