<?php
/**
 * Generic service page renderer.
 *
 * Include this from a thin wrapper with $service_slug set, e.g.
 *
 *   <?php
 *   $service_slug = 'web-development';
 *   require __DIR__ . '/service.php';
 *
 * The page content is loaded from the database (admin managed). If the service
 * or its sections are missing, the canonical content from lib/ServiceDefaults.php
 * is used, so the page always renders.
 */

if (!isset($service_slug) || $service_slug === '') {
    $service_slug = isset($_GET['slug']) ? preg_replace('/[^a-z0-9-]/', '', strtolower((string)$_GET['slug'])) : '';
}

if ($service_slug === '') {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/lib/Settings.php';
require_once __DIR__ . '/lib/Service.php';
require_once __DIR__ . '/lib/ServiceDefaults.php';

$defaults = ServiceDefaults::get($service_slug);
$serviceObj = new Service();
$dbService = $serviceObj->getBySlug($service_slug);

if (!$dbService && !$defaults) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

// Resolve the service record, falling back to the canonical defaults.
$service = [
    'id'               => $dbService['id'] ?? null,
    'title'            => $dbService['title'] ?? ($defaults['title'] ?? ''),
    'description'      => $dbService['description'] ?? ($defaults['description'] ?? ''),
    'short_description'=> $dbService['short_description'] ?? ($defaults['short_description'] ?? ''),
    'image'            => $dbService['image'] ?? ($defaults['image'] ?? ''),
    'image_alt'        => $dbService['image_alt'] ?? ($defaults['image_alt'] ?? ''),
    'hero_image'       => $dbService['hero_image'] ?? ($defaults['hero_image'] ?? ''),
    'hero_subtitle'    => $dbService['hero_subtitle'] ?? ($defaults['hero_subtitle'] ?? ''),
    'seo_title'        => $dbService['seo_title'] ?? ($defaults['page_title'] ?? ''),
    'seo_description'  => $dbService['seo_description'] ?? ($defaults['page_desc'] ?? ''),
    'seo_keywords'     => $dbService['seo_keywords'] ?? ($defaults['page_keywords'] ?? ''),
    'service_category' => $dbService['service_category'] ?? ($defaults['service_category'] ?? ''),
];

// Sections: prefer DB, fall back to defaults when none are configured yet.
$sections = [];
if ($dbService) {
    $sections = $serviceObj->getSections($dbService['id'], true);
    if (empty($sections)) {
        $sections = []; // explicitly configured as empty? fall through to defaults below
    }
}
if (empty($sections) && !empty($defaults['sections'])) {
    $idx = 0;
    foreach ($defaults['sections'] as $s) {
        $s['id'] = 'd' . (++$idx);
        $s['section_type'] = $s['type'];
        $s['column_side'] = $s['side'];
        $s['is_active'] = 1;
        $sections[] = $s;
    }
}

// Loads items for DB sections in a single query.
$dbItems = [];
if ($dbService) {
    $dbItems = $serviceObj->getItemsByService($dbService['id'], true);
}

function svc_e($v) { return htmlspecialchars((string)$v, ENT_QUOTES); }
function svc_img($path) {
    if (!$path) return '';
    return webp_url('/' . ltrim($path, '/'));
}

/** Normalise an item to ['title','text','icon','link'] for both DB rows and defaults. */
function svc_item($it) {
    return [
        'title' => $it['item_title'] ?? ($it['title'] ?? ''),
        'text'  => $it['item_text'] ?? ($it['text'] ?? ''),
        'icon'  => $it['item_icon'] ?? ($it['icon'] ?? ''),
        'link'  => $it['item_link'] ?? ($it['link'] ?? ''),
    ];
}

function svc_items_for($sec, array $dbItems) {
    if (isset($sec['items']) && is_array($sec['items'])) {
        return array_map('svc_item', $sec['items']);
    }
    $raw = $dbItems[$sec['id']] ?? [];
    return array_map('svc_item', $raw);
}

/** Render one section, returning an HTML string. */
function svc_render_section($sec, array $items, array $service, array $ctx) {
    $type = $sec['section_type'] ?? 'content';
    $anchor = !empty($sec['anchor']) ? ' id="' . svc_e($sec['anchor']) . '"' : '';
    $heading = trim((string)($sec['heading'] ?? ''));
    $subheading = trim((string)($sec['subheading'] ?? ''));
    $body = trim((string)($sec['body'] ?? ''));
    $sidebar = '';
    $out = '';

    switch ($type) {
        case 'benefits':
            $rows = '';
            foreach ($items as $it) {
                if ($it['title'] === '') continue;
                $rows .= '<li><a class="wd-benefit-row" href="' . svc_e($it['link'] ?: '#') . '">'
                    . '<span class="wd-benefit-text">' . svc_e($it['title']) . '</span>'
                    . '<span class="wd-arrow" aria-hidden="true"><i class="bi bi-arrow-right"></i></span></a></li>';
            }
            $out = '<div class="wd-block"' . $anchor . '>';
            if ($heading) $out .= '<h2 class="wd-heading">' . svc_e($heading) . '</h2>';
            if ($subheading) $out .= '<p class="wd-subtext">' . svc_e($subheading) . '</p>';
            $out .= '<ul class="wd-benefit-list">' . $rows . '</ul>';
            if (!empty($sec['note'])) $out .= '<p class="wd-subtext wd-note">' . svc_e($sec['note']) . '</p>';
            $out .= '</div>';
            break;

        case 'faq':
            $accId = 'faqAccordion_' . preg_replace('/[^a-zA-Z0-9_]/', '', (string)($sec['id'] ?? 'x'));
            $itemsHtml = '';
            $i = 0;
            foreach ($items as $it) {
                if ($it['title'] === '') continue;
                $i++;
                $show = ($i === 1);
                $qid = $accId . '_q' . $i;
                $itemsHtml .= '<div class="accordion-item">'
                    . '<h2 class="accordion-header">'
                    . '<button class="accordion-button' . ($show ? '' : ' collapsed') . '" type="button" data-bs-toggle="collapse" data-bs-target="#' . $qid . '" aria-expanded="' . ($show ? 'true' : 'false') . '" aria-controls="' . $qid . '">'
                    . svc_e($it['title']) . '</button></h2>'
                    . '<div id="' . $qid . '" class="accordion-collapse collapse' . ($show ? ' show' : '') . '" data-bs-parent="#' . $accId . '">'
                    . '<div class="accordion-body">' . nl2br(svc_e($it['text'])) . '</div></div></div>';
            }
            $out = '<div class="wd-block"' . $anchor . '>';
            if ($heading) $out .= '<h2 class="wd-heading">' . svc_e($heading) . '</h2>';
            if ($subheading) $out .= '<p class="wd-subtext">' . svc_e($subheading) . '</p>';
            $out .= '<div class="accordion wd-faq" id="' . $accId . '">' . $itemsHtml . '</div></div>';
            break;

        case 'cta':
            $out = '<div class="wd-contact-card">';
            if ($heading) $out .= '<h3>' . svc_e($heading) . '</h3>';
            if ($body) $out .= '<p>' . svc_e($body) . '</p>';
            $out .= '<div class="wd-contact-actions">';
            $out .= '<a href="' . svc_e($sec['button_url'] ?: '/contact') . '" class="btn btn-red"><i class="bi bi-envelope me-2"></i>' . svc_e($sec['button_text'] ?: 'Contact Us') . '</a>';
            $out .= '<a href="/contact" class="wd-btn-quote"><i class="bi bi-clipboard-check me-2"></i>Request a Quote</a>';
            $out .= '</div>';
            $out .= '<ul class="wd-contact-meta">'
                . '<li><i class="bi bi-telephone"></i> <a href="tel:' . svc_e($ctx['phone_raw']) . '">' . svc_e($ctx['phone']) . '</a></li>'
                . '<li><i class="bi bi-envelope"></i> <a href="mailto:' . svc_e($ctx['email']) . '">' . svc_e($ctx['email']) . '</a></li>'
                . '<li><i class="bi bi-geo-alt"></i> ' . svc_e($ctx['location']) . '</li>'
                . '</ul></div>';
            break;

        case 'content':
            $img = svc_img($sec['image'] ?? '');
            if ($img) {
                $out .= '<div class="wd-overview-img"><img src="' . svc_e($img) . '" alt="' . svc_e($sec['image_alt'] ?? $service['title']) . '" loading="lazy"></div>';
            }
            $out .= '<div class="wd-block"' . $anchor . '>';
            if ($heading) $out .= '<h2 class="wd-heading">' . svc_e($heading) . '</h2>';
            if ($subheading) $out .= '<p class="wd-subtext">' . svc_e($subheading) . '</p>';
            if ($body) $out .= '<p class="wd-subtext">' . nl2br(svc_e($body)) . '</p>';
            $out .= '</div>';
            break;

        case 'service_grid':
            $cells = '';
            foreach ($items as $it) {
                if ($it['title'] === '') continue;
                $cells .= '<div class="col-sm-6"><a class="wd-service-link" href="' . svc_e($it['link'] ?: '#') . '">'
                    . '<span>' . svc_e($it['title']) . '</span>'
                    . '<span class="wd-arrow" aria-hidden="true"><i class="bi bi-arrow-right"></i></span></a></div>';
            }
            $out = '<div class="wd-block"' . $anchor . '>';
            if ($heading) $out .= '<h3 class="wd-subheading">' . svc_e($heading) . '</h3>';
            if ($subheading) $out .= '<p class="wd-subtext">' . svc_e($subheading) . '</p>';
            $out .= '<div class="row g-3 wd-service-grid">' . $cells . '</div></div>';
            break;

        case 'type_cards':
            $cards = '';
            foreach ($items as $it) {
                if ($it['title'] === '' && $it['text'] === '') continue;
                $cards .= '<div class="col-sm-6"><div class="wd-type-card">';
                if ($it['title']) $cards .= '<h4>' . svc_e($it['title']) . '</h4>';
                if ($it['text']) $cards .= '<p>' . svc_e($it['text']) . '</p>';
                $cards .= '</div></div>';
            }
            $out = '<div class="wd-block"' . $anchor . '>';
            if ($heading) $out .= '<h3 class="wd-subheading">' . svc_e($heading) . '</h3>';
            if ($subheading) $out .= '<p class="wd-subtext">' . svc_e($subheading) . '</p>';
            $out .= '<div class="row g-3 wd-types-grid">' . $cards . '</div></div>';
            break;

        case 'steps':
            $rows = '';
            foreach ($items as $it) {
                if ($it['title'] === '' && $it['text'] === '') continue;
                $rows .= '<li><span class="wd-step-title">' . svc_e($it['title']) . '</span>';
                if ($it['text']) $rows .= '<p class="wd-subtext">' . svc_e($it['text']) . '</p>';
                $rows .= '</li>';
            }
            $out = '<div class="wd-block"' . $anchor . '>';
            if ($heading) $out .= '<h2 class="wd-heading">' . svc_e($heading) . '</h2>';
            if ($subheading) $out .= '<p class="wd-subtext">' . svc_e($subheading) . '</p>';
            $out .= '<ol class="wd-steps">' . $rows . '</ol></div>';
            break;

        case 'tech':
            $pills = '';
            foreach ($items as $it) {
                if ($it['title'] === '') continue;
                $pills .= '<span class="wd-tech-pill">' . svc_e($it['title']) . '</span>';
            }
            $out = '<div class="wd-block"' . $anchor . '>';
            if ($heading) $out .= '<h3 class="wd-subheading">' . svc_e($heading) . '</h3>';
            if ($subheading) $out .= '<p class="wd-subtext">' . svc_e($subheading) . '</p>';
            $out .= '<div class="wd-tech-pills">' . $pills . '</div></div>';
            break;

        default:
            $out = '';
    }

    return $out;
}

/** Render the full-width projects carousel for the service category. */
function svc_render_projects($sec, array $service, $pdo) {
    $category = trim((string)$service['service_category']);
    if ($category === '') return '';
    $stmt = $pdo->prepare("SELECT id, project_title, project_desc, project_link, project_screenshot FROM portfolio WHERE category = ? ORDER BY created_at DESC LIMIT 12");
    $stmt->execute([$category]);
    $projects = $stmt->fetchAll();

    $heading = trim((string)($sec['heading'] ?? ''));
    $sub = trim((string)($sec['subheading'] ?? ''));
    $html = '<section class="section-padding bg-light-section"><div class="container">';
    $html .= '<div class="text-center mb-5">';
    if ($heading) $html .= '<h2>' . svc_e($heading) . '</h2>';
    if ($sub) $html .= '<p class="text-muted">' . svc_e($sub) . '</p>';
    $html .= '</div>';

    if (empty($projects)) {
        $html .= '<div class="col-12 text-center text-muted py-5"><p>No projects yet. Check back soon!</p></div>';
    } else {
        $total = (int)ceil(count($projects) / 3);
        $html .= '<div id="svcProjectsCarousel" class="carousel slide projects-carousel" data-bs-ride="carousel" data-bs-interval="4000">';
        $html .= '<div class="carousel-indicators">';
        for ($i = 0; $i < $total; $i++) {
            $html .= '<button type="button" data-bs-target="#svcProjectsCarousel" data-bs-slide-to="' . $i . '" class="' . ($i === 0 ? 'active' : '') . '"></button>';
        }
        $html .= '</div><div class="carousel-inner">';
        $chunks = array_chunk($projects, 3);
        $slide = 0;
        foreach ($chunks as $chunk) {
            $html .= '<div class="carousel-item ' . ($slide === 0 ? 'active' : '') . '"><div class="row g-4">';
            foreach ($chunk as $p) {
                $img = svc_e(webp_url($p['project_screenshot']));
                $html .= '<div class="col-md-6 col-lg-4"><div class="portfolio-card">'
                    . '<div class="portfolio-img-wrap"><img src="' . $img . '" alt="' . svc_e($p['project_title']) . '" class="portfolio-img" loading="lazy"></div>'
                    . '<div class="portfolio-body"><h5>' . svc_e($p['project_title']) . '</h5>'
                    . '<p class="portfolio-desc-preview">' . svc_e(substr((string)$p['project_desc'], 0, 120)) . '</p>'
                    . '<a href="' . svc_e($p['project_link']) . '" target="_blank" class="portfolio-overlay-btn mt-3"><i class="bi bi-box-arrow-up-right"></i> Go Live</a>'
                    . '</div></div></div>';
            }
            $html .= '</div></div>';
            $slide++;
        }
        $html .= '</div>'
            . '<button class="carousel-control-prev" type="button" data-bs-target="#svcProjectsCarousel" data-bs-slide="prev"><span class="carousel-control-prev-icon" aria-hidden="true"></span><span class="visually-hidden">Previous</span></button>'
            . '<button class="carousel-control-next" type="button" data-bs-target="#svcProjectsCarousel" data-bs-slide="next"><span class="carousel-control-next-icon" aria-hidden="true"></span><span class="visually-hidden">Next</span></button>';
        $html .= '</div>';
    }
    $html .= '</div></section>';
    return $html;
}

/** Render the city / related links buttons block. */
function svc_render_city_links($sec, array $items) {
    $heading = trim((string)($sec['heading'] ?? ''));
    $sub = trim((string)($sec['subheading'] ?? ''));
    $buttons = '';
    foreach ($items as $it) {
        if ($it['title'] === '') continue;
        $buttons .= '<a href="' . svc_e($it['link'] ?: '#') . '" class="btn btn-outline-red">' . svc_e($it['title']) . '</a>';
    }
    if ($buttons === '') return '';
    $html = '<section class="section-padding bg-light-section"><div class="container"><div class="text-center">';
    if ($heading) $html .= '<h2>' . svc_e($heading) . '</h2>';
    if ($sub) $html .= '<p class="text-muted">' . svc_e($sub) . '</p>';
    $html .= '<div class="d-flex flex-wrap justify-content-center gap-2 mt-3">' . $buttons . '</div>';
    $html .= '</div></div></section>';
    return $html;
}

// ---------------------------------------------------------------------------
// SEO + page setup
// ---------------------------------------------------------------------------
$page_title = $service['seo_title'] ?: ($service['title'] . ' | Mtaita Tech');
$page_desc = $service['seo_description'] ?: ($service['description'] ?: $service['short_description']);
$page_keywords = $service['seo_keywords'] ?: '';
$body_class = 'wd-page';
if (!empty($service['image'])) {
    $og_image = SITE_URL . '/' . ltrim(webp_url('/' . ltrim($service['image'], '/')), '/');
}

// FAQ structured data from every active FAQ section.
$faq_entities = [];
foreach ($sections as $sec) {
    if (($sec['section_type'] ?? '') !== 'faq') continue;
    foreach (svc_items_for($sec, $dbItems) as $it) {
        if ($it['title'] === '' || $it['text'] === '') continue;
        $faq_entities[] = [
            '@type' => 'Question',
            'name' => $it['title'],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $it['text']],
        ];
    }
}
$faq_schema = !empty($faq_entities)
    ? ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $faq_entities]
    : null;

