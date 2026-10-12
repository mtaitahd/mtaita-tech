<?php
$page_title = 'Pricing in Tanzania | Website, Software & IT Service Prices | Mtaita Tech';
$page_desc = 'Clear starting prices for web development, software solutions, tourism digital solutions, graphic design, social media, ICT consultancy, infrastructure, and computer maintenance from Mtaita Tech, Tanzania.';
$page_keywords = 'pricing Tanzania, website price Tanzania, software development cost Tanzania, web design price Moshi, IT services price Kilimanjaro, Mtaita Tech pricing';
$page_heading = 'Pricing';
$body_class = 'pricing-page';
require_once 'config.php';
$canonical = SITE_URL . '/pricing';
$og_url = SITE_URL . '/pricing';
require_once 'header.php';
require_once 'db_connect.php';
require_once 'lib/Settings.php';
require_once 'lib/Pricing.php';

$hero_bg   = Settings::get('hero_bg_services', '');
$pr_email  = Settings::get('admin_email', ADMIN_EMAIL);
$pr_phone  = Settings::get('admin_phone', '+255 616 591 639');
$pr_location = Settings::get('admin_location', 'Moshi, Kilimanjaro');
$usd_rate  = Pricing::usdRate();
$valid_year = Settings::get('pricing_valid_year', '');

$services = Pricing::services();
$packages = Pricing::packagesByService($pdo);

// Any explicitly stored USD price means the USD switch is usable even without a rate.
$has_explicit_usd = false;
foreach ($packages as $rows) {
    foreach ($rows as $row) {
        if ($row['price_usd'] !== null && $row['price_usd'] !== '') {
            $has_explicit_usd = true;
            break 2;
        }
    }
}
$usd_available = ($usd_rate > 0) || $has_explicit_usd;

$active_slug = (string)($_GET['service'] ?? '');
if (!Pricing::isValidService($active_slug)) {
    $active_slug = 'web-development';
}
$active_service = $services[$active_slug];

$currency = (string)($_GET['currency'] ?? 'tzs');
$currency = ($currency === 'usd' && $usd_available) ? 'usd' : 'tzs';

$print_mode = isset($_GET['print']);

$active_packages = $packages[$active_slug] ?? [];

$msg_tzs = 'Prices are shown in Tanzanian Shillings (TZS).';
$msg_usd = $usd_rate > 0
    ? 'Converted to US Dollars (USD) at 1 USD = TZS ' . number_format($usd_rate)
    : 'Shown in US Dollars (USD) from our price list.';

function pricing_tzs($value) {
    return 'TZS ' . number_format((float)$value);
}
function pricing_usd($row, $rate) {
    if ($row['price_usd'] !== null && $row['price_usd'] !== '') {
        return (float)$row['price_usd'];
    }
    if ($rate > 0) {
        return round(((float)$row['price_tzs']) / $rate, 2);
    }
    return null;
}
function pricing_service_url($slug, $currency) {
    $url = '/pricing?service=' . urlencode($slug);
    if ($currency === 'usd') $url .= '&currency=usd';
    return $url;
}
?>
<section class="page-header<?= $hero_bg ? ' page-header-with-bg' : '' ?>"<?php if ($hero_bg): ?> style="background-image:url('/<?= htmlspecialchars(webp_url($hero_bg)) ?>')"<?php endif; ?>>
    <div class="container">
        <h1><?= __('pricing') ?></h1>
        <p>Clear, transparent starting prices for our services</p>
    </div>
</section>

