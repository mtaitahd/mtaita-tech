<?php
$page_title = 'SEO Dar es Salaam | Search Engine Optimization Services';
$page_desc = 'Professional SEO services in Dar es Salaam, Tanzania. Rank higher on Google and attract more customers with Mtaita Tech\'s local SEO expertise.';
$page_keywords = 'SEO Dar es Salaam, search engine optimization Dar es Salaam, SEO company Dar es Salaam, digital marketing Dar es Salaam, Google ranking Dar es Salaam, SEO services Dar es Salaam Tanzania';
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
            'name' => 'How long before I see results from SEO in Dar es Salaam?',
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Most clients see initial improvements within 4-6 weeks. Significant ranking improvements typically occur within 3-6 months of consistent SEO work.']
        ],
        [
            '@type' => 'Question',
            'name' => 'Do you work with e-commerce businesses in Dar es Salaam?',
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Yes, we have extensive experience optimizing e-commerce websites for Dar es Salaam-based online stores, including product SEO and category page optimization.']
        ],
        [
            '@type' => 'Question',
            'name' => 'What makes your SEO services different?',
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'We combine technical SEO expertise with deep knowledge of the Tanzanian market. We don\'t just optimize for Google — we optimize for Tanzanian consumers.']
        ]
    ]
];
?>
<script type="application/ld+json"><?= json_encode($faq_schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?></script>
<section class="page-header<?= $hero_bg ? ' page-header-with-bg' : '' ?>"<?php if ($hero_bg): ?> style="background-image:url('/<?= htmlspecialchars(webp_url($hero_bg)) ?>')"<?php endif; ?>>
    <div class="container">
        <h1>SEO Dar es Salaam</h1>
        <p>Professional SEO services in Dar es Salaam, Tanzania</p>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6">
                <h2>SEO Experts in Dar es Salaam</h2>
                <p>Looking for professional SEO services in Dar es Salaam? Mtaita Tech helps businesses in Dar es Salaam rank higher on Google and attract more local customers. As Tanzania\'s largest city, Dar es Salaam offers immense opportunities for businesses with a strong online presence.</p>
                <p>Our SEO team understands the competitive Dar es Salaam market and knows exactly how to help your business stand out in search results.</p>
                <h3>Our SEO Services in Dar es Salaam</h3>
                <ul>
                    <li>Local SEO for Dar es Salaam businesses</li>
                    <li>Comprehensive keyword research</li>
                    <li>On-page and technical SEO optimization</li>
                    <li>Google Business Profile management</li>
                    <li>Content marketing and link building</li>
                    <li>Monthly reporting and analytics</li>
                </ul>
                <a href="/contact" class="btn btn-red mt-3">Get a Free SEO Audit <i class="bi bi-arrow-right ms-2"></i></a>
            </div>
            <div class="col-lg-6">
                <img src="/assets/img/seo.png" alt="SEO Dar es Salaam - Mtaita Tech" class="img-fluid rounded shadow">
            </div>
        </div>
    </div>
</section>

<section class="section-padding bg-light-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2>Why Your Dar es Salaam Business Needs SEO</h2>
            <p class="text-muted">Stand out in Tanzania\'s most competitive market</p>
        </div>
        <div class="row g-4">
            <div class="col-md-6">
                <div class="service-detail-card h-100">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-wrap me-3"><i class="fa-solid fa-magnifying-glass-chart"></i></div>
                        <h4 class="mb-0">High Competition</h4>
                    </div>
                    <p>Dar es Salaam is the most competitive market in Tanzania. Professional SEO gives you the edge over competitors who aren\'t optimizing their online presence.</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="service-detail-card h-100">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-wrap me-3"><i class="fa-solid fa-users"></i></div>
                        <h4 class="mb-0">Massive Audience</h4>
                    </div>
                    <p>With over 6 million people, Dar es Salaam has a huge pool of potential customers searching for your services online every day.</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="service-detail-card h-100">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-wrap me-3"><i class="fa-solid fa-chart-simple"></i></div>
                        <h4 class="mb-0">Measurable ROI</h4>
                    </div>
                    <p>SEO delivers measurable results. We track your rankings, traffic, and conversions so you can see exactly how your investment is performing.</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="service-detail-card h-100">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-wrap me-3"><i class="fa-solid fa-clock"></i></div>
                        <h4 class="mb-0">Long-Term Results</h4>
                    </div>
                    <p>Unlike paid ads that stop when you stop paying, SEO builds sustainable, long-term traffic that keeps growing over time.</p>
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
                                How long before I see results from SEO in Dar es Salaam?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">Most clients see initial improvements within 4-6 weeks. Significant ranking improvements typically occur within 3-6 months of consistent SEO work.</div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Do you work with e-commerce businesses in Dar es Salaam?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">Yes, we have extensive experience optimizing e-commerce websites for Dar es Salaam-based online stores, including product SEO and category page optimization.</div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                What makes your SEO services different?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">We combine technical SEO expertise with deep knowledge of the Tanzanian market. We don\'t just optimize for Google — we optimize for Tanzanian consumers.</div>
                        </div>
                    </div>
                </div>
                <div class="text-center mt-4">
                    <a href="/contact" class="btn btn-red">Get Started with SEO <i class="bi bi-arrow-right ms-2"></i></a>
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
                <a href="/seo-arusha" class="btn btn-outline-red">SEO Arusha</a>
                <a href="/seo-moshi" class="btn btn-outline-red">SEO Moshi</a>
                <a href="/seo-digital-marketing" class="btn btn-outline-red">SEO & Digital Marketing</a>
                <a href="/web-designer-dar-es-salaam" class="btn btn-outline-red">Web Designer Dar es Salaam</a>
            </div>
        </div>
    </div>
</section>

<?php require_once 'footer.php'; ?>