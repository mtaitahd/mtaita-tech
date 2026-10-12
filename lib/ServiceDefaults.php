<?php
/**
 * Canonical default content for the service pages.
 *
 * This is the single source of truth used by:
 *   - database/seed_service_cms.php  (seeds the DB on first install)
 *   - service.php                    (fallback when the DB has no row/sections)
 *
 * Item keys are normalised to the database shape by the renderer, so both the
 * seeder and the public page understand `title / text / link / icon`.
 */
class ServiceDefaults {

    public static function all(): array {
        return [
            'web-development'              => self::webDevelopment(),
            'graphic-design'               => self::graphicDesign(),
            'mobile-apps'                  => self::mobileApps(),
            'seo-digital-marketing'        => self::seoDigitalMarketing(),
            'tourism-website-development'  => self::tourism(),
        ];
    }

    public static function get(string $slug): ?array {
        $all = self::all();
        return $all[$slug] ?? null;
    }

    private static function webDevelopment(): array {
        return [
            'title'            => 'Web Development',
            'card_title'       => 'Web Development',
            'description'      => 'Complete web solutions, from design and development to hosting, SEO, and ongoing website management.',
            'short_description'=> 'Custom, responsive websites and web apps built for performance, security and growth.',
            'page_title'       => 'Web Development Kilimanjaro | Web Design Tanzania',
            'page_desc'        => 'Professional web development in Kilimanjaro and Arusha. Custom websites, eCommerce platforms, and web apps by Mtaita Tech. Affordable & responsive.',
            'page_keywords'    => 'website development Kilimanjaro, web design Tanzania, web developer Arusha, website designer Moshi, eCommerce website Tanzania, professional website Tanzania',
            'hero_subtitle'    => 'Custom websites, web apps & e-commerce solutions tailored for your business',
            'image'            => '/assets/img/web.png',
            'image_alt'        => 'Mtaita Tech web development team',
            'icon'             => 'fa-solid fa-code',
            'service_category' => 'Web Development',
            'card_link'        => '/web-development',
            'show_homepage'    => 1,
            'sort_order'       => 1,
            'sections'         => [
                [
                    'type' => 'benefits', 'side' => 'left',
                    'heading' => 'Why Choose Our Web Team',
                    'subheading' => 'End-to-end website solutions that are modern, secure, and built to grow with your organization.',
                    'items' => [
                        ['title' => 'Complete Web Solutions', 'link' => '#wd-overview'],
                        ['title' => 'Modern & Responsive Design', 'link' => '#wd-services'],
                        ['title' => 'Secure & Connected', 'link' => '/services'],
                        ['title' => 'SEO & Online Visibility', 'link' => '/seo-digital-marketing'],
                        ['title' => 'Ongoing Website Management', 'link' => '/contact'],
                        ['title' => 'Built for Your Industry', 'link' => '#wd-types'],
                    ],
                ],
                [
                    'type' => 'faq', 'side' => 'left', 'anchor' => 'faq',
                    'heading' => 'Frequently Asked Questions',
                    'subheading' => 'Answers to common questions about our web development services.',
                    'items' => [
                        ['title' => 'Do you build custom responsive websites?', 'text' => 'Yes, we build fully custom responsive websites using modern technologies, tailored to your brand and business needs.'],
                        ['title' => 'Do you offer e-commerce with secure payment integration?', 'text' => 'Absolutely. We build e-commerce platforms with secure payment gateways including mobile money, card payments, and more.'],
                        ['title' => 'Do you provide content management systems?', 'text' => 'Yes, we integrate easy-to-use CMS platforms so you can update your website content anytime without technical skills.'],
                        ['title' => 'Is your code SEO-optimized and performance tuned?', 'text' => 'Yes, we follow SEO best practices and performance optimization techniques to ensure fast loading speeds and good search engine rankings.'],
                        ['title' => 'Do you provide ongoing maintenance and support?', 'text' => 'Yes, we offer ongoing maintenance and 24/7 technical support to keep your website running smoothly and securely.'],
                    ],
                ],
                [
                    'type' => 'cta', 'side' => 'left',
                    'heading' => 'Need Help With Web Development?',
                    'body' => 'Talk to our team about your website project and get clear guidance, timelines, and a free quotation.',
                    'button_text' => 'Contact Us', 'button_url' => '/contact',
                ],
                [
                    'type' => 'content', 'side' => 'right', 'anchor' => 'wd-overview',
                    'heading' => 'Web Development Overview',
                    'subheading' => 'We design, develop and support fast, secure websites and web applications that help your business grow online.',
                    'image' => '/assets/img/web.png',
                    'image_alt' => 'Mtaita Tech web development team',
                ],
                [
                    'type' => 'service_grid', 'side' => 'right', 'anchor' => 'wd-services',
                    'heading' => '',
                    'items' => [
                        ['title' => 'Custom Website Development', 'link' => '/services'],
                        ['title' => 'E-Commerce Development', 'link' => '/services'],
                        ['title' => 'CMS & Web Portals', 'link' => '/services'],
                        ['title' => 'Domain, Hosting & Maintenance', 'link' => '/contact'],
                    ],
                ],
                [
                    'type' => 'type_cards', 'side' => 'right', 'anchor' => 'wd-types',
                    'heading' => 'Types of Websites We Develop',
                    'subheading' => 'We design and build all kinds of websites tailored to the specific needs of our clients across different industries.',
                    'items' => [
                        ['title' => 'Corporate Websites', 'text' => 'Professional company websites that present your brand, services, and contact details with clarity and credibility.'],
                        ['title' => 'Financial Institutions', 'text' => 'Secure, compliant websites and digital platforms for banks, SACCOs, and microfinance institutions.'],
                        ['title' => 'Non-Financial Cooperative Websites', 'text' => 'Member-focused websites for co-operatives with services, news, and membership information.'],
                        ['title' => 'E-Commerce Websites', 'text' => 'Online stores with product catalogues, secure checkout, and mobile money or card payment integration.'],
                        ['title' => 'Educational Portals', 'text' => 'School, college, and training websites with courses, admissions, news, and student resources.'],
                        ['title' => 'Church Websites', 'text' => 'Welcoming church websites with sermons, events, ministries, and online giving information.'],
                        ['title' => 'Organization Websites', 'text' => 'Websites for NGOs, associations, and community organizations that share your mission and impact.'],
                        ['title' => 'Tours and Safaris Websites', 'text' => 'Travel and tour websites with destination galleries, itineraries, and enquiry or booking forms.'],
                    ],
                ],
                [
                    'type' => 'projects', 'side' => 'full',
                    'heading' => 'Our Web Development Projects',
                    'subheading' => 'Explore our latest web development work',
                ],
                [
                    'type' => 'city_links', 'side' => 'full',
                    'heading' => 'Web Design Services Across Tanzania',
                    'subheading' => 'Find a professional web designer in your city',
                    'items' => [
                        ['title' => 'Web Designer Dar es Salaam', 'link' => '/web-designer-dar-es-salaam'],
                        ['title' => 'Web Designer Arusha', 'link' => '/web-designer-arusha'],
                        ['title' => 'Web Designer Mwanza', 'link' => '/web-designer-mwanza'],
                    ],
                ],
            ],
        ];
    }