<section class="pricing-main section-padding">
    <div class="container">
        <div class="row g-4 g-lg-5">
            <!-- Left column: service navigation + contact card -->
            <div class="col-lg-5 pricing-col-left">
                <div class="pricing-nav-block">
                    <h2 class="pricing-nav-title">Our Services</h2>
                    <ul class="pricing-service-nav">
                        <?php foreach ($services as $slug => $meta): ?>
                        <li>
                            <a class="pricing-service-item<?= $slug === $active_slug ? ' active' : '' ?>" href="<?= htmlspecialchars(pricing_service_url($slug, $currency)) ?>"<?= $slug === $active_slug ? ' aria-current="page"' : '' ?>>
                                <span><?= htmlspecialchars($meta['label']) ?></span>
                                <span class="pr-arrow" aria-hidden="true"><i class="bi bi-arrow-right"></i></span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="pricing-contact-card">
                    <h3>Need Help With Our Services?</h3>
                    <p>Talk to our team to find the right package for your budget and goals, and get a free quotation.</p>
                    <ul class="pricing-contact-meta">
                        <li><i class="bi bi-telephone"></i> <a href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', $pr_phone)) ?>"><?= htmlspecialchars($pr_phone) ?></a></li>
                        <li><i class="bi bi-envelope"></i> <a href="mailto:<?= htmlspecialchars($pr_email) ?>"><?= htmlspecialchars($pr_email) ?></a></li>
                        <li><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($pr_location) ?></li>
                    </ul>
                    <div class="pricing-contact-actions">
                        <a href="/contact" class="btn btn-red"><i class="bi bi-envelope me-2"></i>Contact Us</a>
                    </div>
                    <a class="pricing-pdf-btn" href="/pricing?service=<?= urlencode($active_slug) ?><?= $currency === 'usd' ? '&currency=usd' : '' ?>&print=1" onclick="window.print();return false;">
                        <i class="bi bi-download"></i> Download Pricing PDF
                    </a>
                </div>
            </div>

            <!-- Right column: pricing header, package panel, info and CTA -->
            <div class="col-lg-7 pricing-col-right">
                <div class="pricing-head">
                    <div class="pricing-head-meta">
                        <?php if ($valid_year !== ''): ?>
                            <span class="pricing-valid"><i class="bi bi-calendar-check"></i> Valid for <?= htmlspecialchars($valid_year) ?></span>
                        <?php endif; ?>
                        <span class="pricing-starting">Starting prices</span>
                    </div>
                    <div class="currency-switch" role="group" aria-label="Select currency">
                        <a class="currency-btn<?= $currency === 'tzs' ? ' active' : '' ?>" href="<?= htmlspecialchars(pricing_service_url($active_slug, 'tzs')) ?>" data-cur="tzs"<?= $currency === 'tzs' ? ' aria-current="true"' : '' ?>>TZS</a>
                        <?php if ($usd_available): ?>
                        <a class="currency-btn<?= $currency === 'usd' ? ' active' : '' ?>" href="<?= htmlspecialchars(pricing_service_url($active_slug, 'usd')) ?>" data-cur="usd"<?= $currency === 'usd' ? ' aria-current="true"' : '' ?>>USD</a>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($usd_available): ?>
                <p class="pricing-currency-msg" data-tzs="<?= htmlspecialchars($msg_tzs) ?>" data-usd="<?= htmlspecialchars($msg_usd) ?>"><?= htmlspecialchars($currency === 'usd' ? $msg_usd : $msg_tzs) ?></p>
                <?php else: ?>
                <p class="pricing-currency-msg"><?= htmlspecialchars($msg_tzs) ?></p>
                <?php endif; ?>

                <div class="pricing-panel" id="pricing-panel">
                    <h2 class="pricing-panel-title"><?= htmlspecialchars($active_service['label']) ?> Pricing</h2>
                    <p class="pricing-panel-desc"><?= htmlspecialchars($active_service['description']) ?></p>

                    <?php if (empty($active_packages)): ?>
                        <div class="pricing-empty">
                            <p><i class="bi bi-info-circle me-2"></i>Pricing for this service is being updated. Please contact us for a tailored quotation.</p>
                            <a href="/contact" class="btn btn-red">Request a Quote</a>
                        </div>
                    <?php else: ?>
                        <div class="pricing-rows">
                            <?php $n = 0; foreach ($active_packages as $pkg): $n++; $usd_val = pricing_usd($pkg, $usd_rate); ?>
                            <div class="pricing-row">
                                <div class="pkg-num"><?= str_pad((string)$n, 2, '0', STR_PAD_LEFT) ?></div>
                                <div class="pkg-body">
                                    <div class="pkg-name"><?= htmlspecialchars($pkg['package_name']) ?></div>
                                    <?php if (!empty($pkg['description'])): ?>
                                        <div class="pkg-desc"><?= htmlspecialchars($pkg['description']) ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="pkg-price-col">
                                    <div class="pkg-starting-label">Starting from</div>
                                    <div class="pkg-price">
                                        <span class="price-amount"
                                              data-tzs="<?= htmlspecialchars(pricing_tzs($pkg['price_tzs'])) ?>"
                                              data-usd="<?= $usd_val !== null ? htmlspecialchars('$' . number_format($usd_val, 2)) : '' ?>"><?= htmlspecialchars($currency === 'usd' && $usd_val !== null ? '$' . number_format($usd_val, 2) : pricing_tzs($pkg['price_tzs'])) ?></span>
                                        <?php if ($pkg['billing'] === 'annual'): ?><span class="pkg-per">/ year</span><?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="pricing-info-panel">
                    <h3>Important</h3>
                    <ul>
                        <li>All prices are starting rates for the scope described. The final price depends on your specific requirements.</li>
                        <li>Custom features, integrations, or content beyond the stated scope may require a separate quotation.</li>
                        <li>Third-party costs such as domains, hosting renewals, licenses, or paid integrations are not included unless stated.</li>
                        <li>Packages billed annually renew on a yearly cycle.</li>
                        <li>Applicable taxes are charged in line with Tanzanian regulations.</li>
                        <li>Prices are set and maintained by Mtaita Tech and may change without prior notice.</li>
                    </ul>
                </div>

                <div class="pricing-steps">
                    <h3>How To Get Started</h3>
                    <ol>
                        <li>
                            <span class="step-num">1</span>
                            <div><strong>Get A Quote</strong><p>Share your requirements with us and receive a free, detailed quotation.</p></div>
                        </li>
                        <li>
                            <span class="step-num">2</span>
                            <div><strong>Choose Your Package</strong><p>Pick the package that fits your budget and goals, or ask us to tailor a custom scope.</p></div>
                        </li>
                        <li>
                            <span class="step-num">3</span>
                            <div><strong>Confirm &amp; Pay</strong><p>Approve the quotation and complete payment using the method we share with you.</p></div>
                        </li>
                        <li>
                            <span class="step-num">4</span>
                            <div><strong>Get Started</strong><p>Our team begins work and keeps you updated on progress at each stage.</p></div>
                        </li>
                        <li>
                            <span class="step-num">5</span>
                            <div><strong>Stay Updated</strong><p>We deliver, support your launch, and remain available for ongoing help.</p></div>
                        </li>
                    </ol>
                </div>

                <div class="pricing-cta">
                    <a href="/contact" class="pricing-cta-btn">Request A Free Quote <i class="bi bi-arrow-right"></i></a>
                    <div class="pricing-cta-phone"><i class="bi bi-telephone"></i> <a href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', $pr_phone)) ?>"><?= htmlspecialchars($pr_phone) ?></a></div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
