<?php
$page_title = 'Web Designer Arusha | Professional Website Design Arusha';
$page_desc = 'Looking for a professional web designer in Arusha? Mtaita Tech offers affordable, responsive website design services in Arusha, Tanzania.';
$page_keywords = 'web designer Arusha, website design Arusha, web developer Arusha, web design Tanzania, website designer Arusha, affordable web design Arusha';
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
            'name' => 'How much does web design cost in Arusha?',
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Our web design packages in Arusha are affordable and tailored to your needs. Contact us for a free consultation and quote.']
        ],
        [
            '@type' => 'Question',
            'name' => 'Do you build websites for hotels and tour operators?',
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Yes! We specialize in tourism websites with booking systems, gallery showcases, and location maps for Arusha-based hospitality businesses.']
        ],
        [
            '@type' => 'Question',
            'name' => 'Do you offer website maintenance?',
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Yes, we provide ongoing maintenance and support to keep your Arusha business website secure and up-to-date.']
        ]
    ]
];
?>
<script type="application/ld+json"><?= json_encode($faq_schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?></script>
<section class="page-header<?= $hero_bg ? ' page-header-with-bg' : '' ?>"<?php if ($hero_bg): ?> style="background-image:url('/<?= htmlspecialchars(webp_url($hero_bg)) ?>')"<?php endif; ?>>
    <div class="container">
        <h1>Web Designer Arusha</h1>
        <p>Professional website design services in Arusha, Tanzania</p>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6">
                <h2>Expert Web Designer in Arusha</h2>
                <p>Looking for a professional web designer in Arusha? Mtaita Tech is your trusted partner for custom website design and development in Arusha. We create stunning, responsive websites that help businesses in Arusha establish a powerful online presence.</p>
                <p>From hotels and tour operators to retail shops and professional services, we build websites that drive results for Arusha-based businesses.</p>
                <h3>Why Choose Mtaita Tech in Arusha?</h3>
                <ul>
                    <li><strong>Local Presence</strong> — We serve Arusha with a deep understanding of the local market</li>
                    <li><strong>Responsive Design</strong> — Websites optimized for all devices</li>
                    <li><strong>SEO Optimized</strong> — Rank higher on Google searches in Arusha</li>
                    <li><strong>Mobile Money Integration</strong> — M-Pesa, Tigo Pesa, Airtel Money</li>
                    <li><strong>Fast Turnaround</strong> — Get your website live quickly</li>
                </ul>
                <a href="/contact" class="btn btn-red mt-3">Get a Free Quote <i class="bi bi-arrow-right ms-2"></i></a>
            </div>
            <div class="col-lg-6">
                <img src="/assets/img/web.png" alt="Web Designer Arusha - Mtaita Tech" class="img-fluid rounded shadow">
            </div>
        </div>
    </div>
</section>

<section class="section-padding bg-light-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2>Our Web Design Services in Arusha</h2>
            <p class="text-muted">Comprehensive website solutions for businesses in Arusha</p>
        </div>
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="service-detail-card">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-wrap me-3"><i class="fa-solid fa-globe"></i></div>
                        <h4 class="mb-0">Business Websites</h4>
                    </div>
                    <p>Professional company websites that showcase your brand and attract customers in Arusha.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="service-detail-card">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-wrap me-3"><i class="fa-solid fa-cart-shopping"></i></div>
                        <h4 class="mb-0">E-Commerce</h4>
                    </div>
                    <p>Online stores with secure payment integration for Arusha-based businesses.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="service-detail-card">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-wrap me-3"><i class="fa-solid fa-hotel"></i></div>
                        <h4 class="mb-0">Tourism Websites</h4>
                    </div>
                    <p>Stunning websites for hotels, lodges, tour operators, and safari companies in Arusha.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="service-detail-card">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-wrap me-3"><i class="fa-solid fa-chart-line"></i></div>
                        <h4 class="mb-0">SEO & Marketing</h4>
                    </div>
                    <p>Get found on Google with our integrated SEO and digital marketing services.</p>
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
                                How much does web design cost in Arusha?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">Our web design packages in Arusha are affordable and tailored to your needs. Contact us for a free consultation and quote.</div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Do you build websites for hotels and tour operators?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">Yes! We specialize in tourism websites with booking systems, gallery showcases, and location maps for Arusha-based hospitality businesses.</div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Do you offer website maintenance?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">Yes, we provide ongoing maintenance and support to keep your Arusha business website secure and up-to-date.</div>
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
            <p class="text-muted">Explore our other web design services across Tanzania</p>
            <div class="d-flex flex-wrap justify-content-center gap-2 mt-3">
                <a href="/web-designer-dar-es-salaam" class="btn btn-outline-red">Web Designer Dar es Salaam</a>
                <a href="/web-designer-mwanza" class="btn btn-outline-red">Web Designer Mwanza</a>
                <a href="/web-development" class="btn btn-outline-red">Web Development Tanzania</a>
                <a href="/seo-arusha" class="btn btn-outline-red">SEO Arusha</a>
            </div>
        </div>
    </div>
</section>

<?php require_once 'footer.php'; ?>