    private static function graphicDesign(): array {
        return [
            'title'            => 'Graphic Design',
            'card_title'       => 'Graphics Design',
            'description'      => 'Memorable, consistent, and impactful visual design, from branding and marketing materials to digital campaigns that strengthen your brand.',
            'short_description'=> 'Logo design, branding, flyers, business cards and social media graphics that make your brand stand out.',
            'page_title'       => 'Graphic Design Services Tanzania | Logo Design, Branding',
            'page_desc'        => 'Professional graphic design services in Tanzania. Logo design, branding, flyers, business cards, social media graphics by Mtaita Tech.',
            'page_keywords'    => 'graphic design Tanzania, logo design Arusha, flyer design Moshi, branding Tanzania, business card design Kilimanjaro, graphic designer Moshi',
            'hero_subtitle'    => 'Creative graphic design solutions to elevate your brand identity',
            'image'            => '/assets/img/design.png',
            'image_alt'        => 'Mtaita Tech graphic design services',
            'icon'             => 'fa-solid fa-palette',
            'service_category' => 'Graphic Design',
            'card_link'        => '/graphic-design',
            'show_homepage'    => 1,
            'sort_order'       => 2,
            'sections'         => [
                [
                    'type' => 'benefits', 'side' => 'left',
                    'heading' => 'Why Choose Our Graphic Design Team',
                    'subheading' => 'Professional designs that capture attention, communicate your message, and strengthen your brand.',
                    'items' => [
                        ['title' => 'Creative & Unique Designs', 'link' => '#wd-overview'],
                        ['title' => 'Brand Identity & Logo Design', 'link' => '#wd-services'],
                        ['title' => 'Marketing Materials', 'link' => '/services'],
                        ['title' => 'Print & Digital Designs', 'link' => '#wd-types'],
                        ['title' => 'Social Media Graphics', 'link' => '/contact'],
                        ['title' => 'Fast Delivery & Revisions', 'link' => '#faq'],
                    ],
                ],
                [
                    'type' => 'faq', 'side' => 'left', 'anchor' => 'faq',
                    'heading' => 'Frequently Asked Questions',
                    'items' => [
                        ['title' => 'Do you design professional logos?', 'text' => 'Yes, we create unique, memorable logos that reflect your brand identity and values.'],
                        ['title' => 'Do you provide full brand identity packages?', 'text' => 'Yes, we design complete branding packages including logos, color palettes, typography, and brand guidelines.'],
                        ['title' => 'Do you design marketing materials?', 'text' => 'Yes, we design flyers, brochures, business cards, banners, posters, and social media graphics.'],
                        ['title' => 'Do you provide print-ready files?', 'text' => 'Yes, we deliver print-ready files in PDF, AI, SVG, JPG, and PNG formats as per your requirements.'],
                        ['title' => 'Do you design social media graphics?', 'text' => 'Yes, we create engaging social media posts, covers, banners, and ad creatives for all platforms.'],
                    ],
                ],
                [
                    'type' => 'cta', 'side' => 'left',
                    'heading' => 'Need Help With Graphic Design?',
                    'body' => 'Talk to our team about your graphic design project and get a free quotation.',
                    'button_text' => 'Contact Us', 'button_url' => '/contact',
                ],
                [
                    'type' => 'content', 'side' => 'right', 'anchor' => 'wd-overview',
                    'heading' => 'Graphic Design Overview',
                    'subheading' => 'We create impactful designs that help businesses stand out and connect with their audience.',
                    'image' => '/assets/img/design.png',
                    'image_alt' => 'Mtaita Tech graphic design services',
                ],
                [
                    'type' => 'service_grid', 'side' => 'right', 'anchor' => 'wd-services',
                    'heading' => 'Graphic Design Services',
                    'items' => [
                        ['title' => 'Logo Design', 'link' => '/services'],
                        ['title' => 'Brand Identity Design', 'link' => '/services'],
                        ['title' => 'Business Card Design', 'link' => '/services'],
                        ['title' => 'Flyer & Brochure Design', 'link' => '/contact'],
                    ],
                ],
                [
                    'type' => 'type_cards', 'side' => 'right', 'anchor' => 'wd-types',
                    'heading' => 'Design Solutions We Offer',
                    'subheading' => 'Creative solutions for all your branding and marketing needs.',
                    'items' => [
                        ['title' => 'Logo Design', 'text' => 'Professional logo creation with multiple concepts and revisions.'],
                        ['title' => 'Branding Packages', 'text' => 'Complete brand identity kits with guidelines for consistent branding.'],
                        ['title' => 'Marketing Materials', 'text' => 'Flyers, posters, banners, and brochures for your marketing campaigns.'],
                        ['title' => 'Social Media Graphics', 'text' => 'Eye-catching posts and ads optimized for all social platforms.'],
                    ],
                ],
                [
                    'type' => 'projects', 'side' => 'full',
                    'heading' => 'Our Graphic Design Projects',
                    'subheading' => 'Explore our latest design work',
                ],
                [
                    'type' => 'city_links', 'side' => 'full',
                    'heading' => 'Graphic Design Services Across Tanzania',
                    'subheading' => 'Find expert designers in your city',
                    'items' => [
                        ['title' => 'Graphic Designer Dar es Salaam', 'link' => '/web-designer-dar-es-salaam'],
                        ['title' => 'Graphic Designer Arusha', 'link' => '/web-designer-arusha'],
                        ['title' => 'Graphic Designer Mwanza', 'link' => '/web-designer-mwanza'],
                    ],
                ],
            ],
        ];
    }

