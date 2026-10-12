<?php
$page_title = 'Mobile App Development Tanzania | Android & iOS Apps';
$page_desc = 'Professional mobile app development in Tanzania. Custom Android and iOS apps, cross-platform solutions, and enterprise mobile applications by Mtaita Tech.';
$page_keywords = 'mobile app development Tanzania, android app development Arusha, iOS app development Moshi, mobile app developer Kilimanjaro, cross-platform apps Tanzania, app development company Tanzania';
$service_category = 'Mobile Apps';
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
        ['@type' => 'Question', 'name' => 'Do you develop native Android and iOS apps?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Yes, we develop native and cross-platform mobile apps for Android, iOS, and hybrid platforms using modern frameworks.']],
        ['@type' => 'Question', 'name' => 'Do you provide cross-platform development?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Yes, we build cross-platform apps using Flutter and React Native to reduce costs and maintain consistency across platforms.']],
        ['@type' => 'Question', 'name' => 'Do you integrate APIs, payments, and backend systems?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Yes, we integrate REST/GraphQL APIs, mobile money, payment gateways, and custom backend systems with your mobile app.']],
        ['@type' => 'Question', 'name' => 'Do you publish apps to Google Play and App Store?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Yes, we handle app store submission, compliance requirements, and publishing to Google Play Store and Apple App Store.']],
        ['@type' => 'Question', 'name' => 'Do you offer app maintenance and updates?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Yes, we provide ongoing maintenance, updates, bug fixes, and performance optimization for your mobile applications.']]
    ]
];
?>
<script type="application/ld+json"><?= json_encode($faq_schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?></script>

<section class="page-header<?= $hero_bg ? ' page-header-with-bg' : '' ?>"<?php if ($hero_bg): ?> style="background-image:url('/<?= htmlspecialchars(webp_url($hero_bg)) ?>')"<?php endif; ?>>
    <div class="container">
        <h1><?= __('mobile_apps') ?></h1>
        <p>Custom Android, iOS &amp; cross-platform mobile apps for businesses and startups</p>
    </div>
</section>

<section class="wd-main section-padding">
    <div class="container">
        <div class="row g-4 g-lg-5">
            <div class="col-lg-6 wd-col-left">
                <div class="wd-block">
                    <h2 class="wd-heading">Why Choose Our Mobile App Team</h2>
                    <p class="wd-subtext">End-to-end mobile app development from concept to deployment with focus on performance, security, and user experience.</p>
                    <ul class="wd-benefit-list">
                        <li><a class="wd-benefit-row" href="#wd-overview"><span class="wd-benefit-text">Native &amp; Cross-Platform Development</span><span class="wd-arrow"><i class="bi bi-arrow-right"></i></span></a></li>
                        <li><a class="wd-benefit-row" href="#wd-services"><span class="wd-benefit-text">User-Centered UI/UX Design</span><span class="wd-arrow"><i class="bi bi-arrow-right"></i></span></a></li>
                        <li><a class="wd-benefit-row" href="/services"><span class="wd-benefit-text">Secure &amp; Scalable Architecture</span><span class="wd-arrow"><i class="bi bi-arrow-right"></i></span></a></li>
                        <li><a class="wd-benefit-row" href="#wd-types"><span class="wd-benefit-text">API &amp; Backend Integration</span><span class="wd-arrow"><i class="bi bi-arrow-right"></i></span></a></li>
                        <li><a class="wd-benefit-row" href="/contact"><span class="wd-benefit-text">App Store Publishing</span><span class="wd-arrow"><i class="bi bi-arrow-right"></i></span></a></li>
                        <li><a class="wd-benefit-row" href="#faq"><span class="wd-benefit-text">Maintenance &amp; Support</span><span class="wd-arrow"><i class="bi bi-arrow-right"></i></span></a></li>
                    </ul>
                </div>

                <div class="wd-block" id="faq">
                    <h2 class="wd-heading">Frequently Asked Questions</h2>
                    <div class="accordion wd-faq" id="faqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header"><button class="accordion-button" data-bs-toggle="collapse" data-bs-target="#faq1">Do you develop native Android and iOS apps?</button></h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion"><div class="accordion-body">Yes, we develop native and cross-platform mobile apps for Android, iOS, and hybrid platforms using modern frameworks.</div></div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header"><button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#faq2">Do you provide cross-platform development?</button></h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body">Yes, we build cross-platform apps using Flutter and React Native to reduce costs and maintain consistency.</div></div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header"><button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#faq3">Do you integrate APIs, payments, and backend systems?</button></h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body">Yes, we integrate REST/GraphQL APIs, mobile money, payment gateways, and custom backend systems.</div></div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header"><button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#faq4">Do you publish apps to Google Play and App Store?</button></h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body">Yes, we handle app store submission, compliance requirements, and publishing to Google Play Store and Apple App Store.</div></div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header"><button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#faq5">Do you offer app maintenance and updates?</button></h2>
                            <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body">Yes, we provide ongoing maintenance, updates, bug fixes, and performance optimization for your mobile applications.</div></div>
                        </div>
                    </div>
                </div>

                <div class="wd-contact-card">
                    <h3>Need Help With Mobile App Development?</h3>
                    <p>Talk to our team about your mobile app idea and get a free consultation.</p>
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
                    <img src="<?= htmlspecialchars(webp_url('/assets/img/mobile.png')) ?>" alt="Mtaita Tech mobile app development" loading="lazy">
                </div>

                <div class="wd-block">
                    <h2 class="wd-heading" id="wd-overview">Mobile App Development Overview</h2>
                    <p class="wd-subtext">We build fast, secure, and user-friendly mobile apps that solve real business problems and deliver great user experiences.</p>
                    <h3 class="wd-subheading" id="wd-services">Mobile App Development Services</h3>
                    <div class="row g-3 wd-service-grid">
                        <div class="col-sm-6"><a class="wd-service-link" href="/services"><span>Android App Development</span><span class="wd-arrow"><i class="bi bi-arrow-right"></i></span></a></div>
                        <div class="col-sm-6"><a class="wd-service-link" href="/services"><span>iOS App Development</span><span class="wd-arrow"><i class="bi bi-arrow-right"></i></span></a></div>
                        <div class="col-sm-6"><a class="wd-service-link" href="/services"><span>Flutter Cross-Platform Apps</span><span class="wd-arrow"><i class="bi bi-arrow-right"></i></span></a></div>
                        <div class="col-sm-6"><a class="wd-service-link" href="/contact"><span>React Native Development</span><span class="wd-arrow"><i class="bi bi-arrow-right"></i></span></a></div>
                    </div>
                </div>

                <div class="wd-block">
                    <h3 class="wd-subheading" id="wd-types">Types of Mobile Apps We Build</h3>
                    <p class="wd-subtext">From startups to enterprises, we develop mobile solutions tailored to your needs.</p>
                    <div class="row g-3 wd-types-grid">
                        <div class="col-sm-6"><div class="wd-type-card"><h4>E-Commerce Apps</h4><p>Mobile shopping apps with cart, checkout, and mobile money integration.</p></div></div>
                        <div class="col-sm-6"><div class="wd-type-card"><h4>Business &amp; CRM Apps</h4><p>Custom apps to streamline sales, operations, and customer management.</p></div></div>
                        <div class="col-sm-6"><div class="wd-type-card"><h4>Delivery &amp; Logistics Apps</h4><p>Track deliveries, manage orders, and optimize routes in real time.</p></div></div>
                        <div class="col-sm-6"><div class="wd-type-card"><h4>Booking &amp; Reservation Apps</h4><p>Seamless booking experiences for hotels, tours, salons, and events.</p></div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-padding bg-light-section">
    <div class="container">
        <div class="text-center mb-5"><h2>Our Mobile App Projects</h2><p class="text-muted">Explore our latest mobile applications</p></div>
        <?php if (empty($projects)): ?>
            <div class="col-12 text-center text-muted py-5"><p>No mobile app projects yet. Check back soon!</p></div>
        <?php else: ?>
        <div id="mobileCarousel" class="carousel slide projects-carousel" data-bs-ride="carousel" data-bs-interval="4000">
            <div class="carousel-indicators">
                <?php $total_slides = ceil(count($projects) / 3); for ($i=0; $i<$total_slides; $i++): ?>
                <button type="button" data-bs-target="#mobileCarousel" data-bs-slide-to="<?= $i ?>" class="<?= $i===0?'active':'' ?>"></button>
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
            <button class="carousel-control-prev" type="button" data-bs-target="#mobileCarousel" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
            <button class="carousel-control-next" type="button" data-bs-target="#mobileCarousel" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
        </div>
        <?php endif; ?>
    </div>
</section>

<section class="section-padding bg-light-section">
    <div class="container">
        <div class="text-center"><h2>Mobile App Development Across Tanzania</h2><p class="text-muted">Find expert app developers in your city</p>
            <div class="d-flex flex-wrap justify-content-center gap-2 mt-3">
                <a href="/mobile-apps-zanzibar" class="btn btn-outline-red">Mobile Apps Zanzibar</a>
                <a href="/web-designer-arusha" class="btn btn-outline-red">App Developers Arusha</a>
                <a href="/web-designer-dar-es-salaam" class="btn btn-outline-red">App Developers Dar es Salaam</a>
            </div>
        </div>
    </div>
</section>
<?php require_once 'footer.php'; ?>
