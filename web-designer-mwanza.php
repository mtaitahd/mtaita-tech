<?php
$page_title = 'Web Designer Mwanza | Professional Website Design Mwanza';
$page_desc = 'Looking for a professional web designer in Mwanza? Mtaita Tech offers affordable, responsive website design services in Mwanza, Tanzania.';
$page_keywords = 'web designer Mwanza, website design Mwanza, web developer Mwanza, web design Tanzania, website designer Mwanza, affordable web design Mwanza';
$service_category = 'Web Development';
require_once 'header.php';
require_once 'db_connect.php';
require_once 'lib/Settings.php';

$hero_bg = Settings::get('hero_bg_services', '');

$faq_schema = [
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => [
        [
            '@type' => 'Question',
            'name' => 'How much does web design cost in Mwanza?',
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Our web design packages in Mwanza start at affordable rates. Contact us for a free quote tailored to your business needs.']
        ],
        [
            '@type' => 'Question',
            'name' => 'Do you offer ongoing support?',
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Yes! We provide ongoing maintenance and 24/7 technical support for all websites we build in Mwanza.']
        ]
    ]
];
?>
<script type="application/ld+json"><?= json_encode($faq_schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?></script>
<section class="page-header<?= $hero_bg ? ' page-header-with-bg' : '' ?>"<?php if ($hero_bg): ?> style="background-image:url('/<?= htmlspecialchars(webp_url($hero_bg)) ?>')"<?php endif; ?>>
    <div class="container">
        <h1>Web Designer Mwanza</h1>
        <p>Professional website design services in Mwanza, Tanzania</p>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6">
                <h2>Expert Web Designer in Mwanza</h2>
                <p>Looking for a professional web designer in Mwanza? Mtaita Tech offers custom website design and development services for businesses in Mwanza, Tanzania\'s second-largest city.</p>
                <p>Whether you run a retail business, hotel, restaurant, or professional service, we build websites that help you attract more customers and grow your business online.</p>
                <h3>Why Choose Mtaita Tech in Mwanza?</h3>
                <ul>
                    <li><strong>Modern Designs</strong> — Contemporary, eye-catching website designs</li>
                    <li><strong>Mobile-First</strong> — Optimized for the growing mobile user base in Mwanza</li>
                    <li><strong>SEO Ready</strong> — Built to rank on Google from day one</li>
                    <li><strong>Local Payments</strong> — M-Pesa, Tigo Pesa, Airtel Money integration</li>
                    <li><strong>Ongoing Support</strong> — We\'re here when you need us</li>
                </ul>
                <a href="/contact" class="btn btn-red mt-3">Get a Free Quote <i class="bi bi-arrow-right ms-2"></i></a>
            </div>
            <div class="col-lg-6">
                <img src="/assets/img/web.png" alt="Web Designer Mwanza - Mtaita Tech" class="img-fluid rounded shadow">
            </div>
        </div>
    </div>
</section>

<section class="section-padding bg-light-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2>Our Web Design Services in Mwanza</h2>
            <p class="text-muted">Comprehensive website solutions for businesses in Mwanza</p>
        </div>
        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="service-detail-card">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-wrap me-3"><i class="fa-solid fa-globe"></i></div>
                        <h4 class="mb-0">Business Websites</h4>
                    </div>
                    <p>Professional websites that establish your brand in the Mwanza market.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="service-detail-card">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-wrap me-3"><i class="fa-solid fa-cart-shopping"></i></div>
                        <h4 class="mb-0">E-Commerce</h4>
                    </div>
                    <p>Online stores with mobile money and card payment integration for Mwanza businesses.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="service-detail-card">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-wrap me-3"><i class="fa-solid fa-chart-line"></i></div>
                        <h4 class="mb-0">SEO & Marketing</h4>
                    </div>
                    <p>Get found on Google with our integrated SEO services for Mwanza.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h2 class="text-center mb-5">Frequently Asked Questions</h2>
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                How much does web design cost in Mwanza?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">Our web design packages in Mwanza start at affordable rates. Contact us for a free quote tailored to your business needs.</div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Do you offer ongoing support?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">Yes! We provide ongoing maintenance and 24/7 technical support for all websites we build in Mwanza.</div>
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

<section class="section-padding bg-light-section">
    <div class="container">
        <div class="text-center">
            <h2>Related Services</h2>
            <p class="text-muted">Explore our other services</p>
            <div class="d-flex flex-wrap justify-content-center gap-2 mt-3">
                <a href="/web-designer-dar-es-salaam" class="btn btn-outline-red">Web Designer Dar es Salaam</a>
                <a href="/web-designer-arusha" class="btn btn-outline-red">Web Designer Arusha</a>
                <a href="/web-development" class="btn btn-outline-red">Web Development Tanzania</a>
                <a href="/seo-dar-es-salaam" class="btn btn-outline-red">SEO Dar es Salaam</a>
            </div>
        </div>
    </div>
</section>

<?php require_once 'footer.php'; ?>