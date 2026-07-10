<?php
$page_title = 'About Mtaita Tech | Software Company Tanzania';
$page_desc = 'Learn about Mtaita Tech — a trusted software development company in Kilimanjaro and Arusha. We deliver custom websites, mobile apps & business systems.';
$page_keywords = 'about Mtaita Tech, software company Tanzania, IT company Kilimanjaro, web development agency Arusha, custom software developer Tanzania';
$page_heading = 'About Mtaita Tech';
require_once 'header.php';
require_once 'db_connect.php';
require_once 'lib/Settings.php';

$hero_bg = Settings::get('hero_bg_about', '');
?>

<section class="page-header<?= $hero_bg ? ' page-header-with-bg' : '' ?>"<?php if ($hero_bg): ?> style="background-image:url('/<?= htmlspecialchars($hero_bg) ?>')"<?php endif; ?>>
    <div class="container">
        <h1><?= __('about_title') ?></h1>
        <p><?= __('about_subtitle') ?></p>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <h2><?= __('about_who_heading') ?></h2>
                <p><?= __('about_who_text1') ?></p>
                <p><?= __('about_who_text2') ?></p>
                <div class="row g-3 mt-4">
                    <div class="col-6">
                        <div class="stat-number">50+</div>
                        <p class="text-muted small"><?= __('about_stat_projects') ?></p>
                    </div>
                    <div class="col-6">
                        <div class="stat-number">30+</div>
                        <p class="text-muted small"><?= __('about_stat_clients') ?></p>
                    </div>
                    <div class="col-6">
                        <div class="stat-number">4+</div>
                        <p class="text-muted small"><?= __('about_stat_experience') ?></p>
                    </div>
                    <div class="col-6">
                        <div class="stat-number">24/7</div>
                        <p class="text-muted small"><?= __('about_stat_support') ?></p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <?php require_once __DIR__ . '/lib/Settings.php'; $about_img = Settings::get('about_image', 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=600&q=80'); ?>
                <img src="<?= htmlspecialchars($about_img) ?>" alt="About Mtaita Tech" class="img-fluid rounded-4 shadow">
            </div>
        </div>
    </div>
</section>

<section class="section-padding bg-light-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2><?= __('about_skills_heading') ?></h2>
            <p class="text-muted"><?= __('about_skills_subtitle') ?></p>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="skill-card">
                    <div class="d-flex justify-content-between">
                        <span><?= __('about_skill_web') ?></span><span>95%</span>
                    </div>
                    <div class="progress mt-2 mb-3">
                        <div class="progress-bar" style="width:95%"></div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span><?= __('about_skill_graphic') ?></span><span>90%</span>
                    </div>
                    <div class="progress mt-2 mb-3">
                        <div class="progress-bar" style="width:90%"></div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span><?= __('about_skill_mobile') ?></span><span>85%</span>
                    </div>
                    <div class="progress mt-2 mb-3">
                        <div class="progress-bar" style="width:85%"></div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span><?= __('about_skill_seo') ?></span><span>80%</span>
                    </div>
                    <div class="progress mt-2">
                        <div class="progress-bar" style="width:80%"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="skill-card h-100 d-flex flex-column justify-content-center">
                    <h4><?= __('about_mission_heading') ?></h4>
                    <p><?= __('about_mission_text') ?></p>
                    <h4><?= __('about_vision_heading') ?></h4>
                    <p><?= __('about_vision_text') ?></p>
                </div>
            </div>
        </div>
    </div>
</section>



<?php
require_once 'footer.php';
?>
