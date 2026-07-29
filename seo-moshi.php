<?php
$page_title = 'SEO Moshi | Search Engine Optimization Kilimanjaro';
$page_desc = 'Professional SEO services in Moshi, Kilimanjaro. Rank higher on Google and attract more customers with Mtaita Tech\'s local SEO expertise.';
$page_keywords = 'SEO Moshi, SEO Kilimanjaro, search engine optimization Moshi, SEO company Moshi, digital marketing Kilimanjaro, Google ranking Moshi';
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
            'name' => 'How long does SEO take to show results in Moshi?',
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Typically, you can start seeing improvements in 4-8 weeks. Significant ranking gains usually occur within 3-6 months of consistent SEO work.']
        ],
        [
            '@type' => 'Question',
            'name' => 'Do you offer SEO for small businesses in Kilimanjaro?',
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Absolutely. We have affordable SEO packages designed specifically for small and medium businesses in the Kilimanjaro region.']
        ],
        [
            '@type' => 'Question',
            'name' => 'What is included in your SEO service?',
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Our SEO packages include keyword research, on-page optimization, technical SEO, content creation, local SEO, Google Business Profile optimization, and monthly reporting.']
        ]
    ]
];
?>
<script type="application/ld+json"><?= json_encode($faq_schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?></script>
<section class="page-header<?= $hero_bg ? ' page-header-with-bg' : '' ?>"<?php if ($hero_bg): ?> style="background-image:url('/<?= htmlspecialchars(webp_url($hero_bg)) ?>')"<?php endif; ?>>
    <div class="container">
        <h1>SEO Moshi — Kilimanjaro</h1>
        <p>Professional SEO services in Moshi, Kilimanjaro region</p>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6">
                <h2>SEO Experts in Moshi, Kilimanjaro</h2>
                <p>Are you looking for professional SEO services in Moshi? Mtaita Tech helps businesses in Kilimanjaro rank higher on Google and attract more local customers.</p>
                <p>Based in the Kilimanjaro region, we understand the local market and know exactly how to optimize your website for searches in Moshi, Kilimanjaro, and across Tanzania.</p>
                <h3>Our SEO Services in Moshi</h3>
                <ul>
                    <li>Local SEO for Moshi and Kilimanjaro businesses</li>
                    <li>Keyword research targeting Tanzanian audiences</li>
                    <li>On-page optimization and technical SEO</li>
                    <li>Google Business Profile optimization</li>
                    <li>Content strategy and blog writing</li>
                    <li>Monthly performance reports</li>
                </ul>
                <a href="/contact" class="btn btn-red mt-3">Get a Free SEO Audit <i class="bi bi-arrow-right ms-2"></i></a>
            </div>
            <div class="col-lg-6">
                <img src="/assets/img/seo.png" alt="SEO Moshi - Mtaita Tech" class="img-fluid rounded shadow">
            </div>
        </div>
    </div>
</section>

<section class="section-padding bg-light-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2>Why Local SEO Matters for Moshi Businesses</h2>
            <p class="text-muted">Reach customers in Moshi and the Kilimanjaro region</p>
        </div>
        <div class="row g-4">
            <div class="col-md-6">
                <div class="service-detail-card h-100">
                    <h4>Target Local Customers</h4>
                    <p>When someone in Moshi searches for "web developer Kilimanjaro" or "graphic designer Moshi," you want to be the first result they see. Local SEO makes this happen.</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="service-detail-card h-100">
                    <h4>Beat Local Competitors</h4>
                    <p>Many businesses in Moshi haven\'t invested in SEO. By optimizing your website now, you\'ll gain a significant advantage over competitors.</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="service-detail-card h-100">
                    <h4>Cost-Effective Marketing</h4>
                    <p>Unlike paid advertising, SEO delivers long-term, sustainable traffic to your website without ongoing costs per click.</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="service-detail-card h-100">
                    <h4>Track Your Growth</h4>
                    <p>We provide detailed reports showing your rankings, traffic, and conversions so you can see the real impact of our SEO work.</p>
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
                                How long does SEO take to show results in Moshi?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">Typically, you can start seeing improvements in 4-8 weeks. Significant ranking gains usually occur within 3-6 months of consistent SEO work.</div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Do you offer SEO for small businesses in Kilimanjaro?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">Absolutely. We have affordable SEO packages designed specifically for small and medium businesses in the Kilimanjaro region.</div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                What is included in your SEO service?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">Our SEO packages include keyword research, on-page optimization, technical SEO, content creation, local SEO, Google Business Profile optimization, and monthly reporting.</div>
                        </div>
                    </div>
                </div>
                <div class="text-center mt-4">
                    <a href="/contact" class="btn btn-red">Get a Free SEO Consultation <i class="bi bi-arrow-right ms-2"></i></a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-padding bg-light-section">
    <div class="container">
        <div class="text-center">
            <h2>Related Services</h2>
            <p class="text-muted">Explore our other SEO services across Tanzania</p>
            <div class="d-flex flex-wrap justify-content-center gap-2 mt-3">
                <a href="/seo-arusha" class="btn btn-outline-red">SEO Arusha</a>
                <a href="/seo-dar-es-salaam" class="btn btn-outline-red">SEO Dar es Salaam</a>
                <a href="/seo-digital-marketing" class="btn btn-outline-red">SEO & Digital Marketing</a>
                <a href="/web-development" class="btn btn-outline-red">Web Development</a>
            </div>
        </div>
    </div>
</section>

<?php require_once 'footer.php'; ?>