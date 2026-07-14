<?php
$page_title = 'Mtaita Tech | Software Company Kilimanjaro & Arusha';
$page_desc = 'Software development company in Kilimanjaro and Arusha. We build websites, mobile apps, POS systems, inventory software & custom business solutions.';
$page_keywords = 'software company Kilimanjaro, software company Arusha, website development Tanzania, IT company Tanzania, web design Tanzania, custom software development, POS system Tanzania';
$page_heading = 'Software Development Company in Kilimanjaro & Arusha';
require_once 'header.php';
require_once 'db_connect.php';
require_once 'lib/News.php';
require_once 'lib/Partner.php';
require_once 'lib/Testimonial.php';
require_once 'lib/Solution.php';

// Fetch active posters for hero slider
$posters = $pdo->query("SELECT poster_image_path, poster_title, redirect_link FROM posters WHERE is_active = 1 ORDER BY created_at DESC")->fetchAll();

// Fetch news, partners, testimonials, solutions
$newsObj = new News();
$news_items = $newsObj->getRecent(4);

$partnerObj = new Partner();
$partners = $partnerObj->getActive();

$testimonialObj = new Testimonial();
$testimonials = $testimonialObj->getApproved();

$solutionObj = new Solution();
$solutions = $solutionObj->getActive();

// Fetch all portfolio items for live systems scrolling section
$live_systems = $pdo->query("SELECT id, project_title, project_desc, project_link, project_screenshot, category, tech_stack FROM portfolio WHERE category != 'Graphic Design' ORDER BY created_at DESC LIMIT 12")->fetchAll();


?>

<section class="hero-split position-relative overflow-hidden" id="hero-section">
    <?php if (!empty($posters)): ?>
    <div class="hero-posters-wrap">
        <?php foreach ($posters as $i => $po): ?>
        <div class="hero-poster-bg <?= $i === 0 ? 'active' : '' ?>" style="background-image:url('/<?= htmlspecialchars(webp_url($po['poster_image_path'])) ?>');"></div>
        <?php endforeach; ?>
        <div class="hero-poster-overlay"></div>
    </div>
    <div class="hero-poster-nav">
        <?php foreach ($posters as $i => $po): ?>
        <span class="hero-poster-dot <?= $i === 0 ? 'active' : '' ?>" data-index="<?= $i ?>"></span>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <div class="container position-relative" style="z-index:5;">
        <div class="row align-items-center" style="min-height:90vh;">
            <div class="col-lg-12 hero-split-left">
                <div class="hero-split-content">
                    <div class="hero-typewriter" style="font-size: 3.8rem; font-weight: 900; line-height: 1.3; margin-bottom: 32px; letter-spacing: -1px;">
    <div class="typewriter-wrap">
        <h1><span class="typewriter-text" id="typewriterText" style="color:#FFD700;-webkit-text-fill-color:#FFD700;"></span></h1>
        <span class="typewriter-cursor" style="color:#FFD700;-webkit-text-fill-color:#FFD700;">|</span>
    </div>
</div>
                                        <div class="d-flex gap-4 flex-wrap justify-content-center">
                        <a href="/contact" class="btn hero-split-cta-primary"><?= __('hero_get_started') ?> <i class="bi bi-arrow-right ms-2"></i></a>
                        <a href="/all-projects" class="btn hero-split-cta-secondary"><?= __('hero_explore_systems') ?> <i class="bi bi-grid ms-2"></i></a>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Choose THE BEST Section -->
<section class="choose-best">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <h2><?= __('choose_title') ?></h2>
                <p><?= __('choose_subtitle') ?></p>
                <ul class="feature-list mt-4">
                    <li><i class="bi bi-check-circle-fill"></i> <?= __('choose_feature1') ?></li>
                    <li><i class="bi bi-check-circle-fill"></i> <?= __('choose_feature2') ?></li>
                    <li><i class="bi bi-check-circle-fill"></i> <?= __('choose_feature3') ?></li>
                    <li><i class="bi bi-check-circle-fill"></i> <?= __('choose_feature4') ?></li>
                    <li><i class="bi bi-check-circle-fill"></i> <?= __('choose_feature5') ?></li>
                </ul>
            </div>
            <div class="col-lg-6">
                <div class="row g-4">
                    <div class="col-6">
                        <div class="stat-box text-center">
                            <h3><span class="count-up" data-target="5">0</span>+</h3>
                            <p><?= __('choose_projects') ?></p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-box text-center">
                            <h3><span class="count-up" data-target="200">0</span>+</h3>
                            <p><?= __('choose_clients') ?></p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-box text-center">
                            <h3><span class="count-up" data-target="4">0</span>+</h3>
                            <p><?= __('choose_experience') ?></p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-box text-center">
                            <h3>24/7</h3>
                            <p><?= __('choose_support') ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Our Solutions -->