(function () {
    var switchEl = document.querySelector('.currency-switch');
    if (switchEl) {
        var msg = document.querySelector('.pricing-currency-msg');
        switchEl.addEventListener('click', function (e) {
            var btn = e.target.closest('.currency-btn');
            if (!btn) return;
            e.preventDefault();
            var cur = btn.getAttribute('data-cur');
            document.querySelectorAll('.price-amount').forEach(function (el) {
                var usd = el.getAttribute('data-usd');
                if (cur === 'usd') {
                    el.textContent = usd ? usd : 'Contact us';
                } else {
                    el.textContent = el.getAttribute('data-tzs') || '';
                }
            });
            switchEl.querySelectorAll('.currency-btn').forEach(function (b) {
                var on = b.getAttribute('data-cur') === cur;
                b.classList.toggle('active', on);
                if (on) { b.setAttribute('aria-current', 'true'); } else { b.removeAttribute('aria-current'); }
            });
            if (msg && msg.hasAttribute('data-usd')) {
                msg.textContent = cur === 'usd' ? msg.getAttribute('data-usd') : msg.getAttribute('data-tzs');
            }
            try {
                var url = new URL(window.location.href);
                url.searchParams.set('currency', cur);
                history.replaceState({}, '', url);
            } catch (err) {}
        });
    }

    <?php if ($print_mode): ?>
    window.addEventListener('load', function () {
        setTimeout(function () { window.print(); }, 500);
    });
    <?php endif; ?>
})();
</script>

<?php require_once 'footer.php'; ?>
