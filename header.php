<?php
require_once __DIR__ . '/config.php';
require_once 'db_connect.php';
require_once __DIR__ . '/auth_helper.php';

$current_file = basename($_SERVER['SCRIPT_NAME']);
$page = str_replace('.php', '', $current_file);
if ($page === 'index') $page = '';

// Auto-noindex private/auth pages
$noindex_pages = ['register','forgot-password','reset-password','otp-verify','resend-otp','checkout','payment-pending','check-payment-status','google-callback','download-product','logout','success','cancel','dashboard','lesson','lesson_progress_ajax'];
if (!isset($meta_robots) && in_array($page, $noindex_pages)) {
    $meta_robots = 'noindex, nofollow';
}

$nav_courses = $pdo->query("SELECT id, title, slug FROM courses WHERE status = 'published' ORDER BY title ASC LIMIT 10")->fetchAll();
?>
<!DOCTYPE html>
<html lang="<?= $lang_code ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta name="theme-color" content="#0F172A">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="description" content="<?= htmlspecialchars($page_desc ?? 'Mtaita Tech — IT & Graphic Design Agency') ?>">
    <meta name="keywords" content="<?= htmlspecialchars($page_keywords ?? 'IT services, graphic design, web development') ?>">
    <meta name="author" content="Mtaita Tech">
    <meta name="robots" content="<?= htmlspecialchars($meta_robots ?? 'index, follow') ?>">
    <meta name="googlebot" content="<?= htmlspecialchars($meta_robots ?? 'index, follow') ?>">
    <meta property="og:title" content="<?= htmlspecialchars($page_title ?? SITE_NAME) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($page_desc ?? '') ?>">
    <meta property="og:image" content="<?= htmlspecialchars($og_image ?? SITE_URL . '/assets/img/jj.png') ?>">
    <meta property="og:url" content="<?= SITE_URL . $_SERVER['REQUEST_URI'] ?>">
    <meta property="og:type" content="<?= htmlspecialchars($og_type ?? 'website') ?>">
    <?php if (!empty($article_published)): ?>
    <meta property="article:published_time" content="<?= $article_published ?>">
    <meta property="article:author" content="Mtaita Tech">
    <?php endif; ?>
    <meta name="twitter:card" content="summary_large_image">
    <link rel="canonical" href="<?= SITE_URL . $_SERVER['REQUEST_URI'] ?>">
    <link rel="icon" type="image/png" href="<?= SITE_URL ?>/assets/img/jj.png">
    <title><?= htmlspecialchars($page_title ?? SITE_NAME . ' — ' . SITE_TAGLINE) ?></title>
    <?php if (!empty($article_published)): ?>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Article",
        "headline": <?= json_encode($page_title) ?>,
        "description": <?= json_encode($page_desc) ?>,
        "image": <?= json_encode($og_image) ?>,
        "datePublished": <?= json_encode($article_published) ?>,
        "author": {
            "@type": "Organization",
            "name": "Mtaita Tech",
            "url": "<?= SITE_URL ?>"
        },
        "publisher": {
            "@type": "Organization",
            "name": "Mtaita Tech"
        }
    }
    </script>
    <?php endif; ?>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "Mtaita Tech",
        "url": "<?= SITE_URL ?>",
        "logo": "<?= SITE_URL ?>/assets/img/jj.png",
        "description": "Software development company in Kilimanjaro and Arusha, Tanzania. We build websites, mobile apps, POS systems, inventory software, and custom business solutions.",
        "foundingDate": "2023",
        "email": "<?= ADMIN_EMAIL ?>",
        "address": {
            "@type": "PostalAddress",
            "addressLocality": "Kilimanjaro",
            "addressRegion": "Kilimanjaro",
            "addressCountry": "TZ"
        },
        "contactPoint": {
            "@type": "ContactPoint",
            "contactType": "sales",
            "availableLanguage": ["English", "Swahili"]
        }
    }
    </script>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "LocalBusiness",
        "name": "Mtaita Tech",
        "image": "<?= SITE_URL ?>/assets/img/jj.png",
        "url": "<?= SITE_URL ?>",
        "description": "Mtaita Tech is a leading software development company in Kilimanjaro and Arusha, Tanzania, specializing in web development, mobile apps, POS systems, inventory management, and custom business software.",
        "address": {
            "@type": "PostalAddress",
            "addressLocality": "Kilimanjaro",
            "addressRegion": "Kilimanjaro",
            "addressCountry": "TZ"
        },
        "areaServed": [
            {"@type": "City", "name": "Kilimanjaro"},
            {"@type": "City", "name": "Arusha"},
            {"@type": "City", "name": "Moshi"},
            {"@type": "Country", "name": "Tanzania"}
        ]
    }
    </script>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "Mtaita Tech",
        "url": "<?= SITE_URL ?>"
    }
    </script>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            {"@type": "ListItem", "position": 1, "name": "Home", "item": "<?= SITE_URL ?>/"},
            {"@type": "ListItem", "position": 2, "name": "<?= htmlspecialchars($page_heading ?: ($page_title ?: 'Page')) ?>", "item": "<?= SITE_URL . $_SERVER['REQUEST_URI'] ?>"}
        ]
    }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@2.3.4/dist/aos.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <style>
        .page-loader.hidden { display: none; }
    </style>
