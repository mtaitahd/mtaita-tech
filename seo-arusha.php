<?php
$page_title = 'SEO Arusha | Search Engine Optimization Services Arusha';
$page_desc = 'Professional SEO services in Arusha, Tanzania. Rank higher on Google and attract more customers with Mtaita Tech\'s local SEO expertise in Arusha.';
$page_keywords = 'SEO Arusha, search engine optimization Arusha, SEO company Arusha, digital marketing Arusha, Google ranking Arusha, SEO services Arusha Tanzania';
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
            'name' => 'How is SEO different for Arusha businesses?',
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Local SEO focuses on near me searches and location-specific keywords. For Arusha businesses, we target searches like hotel Arusha, safari Arusha, and restaurant Arusha.']
        ],
        [
            '@type' => 'Question',
            'name' => 'Do you optimize Google Business Profile?',
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Yes, we fully optimize your Google Business Profile to appear in local map packs and local search results in Arusha.']
        ],
        [
            '@type' => 'Question',
            'name' => 'How much does SEO cost in Arusha?',
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Our SEO packages are affordable and tailored to your business size and goals. Contact us for a free consultation and quote.']
        ]
    ]
];
?>
<script type="application/ld+json"><?= json_encode($faq_schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?></script>
<section class="page-header<?= $hero_bg ? ' page-header-with-bg' : '' ?>"<?php if ($hero_bg): ?> style="background-image:url('/<?= htmlspecialchars(webp_url($hero_bg)) ?>')"<?php endif; ?>>
    <div class="container">
        <h1>SEO Arusha</h1>
        <p>Professional SEO services in Arusha, Tanzania</p>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6">
                <h2>SEO Experts in Arusha</h2>
                <p>Looking for professional SEO services in Arusha? Mtaita Tech helps businesses in Arusha rank higher on Google and attract more local customers. Our team understands the Arusha market and knows exactly how to optimize your online presence.</p>
                <p>From hotels and tour operators to retail and professional services, we help Arusha businesses dominate local search results.</p>
                <h3>Our SEO Services in Arusha</h3>
                <ul>
                    <li>Local SEO for Arusha businesses</li>
                    <li>Keyword research targeting Arusha audiences</li>
                    <li>On-page and technical SEO</li>
                    <li>Google Business Profile optimization</li>
                    <li>Content marketing and blog strategy</li>
                    <li>Monthly performance tracking and reports</li>
                </ul>
                <a href="/contact" class="btn btn-red mt-3">Get a Free SEO Audit <i class="bi bi-arrow-right ms-2"></i></a>
            </div>
            <div class="col-lg-6">
                <img src="/assets/img/seo.png" alt="SEO Arusha - Mtaita Tech" class="img-fluid rounded shadow">
            </div>
        </div>
    </div>
</section>

<section class="section-padding bg-light-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2>Why Choose Mtaita Tech for SEO in Arusha</h2>
            <p class="text-muted">We deliver measurable results for Arusha businesses</p>
        </div>
        <div class="row g-4">
            <div class="col-md-6">
                <div class="service-detail-card h-100">
                    <h4>Proven Track Record</h4>
                    <p>We\'ve helped businesses across Arusha improve their Google rankings and attract more customers through organic search.</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="service-detail-card h-100">
                    <h4>Tourism & Hospitality SEO</h4>
                    <p>Arusha is the gateway to Tanzania\'s safari tourism. We specialize in SEO for hotels, lodges, and tour operators.</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="service-detail-card h-100">
                    <h4>Transparent Reporting</h4>
                    <p>You\'ll receive detailed monthly reports showing your keyword rankings, website traffic, and ROI.</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="service-detail-card h-100">
                    <h4>Affordable Packages</h4>
                    <p>SEO doesn\'t have to be expensive. We offer competitive pricing designed for Arusha\'s business community.</p>
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
                                How is SEO different for Arusha businesses?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">Local SEO focuses on "near me" searches and location-specific keywords. For Arusha businesses, we target searches like "hotel Arusha," "safari Arusha," and "restaurant Arusha."</div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Do you optimize Google Business Profile?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">Yes, we fully optimize your Google Business Profile to appear in local map packs and local search results in Arusha.</div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                How much does SEO cost in Arusha?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">Our SEO packages are affordable and tailored to your business size and goals. Contact us for a free consultation and quote.</div>
                        </div>
                    </div>
                </div>
                <div class="text-center mt-4">
                    <a href="/contact" class="btn btn-red">Start Your SEO Journey <i class="bi bi-arrow-right ms-2"></i></a>
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
                <a href="/seo-moshi" class="btn btn-outline-red">SEO Moshi</a>
                <a href="/seo-dar-es-salaam" class="btn btn-outline-red">SEO Dar es Salaam</a>
                <a href="/seo-digital-marketing" class="btn btn-outline-red">SEO & Digital Marketing</a>
                <a href="/web-designer-arusha" class="btn btn-outline-red">Web Designer Arusha</a>
            </div>
        </div>
    </div>
</section>

<?php require_once 'footer.php'; ?>