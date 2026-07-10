<?php
$page_title = 'Portfolio | Mtaita Tech Projects & Work Tanzania';
$page_desc = 'View Mtaita Tech\'s portfolio. Showcasing website development, mobile app, and business system projects delivered for clients across Tanzania.';
$page_keywords = 'Mtaita Tech portfolio, web development projects Tanzania, website portfolio Kilimanjaro, software projects Arusha, business systems Tanzania';
require_once 'header.php';
require_once 'lib/Settings.php';

$hero_bg = Settings::get('hero_bg_portfolio', '');
?>

<section class="page-header<?= $hero_bg ? ' page-header-with-bg' : '' ?>"<?php if ($hero_bg): ?> style="background-image:url('/<?= htmlspecialchars(webp_url($hero_bg)) ?>')"<?php endif; ?>>
    <div class="container">
        <h1>About Me</h1>
        <p>Full-Stack Developer &amp; IT Professional</p>
    </div>
</section>

<section class="section-padding bg-light-section">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-4 text-center">
                <img src="<?= webp_url('/assets/img/212869619.jpg') ?>" alt="Jahnson Paulo Mtaita" class="about-img">
                <div class="mt-3">
                    <a href="#" class="btn btn-red btn-sm" onclick="alert('CV will be available soon'); return false;">
                        <i class="bi bi-file-earmark-text me-1"></i> Download CV
                    </a>
                </div>
            </div>
            <div class="col-lg-8">
                <h2>About Me</h2>
                <h4 class="text-muted mb-3" style="font-weight: 600;">Jahnson Paulo Mtaita</h4>
                <p>I am a passionate and dedicated <strong>Full-Stack Developer</strong> with a strong foundation in web development, graphic design, and IT solutions. I hold a <strong>Diploma in Business and Information Communication Technology (DBICT)</strong> from Moshi Cooperative University (MoCU) and I am currently pursuing a <strong>Bachelor of Business and Information Communication Technology (BBICT)</strong> at the same institution.</p>
                <p>My expertise spans across modern web technologies including PHP, JavaScript, Bootstrap, MySQL, and various front-end frameworks. I specialize in building responsive, user-friendly websites and applications that solve real-world problems. Beyond coding, I have a keen eye for graphic design, creating compelling brand identities and digital assets.</p>
                <p>I am committed to continuous learning and staying up-to-date with the latest industry trends. Whether it's developing a complex web application, designing a logo, or crafting a digital marketing strategy, I bring creativity, precision, and a problem-solving mindset to every project.</p>
                <div class="row g-3 mt-4">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-3 p-3" style="background: var(--white); border: 1px solid var(--border-light);">
                            <i class="bi bi-mortarboard-fill" style="font-size: 2rem; color: var(--red);"></i>
                            <div>
                                <strong>DBICT</strong><br>
                                <small class="text-muted">Moshi Cooperative University</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-3 p-3" style="background: var(--white); border: 1px solid var(--border-light);">
                            <i class="bi bi-mortarboard-fill" style="font-size: 2rem; color: var(--red);"></i>
                            <div>
                                <strong>BBICT (Ongoing)</strong><br>
                                <small class="text-muted">Moshi Cooperative University</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="text-center mb-5">
            <h2>My Skills</h2>
            <p class="text-muted">Technologies I work with daily</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="skill-card">
                    <div class="d-flex justify-content-between">
                        <span>Web Development</span><span>95%</span>
                    </div>
                    <div class="progress mt-2 mb-3">
                        <div class="progress-bar" style="width:95%"></div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Graphic Design</span><span>90%</span>
                    </div>
                    <div class="progress mt-2 mb-3">
                        <div class="progress-bar" style="width:90%"></div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Mobile Apps</span><span>85%</span>
                    </div>
                    <div class="progress mt-2 mb-3">
                        <div class="progress-bar" style="width:85%"></div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>SEO & Marketing</span><span>80%</span>
                    </div>
                    <div class="progress mt-2">
                        <div class="progress-bar" style="width:80%"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="skill-card h-100 d-flex flex-column justify-content-center">
                    <h4>My Mission</h4>
                    <p>To provide businesses with innovative, high-quality IT and design solutions that enhance their digital footprint and drive sustainable growth.</p>
                    <h4>My Vision</h4>
                    <p>To be East Africa's most trusted partner for digital transformation, recognized for creativity, reliability, and technical excellence.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
require_once 'footer.php';
?>
