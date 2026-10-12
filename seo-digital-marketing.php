<?php
$page_title = 'SEO & Digital Marketing Services | Tanzania';
$page_desc = 'Professional SEO and digital marketing in Kilimanjaro and Tanzania. Grow your online presence, rank higher on Google, attract more customers.';
$page_keywords = 'SEO company Kilimanjaro, digital marketing Tanzania, SEO services Arusha, search engine optimization Tanzania, social media marketing Tanzania, online marketing Tanzania';
$service_category = 'SEO & Digital Marketing';
$body_class = 'wd-page';
require_once 'header.php';
require_once 'db_connect.php';
require_once 'lib/Settings.php';

$hero_bg = Settings::get('hero_bg_services', '');

$wd_email = Settings::get('admin_email', ADMIN_EMAIL);
$wd_phone = Settings::get('admin_phone', '+255 616 591 639');
$wd_location = Settings::get('admin_location', 'Moshi, Kilimanjaro');

$stmt = $pdo->prepare("SELECT id, project_title, project_desc, project_link, project_screenshot FROM portfolio WHERE category = ? ORDER BY created_at DESC LIMIT 12");
$stmt->execute([$service_category]);
$projects = $stmt->fetchAll();

$faq_schema = [
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => [
        ['@type' => 'Question', 'name' => 'Do you offer on-page & off-page SEO optimization?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Yes, we provide comprehensive on-page and off-page SEO optimization to improve your search engine rankings and visibility.']],
        ['@type' => 'Question', 'name' => 'Do you conduct keyword research & competitor analysis?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Absolutely. We perform in-depth keyword research and competitor analysis to develop a data-driven SEO strategy tailored to your business.']],
        ['@type' => 'Question', 'name' => 'Do you provide social media management & content strategy?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Yes, we manage your social media presence and create content strategies that engage your audience and drive growth.']],
        ['@type' => 'Question', 'name' => 'Do you provide Google Analytics & performance reporting?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Yes, we provide detailed analytics and monthly performance reports so you can track traffic, rankings, and ROI.']],
        ['@type' => 'Question', 'name' => 'Do you offer pay-per-click (PPC) campaign management?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Yes, we manage PPC campaigns across Google Ads and social media platforms to drive targeted traffic and maximize your ad spend ROI.']]
    ]
];
?>
<script type="application/ld+json"><?= json_encode($faq_schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?></script>

<section class="page-header<?= $hero_bg ? ' page-header-with-bg' : '' ?>"<?php if ($hero_bg): ?> style="background-image:url('/<?= htmlspecialchars(webp_url($hero_bg)) ?>')"<?php endif; ?>>
    <div class="container">
        <h1><?= __('seo_digital_marketing') ?></h1>
        <p>Data-driven SEO &amp; digital marketing strategies to grow your online presence</p>
    </div>
</section>

<section class="wd-main section-padding">
    <div class="container">
        <div class="row g-4 g-lg-5">
            <!-- Left column -->
            <div class="col-lg-6 wd-col-left">
                <div class="wd-block">
                    <h2 class="wd-heading">Why Choose Our SEO &amp; Digital Marketing Team</h2>
                    <p class="wd-subtext">Results-focused digital marketing solutions designed to drive traffic, leads, and revenue for businesses in Tanzania.</p>
                    <ul class="wd-benefit-list">
                        <li>
                            <a class="wd-benefit-row" href="#wd-overview">
                                <span class="wd-benefit-text">Data-Driven Strategies</span>
                                <span class="wd-arrow" aria-hidden="true"><i class="bi bi-arrow-right"></i></span>
                            </a>
                        </li>
                        <li>
                            <a class="wd-benefit-row" href="#wd-services">
                                <span class="wd-benefit-text">On-Page &amp; Off-Page SEO</span>
                                <span class="wd-arrow" aria-hidden="true"><i class="bi bi-arrow-right"></i></span>
                            </a>
                        </li>
                        <li>
                            <a class="wd-benefit-row" href="/services">
                                <span class="wd-benefit-text">Social Media Management</span>
                                <span class="wd-arrow" aria-hidden="true"><i class="bi bi-arrow-right"></i></span>
                            </a>
                        </li>
                        <li>
                            <a class="wd-benefit-row" href="#wd-types">
                                <span class="wd-benefit-text">Paid Campaigns (PPC)</span>
                                <span class="wd-arrow" aria-hidden="true"><i class="bi bi-arrow-right"></i></span>
                            </a>
                        </li>
                        <li>
                            <a class="wd-benefit-row" href="/contact">
                                <span class="wd-benefit-text">Content Strategy &amp; Copywriting</span>
                                <span class="wd-arrow" aria-hidden="true"><i class="bi bi-arrow-right"></i></span>
                            </a>
                        </li>
                        <li>
                            <a class="wd-benefit-row" href="#faq">
                                <span class="wd-benefit-text">Transparent Reporting &amp; Analytics</span>
                                <span class="wd-arrow" aria-hidden="true"><i class="bi bi-arrow-right"></i></span>
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="wd-block" id="faq">
                    <h2 class="wd-heading">Frequently Asked Questions</h2>
                    <p class="wd-subtext">Answers to common questions about our SEO &amp; digital marketing services.</p>
                    <div class="accordion wd-faq" id="faqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1" aria-expanded="true" aria-controls="faq1">
                                    Do you offer on-page &amp; off-page SEO optimization?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">Yes, we provide comprehensive on-page and off-page SEO optimization to improve your search engine rankings and visibility.</div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2" aria-expanded="false" aria-controls="faq2">
                                    Do you conduct keyword research &amp; competitor analysis?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">Absolutely. We perform in-depth keyword research and competitor analysis to develop a data-driven SEO strategy tailored to your business.</div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3" aria-expanded="false" aria-controls="faq3">
                                    Do you provide social media management &amp; content strategy?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">Yes, we manage your social media presence and create content strategies that engage your audience and drive growth.</div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4" aria-expanded="false" aria-controls="faq4">
                                    Do you provide Google Analytics &amp; performance reporting?
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">Yes, we provide detailed analytics and monthly performance reports so you can track traffic, rankings, and ROI.</div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5" aria-expanded="false" aria-controls="faq5">
                                    Do you offer pay-per-click (PPC) campaign management?
                                </button>
                            </h2>
                            <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">Yes, we manage PPC campaigns across Google Ads and social media platforms to drive targeted traffic and maximize your ad spend ROI.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="wd-contact-card">
                    <h3>Need Help With SEO &amp; Digital Marketing?</h3>
                    <p>Talk to our team about your goals and get a tailored strategy to grow your online presence.</p>
                    <div class="wd-contact-actions">
                        <a href="/contact" class="btn btn-red"><i class="bi bi-envelope me-2"></i>Contact Us</a>
                        <a href="/contact" class="wd-btn-quote"><i class="bi bi-clipboard-check me-2"></i>Request a Quote</a>
                    </div>
                    <ul class="wd-contact-meta">
                        <li><i class="bi bi-telephone"></i> <a href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', $wd_phone)) ?>"><?= htmlspecialchars($wd_phone) ?></a></li>
                        <li><i class="bi bi-envelope"></i> <a href="mailto:<?= htmlspecialchars($wd_email) ?>"><?= htmlspecialchars($wd_email) ?></a></li>
                        <li><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($wd_location) ?></li>
                    </ul>
                </div>
            </div>

            <!-- Right column -->
            <div class="col-lg-6 wd-col-right">
                <div class="wd-overview-img">
                    <img src="<?= htmlspecialchars(webp_url('/assets/img/seo.png')) ?>" alt="Mtaita Tech SEO &amp; digital marketing services" loading="lazy">
                </div>

                <div class="wd-block">
                    <h2 class="wd-heading" id="wd-overview">SEO &amp; Digital Marketing Overview</h2>
                    <p class="wd-subtext">We help businesses attract the right audience, generate qualified leads, and grow sustainably with measurable digital marketing strategies.</p>

                    <h3 class="wd-subheading" id="wd-services">Our Digital Marketing Services</h3>
                    <div class="row g-3 wd-service-grid">
                        <div class="col-sm-6">
                            <a class="wd-service-link" href="/services">
                                <span>Search Engine Optimization (SEO)</span>
                                <span class="wd-arrow" aria-hidden="true"><i class="bi bi-arrow-right"></i></span>
                            </a>
                        </div>
                        <div class="col-sm-6">
                            <a class="wd-service-link" href="/seo-digital-marketing">
                                <span>Pay-Per-Click (PPC) Advertising</span>
                                <span class="wd-arrow" aria-hidden="true"><i class="bi bi-arrow-right"></i></span>
                            </a>
                        </div>
                        <div class="col-sm-6">
                            <a class="wd-service-link" href="/services">
                                <span>Social Media Management</span>
                                <span class="wd-arrow" aria-hidden="true"><i class="bi bi-arrow-right"></i></span>
                            </a>
                        </div>
                        <div class="col-sm-6">
                            <a class="wd-service-link" href="/contact">
                                <span>Content Marketing &amp; Copywriting</span>
                                <span class="wd-arrow" aria-hidden="true"><i class="bi bi-arrow-right"></i></span>
                            </a>
                        </div>
                        <div class="col-sm-6">
                            <a class="wd-service-link" href="/seo-digital-marketing">
                                <span>Email Marketing &amp; Automation</span>
                                <span class="wd-arrow" aria-hidden="true"><i class="bi bi-arrow-right"></i></span>
                            </a>
                        </div>
                        <div class="col-sm-6">
                            <a class="wd-service-link" href="/seo-digital-marketing">
                                <span>Google Analytics &amp; Reporting</span>
                                <span class="wd-arrow" aria-hidden="true"><i class="bi bi-arrow-right"></i></span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="wd-block">
                    <h3 class="wd-subheading" id="wd-types">SEO &amp; Marketing Solutions We Offer</h3>
                    <p class="wd-subtext">Tailored campaigns to help businesses rank higher, convert better, and scale faster.</p>
                    <div class="row g-3 wd-types-grid">
                        <div class="col-sm-6">
                            <div class="wd-type-card">
                                <h4>Local SEO</h4>
                                <p>Boost visibility for Moshi, Arusha, Dar es Salaam and other Tanzanian cities to attract nearby customers.</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="wd-type-card">
                                <h4>E-Commerce SEO</h4>
                                <p>Optimize product pages, categories, and technical SEO to drive more sales from search engines.</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="wd-type-card">
                                <h4>Technical SEO</h4>
                                <p>Improve site speed, mobile-friendliness, crawlability, and structured data for better rankings.</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="wd-type-card">
                                <h4>Google &amp; Meta Ads</h4>
                                <p>Targeted PPC campaigns on Google, Facebook, Instagram &amp; LinkedIn to reach your ideal customers.</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="wd-type-card">
                                <h4>Social Media Marketing</h4>
                                <p>Build brand awareness, grow engagement, and drive conversions with strategic social media campaigns.</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="wd-type-card">
                                <h4>Content &amp; Link Building</h4>
                                <p>Create valuable content and earn high-quality backlinks to establish authority in your niche.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-padding bg-light-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2>Our SEO &amp; Digital Marketing Projects</h2>
            <p class="text-muted">Explore our latest marketing campaigns &amp; SEO work</p>
        </div>
        <?php if (empty($projects)): ?>
            <div class="col-12 text-center text-muted py-5"><p>No SEO &amp; marketing projects yet. Check back soon!</p></div>
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
                                    <img src="<?= htmlspecialchars(webp_url($project['project_screenshot'])) ?>" alt="<?= htmlspecialchars($project['project_title']) ?>" class="portfolio-img" loading="lazy" onerror="this.src='https://via.placeholder.com/600x400/0F172A/DC2626?text=Mtaita+Tech'">
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
        <div class="text-center">
            <h2>SEO Services Across Tanzania</h2>
            <p class="text-muted">Find expert SEO services in your city</p>
            <div class="d-flex flex-wrap justify-content-center gap-2 mt-3">
                <a href="/seo-dar-es-salaam" class="btn btn-outline-red">SEO Dar es Salaam</a>
                <a href="/seo-arusha" class="btn btn-outline-red">SEO Arusha</a>
                <a href="/seo-moshi" class="btn btn-outline-red">SEO Moshi</a>
            </div>
        </div>
    </div>
</section>
<?php require_once 'footer.php'; ?>
