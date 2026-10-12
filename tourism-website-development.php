<?php
$page_title = 'Tourism Website Development Tanzania | Tour & Safari Websites';
$page_desc = 'Professional tourism website development in Tanzania. Mtaita Tech builds tour operator websites, safari booking systems, tour management dashboards, and digital solutions for travel businesses.';
$page_keywords = 'tourism website development Tanzania, tour operator website design, safari booking system, tour management dashboard, travel agency website Tanzania, lodge website design, Kilimanjaro tours website';
$body_class = 'wd-page';
require_once 'header.php';
require_once 'db_connect.php';
require_once 'lib/Settings.php';

$hero_bg = Settings::get('hero_bg_services', '');

$tourism_email = Settings::get('admin_email', ADMIN_EMAIL);
$tourism_phone = Settings::get('admin_phone', '+255 616 591 639');
$tourism_location = Settings::get('admin_location', 'Moshi, Kilimanjaro');

$faq_schema = [
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => [
        ['@type' => 'Question', 'name' => 'What is included in a tourism website?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'A tourism website typically includes a homepage, tour and package pages, destination and attraction pages, photo galleries, itinerary layouts, an enquiry form, and contact details. Additional modules such as booking systems or management dashboards are available as custom development.']],
        ['@type' => 'Question', 'name' => 'Can you build safari and tour booking systems?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Yes. We build enquiry and booking systems for safaris and tours. The exact workflow, whether enquiry-only, request-to-book, or full online booking, is defined with you during planning and delivered as custom development.']],
        ['@type' => 'Question', 'name' => 'Can visitors book tours directly through our website?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Yes, where the agreed project scope includes an online booking workflow. Many operators start with an enquiry system and add direct booking and online payments as a later phase.']],
        ['@type' => 'Question', 'name' => 'Can you integrate mobile money and online payments?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Payment integration is available as a custom feature and depends on your chosen provider and account approvals. We integrate supported gateways, such as mobile money and card processors, once your merchant accounts are ready.']],
        ['@type' => 'Question', 'name' => 'Can we manage tours, prices, and bookings through an admin dashboard?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Yes. A tour management dashboard is available as custom development, allowing your team to manage tours, packages, prices, enquiries, and bookings. The exact features are agreed before development begins.']],
        ['@type' => 'Question', 'name' => 'Can the website display itineraries, destinations, galleries, and tour packages?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Yes. We design destination and attraction pages, day-by-day itineraries, tour package listings, and responsive photo galleries as part of the standard tourism website.']],
        ['@type' => 'Question', 'name' => 'Can you build a multilingual tourism website?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Yes. We can build multilingual tourism websites so you can serve visitors in different languages, such as English and Kiswahili, with a language switcher.']],
        ['@type' => 'Question', 'name' => 'Do you provide hosting, domain setup, and maintenance?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Yes. We assist with domain registration, hosting setup, security configuration, and ongoing maintenance and support as part of our service.']],
        ['@type' => 'Question', 'name' => 'Can the website be optimized for search engines?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Yes. We follow SEO best practices when building your website, and can provide ongoing SEO and digital marketing services to help travellers find your business online.']],
        ['@type' => 'Question', 'name' => 'How do we get started?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Simply contact us with your requirements. We will discuss your goals, recommend the right scope, and provide a quotation and timeline for your tourism website project.']]
    ]
];
?>