$hero_path = $service['hero_image'] ?: Settings::get('hero_bg_services', '');
if (empty($hero_path) && !empty($service['image'])) {
    $hero_path = $service['image'];
}
$hero_url = $hero_path ? svc_e(webp_url('/' . ltrim($hero_path, '/'))) : '';

$ctx = [
    'email' => Settings::get('admin_email', ADMIN_EMAIL),
    'phone' => Settings::get('admin_phone', '+255 616 591 639'),
    'location' => Settings::get('admin_location', 'Moshi, Kilimanjaro'),
];
$ctx['phone_raw'] = preg_replace('/\s+/', '', $ctx['phone']);

require_once __DIR__ . '/header.php';
?>

<?php if ($faq_schema): ?>
<script type="application/ld+json"><?= json_encode($faq_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
<?php endif; ?>

<section class="page-header<?= $hero_url ? ' page-header-with-bg' : '' ?>"<?php if ($hero_url): ?> style="background-image:url('/<?= $hero_url ?>')"<?php endif; ?>>
    <div class="container">
        <h1><?= svc_e($service['title']) ?></h1>
        <p><?= svc_e($service['hero_subtitle'] ?: $service['short_description']) ?></p>
    </div>
</section>

<?php
$leftSections = [];
$rightSections = [];
$fullSections = [];
foreach ($sections as $sec) {
    $side = $sec['column_side'] ?? 'left';
    if ($side === 'right') $rightSections[] = $sec;
    elseif ($side === 'full') $fullSections[] = $sec;
    else $leftSections[] = $sec;
}

if (!empty($leftSections) || !empty($rightSections)):
?>
<section class="wd-main section-padding">
    <div class="container">
        <div class="row g-4 g-lg-5">
            <div class="col-lg-6 wd-col-left">
                <?php foreach ($leftSections as $sec): ?>
                    <?= svc_render_section($sec, svc_items_for($sec, $dbItems), $service, $ctx) ?>
                <?php endforeach; ?>
            </div>
            <div class="col-lg-6 wd-col-right">
                <?php foreach ($rightSections as $sec): ?>
                    <?= svc_render_section($sec, svc_items_for($sec, $dbItems), $service, $ctx) ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<?php foreach ($fullSections as $sec): ?>
    <?php
        $type = $sec['section_type'] ?? '';
        if ($type === 'projects') {
            echo svc_render_projects($sec, $service, $pdo);
        } elseif ($type === 'city_links') {
            echo svc_render_city_links($sec, svc_items_for($sec, $dbItems));
        } else {
            echo svc_render_section($sec, svc_items_for($sec, $dbItems), $service, $ctx);
        }
    ?>
<?php endforeach; ?>

<?php require_once __DIR__ . '/footer.php'; ?>
