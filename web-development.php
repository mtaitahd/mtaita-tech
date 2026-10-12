<?php
$page_title = 'Web Development Kilimanjaro | Web Design Tanzania';
$page_desc = 'Professional web development in Kilimanjaro and Arusha. Custom websites, eCommerce platforms, and web apps by Mtaita Tech. Affordable & responsive.';
$page_keywords = 'website development Kilimanjaro, web design Tanzania, web developer Arusha, website designer Moshi, eCommerce website Tanzania, professional website Tanzania';
$service_category = 'Web Development';
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
        ['@type' => 'Question', 'name' => 'Do you build custom responsive websites?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Yes, we build fully custom responsive websites using modern technologies, tailored to your brand and business needs.']],
        ['@type' => 'Question', 'name' => 'Do you offer e-commerce with secure payment integration?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Absolutely. We build e-commerce platforms with secure payment gateways including mobile money, card payments, and more.']],
        ['@type' => 'Question', 'name' => 'Do you provide content management systems?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Yes, we integrate easy-to-use CMS platforms so you can update your website content anytime without technical skills.']],
        ['@type' => 'Question', 'name' => 'Is your code SEO-optimized and performance tuned?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Yes, we follow SEO best practices and performance optimization techniques to ensure fast loading speeds and good search engine rankings.']],
        ['@type' => 'Question', 'name' => 'Do you provide ongoing maintenance and support?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Yes, we offer ongoing maintenance and 24/7 technical support to keep your website running smoothly and securely.']]
    ]
];
?>