    private static function mobileApps(): array {
        return [
            'title'            => 'Mobile Apps',
            'card_title'       => 'Mobile App Development',
            'description'      => 'Custom Android, iOS and cross-platform mobile apps built for performance, security and great user experience.',
            'short_description'=> 'Android, iOS and cross-platform apps, from concept and design to launch and support.',
            'page_title'       => 'Mobile App Development Tanzania | Android & iOS Apps',
            'page_desc'        => 'Professional mobile app development in Tanzania. Custom Android and iOS apps, cross-platform solutions, and enterprise mobile applications by Mtaita Tech.',
            'page_keywords'    => 'mobile app development Tanzania, android app development Arusha, iOS app development Moshi, mobile app developer Kilimanjaro, cross-platform apps Tanzania, app development company Tanzania',
            'hero_subtitle'    => 'Custom Android, iOS & cross-platform mobile apps for businesses and startups',
            'image'            => '/assets/img/mobile.png',
            'image_alt'        => 'Mtaita Tech mobile app development',
            'icon'             => 'fa-solid fa-mobile-screen',
            'service_category' => 'Mobile Apps',
            'card_link'        => '/mobile-apps',
            'show_homepage'    => 0,
            'sort_order'       => 3,
            'sections'         => [
                [
                    'type' => 'benefits', 'side' => 'left',
                    'heading' => 'Why Choose Our Mobile App Team',
                    'subheading' => 'End-to-end mobile app development from concept to deployment with focus on performance, security, and user experience.',
                    'items' => [
                        ['title' => 'Native & Cross-Platform Development', 'link' => '#wd-overview'],
                        ['title' => 'User-Centered UI/UX Design', 'link' => '#wd-services'],
                        ['title' => 'Secure & Scalable Architecture', 'link' => '/services'],
                        ['title' => 'API & Backend Integration', 'link' => '#wd-types'],
                        ['title' => 'App Store Publishing', 'link' => '/contact'],
                        ['title' => 'Maintenance & Support', 'link' => '#faq'],
                    ],
                ],
                [
                    'type' => 'faq', 'side' => 'left', 'anchor' => 'faq',
                    'heading' => 'Frequently Asked Questions',
                    'items' => [
                        ['title' => 'Do you develop native Android and iOS apps?', 'text' => 'Yes, we develop native and cross-platform mobile apps for Android, iOS, and hybrid platforms using modern frameworks.'],
                        ['title' => 'Do you provide cross-platform development?', 'text' => 'Yes, we build cross-platform apps using Flutter and React Native to reduce costs and maintain consistency across platforms.'],
                        ['title' => 'Do you integrate APIs, payments, and backend systems?', 'text' => 'Yes, we integrate REST/GraphQL APIs, mobile money, payment gateways, and custom backend systems with your mobile app.'],
                        ['title' => 'Do you publish apps to Google Play and App Store?', 'text' => 'Yes, we handle app store submission, compliance requirements, and publishing to Google Play Store and Apple App Store.'],
                        ['title' => 'Do you offer app maintenance and updates?', 'text' => 'Yes, we provide ongoing maintenance, updates, bug fixes, and performance optimization for your mobile applications.'],
                    ],
                ],
                [
                    'type' => 'cta', 'side' => 'left',
                    'heading' => 'Need Help With Mobile App Development?',
                    'body' => 'Talk to our team about your mobile app idea and get a free consultation.',
                    'button_text' => 'Contact Us', 'button_url' => '/contact',
                ],
                [
                    'type' => 'content', 'side' => 'right', 'anchor' => 'wd-overview',
                    'heading' => 'Mobile App Development Overview',
                    'subheading' => 'We build fast, secure, and user-friendly mobile apps that solve real business problems and deliver great user experiences.',
                    'image' => '/assets/img/mobile.png',
                    'image_alt' => 'Mtaita Tech mobile app development',
                ],
                [
                    'type' => 'service_grid', 'side' => 'right', 'anchor' => 'wd-services',
                    'heading' => 'Mobile App Development Services',
                    'items' => [
                        ['title' => 'Android App Development', 'link' => '/services'],
                        ['title' => 'iOS App Development', 'link' => '/services'],
                        ['title' => 'Flutter Cross-Platform Apps', 'link' => '/services'],
                        ['title' => 'React Native Development', 'link' => '/contact'],
                    ],
                ],
                [
                    'type' => 'type_cards', 'side' => 'right', 'anchor' => 'wd-types',
                    'heading' => 'Types of Mobile Apps We Build',
                    'subheading' => 'From startups to enterprises, we develop mobile solutions tailored to your needs.',
                    'items' => [
                        ['title' => 'E-Commerce Apps', 'text' => 'Mobile shopping apps with cart, checkout, and mobile money integration.'],
                        ['title' => 'Business & CRM Apps', 'text' => 'Custom apps to streamline sales, operations, and customer management.'],
                        ['title' => 'Delivery & Logistics Apps', 'text' => 'Track deliveries, manage orders, and optimize routes in real time.'],
                        ['title' => 'Booking & Reservation Apps', 'text' => 'Seamless booking experiences for hotels, tours, salons, and events.'],
                    ],
                ],
                [
                    'type' => 'projects', 'side' => 'full',
                    'heading' => 'Our Mobile App Projects',
                    'subheading' => 'Explore our latest mobile applications',
                ],
                [
                    'type' => 'city_links', 'side' => 'full',
                    'heading' => 'Mobile App Development Across Tanzania',
                    'subheading' => 'Find expert app developers in your city',
                    'items' => [
                        ['title' => 'Mobile Apps Zanzibar', 'link' => '/mobile-apps-zanzibar'],
                        ['title' => 'App Developers Arusha', 'link' => '/web-designer-arusha'],
                        ['title' => 'App Developers Dar es Salaam', 'link' => '/web-designer-dar-es-salaam'],
                    ],
                ],
            ],
        ];
    }

