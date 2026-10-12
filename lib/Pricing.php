<?php

/**
 * Shared service-category definitions and helpers for the pricing feature.
 * Both the public pricing page and the admin pricing manager use this list
 * so the slugs always stay in sync.
 */
class Pricing
{
    public static function services(): array
    {
        return [
            'web-development' => [
                'label' => 'Web Development',
                'description' => 'Complete web solutions, design, development, hosting, SEO, and ongoing management for schools, businesses, and institutions.',
            ],
            'software-solutions' => [
                'label' => 'Software Solutions',
                'description' => 'Custom business systems, management software, and automation built around the way your organisation actually works.',
            ],
            'tourism-digital' => [
                'label' => 'Tourism Digital Solutions',
                'description' => 'Tour operator websites, safari booking systems, and digital solutions for travel, hospitality, and destination businesses.',
            ],
            'graphics-design' => [
                'label' => 'Graphics Design',
                'description' => 'Logos, brand identities, marketing materials, and visual design that make your business stand out.',
            ],
            'social-media' => [
                'label' => 'Social Media Management',
                'description' => 'Professional social media setup, branding, and ongoing management to grow your visibility and engagement.',
            ],
            'ict-consultancy' => [
                'label' => 'ICT Consultancy & Training',
                'description' => 'ICT strategy, systems advice, and professional training to build your team\'s digital capacity.',
            ],
            'ict-infrastructure' => [
                'label' => 'ICT Infrastructure',
                'description' => 'Network setup, hardware, cloud, and infrastructure solutions for reliable day-to-day business operations.',
            ],
            'computer-maintenance' => [
                'label' => 'Computer Maintenance',
                'description' => 'Computer repair, servicing, and preventive maintenance to keep your equipment running efficiently.',
            ],
        ];
    }

    public static function isValidService(string $slug): bool
    {
        return array_key_exists($slug, self::services());
    }

    public static function label(string $slug): string
    {
        $services = self::services();
        return $services[$slug]['label'] ?? ucwords(str_replace('-', ' ', $slug));
    }

    /**
     * Fetch active pricing packages grouped by service slug.
     * Returns an empty array when the table does not exist yet.
     */
    public static function packagesByService(PDO $pdo): array
    {
        $grouped = [];
        try {
            $rows = $pdo->query("SELECT * FROM service_pricing WHERE is_active = 1 ORDER BY service_slug ASC, sort_order ASC, id ASC")->fetchAll();
            foreach ($rows as $row) {
                $grouped[$row['service_slug']][] = $row;
            }
        } catch (Exception $e) {
            return [];
        }
        return $grouped;
    }

    public static function usdRate(): float
    {
        return (float)Settings::get('usd_rate', '0');
    }
}