</head>
<body class="<?= htmlspecialchars($body_class ?? '') ?>">

<!-- Page Loader -->
<div id="pageLoader" class="page-loader">
    <div class="loader-spinner">
        <img src="/assets/img/jj.png" alt="Loading">
    </div>
</div>
<script>
window.addEventListener('load', function() {
    var loader = document.getElementById('pageLoader');
    if (loader) {
        setTimeout(function() {
            loader.classList.add('hidden');
        }, 300);
    }
});
document.addEventListener('submit', function() {
    var loader = document.getElementById('pageLoader');
    if (loader) loader.classList.remove('hidden');
});
</script>

<?php if (empty($hide_navbar)): ?>
<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
        <a class="navbar-brand" href="/">
            <img src="/assets/img/jj.png" alt="Mtaita Tech" height="64">
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav ms-auto">
                <?php if ($page !== ''): ?><li class="nav-item"><a class="nav-link <?= $page === '' ? 'active' : '' ?>" href="/"><i class="bi bi-house me-1"></i><?= __('home') ?></a></li><?php endif; ?>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= $page === 'services' ? 'active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-grid me-1"></i><?= __('services') ?></a>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/web-development"><?= __('web_development') ?></a></li>
                        <li><a class="dropdown-item" href="/graphic-design"><?= __('graphic_design') ?></a></li>
                        <li><a class="dropdown-item" href="/mobile-apps"><?= __('mobile_apps') ?></a></li>
                        <li><a class="dropdown-item" href="/seo-digital-marketing"><?= __('seo_digital_marketing') ?></a></li>
                    </ul>
                </li>

                <li class="nav-item"><a class="nav-link" href="https://mtaitatech.online/web-development" target="_blank"><i class="bi bi-briefcase me-1"></i><?= __('portfolio') ?></a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= $page === 'courses' ? 'active' : '' ?>" href="/courses" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-book me-1"></i><?= __('courses_nav') ?>
                    </a>
                    <ul class="dropdown-menu">
                        <?php foreach ($nav_courses as $nc): ?>
                        <li><a class="dropdown-item" href="/course/<?= htmlspecialchars($nc['slug']) ?>"><?= htmlspecialchars($nc['title']) ?></a></li>
                        <?php endforeach; ?>
                        <?php if (count($nav_courses) >= 10): ?>
                        <li><hr class="dropdown-divider"></li>
                        <?php endif; ?>
                        <li><a class="dropdown-item fw-bold text-primary" href="/courses"><i class="bi bi-grid me-1"></i><?= __('all_courses') ?></a></li>
                    </ul>
                </li>
                <li class="nav-item"><a class="nav-link <?= $page === 'digital_products' ? 'active' : '' ?>" href="/digital_products"><i class="bi bi-box-seam me-1"></i><?= __('digital_products_nav') ?></a></li>
                <li class="nav-item"><a class="nav-link <?= $page === 'blog' ? 'active' : '' ?>" href="/blog"><i class="bi bi-pencil-square me-1"></i>Blog</a></li>
                <li class="nav-item"><a class="nav-link <?= $page === 'contact' ? 'active' : '' ?>" href="/contact"><i class="bi bi-envelope me-1"></i><?= __('contact_us') ?></a></li>

                <?php if (isPublicLoggedIn()): ?>
                <li class="nav-item dropdown ms-lg-2">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle"></i> <span class="d-lg-none"><?= __('account') ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="dashboard"><i class="bi bi-speedometer2 me-2"></i><?= __('dashboard') ?></a></li>
                        <li><a class="dropdown-item" href="courses"><i class="bi bi-book me-2"></i><?= __('courses_nav') ?></a></li>
                        <li><a class="dropdown-item" href="digital_products"><i class="bi bi-box me-2"></i><?= __('digital_products_nav') ?></a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout" style="color: var(--red);"><i class="bi bi-box-arrow-right me-2"></i><?= __('logout') ?></a></li>
                    </ul>
                </li>
                <?php else: ?>
                <li class="nav-item ms-lg-2">
                    <a class="nav-link" href="login"><i class="bi bi-person"></i> <span class="d-lg-none"><?= __('login') ?></span></a>
                </li>
                <?php endif; ?>

                <li class="nav-item ms-lg-2 lang-switcher">
                    <a class="nav-link lang-toggle" href="/lang_switch?lang=<?= $lang_code === 'en' ? 'sw' : 'en' ?>" title="<?= __('language') ?>">
                        <span class="lang-flag-current"><?= $lang_code === 'en' ? '🇺🇸' : '🇹🇿' ?></span>
                        <span class="lang-label"><?= $lang_code === 'en' ? 'EN' : 'SW' ?></span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<?php endif; ?>

<main>
