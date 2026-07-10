<?php
$page_title = 'Mobile App Development | Android & iOS Tanzania';
$page_desc = 'Professional mobile app development in Arusha and Kilimanjaro. Custom Android and iOS applications built with modern tech by Mtaita Tech.';
$page_keywords = 'mobile app development Arusha, Android app development Tanzania, iOS app developer Tanzania, mobile app developer Kilimanjaro, cross-platform apps Tanzania';
$service_category = 'Mobile Apps';
require_once 'header.php';
require_once 'db_connect.php';
require_once 'lib/Settings.php';

$hero_bg = Settings::get('hero_bg_services', '');

$stmt = $pdo->prepare("SELECT id, project_title, project_desc, project_link, project_screenshot FROM portfolio WHERE category = ? ORDER BY created_at DESC LIMIT 12");
$stmt->execute([$service_category]);
$projects = $stmt->fetchAll();
?>

<section class="page-header<?= $hero_bg ? ' page-header-with-bg' : '' ?>"<?php if ($hero_bg): ?> style="background-image:url('/<?= htmlspecialchars(webp_url($hero_bg)) ?>')"<?php endif; ?>>
    <div class="container">
        <h1><?= __('mobile_apps') ?></h1>
        <p>Innovative mobile applications for iOS & Android built with cutting-edge technology</p>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="text-center mb-5">
            <h2>Our Mobile App Projects</h2>
            <p class="text-muted">Explore our latest mobile app development work</p>
        </div>
        <?php if (empty($projects)): ?>
            <div class="col-12 text-center text-muted py-5"><p>No mobile app projects yet. Check back soon!</p></div>
        <?php else: ?>
        <div id="mobileCarousel" class="carousel slide projects-carousel" data-bs-ride="carousel" data-bs-interval="4000">
            <div class="carousel-indicators">
                <?php $total_slides = ceil(count($projects) / 3); ?>
                <?php for ($i = 0; $i < $total_slides; $i++): ?>
                <button type="button" data-bs-target="#mobileCarousel" data-bs-slide-to="<?= $i ?>" class="<?= $i === 0 ? 'active' : '' ?>" aria-current="<?= $i === 0 ? 'true' : 'false' ?>" aria-label="Slide <?= $i + 1 ?>"></button>
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
            <button class="carousel-control-prev" type="button" data-bs-target="#mobileCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#mobileCarousel" data-bs-slide="next">
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
            <p class="text-muted">Everything you need to know about our mobile app services</p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                Do you offer native & cross-platform app development?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">Yes, we develop both native iOS/Android apps and cross-platform apps using React Native and Flutter for consistent performance across devices.</div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Do you provide clean, intuitive UI/UX design for mobile?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">Absolutely. We design clean and intuitive mobile interfaces focused on excellent user experience and smooth navigation.</div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Do you handle backend API integration & cloud sync?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">Yes, we integrate robust backend APIs and cloud synchronization to ensure your app data is always up-to-date and accessible.</div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                Do you assist with app store deployment & submission?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">Yes, we handle the full deployment process including App Store and Google Play submission, ensuring your app meets all platform guidelines.</div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                Do you provide ongoing support & feature updates?
                            </button>
                        </h2>
                        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">Yes, we offer ongoing maintenance, support, and regular feature updates to keep your app running smoothly and up-to-date.</div>
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
