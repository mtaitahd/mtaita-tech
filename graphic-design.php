<?php
$page_title = 'Graphic Design Services | Logo & Brand Tanzania';
$page_desc = 'Professional graphic design in Kilimanjaro and Tanzania. Logo design, branding, social media graphics, and print materials by Mtaita Tech.';
$page_keywords = 'graphic design Tanzania, logo design Kilimanjaro, branding services Arusha, graphic designer Moshi, social media graphics Tanzania, print design Tanzania';
$service_category = 'Graphic Design';
require_once 'header.php';
require_once 'db_connect.php';
require_once 'lib/Settings.php';

$hero_bg = Settings::get('hero_bg_graphic_design', '');

$stmt = $pdo->prepare("SELECT id, project_title, project_desc, project_link, project_screenshot FROM portfolio WHERE category = ? ORDER BY created_at DESC LIMIT 12");
$stmt->execute([$service_category]);
$projects = $stmt->fetchAll();
?>

<section class="page-header<?= $hero_bg ? ' page-header-with-bg' : '' ?>"<?php if ($hero_bg): ?> style="background-image:url('/<?= htmlspecialchars($hero_bg) ?>')"<?php endif; ?>>
    <div class="container">
        <h1><?= __('graphic_design') ?></h1>
        <p>Stunning visuals & brand identities that make your business stand out</p>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="text-center mb-5">
            <h2>Our Graphic Design Projects</h2>
            <p class="text-muted">Explore our latest graphic design work</p>
        </div>
        <?php if (empty($projects)): ?>
            <div class="col-12 text-center text-muted py-5"><p>No graphic design projects yet. Check back soon!</p></div>
        <?php else: ?>
        <div id="gdCarousel" class="carousel slide projects-carousel" data-bs-ride="carousel" data-bs-interval="4000">
            <div class="carousel-indicators">
                <?php $total_slides = ceil(count($projects) / 3); ?>
                <?php for ($i = 0; $i < $total_slides; $i++): ?>
                <button type="button" data-bs-target="#gdCarousel" data-bs-slide-to="<?= $i ?>" class="<?= $i === 0 ? 'active' : '' ?>" aria-current="<?= $i === 0 ? 'true' : 'false' ?>" aria-label="Slide <?= $i + 1 ?>"></button>
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
            <button class="carousel-control-prev" type="button" data-bs-target="#gdCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#gdCarousel" data-bs-slide="next">
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
            <p class="text-muted">Everything you need to know about our graphic design services</p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                Do you offer professional logo and brand identity design?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">Yes, we create professional logos and complete brand identity packages including color palettes, typography, and brand guidelines.</div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Do you design social media and marketing materials?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">Absolutely. We design custom social media graphics, flyers, brochures, and all marketing collateral tailored to your brand.</div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Do you create business cards, flyers, and print materials?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">Yes, we design business cards, flyers, banners, and all print collateral with print-ready specifications.</div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                Do you provide UI/UX design for web and mobile?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">Yes, we design intuitive and visually appealing UI/UX for websites and mobile applications to enhance user experience.</div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                Do you offer fast turnaround with revisions?
                            </button>
                        </h2>
                        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">Yes, we offer fast turnaround times and unlimited revisions to ensure you are completely satisfied with the final design.</div>
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
