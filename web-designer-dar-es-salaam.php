<?php
$page_title = 'Web Designer Dar es Salaam | Professional Website Design';
$page_desc = 'Looking for a professional web designer in Dar es Salaam? Mtaita Tech offers affordable, responsive website design services in Dar es Salaam, Tanzania.';
$page_keywords = 'web designer Dar es Salaam, website design Dar es Salaam, web developer Dar es Salaam, web design Tanzania, website designer Dar es Salaam, affordable web design Dar es Salaam';
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
            'name' => 'How much does web design cost in Dar es Salaam?',
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Our web design packages in Dar es Salaam start from affordable rates depending on your needs. Contact us for a free, no-obligation quote tailored to your business.']
        ],
        [
            '@type' => 'Question',
            'name' => 'How long does it take to build a website?',
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'A standard business website typically takes 1-2 weeks. More complex projects like e-commerce sites may take 3-4 weeks. We will provide a clear timeline during our consultation.']
        ],
        [
            '@type' => 'Question',
            'name' => 'Do you offer website maintenance in Dar es Salaam?',
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Yes! We offer ongoing maintenance and support packages to keep your website secure, updated, and performing at its best.']
        ],
        [
            '@type' => 'Question',
            'name' => 'Will my website be mobile-friendly?',
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Absolutely. All our websites are fully responsive and optimized for mobile phones, tablets, and desktop computers.']
        ]
    ]
];
?>
<script type="application/ld+json"><?= json_encode($faq_schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?></script>
<section class="page-header<?= $hero_bg ? ' page-header-with-bg' : '' ?>"<?php if ($hero_bg): ?> style="background-image:url('/<?= htmlspecialchars(webp_url($hero_bg)) ?>')"<?php endif; ?>>
    <div class="container">
        <h1>Web Designer Dar es Salaam</h1>
        <p>Professional website design services in Dar es Salaam, Tanzania</p>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6">
                <h2>Expert Web Designer in Dar es Salaam</h2>
                <p>Are you looking for a professional web designer in Dar es Salaam? Mtaita Tech is your trusted partner for custom website design and development. We create stunning, responsive websites that help businesses in Dar es Salaam establish a powerful online presence.</p>
                <p>Whether you need a simple business website, an e-commerce store, or a complex web application, our team has the skills and experience to deliver exceptional results.</p>
                <h3>Why Choose Mtaita Tech?</h3>
                <ul>
                    <li><strong>Local Expertise</strong> — We understand the Dar es Salaam market and Tanzanian consumers</li>
                    <li><strong>Responsive Design</strong> — Websites that look great on mobile, tablet, and desktop</li>
                    <li><strong>SEO Optimized</strong> — Built to rank high on Google searches</li>
                    <li><strong>Mobile Money Integration</strong> — Accept M-Pesa, Tigo Pesa, and Airtel Money</li>
                    <li><strong>Affordable Prices</strong> — Quality web design within your budget</li>
                </ul>
                <a href="/contact" class="btn btn-red mt-3">Get a Free Quote <i class="bi bi-arrow-right ms-2"></i></a>
            </div>
            <div class="col-lg-6">
                <img src="/assets/img/web.png" alt="Web Designer Dar es Salaam - Mtaita Tech" class="img-fluid rounded shadow">
            </div>
        </div>
    </div>
</section>

<section class="section-padding bg-light-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2>Our Web Design Services in Dar es Salaam</h2>
            <p class="text-muted">Comprehensive website solutions for businesses in Dar es Salaam</p>
        </div>
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="service-detail-card">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-wrap me-3"><i class="fa-solid fa-globe"></i></div>
                        <h4 class="mb-0">Business Websites</h4>
                    </div>
                    <p>Professional company websites that showcase your brand and attract customers in Dar es Salaam.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="service-detail-card">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-wrap me-3"><i class="fa-solid fa-cart-shopping"></i></div>
                        <h4 class="mb-0">E-Commerce</h4>
                    </div>
                    <p>Online stores with secure payment integration including mobile money and card payments.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="service-detail-card">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-wrap me-3"><i class="fa-solid fa-pen-ruler"></i></div>
                        <h4 class="mb-0">Custom CMS</h4>
                    </div>
                    <p>Easy-to-use content management systems so you can update your website without technical skills.</p>
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
                                How much does web design cost in Dar es Salaam?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">Our web design packages in Dar es Salaam start from affordable rates depending on your needs. Contact us for a free, no-obligation quote tailored to your business.</div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                How long does it take to build a website?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">A standard business website typically takes 1-2 weeks. More complex projects like e-commerce sites may take 3-4 weeks. We'll provide a clear timeline during our consultation.</div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Do you offer website maintenance in Dar es Salaam?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">Yes! We offer ongoing maintenance and support packages to keep your website secure, updated, and performing at its best.</div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                Will my website be mobile-friendly?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">Absolutely. All our websites are fully responsive and optimized for mobile phones, tablets, and desktop computers.</div>
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
                <a href="/web-designer-arusha" class="btn btn-outline-red">Web Designer Arusha</a>
                <a href="/web-designer-mwanza" class="btn btn-outline-red">Web Designer Mwanza</a>
                <a href="/web-development" class="btn btn-outline-red">Web Development Tanzania</a>
                <a href="/seo-dar-es-salaam" class="btn btn-outline-red">SEO Dar es Salaam</a>
            </div>
        </div>
    </div>
</section>

<?php require_once 'footer.php'; ?>