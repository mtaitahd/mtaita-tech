<?php
$page_title = 'Graphic Design Services Tanzania | Logo Design, Branding';
$page_desc = 'Professional graphic design services in Tanzania. Logo design, branding, flyers, business cards, social media graphics by Mtaita Tech.';
$page_keywords = 'graphic design Tanzania, logo design Arusha, flyer design Moshi, branding Tanzania, business card design Kilimanjaro, graphic designer Moshi';
$service_category = 'Graphic Design';
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
        ['@type' => 'Question', 'name' => 'Do you design professional logos?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Yes, we create unique, memorable logos that reflect your brand identity and values.']],
        ['@type' => 'Question', 'name' => 'Do you provide full brand identity packages?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Yes, we design complete branding packages including logos, color palettes, typography, and brand guidelines.']],
        ['@type' => 'Question', 'name' => 'Do you design marketing materials?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Yes, we design flyers, brochures, business cards, banners, posters, and social media graphics.']],
        ['@type' => 'Question', 'name' => 'Do you provide print-ready files?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Yes, we deliver print-ready files in PDF, AI, SVG, JPG, and PNG formats as per your requirements.']],
        ['@type' => 'Question', 'name' => 'Do you design social media graphics?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Yes, we create engaging social media posts, covers, banners, and ad creatives for all platforms.']]
    ]
];
?>
<script type="application/ld+json"><?= json_encode($faq_schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?></script>

<section class="page-header<?= $hero_bg ? ' page-header-with-bg' : '' ?>"<?php if ($hero_bg): ?> style="background-image:url('/<?= htmlspecialchars(webp_url($hero_bg)) ?>')"<?php endif; ?>>
    <div class="container">
        <h1><?= __('graphic_design') ?></h1>
        <p>Creative graphic design solutions to elevate your brand identity</p>
    </div>
</section>

<section class="wd-main section-padding">
    <div class="container">
        <div class="row g-4 g-lg-5">
            <div class="col-lg-6 wd-col-left">
                <div class="wd-block">
                    <h2 class="wd-heading">Why Choose Our Graphic Design Team</h2>
                    <p class="wd-subtext">Professional designs that capture attention, communicate your message, and strengthen your brand.</p>
                    <ul class="wd-benefit-list">
                        <li><a class="wd-benefit-row" href="#wd-overview"><span class="wd-benefit-text">Creative &amp; Unique Designs</span><span class="wd-arrow"><i class="bi bi-arrow-right"></i></span></a></li>
                        <li><a class="wd-benefit-row" href="#wd-services"><span class="wd-benefit-text">Brand Identity &amp; Logo Design</span><span class="wd-arrow"><i class="bi bi-arrow-right"></i></span></a></li>
                        <li><a class="wd-benefit-row" href="/services"><span class="wd-benefit-text">Marketing Materials</span><span class="wd-arrow"><i class="bi bi-arrow-right"></i></span></a></li>
                        <li><a class="wd-benefit-row" href="#wd-types"><span class="wd-benefit-text">Print &amp; Digital Designs</span><span class="wd-arrow"><i class="bi bi-arrow-right"></i></span></a></li>
                        <li><a class="wd-benefit-row" href="/contact"><span class="wd-benefit-text">Social Media Graphics</span><span class="wd-arrow"><i class="bi bi-arrow-right"></i></span></a></li>
                        <li><a class="wd-benefit-row" href="#faq"><span class="wd-benefit-text">Fast Delivery &amp; Revisions</span><span class="wd-arrow"><i class="bi bi-arrow-right"></i></span></a></li>
                    </ul>
                </div>

                <div class="wd-block" id="faq">
                    <h2 class="wd-heading">Frequently Asked Questions</h2>
                    <div class="accordion wd-faq" id="faqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header"><button class="accordion-button" data-bs-toggle="collapse" data-bs-target="#faq1">Do you design professional logos?</button></h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion"><div class="accordion-body">Yes, we create unique, memorable logos that reflect your brand identity and values.</div></div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header"><button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#faq2">Do you provide full brand identity packages?</button></h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body">Yes, we design complete branding packages including logos, color palettes, typography, and brand guidelines.</div></div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header"><button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#faq3">Do you design marketing materials?</button></h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body">Yes, we design flyers, brochures, business cards, banners, posters, and social media graphics.</div></div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header"><button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#faq4">Do you provide print-ready files?</button></h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body">Yes, we deliver print-ready files in PDF, AI, SVG, JPG, and PNG formats as per your requirements.</div></div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header"><button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#faq5">Do you design social media graphics?</button></h2>
                            <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body">Yes, we create engaging social media posts, covers, banners, and ad creatives for all platforms.</div></div>
                        </div>
                    </div>
                </div>

                <div class="wd-contact-card">
                    <h3>Need Help With Graphic Design?</h3>
                    <p>Talk to our team about your design needs and get a custom quote.</p>
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

            <div class="col-lg-6 wd-col-right">
                <div class="wd-overview-img">
                    <img src="<?= htmlspecialchars(webp_url('/assets/img/design.png')) ?>" alt="Mtaita Tech graphic design services" loading="lazy">
                </div>

                <div class="wd-block">
                    <h2 class="wd-heading" id="wd-overview">Graphic Design Overview</h2>
                    <p class="wd-subtext">We create impactful designs that help businesses stand out and connect with their audience.</p>
                    <h3 class="wd-subheading" id="wd-services">Graphic Design Services</h3>
                    <div class="row g-3 wd-service-grid">
                        <div class="col-sm-6"><a class="wd-service-link" href="/services"><span>Logo Design</span><span class="wd-arrow"><i class="bi bi-arrow-right"></i></span></a></div>
                        <div class="col-sm-6"><a class="wd-service-link" href="/services"><span>Brand Identity Design</span><span class="wd-arrow"><i class="bi bi-arrow-right"></i></span></a></div>
                        <div class="col-sm-6"><a class="wd-service-link" href="/services"><span>Business Card Design</span><span class="wd-arrow"><i class="bi bi-arrow-right"></i></span></a></div>
                        <div class="col-sm-6"><a class="wd-service-link" href="/contact"><span>Flyer &amp; Brochure Design</span><span class="wd-arrow"><i class="bi bi-arrow-right"></i></span></a></div>
                    </div>
                </div>

                <div class="wd-block">
                    <h3 class="wd-subheading" id="wd-types">Design Solutions We Offer</h3>
                    <p class="wd-subtext">Creative solutions for all your branding and marketing needs.</p>
                    <div class="row g-3 wd-types-grid">
                        <div class="col-sm-6"><div class="wd-type-card"><h4>Logo Design</h4><p>Professional logo creation with multiple concepts and revisions.</p></div></div>
                        <div class="col-sm-6"><div class="wd-type-card"><h4>Branding Packages</h4><p>Complete brand identity kits with guidelines for consistent branding.</p></div></div>
                        <div class="col-sm-6"><div class="wd-type-card"><h4>Marketing Materials</h4><p>Flyers, posters, banners, and brochures for your marketing campaigns.</p></div></div>
                        <div class="col-sm-6"><div class="wd-type-card"><h4>Social Media Graphics</h4><p>Eye-catching posts and ads optimized for all social platforms.</p></div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-padding bg-light-section">
    <div class="container">
        <div class="text-center mb-5"><h2>Our Graphic Design Projects</h2><p class="text-muted">Explore our latest design work</p></div>
        <?php if (empty($projects)): ?>
            <div class="col-12 text-center text-muted py-5"><p>No graphic design projects yet. Check back soon!</p></div>
        <?php else: ?>
        <div id="designCarousel" class="carousel slide projects-carousel" data-bs-ride="carousel" data-bs-interval="4000">
            <div class="carousel-indicators">
                <?php $total_slides = ceil(count($projects) / 3); for ($i=0; $i<$total_slides; $i++): ?>
                <button type="button" data-bs-target="#designCarousel" data-bs-slide-to="<?= $i ?>" class="<?= $i===0?'active':'' ?>"></button>
                <?php endfor; ?>
            </div>
            <div class="carousel-inner">
                <?php $chunks = array_chunk($projects, 3); $slide_idx=0; foreach ($chunks as $chunk): ?>
                <div class="carousel-item <?= $slide_idx===0?'active':'' ?>">
                    <div class="row g-4">
                        <?php foreach ($chunk as $project): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="portfolio-card">
                                <div class="portfolio-img-wrap"><img src="<?= htmlspecialchars(webp_url($project['project_screenshot'])) ?>" class="portfolio-img" alt="<?= htmlspecialchars($project['project_title']) ?>" loading="lazy"></div>
                                <div class="portfolio-body"><h5><?= htmlspecialchars($project['project_title']) ?></h5><p class="portfolio-desc-preview"><?= htmlspecialchars(substr($project['project_desc'],0,120)) ?></p><a href="<?= htmlspecialchars($project['project_link']) ?>" target="_blank" class="portfolio-overlay-btn mt-3">Go Live</a></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php $slide_idx++; endforeach; ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#designCarousel" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
            <button class="carousel-control-next" type="button" data-bs-target="#designCarousel" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
        </div>
        <?php endif; ?>
    </div>
</section>

<section class="section-padding bg-light-section">
    <div class="container">
        <div class="text-center"><h2>Graphic Design Services Across Tanzania</h2><p class="text-muted">Find expert designers in your city</p>
            <div class="d-flex flex-wrap justify-content-center gap-2 mt-3">
                <a href="/web-designer-dar-es-salaam" class="btn btn-outline-red">Graphic Designer Dar es Salaam</a>
                <a href="/web-designer-arusha" class="btn btn-outline-red">Graphic Designer Arusha</a>
                <a href="/web-designer-mwanza" class="btn btn-outline-red">Graphic Designer Mwanza</a>
            </div>
        </div>
    </div>
</section>
<?php require_once 'footer.php'; ?>
