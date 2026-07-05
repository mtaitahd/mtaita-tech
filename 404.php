<?php
http_response_code(404);
$page_title = 'Page Not Found | Mtaita Tech';
$page_desc = 'The page you are looking for does not exist. Return to Mtaita Tech to explore our software development services in Tanzania.';
$page_keywords = '404 page not found, Mtaita Tech, software company Tanzania';
require_once __DIR__ . '/header.php';
?>
<section class="page-header">
    <div class="container text-center">
        <h1 class="display-1 fw-bold text-red" style="font-size:clamp(3rem, 15vw, 6rem);">Page Not Found</h1>
        <p class="mb-3" style="font-size:clamp(1rem, 4vw, 1.5rem);color:#94A3B8;">404</p>
        <p class="text-muted mb-4" style="font-size:clamp(0.85rem, 3vw, 1.1rem);">Sorry, the page you are looking for does not exist or has been moved.</p>
        <a href="/" class="btn btn-red btn-lg"><i class="bi bi-house-door me-2"></i>Return Home</a>
    </div>
</section>
<?php require_once __DIR__ . '/footer.php'; ?>
