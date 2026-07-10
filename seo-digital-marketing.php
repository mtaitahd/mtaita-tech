<?php
$page_title = 'SEO & Digital Marketing Services | Tanzania';
$page_desc = 'Professional SEO and digital marketing in Kilimanjaro and Tanzania. Grow your online presence, rank higher on Google, attract more customers.';
$page_keywords = 'SEO company Kilimanjaro, digital marketing Tanzania, SEO services Arusha, search engine optimization Tanzania, social media marketing Tanzania, online marketing Tanzania';
$service_category = 'SEO & Digital Marketing';
require_once 'header.php';
require_once 'db_connect.php';
require_once 'lib/Settings.php';

$hero_bg = Settings::get('hero_bg_services', '');

$stmt = $pdo->prepare("SELECT id, project_title, project_desc, project_link, project_screenshot FROM portfolio WHERE category = ? ORDER BY created_at DESC LIMIT 12");
$stmt->execute([$service_category]);
$projects = $stmt->fetchAll();
?>

<section class="page-header<?= $hero_bg ? ' page-header-with-bg' : '' ?>"<?php if ($hero_bg): ?> style="background-image:url('/<?= htmlspecialchars($hero_bg) ?>')"<?php endif; ?>>
    <div class="container">
        <h1><?= __('seo_digital_marketing') ?></h1>
        <p>Data-driven SEO & digital marketing strategies to grow your online presence</p>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="text-center mb-5">
            <h2>Our SEO & Digital Marketing Projects</h2>
            <p class="text-muted">Explore our latest marketing campaigns & SEO work</p>
        </div>
        <?php if (empty($projects)): ?>
            <div class="col-12 text-center text-muted py-5"><p>No SEO & marketing projects yet. Check back soon!</p></div>
        <?php else: ?>
        <div id="seoCarousel" class="carousel slide projects-carousel" data-bs-ride="carousel" data-bs-interval="4000">
            <div class="carousel-indicators">
                <?php $total_slides = ceil(count($projects) / 3); ?>
                <?php for ($i = 0; $i < $total_slides; $i++): ?>
                <button type="button" data-bs-target="#seoCarousel" data-bs-slide-to="<?= $i ?>" class="<?= $i === 0 ? 'active' : '' ?>" aria-current="<?= $i === 0 ? 'true' : 'false' ?>" aria-label="Slide <?= $i + 1 ?>"></button>
                <?php endfor; ?>
            </div>
            <div class="carousel-inner">
                <?php $chunks = array_chunk($projects, 3); ?>
                <?php $slide_idx = 0; foreach ($chunks as $chunk): ?>
                <div class="carousel-item <?= $slide_idx === 0 ? 'active' : '' ?>">
                    <div class="row g-4">
                        <?php foreach ($chunk as $project): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="portfolio-card">
                                <div class="portfolio-img-wrap">
                                    <img src="<?= htmlspecialchars($project['project_screenshot']) ?>" alt="<?= htmlspecialchars($project['project_title']) ?>" class="portfolio-img" loading="lazy" onerror="this.src='https://via.placeholder.com/600x400/0F172A/DC2626?text=Mtaita+Tech'">
                                </div>
                                <div class="portfolio-body">
                                    <h5><?= htmlspecialchars($project['project_title']) ?></h5>
                                    <p class="portfolio-desc-preview"><?= htmlspecialchars(substr($project['project_desc'], 0, 120)) ?></p>
                                    <a href="<?= htmlspecialchars($project['project_link']) ?>" target="_blank" class="portfolio-overlay-btn mt-3">
                                        <i class="bi bi-box-arrow-up-right"></i> Go Live
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php $slide_idx++; endforeach; ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#seoCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#seoCarousel" data-bs-slide="next">
                <span class="visually-hidden">Next</span>
            </button>
        </div>
        <?php endif; ?>
    </div>
</section>

<section class="section-padding bg-light-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2>Frequently Asked Questions</h2>
            <p class="text-muted">Everything you need to know about our SEO & digital marketing services</p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                Do you offer on-page & off-page SEO optimization?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">Yes, we provide comprehensive on-page and off-page SEO optimization to improve your search engine rankings and visibility.</div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Do you conduct keyword research & competitor analysis?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">Absolutely. We perform in-depth keyword research and competitor analysis to develop a data-driven SEO strategy tailored to your business.</div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Do you provide social media management & content strategy?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">Yes, we manage your social media presence and create content strategies that engage your audience and drive growth.</div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                Do you provide Google Analytics & performance reporting?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">Yes, we provide detailed analytics and monthly performance reports so you can track traffic, rankings, and ROI.</div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                Do you offer pay-per-click (PPC) campaign management?
                            </button>
                        </h2>
                        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">Yes, we manage PPC campaigns across Google Ads and social media platforms to drive targeted traffic and maximize your ad spend ROI.</div>
                        </div>
                    </div>
                </div>
                <div class="text-center mt-4">
                    <a href="/contact" class="btn btn-red">Start Your Project <i class="bi bi-arrow-right ms-2"></i></a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'footer.php'; ?>