<?php if (!empty($solutions)): ?>
<section class="section-padding solutions-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2><?= __('solutions_heading') ?></h2>
            <p class="text-muted"><?= __('solutions_subtitle') ?></p>
        </div>
        <div class="row g-4">
            <?php foreach ($solutions as $sol): ?>
            <div class="col-md-6 col-lg-3">
                <div class="solution-card" data-aos="fade-up">
                    <div class="solution-icon">
                        <?php if ($sol['icon']): ?>
                        <i class="bi bi-<?= htmlspecialchars($sol['icon']) ?>"></i>
                        <?php else: ?>
                        <i class="bi bi-box"></i>
                        <?php endif; ?>
                    </div>
                    <h4><?= htmlspecialchars($sol['title']) ?></h4>
                    <p><?= htmlspecialchars($sol['description']) ?></p>
                    <?php if ($sol['link']): ?>
                    <a href="<?= htmlspecialchars($sol['link']) ?>" target="_blank" class="solution-link"><?= __('learn_more') ?> <i class="bi bi-arrow-right"></i></a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Live Systems / Recent Work - Horizontal Scroll -->
<?php if (!empty($live_systems)): ?>
<section class="section-padding live-systems-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2><?= __('live_systems_heading') ?></h2>
            <p class="text-muted"><?= __('live_systems_subtitle') ?></p>
        </div>
        <div class="live-scroll-wrap">
            <div class="live-scroll-track" id="liveScrollTrack">
                <?php for ($rep = 0; $rep < 2; $rep++): ?>
                <?php foreach ($live_systems as $ls): ?>
                <div class="live-scroll-item">
                    <div class="live-system-card">
                        <div class="live-system-img-wrap">
                            <img src="<?= htmlspecialchars(webp_url($ls['project_screenshot'])) ?>" alt="<?= htmlspecialchars($ls['project_title']) ?>" loading="lazy" onerror="this.src='https://via.placeholder.com/400x300/0F172A/00E5FF?text=System'">
                            <div class="live-system-badge"><?= __('live_badge') ?></div>
                        </div>
                        <div class="live-system-body">
                            <h5><?= htmlspecialchars($ls['project_title']) ?></h5>
                            <?php if (!empty($ls['tech_stack'])): ?>
                            <div class="live-system-tags">
                                <?php $tags = explode(',', $ls['tech_stack']); ?>
                                <?php foreach (array_slice($tags, 0, 3) as $tag): ?>
                                <span class="live-system-tag"><?= htmlspecialchars(trim($tag)) ?></span>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                            <p class="live-system-desc"><?= htmlspecialchars(substr($ls['project_desc'] ?? '', 0, 80)) ?></p>
                            <a href="<?= htmlspecialchars($ls['project_link']) ?>" target="_blank" class="btn btn-sm btn-outline-primary"><?= __('view_system') ?> <i class="bi bi-box-arrow-up-right ms-1"></i></a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endfor; ?>
            </div>
        </div>
        <div class="text-center mt-4">
            <a href="/all-projects" class="btn-red"><?= __('projects_view_all') ?> <i class="bi bi-arrow-right ms-1"></i></a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Our Proudly Partners -->
<?php if (!empty($partners)): ?>
<section class="section-padding partners-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2><?= __('partners_heading') ?></h2>
            <p class="text-muted"><?= __('partners_subtitle') ?></p>
        </div>
        <div class="swiper partners-swiper">
            <div class="swiper-wrapper">
                <?php foreach ($partners as $partner): ?>
                <div class="swiper-slide">
                    <div class="partner-card">
                        <?php if ($partner['website_url']): ?>
                        <a href="<?= htmlspecialchars($partner['website_url']) ?>" target="_blank" rel="noopener" class="partner-card-link">
                        <?php endif; ?>
                        <?php if ($partner['logo']): ?>
                        <img src="<?= htmlspecialchars(webp_url($partner['logo'])) ?>" alt="<?= htmlspecialchars($partner['name']) ?>" class="partner-logo" loading="lazy" onerror="this.src='https://via.placeholder.com/150x60/0F172A/00E5FF?text=<?= urlencode($partner['name']) ?>'">
                        <?php else: ?>
                        <span class="partner-name-only"><?= htmlspecialchars($partner['name']) ?></span>
                        <?php endif; ?>
                        <?php if ($partner['website_url']): ?>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="swiper-pagination"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Testimonials -->