    private static function seoDigitalMarketing(): array {
        return [
            'title'            => 'SEO & Digital Marketing',
            'card_title'       => 'Social Media Management',
            'description'      => 'Professional social media setup, branding, and management, making your business visible, interactive, and trusted across digital platforms.',
            'short_description'=> 'SEO, social media management, PPC and content marketing that grow your visibility and leads.',
            'page_title'       => 'SEO & Digital Marketing Services | Tanzania',
            'page_desc'        => 'Professional SEO and digital marketing in Kilimanjaro and Tanzania. Grow your online presence, rank higher on Google, attract more customers.',
            'page_keywords'    => 'SEO company Kilimanjaro, digital marketing Tanzania, SEO services Arusha, search engine optimization Tanzania, social media marketing Tanzania, online marketing Tanzania',
            'hero_subtitle'    => 'Data-driven SEO & digital marketing strategies to grow your online presence',
            'image'            => '/assets/img/seo.png',
            'image_alt'        => 'Mtaita Tech SEO and digital marketing',
            'icon'             => 'fa-solid fa-bullhorn',
            'service_category' => 'SEO & Digital Marketing',
            'card_link'        => '/seo-digital-marketing',
            'show_homepage'    => 1,
            'sort_order'       => 4,
            'sections'         => [
                [
                    'type' => 'benefits', 'side' => 'left',
                    'heading' => 'Why Choose Our Digital Marketing Team',
                    'subheading' => 'Strategies built around measurable results, transparent reporting, and long-term growth for your business.',
                    'items' => [
                        ['title' => 'Data-Driven Strategies', 'link' => '#wd-overview'],
                        ['title' => 'On-Page & Off-Page SEO', 'link' => '#wd-services'],
                        ['title' => 'Social Media Management', 'link' => '/services'],
                        ['title' => 'Paid Campaigns (PPC)', 'link' => '#wd-types'],
                        ['title' => 'Content Strategy & Copywriting', 'link' => '/contact'],
                        ['title' => 'Transparent Reporting & Analytics', 'link' => '#faq'],
                    ],
                ],
                [
                    'type' => 'faq', 'side' => 'left', 'anchor' => 'faq',
                    'heading' => 'Frequently Asked Questions',
                    'subheading' => 'Answers to common questions about our SEO & digital marketing services.',
                    'items' => [
                        ['title' => 'Do you offer on-page & off-page SEO optimization?', 'text' => 'Yes, we provide comprehensive on-page and off-page SEO optimization to improve your search engine rankings and visibility.'],
                        ['title' => 'Do you conduct keyword research & competitor analysis?', 'text' => 'Absolutely. We perform in-depth keyword research and competitor analysis to develop a data-driven SEO strategy tailored to your business.'],
                        ['title' => 'Do you provide social media management & content strategy?', 'text' => 'Yes, we manage your social media presence and create content strategies that engage your audience and drive growth.'],
                        ['title' => 'Do you provide Google Analytics & performance reporting?', 'text' => 'Yes, we provide detailed analytics and monthly performance reports so you can track traffic, rankings, and ROI.'],
                        ['title' => 'Do you offer pay-per-click (PPC) campaign management?', 'text' => 'Yes, we manage PPC campaigns across Google Ads and social media platforms to drive targeted traffic and maximize your ad spend ROI.'],
                    ],
                ],
                [
                    'type' => 'cta', 'side' => 'left',
                    'heading' => 'Need Help With SEO & Digital Marketing?',
                    'body' => 'Talk to our team about your goals and get a tailored strategy to grow your online presence.',
                    'button_text' => 'Contact Us', 'button_url' => '/contact',
                ],
                [
                    'type' => 'content', 'side' => 'right', 'anchor' => 'wd-overview',
                    'heading' => 'SEO & Digital Marketing Overview',
                    'subheading' => 'We help businesses attract the right audience, generate qualified leads, and grow sustainably with measurable digital marketing strategies.',
                    'image' => '/assets/img/seo.png',
                    'image_alt' => 'Mtaita Tech SEO and digital marketing',
                ],
                [
                    'type' => 'service_grid', 'side' => 'right', 'anchor' => 'wd-services',
                    'heading' => 'Our Digital Marketing Services',
                    'items' => [
                        ['title' => 'Search Engine Optimization (SEO)', 'link' => '/services'],
                        ['title' => 'Pay-Per-Click (PPC) Advertising', 'link' => '/seo-digital-marketing'],
                        ['title' => 'Social Media Marketing', 'link' => '/seo-digital-marketing'],
                        ['title' => 'Content & Copywriting', 'link' => '/services'],
                        ['title' => 'Email Marketing & Automation', 'link' => '/services'],
                        ['title' => 'Analytics & Performance Reporting', 'link' => '/contact'],
                    ],
                ],
                [
                    'type' => 'type_cards', 'side' => 'right', 'anchor' => 'wd-types',
                    'heading' => 'SEO & Marketing Solutions We Offer',
                    'subheading' => 'Tailored campaigns to help businesses rank higher, convert better, and scale faster.',
                    'items' => [
                        ['title' => 'Local SEO', 'text' => 'Boost visibility for Moshi, Arusha, Dar es Salaam and other Tanzanian cities to attract nearby customers.'],
                        ['title' => 'E-Commerce SEO', 'text' => 'Optimize product pages, categories, and technical SEO to drive more sales from search engines.'],
                        ['title' => 'Technical SEO', 'text' => 'Improve site speed, mobile-friendliness, crawlability, and structured data for better rankings.'],
                        ['title' => 'Google & Meta Ads', 'text' => 'Targeted PPC campaigns on Google, Facebook, Instagram & LinkedIn to reach your ideal customers.'],
                        ['title' => 'Social Media Marketing', 'text' => 'Build brand awareness, grow engagement, and drive conversions with strategic social media campaigns.'],
                        ['title' => 'Content & Link Building', 'text' => 'Create valuable content and earn high-quality backlinks to establish authority in your niche.'],
                    ],
                ],
                [
                    'type' => 'projects', 'side' => 'full',
                    'heading' => 'Our SEO & Digital Marketing Projects',
                    'subheading' => 'Explore our latest marketing campaigns & SEO work',
                ],
                [
                    'type' => 'city_links', 'side' => 'full',
                    'heading' => 'SEO & Digital Marketing Across Tanzania',
                    'subheading' => 'Find expert SEO & digital marketing in your city',
                    'items' => [
                        ['title' => 'SEO Moshi', 'link' => '/seo-moshi'],
                        ['title' => 'SEO Arusha', 'link' => '/seo-arusha'],
                        ['title' => 'SEO Dar es Salaam', 'link' => '/seo-dar-es-salaam'],
                    ],
                ],
            ],
        ];
    }