<script type="application/ld+json"><?= json_encode($faq_schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?></script>

<section class="page-header<?= $hero_bg ? ' page-header-with-bg' : '' ?>"<?php if ($hero_bg): ?> style="background-image:url('/<?= htmlspecialchars(webp_url($hero_bg)) ?>')"<?php endif; ?>>
    <div class="container">
        <h1><?= __('tourism_website_development') ?></h1>
        <p>Tour operator, safari, lodge &amp; travel website solutions built to attract travellers and turn enquiries into bookings</p>
    </div>
</section>

<!-- Tourism Website Development two-column content -->
<section class="wd-main section-padding">
    <div class="container">
        <div class="row g-4 g-lg-5">
            <!-- Left column -->
            <div class="col-lg-6 wd-col-left">
                <div class="wd-block">
                    <h2 class="wd-heading">Why Choose Our Tourism Solutions?</h2>
                    <p class="wd-subtext">We build practical digital solutions that help tour operators showcase experiences, manage enquiries, simplify bookings, and grow their businesses online.</p>
                    <ul class="wd-benefit-list">
                        <li>
                            <a class="wd-benefit-row" href="#tourism-overview">
                                <span class="wd-benefit-text">Custom Tour &amp; Safari Websites</span>
                                <span class="wd-arrow" aria-hidden="true"><i class="bi bi-arrow-right"></i></span>
                            </a>
                        </li>
                        <li>
                            <a class="wd-benefit-row" href="#tourism-services">
                                <span class="wd-benefit-text">Tour Packages &amp; Itinerary Management</span>
                                <span class="wd-arrow" aria-hidden="true"><i class="bi bi-arrow-right"></i></span>
                            </a>
                        </li>
                        <li>
                            <a class="wd-benefit-row" href="#tourism-services">
                                <span class="wd-benefit-text">Online Booking &amp; Enquiry Systems</span>
                                <span class="wd-arrow" aria-hidden="true"><i class="bi bi-arrow-right"></i></span>
                            </a>
                        </li>
                        <li>
                            <a class="wd-benefit-row" href="/services">
                                <span class="wd-benefit-text">Secure Payment Integration</span>
                                <span class="wd-arrow" aria-hidden="true"><i class="bi bi-arrow-right"></i></span>
                            </a>
                        </li>
                        <li>
                            <a class="wd-benefit-row" href="#tourism-types">
                                <span class="wd-benefit-text">Tour Management Dashboard</span>
                                <span class="wd-arrow" aria-hidden="true"><i class="bi bi-arrow-right"></i></span>
                            </a>
                        </li>
                        <li>
                            <a class="wd-benefit-row" href="#tourism-overview">
                                <span class="wd-benefit-text">Mobile-Friendly Design</span>
                                <span class="wd-arrow" aria-hidden="true"><i class="bi bi-arrow-right"></i></span>
                            </a>
                        </li>
                        <li>
                            <a class="wd-benefit-row" href="/seo-digital-marketing">
                                <span class="wd-benefit-text">SEO &amp; Online Visibility</span>
                                <span class="wd-arrow" aria-hidden="true"><i class="bi bi-arrow-right"></i></span>
                            </a>
                        </li>
                        <li>
                            <a class="wd-benefit-row" href="/contact">
                                <span class="wd-benefit-text">Ongoing Maintenance &amp; Support</span>
                                <span class="wd-arrow" aria-hidden="true"><i class="bi bi-arrow-right"></i></span>
                            </a>
                        </li>
                    </ul>
                    <p class="wd-subtext wd-note">Booking workflows, payment gateways, and management dashboards are delivered as optional custom development, scoped and confirmed with you before implementation.</p>
                </div>

                <div class="wd-block" id="tourism-faq">
                    <h2 class="wd-heading">Frequently Asked Questions</h2>
                    <p class="wd-subtext">Answers to common questions about our tourism website development services.</p>
                    <div class="accordion wd-faq" id="faqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1" aria-expanded="true" aria-controls="faq1">
                                    What is included in a tourism website?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">A tourism website typically includes a homepage, tour and package pages, destination and attraction pages, photo galleries, itinerary layouts, an enquiry form, and contact details. Additional modules such as booking systems or management dashboards are available as custom development.</div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2" aria-expanded="false" aria-controls="faq2">
                                    Can you build safari and tour booking systems?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">Yes. We build enquiry and booking systems for safaris and tours. The exact workflow, whether enquiry-only, request-to-book, or full online booking, is defined with you during planning and delivered as custom development.</div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3" aria-expanded="false" aria-controls="faq3">
                                    Can visitors book tours directly through our website?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">Yes, where the agreed project scope includes an online booking workflow. Many operators start with an enquiry system and add direct booking and online payments as a later phase.</div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4" aria-expanded="false" aria-controls="faq4">
                                    Can you integrate mobile money and online payments?
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">Payment integration is available as a custom feature and depends on your chosen provider and account approvals. We integrate supported gateways, such as mobile money and card processors, once your merchant accounts are ready.</div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5" aria-expanded="false" aria-controls="faq5">
                                    Can we manage tours, prices, and bookings through an admin dashboard?
                                </button>
                            </h2>
                            <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">Yes. A tour management dashboard is available as custom development, allowing your team to manage tours, packages, prices, enquiries, and bookings. The exact features are agreed before development begins.</div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6" aria-expanded="false" aria-controls="faq6">
                                    Can the website display itineraries, destinations, galleries, and tour packages?
                                </button>
                            </h2>
                            <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">Yes. We design destination and attraction pages, day-by-day itineraries, tour package listings, and responsive photo galleries as part of the standard tourism website.</div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq7" aria-expanded="false" aria-controls="faq7">
                                    Can you build a multilingual tourism website?
                                </button>
                            </h2>
                            <div id="faq7" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">Yes. We can build multilingual tourism websites so you can serve visitors in different languages, such as English and Kiswahili, with a language switcher.</div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq8" aria-expanded="false" aria-controls="faq8">
                                    Do you provide hosting, domain setup, and maintenance?
                                </button>
                            </h2>
                            <div id="faq8" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">Yes. We assist with domain registration, hosting setup, security configuration, and ongoing maintenance and support as part of our service.</div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq9" aria-expanded="false" aria-controls="faq9">
                                    Can the website be optimized for search engines?
                                </button>
                            </h2>
                            <div id="faq9" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">Yes. We follow SEO best practices when building your website, and can provide ongoing SEO and digital marketing services to help travellers find your business online.</div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq10" aria-expanded="false" aria-controls="faq10">
                                    How do we get started?
                                </button>
                            </h2>
                            <div id="faq10" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">Simply contact us with your requirements. We will discuss your goals, recommend the right scope, and provide a quotation and timeline for your tourism website project.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="wd-contact-card">
                    <h3>Need a Tourism Website for Your Business?</h3>
                    <p>Let's build a professional online presence for your tours, safaris, and travel services.</p>
                    <div class="wd-contact-actions">
                        <a href="/contact" class="btn btn-red"><i class="bi bi-envelope me-2"></i>Contact Us</a>
                        <a href="/contact" class="wd-btn-quote"><i class="bi bi-clipboard-check me-2"></i>Request a Quote</a>
                    </div>
                    <ul class="wd-contact-meta">
                        <li><i class="bi bi-telephone"></i> <a href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', $tourism_phone)) ?>"><?= htmlspecialchars($tourism_phone) ?></a></li>
                        <li><i class="bi bi-envelope"></i> <a href="mailto:<?= htmlspecialchars($tourism_email) ?>"><?= htmlspecialchars($tourism_email) ?></a></li>
                        <li><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($tourism_location) ?></li>
                    </ul>
                </div>
            </div>

            <!-- Right column -->
            <div class="col-lg-6 wd-col-right">
                <div class="wd-overview-img">
                    <img src="<?= htmlspecialchars(webp_url('/assets/img/web.png')) ?>" alt="Mtaita Tech tourism website development" loading="lazy">
                </div>

                <div class="wd-block">
                    <h2 class="wd-heading" id="tourism-overview">Tourism Website Development Overview</h2>
                    <p class="wd-subtext">We create professional tourism websites that help safari operators, tour companies, lodges, and destination businesses showcase their services, attract travellers, and convert enquiries into bookings.</p>

                    <h3 class="wd-subheading" id="tourism-services">Tourism Website Development Services</h3>
                    <div class="row g-3 wd-service-grid">
                        <div class="col-sm-6">
                            <a class="wd-service-link" href="#tourism-types">
                                <span>Tour &amp; Safari Website Design</span>
                                <span class="wd-arrow" aria-hidden="true"><i class="bi bi-arrow-right"></i></span>
                            </a>
                        </div>
                        <div class="col-sm-6">
                            <a class="wd-service-link" href="#tourism-types">
                                <span>Tour Packages &amp; Itinerary Management</span>
                                <span class="wd-arrow" aria-hidden="true"><i class="bi bi-arrow-right"></i></span>
                            </a>
                        </div>
                        <div class="col-sm-6">
                            <a class="wd-service-link" href="/contact">
                                <span>Online Booking &amp; Enquiry Systems</span>
                                <span class="wd-arrow" aria-hidden="true"><i class="bi bi-arrow-right"></i></span>
                            </a>
                        </div>
                        <div class="col-sm-6">
                            <a class="wd-service-link" href="/services">
                                <span>Tour Operator Admin Dashboards</span>
                                <span class="wd-arrow" aria-hidden="true"><i class="bi bi-arrow-right"></i></span>
                            </a>
                        </div>
                        <div class="col-sm-6">
                            <a class="wd-service-link" href="/contact">
                                <span>Mobile Money &amp; Payment Integration</span>
                                <span class="wd-arrow" aria-hidden="true"><i class="bi bi-arrow-right"></i></span>
                            </a>
                        </div>
                        <div class="col-sm-6">
                            <a class="wd-service-link" href="#tourism-types">
                                <span>Destination &amp; Attraction Pages</span>
                                <span class="wd-arrow" aria-hidden="true"><i class="bi bi-arrow-right"></i></span>
                            </a>
                        </div>
                        <div class="col-sm-6">
                            <a class="wd-service-link" href="/seo-digital-marketing">
                                <span>SEO &amp; Digital Marketing Readiness</span>
                                <span class="wd-arrow" aria-hidden="true"><i class="bi bi-arrow-right"></i></span>
                            </a>
                        </div>
                        <div class="col-sm-6">
                            <a class="wd-service-link" href="/services">
                                <span>Website Maintenance &amp; Security</span>
                                <span class="wd-arrow" aria-hidden="true"><i class="bi bi-arrow-right"></i></span>
                            </a>
                        </div>
                    </div>

                    <h3 class="wd-subheading">Tourism Digital Solutions</h3>
                    <p class="wd-subtext">From tour packages and interactive itineraries to enquiry forms, booking workflows, and management dashboards, our solutions are designed around the needs of your tourism business.</p>
                </div>

                <div class="wd-block">
                    <h3 class="wd-subheading" id="tourism-types">Types of Tourism Websites We Build</h3>
                    <p class="wd-subtext">Digital experiences tailored to different tourism businesses, destinations, and travel services.</p>
                    <div class="row g-3 wd-types-grid">
                        <div class="col-sm-6">
                            <div class="wd-type-card">
                                <h4>Safari Tour Operator Websites</h4>
                                <p>Showcase safari packages, wildlife destinations, itineraries, accommodation options, and enquiry forms.</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="wd-type-card">
                                <h4>Tour Company Websites</h4>
                                <p>Present tours, activities, trip schedules, destinations, pricing information, and customer enquiries.</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="wd-type-card">
                                <h4>Lodge &amp; Accommodation Websites</h4>
                                <p>Display rooms, facilities, photo galleries, location information, and booking enquiry options.</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="wd-type-card">
                                <h4>Destination &amp; Attraction Websites</h4>
                                <p>Present national parks, waterfalls, cultural experiences, attractions, and visitor information.</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="wd-type-card">
                                <h4>Mountain Climbing Websites</h4>
                                <p>Show climbing routes, packages, day-by-day itineraries, preparation information, and booking enquiries.</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="wd-type-card">
                                <h4>Travel Agency Websites</h4>
                                <p>Showcase travel packages, destinations, transport options, and customer enquiry workflows.</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="wd-type-card">
                                <h4>Tour Booking Platforms</h4>
                                <p>Provide suitable booking forms, availability workflows, booking management, and payment integrations when supported by the agreed project scope.</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="wd-type-card">
                                <h4>Tourism Management Systems</h4>
                                <p>Provide administrative tools for managing tours, packages, enquiries, bookings, and other agreed business operations.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'footer.php'; ?>