<script type="application/ld+json"><?= json_encode($faq_schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?></script>

<section class="page-header<?= $hero_bg ? ' page-header-with-bg' : '' ?>"<?php if ($hero_bg): ?> style="background-image:url('/<?= htmlspecialchars(webp_url($hero_bg)) ?>')"<?php endif; ?>>
    <div class="container">
        <h1><?= __('web_development') ?></h1>
        <p>Custom websites, web apps & e-commerce solutions tailored for your business</p>
    </div>
</section>

<!-- Web Development two-column content -->
<section class="wd-main section-padding">
    <div class="container">
        <div class="row g-4 g-lg-5">
            <!-- Left column -->
            <div class="col-lg-6 wd-col-left">
                <div class="wd-block">
                    <h2 class="wd-heading">Why Choose Our Web Team</h2>
                    <p class="wd-subtext">End-to-end website solutions that are modern, secure, and built to grow with your organization.</p>
                    <ul class="wd-benefit-list">
                        <li>
                            <a class="wd-benefit-row" href="#wd-overview">
                                <span class="wd-benefit-text">Complete Web Solutions</span>
                                <span class="wd-arrow" aria-hidden="true"><i class="bi bi-arrow-right"></i></span>
                            </a>
                        </li>
                        <li>
                            <a class="wd-benefit-row" href="#wd-services">
                                <span class="wd-benefit-text">Modern &amp; Responsive Design</span>
                                <span class="wd-arrow" aria-hidden="true"><i class="bi bi-arrow-right"></i></span>
                            </a>
                        </li>
                        <li>
                            <a class="wd-benefit-row" href="/services">
                                <span class="wd-benefit-text">Secure &amp; Connected</span>
                                <span class="wd-arrow" aria-hidden="true"><i class="bi bi-arrow-right"></i></span>
                            </a>
                        </li>
                        <li>
                            <a class="wd-benefit-row" href="/seo-digital-marketing">
                                <span class="wd-benefit-text">SEO &amp; Online Visibility</span>
                                <span class="wd-arrow" aria-hidden="true"><i class="bi bi-arrow-right"></i></span>
                            </a>
                        </li>
                        <li>
                            <a class="wd-benefit-row" href="/contact">
                                <span class="wd-benefit-text">Ongoing Website Management</span>
                                <span class="wd-arrow" aria-hidden="true"><i class="bi bi-arrow-right"></i></span>
                            </a>
                        </li>
                        <li>
                            <a class="wd-benefit-row" href="#wd-types">
                                <span class="wd-benefit-text">Built for Your Industry</span>
                                <span class="wd-arrow" aria-hidden="true"><i class="bi bi-arrow-right"></i></span>
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="wd-block" id="faq">
                    <h2 class="wd-heading">Frequently Asked Questions</h2>
                    <p class="wd-subtext">Answers to common questions about our web development services.</p>
                    <div class="accordion wd-faq" id="faqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1" aria-expanded="true" aria-controls="faq1">
                                    Do you build custom responsive websites?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">Yes, we build fully custom responsive websites using modern technologies, tailored to your brand and business needs.</div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2" aria-expanded="false" aria-controls="faq2">
                                    Do you offer e-commerce with secure payment integration?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">Absolutely. We build e-commerce platforms with secure payment gateways including mobile money, card payments, and more.</div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3" aria-expanded="false" aria-controls="faq3">
                                    Do you provide content management systems?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">Yes, we integrate easy-to-use CMS platforms so you can update your website content anytime without technical skills.</div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4" aria-expanded="false" aria-controls="faq4">
                                    Is your code SEO-optimized and performance tuned?
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">Yes, we follow SEO best practices and performance optimization techniques to ensure fast loading speeds and good search engine rankings.</div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5" aria-expanded="false" aria-controls="faq5">
                                    Do you provide ongoing maintenance and support?
                                </button>
                            </h2>
                            <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">Yes, we offer ongoing maintenance and 24/7 technical support to keep your website running smoothly and securely.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="wd-contact-card">
                    <h3>Need Help With Web Development?</h3>
                    <p>Talk to our team about your website project and get clear guidance, timelines, and a free quotation.</p>
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
                    <img src="<?= htmlspecialchars(webp_url('/assets/img/web.png')) ?>" alt="Mtaita Tech web development team" loading="lazy">
                </div>

                <div class="wd-block">
                    <h2 class="wd-heading" id="wd-overview">Web Development Overview</h2>
                    <p class="wd-subtext">We deliver complete web solutions, from design to deployment, hosting, and ongoing management.</p>

                    <h3 class="wd-subheading" id="wd-services">Web Development</h3>
                    <div class="row g-3 wd-service-grid">
                        <div class="col-sm-6">
                            <a class="wd-service-link" href="/services">
                                <span>Website Design | Development | Branding</span>
                                <span class="wd-arrow" aria-hidden="true"><i class="bi bi-arrow-right"></i></span>
                            </a>
                        </div>
                        <div class="col-sm-6">
                            <a class="wd-service-link" href="/seo-digital-marketing">
                                <span>Search Engine Optimization (SEO)</span>
                                <span class="wd-arrow" aria-hidden="true"><i class="bi bi-arrow-right"></i></span>
                            </a>
                        </div>
                        <div class="col-sm-6">
                            <a class="wd-service-link" href="/services">
                                <span>Domain | Hosting | Integrations | Security</span>
                                <span class="wd-arrow" aria-hidden="true"><i class="bi bi-arrow-right"></i></span>
                            </a>
                        </div>
                        <div class="col-sm-6">
                            <a class="wd-service-link" href="/contact">
                                <span>Maintenance &amp; Website Management</span>
                                <span class="wd-arrow" aria-hidden="true"><i class="bi bi-arrow-right"></i></span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="wd-block">
                    <h3 class="wd-subheading" id="wd-types">Types of Websites We Develop</h3>
                    <p class="wd-subtext">We design and build all kinds of websites tailored to the specific needs of our clients across different industries.</p>
                    <div class="row g-3 wd-types-grid">
                        <div class="col-sm-6">
                            <div class="wd-type-card">
                                <h4>Corporate Websites</h4>
                                <p>Professional company websites that present your brand, services, and contact details with clarity and credibility.</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="wd-type-card">
                                <h4>Financial Institutions</h4>
                                <p>Secure, compliant websites and digital platforms for banks, SACCOs, and microfinance institutions.</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="wd-type-card">
                                <h4>Non-Financial Cooperative Websites</h4>
                                <p>Member-focused websites for co-operatives with services, news, and membership information.</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="wd-type-card">
                                <h4>E-Commerce Websites</h4>
                                <p>Online stores with product catalogues, secure checkout, and mobile money or card payment integration.</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="wd-type-card">
                                <h4>Educational Portals</h4>
                                <p>School, college, and training websites with courses, admissions, news, and student resources.</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="wd-type-card">
                                <h4>Church Websites</h4>
                                <p>Welcoming church websites with sermons, events, ministries, and online giving information.</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="wd-type-card">
                                <h4>Organization Websites</h4>
                                <p>Websites for NGOs, associations, and community organizations that share your mission and impact.</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="wd-type-card">
                                <h4>Tours and Safaris Websites</h4>
                                <p>Travel and tour websites with destination galleries, itineraries, and enquiry or booking forms.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Web Development Projects -->
<section class="section-padding bg-light-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2>Our Web Development Projects</h2>
            <p class="text-muted">Explore our latest web development work</p>
        </div>
        <?php if (empty($projects)): ?>
            <div class="col-12 text-center text-muted py-5"><p>No web development projects yet. Check back soon!</p></div>
        <?php else: ?>
        <div id="webDevCarousel" class="carousel slide projects-carousel" data-bs-ride="carousel" data-bs-interval="4000">
            <div class="carousel-indicators">
                <?php $total_slides = ceil(count($projects) / 3); ?>
                <?php for ($i = 0; $i < $total_slides; $i++): ?>
                <button type="button" data-bs-target="#webDevCarousel" data-bs-slide-to="<?= $i ?>" class="<?= $i === 0 ? 'active' : '' ?>" aria-current="<?= $i === 0 ? 'true' : 'false' ?>" aria-label="Slide <?= $i + 1 ?>"></button>
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
            <button class="carousel-control-prev" type="button" data-bs-target="#webDevCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#webDevCarousel" data-bs-slide="next">
                <span class="visually-hidden">Next</span>
            </button>
        </div>
        <?php endif; ?>
    </div>
</section>

<section class="section-padding bg-light-section">
    <div class="container">
        <div class="text-center">
            <h2>Web Design Services Across Tanzania</h2>
            <p class="text-muted">Find a professional web designer in your city</p>
            <div class="d-flex flex-wrap justify-content-center gap-2 mt-3">
                <a href="/web-designer-dar-es-salaam" class="btn btn-outline-red">Web Designer Dar es Salaam</a>
                <a href="/web-designer-arusha" class="btn btn-outline-red">Web Designer Arusha</a>
                <a href="/web-designer-mwanza" class="btn btn-outline-red">Web Designer Mwanza</a>
            </div>
        </div>
    </div>
</section>
<?php require_once 'footer.php'; ?>