    private static function tourism(): array {
        return [
            'title'            => 'Tourism Website Development',
            'card_title'       => 'Tourism Website Development',
            'description'      => 'Tour operator, safari, lodge and travel websites built to attract travellers and turn enquiries into bookings.',
            'short_description'=> 'Tour operator, safari, lodge and travel websites with itineraries, galleries and booking enquiries.',
            'page_title'       => 'Tourism Website Development Tanzania | Tour & Safari Websites',
            'page_desc'        => 'Professional tourism website development in Tanzania. Mtaita Tech builds tour operator websites, safari booking systems, tour management dashboards, and digital solutions for travel businesses.',
            'page_keywords'    => 'tourism website development Tanzania, tour operator website design, safari booking system, tour management dashboard, travel agency website Tanzania, lodge website design, Kilimanjaro tours website',
            'hero_subtitle'    => 'Tour operator, safari, lodge & travel website solutions built to attract travellers and turn enquiries into bookings',
            'image'            => '/assets/img/web.png',
            'image_alt'        => 'Mtaita Tech tourism website development',
            'icon'             => 'fa-solid fa-plane',
            'service_category' => '',
            'card_link'        => '/tourism-website-development',
            'show_homepage'    => 0,
            'sort_order'       => 5,
            'sections'         => [
                [
                    'type' => 'benefits', 'side' => 'left',
                    'heading' => 'Why Choose Our Tourism Solutions?',
                    'subheading' => 'We build practical digital solutions that help tour operators showcase experiences, manage enquiries, simplify bookings, and grow their businesses online.',
                    'note' => 'Booking workflows, payment gateways, and management dashboards are delivered as optional custom development, scoped and confirmed with you before implementation.',
                    'items' => [
                        ['title' => 'Custom Tour & Safari Websites', 'link' => '#tourism-overview'],
                        ['title' => 'Tour Packages & Itinerary Management', 'link' => '#tourism-services'],
                        ['title' => 'Online Booking & Enquiry Systems', 'link' => '#tourism-services'],
                        ['title' => 'Secure Payment Integration', 'link' => '/services'],
                        ['title' => 'Tour Management Dashboard', 'link' => '#tourism-types'],
                        ['title' => 'Mobile-Friendly Design', 'link' => '#tourism-overview'],
                        ['title' => 'SEO & Online Visibility', 'link' => '/seo-digital-marketing'],
                        ['title' => 'Ongoing Maintenance & Support', 'link' => '/contact'],
                    ],
                ],
                [
                    'type' => 'faq', 'side' => 'left', 'anchor' => 'tourism-faq',
                    'heading' => 'Frequently Asked Questions',
                    'subheading' => 'Answers to common questions about our tourism website development services.',
                    'items' => [
                        ['title' => 'What is included in a tourism website?', 'text' => 'A tourism website typically includes a homepage, tour and package pages, destination and attraction pages, photo galleries, itinerary layouts, an enquiry form, and contact details. Additional modules such as booking systems or management dashboards are available as custom development.'],
                        ['title' => 'Can you build safari and tour booking systems?', 'text' => 'Yes. We build enquiry and booking systems for safaris and tours. The exact workflow, whether enquiry-only, request-to-book, or full online booking, is defined with you during planning and delivered as custom development.'],
                        ['title' => 'Can visitors book tours directly through our website?', 'text' => 'Yes, where the agreed project scope includes an online booking workflow. Many operators start with an enquiry system and add direct booking and online payments as a later phase.'],
                        ['title' => 'Can you integrate mobile money and online payments?', 'text' => 'Payment integration is available as a custom feature and depends on your chosen provider and account approvals. We integrate suitable gateways, such as mobile money and card processors, once your merchant accounts are ready.'],
                        ['title' => 'Can we manage tours, prices, and bookings through an admin dashboard?', 'text' => 'Yes. A tour management dashboard is available as custom development, allowing your team to manage tours, packages, prices, enquiries, and bookings. The exact features are agreed before development begins.'],
                        ['title' => 'Can the website display itineraries, destinations, galleries, and tour packages?', 'text' => 'Yes. We design destination and attraction pages, day-by-day itineraries, tour package listings, and responsive photo galleries as part of the standard tourism website.'],
                        ['title' => 'Can you build a multilingual tourism website?', 'text' => 'Yes. We can build multilingual tourism websites so you can serve visitors in different languages, such as English and Kiswahili, with a language switcher.'],
                        ['title' => 'Do you provide hosting, domain setup, and maintenance?', 'text' => 'Yes. We assist with domain registration, hosting setup, security configuration, and ongoing maintenance and support as part of our service.'],
                        ['title' => 'Can the website be optimized for search engines?', 'text' => 'Yes. We follow SEO best practices when building your website, and can provide ongoing SEO and digital marketing services to help travellers find your business online.'],
                        ['title' => 'How do we get started?', 'text' => 'Simply contact us with your requirements. We will discuss your goals, recommend the right scope, and provide a quotation and timeline for your tourism website project.'],
                    ],
                ],
                [
                    'type' => 'content', 'side' => 'right', 'anchor' => 'tourism-overview',
                    'heading' => 'Tourism Website Development Overview',
                    'subheading' => 'We create professional tourism websites that help safari operators, tour companies, lodges, and destination businesses showcase their services, attract travellers, and convert enquiries into bookings.',
                    'image' => '/assets/img/web.png',
                    'image_alt' => 'Mtaita Tech tourism website development',
                ],
                [
                    'type' => 'service_grid', 'side' => 'right', 'anchor' => 'tourism-services',
                    'heading' => 'Tourism Website Development Services',
                    'items' => [
                        ['title' => 'Tour & Safari Website Design', 'link' => '#tourism-types'],
                        ['title' => 'Tour Packages & Itinerary Management', 'link' => '#tourism-types'],
                        ['title' => 'Online Booking & Enquiry Systems', 'link' => '/contact'],
                        ['title' => 'Tour Operator Admin Dashboards', 'link' => '/services'],
                        ['title' => 'Mobile Money & Payment Integration', 'link' => '/contact'],
                        ['title' => 'Destination & Attraction Pages', 'link' => '#tourism-types'],
                        ['title' => 'SEO & Online Visibility', 'link' => '/seo-digital-marketing'],
                        ['title' => 'Maintenance & Support', 'link' => '/services'],
                    ],
                ],
                [
                    'type' => 'content', 'side' => 'right',
                    'heading' => 'Tourism Digital Solutions',
                    'subheading' => 'From tour packages and interactive itineraries to enquiry forms, booking workflows, and management dashboards, our solutions are designed around the needs of your tourism business.',
                ],
                [
                    'type' => 'type_cards', 'side' => 'right', 'anchor' => 'tourism-types',
                    'heading' => 'Types of Tourism Websites We Build',
                    'subheading' => 'Digital experiences tailored to different tourism businesses, destinations, and travel services.',
                    'items' => [
                        ['title' => 'Safari Tour Operator Websites', 'text' => 'Showcase safari packages, wildlife destinations, itineraries, accommodation options, and enquiry forms.'],
                        ['title' => 'Tour Company Websites', 'text' => 'Present tours, activities, trip schedules, destinations, pricing information, and customer enquiries.'],
                        ['title' => 'Lodge & Accommodation Websites', 'text' => 'Display rooms, facilities, photo galleries, location information, and booking enquiry options.'],
                        ['title' => 'Destination & Attraction Websites', 'text' => 'Present national parks, waterfalls, cultural experiences, attractions, and visitor information.'],
                        ['title' => 'Mountain Climbing Websites', 'text' => 'Present mountain routes, permits, gear lists, altitude guidance, and climbing itineraries.'],
                        ['title' => 'Travel Agency Websites', 'text' => 'Promote travel packages, flights, transfers, and tailored itineraries with easy enquiry forms.'],
                        ['title' => 'Activity & Excursion Websites', 'text' => 'Highlight day trips, cultural tours, and adventure activities with galleries and booking enquiries.'],
                        ['title' => 'Multi-Language Tourism Portals', 'text' => 'Reach international visitors with localized content, currency options, and multilingual navigation.'],
                    ],
                ],
            ],
        ];
    }
}