<?php if (!empty($testimonials)): ?>
<section class="testimonial-section">
    <div class="container">
        <div class="testimonial-header">
            <h2 class="testimonial-heading">Our Latest Customer Reviews</h2>
            <a href="https://g.page/r/CV7D8gf6yuhmEAE/review" target="_blank" rel="nofollow noopener" class="btn btn-review-cta">Leave a Review <i class="bi bi-arrow-right ms-2"></i></a>
        </div>
        <?php $testimonialGroups = array_chunk($testimonials, 2); ?>
        <div class="testimonial-carousel-wrapper">
            <div id="testimonialCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000" data-bs-pause="hover">
                <div class="carousel-inner">
                    <?php foreach ($testimonialGroups as $gi => $group): ?>
                    <div class="carousel-item <?= $gi === 0 ? 'active' : '' ?>">
                        <div class="testimonial-slide-row">
                            <?php foreach ($group as $t): ?>
                            <div class="testimonial-card">
                                <?php if ($t['avatar']): ?>
                                <img src="<?= htmlspecialchars(webp_url($t['avatar'])) ?>" alt="<?= htmlspecialchars($t['name']) ?>" class="testimonial-avatar" onerror="this.style.display='none'">
                                <?php else: ?>
                                <div class="testimonial-avatar-placeholder"><?= strtoupper(mb_substr(htmlspecialchars($t['name']), 0, 1)) ?></div>
                                <?php endif; ?>
                                <h4 class="testimonial-name"><?= htmlspecialchars($t['name']) ?></h4>
                                <div class="testimonial-stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="bi bi-star<?= $i <= ($t['rating'] ?? 5) ? '-fill' : '' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <p class="testimonial-content">"<?= htmlspecialchars($t['content']) ?>"</p>
                                <a href="https://g.page/r/CV7D8gf6yuhmEAE/review" target="_blank" rel="nofollow noopener" class="testimonial-read-more">Read more <i class="bi bi-arrow-right"></i></a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php if (count($testimonials) > 1): ?>
                <button type="button" class="testimonial-nav-btn testimonial-nav-prev" data-bs-target="#testimonialCarousel" data-bs-slide="prev">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <button type="button" class="testimonial-nav-btn testimonial-nav-next" data-bs-target="#testimonialCarousel" data-bs-slide="next">
                    <i class="bi bi-chevron-right"></i>
                </button>
                <div class="testimonial-indicators">
                    <?php foreach ($testimonialGroups as $gi => $group): ?>
                    <button type="button" data-bs-target="#testimonialCarousel" data-bs-slide-to="<?= $gi ?>" class="<?= $gi === 0 ? 'active' : '' ?>" aria-current="<?= $gi === 0 ? 'true' : 'false' ?>"></button>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Contact CTA Section -->
<section class="section-padding cta-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h2><?= __('cta_work_together') ?></h2>
                <p class="text-muted mb-4"><?= __('cta_work_text') ?></p>
                <form action="/contact" method="POST" class="row g-3 justify-content-center">
                    <div class="col-md-5">
                        <input type="text" name="name" class="form-control" placeholder="<?= __('form_name') ?>" required>
                    </div>
                    <div class="col-md-5">
                        <input type="email" name="email" class="form-control" placeholder="<?= __('form_email') ?>" required>
                    </div>
                    <div class="col-12">
                        <textarea name="message" rows="3" class="form-control" placeholder="<?= __('form_message') ?>" required></textarea>
                    </div>
                    <div class="col-12">
                        <button type="submit" name="send_contact" class="btn-red w-100"><?= __('send_message') ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($posters) && count($posters) > 1): ?>
<script>
$(function() {
    var posters = $('.hero-poster-bg');
    var dots = $('.hero-poster-dot');
    var current = 0;
    var timer;

    function showSlide(idx) {
        posters.removeClass('active').eq(idx).addClass('active');
        dots.removeClass('active').eq(idx).addClass('active');
        current = idx;
    }

    function nextSlide() {
        var next = (current + 1) % posters.length;
        showSlide(next);
    }

    dots.on('click', function() {
        var idx = $(this).data('index');
        showSlide(idx);
        clearInterval(timer);
        timer = setInterval(nextSlide, 5000);
    });

    timer = setInterval(nextSlide, 5000);
});
</script>
<?php endif; ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var sentences = ['<?= __('hero_typewriter') ?>'];
    var el = document.getElementById('typewriterText');
    var charIdx = 0, senIdx = 0, isDeleting = false;
    var speed = 80, pause = 2000;

    function type() {
        var full = sentences[senIdx];
        if (isDeleting) {
            charIdx--;
            el.textContent = full.substring(0, charIdx);
            if (charIdx === 0) {
                isDeleting = false;
                senIdx = (senIdx + 1) % sentences.length;
                setTimeout(type, 300);
                return;
            }
            setTimeout(type, speed / 2);
        } else {
            charIdx++;
            el.textContent = full.substring(0, charIdx);
            if (charIdx === full.length) {
                isDeleting = true;
                setTimeout(type, pause);
                return;
            }
            setTimeout(type, speed);
        }
    }
    type();

    // Count-up animation for stat numbers
    var counted = false;
    function animateCounters() {
        if (counted) return;
        counted = true;
        document.querySelectorAll('.count-up').forEach(function(el) {
            var target = parseInt(el.dataset.target);
            var current = 0;
            var step = Math.max(1, Math.ceil(target / 60));
            var interval = setInterval(function() {
                current += step;
                if (current >= target) {
                    current = target;
                    clearInterval(interval);
                }
                el.textContent = current;
            }, 25);
        });
    }

    var observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(e) {
            if (e.isIntersecting) {
                animateCounters();
                observer.disconnect();
            }
        });
    });
    var statBox = document.querySelector('.stat-box');
    if (statBox) observer.observe(statBox.closest('.choose-best') || statBox);
});
</script>
<?php require_once 'footer.php'; ?>
