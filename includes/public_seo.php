<?php
declare(strict_types=1);

require_once __DIR__ . '/../cms/config/database.php';

/** Default social-share image (1200 x 630) used when a page has none in the CMS. */
const PUBLIC_DEFAULT_OG_IMAGE = 'uploads/og-default.jpg';

function public_e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function public_base_url(): string
{
    $envUrl = trim((string) getenv('SITE_URL'));
    if ($envUrl !== '') {
        return rtrim($envUrl, '/');
    }

    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ((int) ($_SERVER['SERVER_PORT'] ?? 80) === 443);
    $scheme = $https ? 'https' : 'http';
    $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
    $scriptDir = str_replace('\\', '/', dirname((string) ($_SERVER['SCRIPT_NAME'] ?? '')));
    $basePath = ($scriptDir === '/' || $scriptDir === '.' || $scriptDir === '') ? '' : rtrim($scriptDir, '/');
    if (substr($basePath, -4) === '/cms') {
        $basePath = substr($basePath, 0, -4);
    }

    return $scheme . '://' . $host . $basePath;
}

function public_page_file_map(): array
{
    // The homepage's canonical address is the site root; /home and /index.php redirect there.
    return [
        'home' => ['file' => 'index.php', 'route' => '', 'name' => 'Home'],
        'about' => ['file' => 'about.php', 'route' => 'about', 'name' => 'About'],
        'specialties' => ['file' => 'specialties.php', 'route' => 'specialties', 'name' => 'Specialties'],
        'doctors' => ['file' => 'doctors.php', 'route' => 'doctors', 'name' => 'Doctors'],
        'blog_index' => ['file' => 'blog.php', 'route' => 'blog', 'name' => 'Blog'],
        'contact' => ['file' => 'contact.php', 'route' => 'contact', 'name' => 'Contact'],
        'privacy' => ['file' => 'privacy.php', 'route' => 'privacy', 'name' => 'Privacy Policy'],
        'terms' => ['file' => 'terms.php', 'route' => 'terms', 'name' => 'Terms & Conditions'],
        'accessibility' => ['file' => 'accessibility.php', 'route' => 'accessibility', 'name' => 'Accessibility'],
    ];
}

function public_route_for_page_type(string $pageType): string
{
    $map = public_page_file_map();
    $row = $map[$pageType] ?? $map['home'];

    return $row['route'];
}

function public_file_for_page_type(string $pageType): string
{
    $map = public_page_file_map();
    $row = $map[$pageType] ?? $map['home'];

    return $row['file'];
}

function public_page_url(string $pageType): string
{
    return public_base_url() . '/' . public_route_for_page_type($pageType);
}

/**
 * Fallback titles and descriptions, used when the CMS has no SEO row for a page.
 * Titles stay within ~60 characters and descriptions within ~155 so search results do not cut them off.
 */
function public_static_seo_defaults(string $pageType): array
{
    $defaults = [
        'home' => [
            'title' => 'NMC Clinic Butwal | Specialists, Lab Tests & Physiotherapy',
            'description' => "Specialist doctors, in-house lab tests and diagnostics, physiotherapy, women's health and kidney care at NMC Clinic, Traffic Chowk, Butwal. Book a visit.",
        ],
        'about' => [
            'title' => 'About NMC Clinic | Community Healthcare in Butwal',
            'description' => "NMC is a community clinic in Butwal bringing specialist consultation, diagnostics, physiotherapy, women's health and kidney care together under one roof.",
        ],
        'specialties' => [
            'title' => 'Medical Specialties in Butwal | NMC Clinic',
            'description' => 'Specialist consultation, advanced diagnostics, laboratory services, physiotherapy, obstetrics & gynecology and kidney care at NMC Clinic, Butwal.',
        ],
        'doctors' => [
            'title' => 'Doctors in Butwal | NMC Clinic Medical Team',
            'description' => 'Meet the doctors at NMC Clinic Butwal. See specialties and clinic days for general medicine, gynecology, nephrology and physiotherapy, then book a visit.',
        ],
        'blog_index' => [
            'title' => 'Health Guides & Articles | NMC Clinic Butwal',
            'description' => "Practical health articles from the NMC Clinic team in Butwal, covering diagnostics, women's health, physiotherapy, kidney care and everyday wellness.",
        ],
        'contact' => [
            'title' => 'Contact NMC Clinic | Branches, Map & Opening Hours',
            'description' => 'Find NMC Clinic branches in Butwal and Bhairahawa. Get directions, phone numbers and opening hours, or send our reception team a message.',
        ],
    ];

    return $defaults[$pageType] ?? [
        'title' => 'NMC Clinic Butwal',
        'description' => 'NMC Clinic in Butwal provides specialist consultation and clinical services.',
    ];
}

function public_seo_for_page_type(string $pageType): array
{
    $default = public_static_seo_defaults($pageType);
    $canonical = public_page_url($pageType);
    $defaultImage = public_base_url() . '/' . PUBLIC_DEFAULT_OG_IMAGE;

    try {
        $stmt = db()->prepare(
            'SELECT sm.*, m.file_path AS og_image_path
             FROM seo_metadata sm
             INNER JOIN pages p ON p.id = sm.page_id
             LEFT JOIN media_assets m ON m.id = sm.og_image_id AND m.deleted_at IS NULL
             WHERE p.page_type = :page_type
               AND p.deleted_at IS NULL
               AND sm.page_id IS NOT NULL
             ORDER BY p.status = "published" DESC, sm.updated_at DESC
             LIMIT 1'
        );
        $stmt->execute([':page_type' => $pageType]);
        $row = $stmt->fetch();
    } catch (Throwable $exception) {
        $row = null;
    }

    if (!$row) {
        return [
            'page_type' => $pageType,
            'title' => $default['title'],
            'description' => $default['description'],
            'canonical' => $canonical,
            'robots' => 'index, follow, max-image-preview:large',
            'og_title' => $default['title'],
            'og_description' => $default['description'],
            'og_image' => $defaultImage,
            'og_image_is_default' => true,
            'twitter_card' => 'summary_large_image',
            'schema_json' => null,
        ];
    }

    $ogImage = $row['og_image_path'] ? public_base_url() . '/' . ltrim((string) $row['og_image_path'], '/') : null;

    return [
        'page_type' => $pageType,
        'title' => (string) $row['meta_title'],
        'description' => (string) $row['meta_description'],
        // Seeded rows point at localhost .html files; only trust a canonical an editor actually set.
        'canonical' => ($row['canonical_url'] && !preg_match('#localhost|\.html$#', (string) $row['canonical_url']))
            ? (string) $row['canonical_url'] : $canonical,
        'robots' => ((int) $row['robots_index'] === 1 ? 'index' : 'noindex') . ', ' . ((int) $row['robots_follow'] === 1 ? 'follow' : 'nofollow')
            . ((int) $row['robots_index'] === 1 ? ', max-image-preview:large' : ''),
        'og_title' => (string) ($row['og_title'] ?: $row['meta_title']),
        'og_description' => (string) ($row['og_description'] ?: $row['meta_description']),
        'og_image' => $ogImage ?? $defaultImage,
        'og_image_is_default' => $ogImage === null,
        'twitter_card' => (string) $row['twitter_card'],
        'schema_json' => $row['schema_json_override'] ?: null,
    ];
}

function public_render_seo_meta(array $seo, string $ogType = 'website'): void
{
    ?>
<title><?= public_e($seo['title']) ?></title>
<meta name="description" content="<?= public_e($seo['description']) ?>">
<link rel="canonical" href="<?= public_e($seo['canonical']) ?>">
<meta name="robots" content="<?= public_e($seo['robots']) ?>">
<meta property="og:site_name" content="NMC Clinic">
<meta property="og:locale" content="en_US">
<meta property="og:type" content="<?= public_e($ogType) ?>">
<meta property="og:title" content="<?= public_e($seo['og_title']) ?>">
<meta property="og:description" content="<?= public_e($seo['og_description']) ?>">
<meta property="og:url" content="<?= public_e($seo['canonical']) ?>">
<?php if (!empty($seo['og_image'])): ?>
<meta property="og:image" content="<?= public_e($seo['og_image']) ?>">
<?php if (!empty($seo['og_image_is_default'])): ?>
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="The NMC Clinic team at the Butwal clinic">
<?php endif; ?>
<meta name="twitter:image" content="<?= public_e($seo['og_image']) ?>">
<?php endif; ?>
<meta name="twitter:card" content="<?= public_e($seo['twitter_card']) ?>">
<meta name="twitter:title" content="<?= public_e($seo['og_title']) ?>">
<meta name="twitter:description" content="<?= public_e($seo['og_description']) ?>">
<?php if (!empty($seo['schema_json'])): ?>
<script type="application/ld+json"><?= $seo['schema_json'] ?></script>
<?php endif; ?>
    <?php
}

/** Favicons, theme colour and the hero background preload (the largest image above the fold). */
function public_render_head_assets(bool $preloadHero = true): void
{
    $base = public_e(public_base_url());
    ?>
<link rel="icon" href="<?= $base ?>/uploads/favicon-32.png" type="image/png" sizes="32x32">
<link rel="apple-touch-icon" href="<?= $base ?>/uploads/apple-touch-icon.png">
<meta name="theme-color" content="#286963">
<?php if ($preloadHero): ?>
<link rel="preload" as="image" href="<?= $base ?>/uploads/backgound.webp" type="image/webp" fetchpriority="high">
<?php endif; ?>
    <?php
}

/* ---------- Structured data (schema.org JSON-LD) ------------------------- */

function public_schema_clinic_data(): array
{
    $data = [
        'name' => 'NMC Clinic',
        'phone' => '+977 71-000000',
        'email' => 'info@nmcbutwal.com.np',
        'address' => ['street' => 'Traffic Chowk', 'city' => 'Butwal', 'region' => 'Rupandehi', 'country' => 'NP'],
        'geo' => ['lat' => '27.7000', 'lng' => '83.4480'],
        'hours' => [],
        'socials' => [],
        'branches' => [],
    ];

    try {
        $settings = db()->query('SELECT * FROM business_contact_settings ORDER BY id ASC LIMIT 1')->fetch();
        if ($settings) {
            $data['name'] = (string) ($settings['clinic_name'] ?: $data['name']);
            $data['phone'] = (string) ($settings['primary_phone'] ?: $data['phone']);
            $data['email'] = (string) ($settings['email'] ?: $data['email']);
            $data['address'] = [
                'street' => (string) $settings['address_line'],
                'city' => (string) $settings['city'],
                'region' => (string) ($settings['district'] ?? ''),
                'country' => strtolower((string) $settings['country']) === 'nepal' ? 'NP' : (string) $settings['country'],
            ];
            if ($settings['latitude'] !== null && $settings['longitude'] !== null) {
                $data['geo'] = ['lat' => (string) $settings['latitude'], 'lng' => (string) $settings['longitude']];
            }
        }

        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        foreach (db()->query('SELECT day_of_week, opens_at, closes_at, is_closed FROM opening_hours ORDER BY day_of_week')->fetchAll() as $hour) {
            if ((int) $hour['is_closed'] === 1 || !$hour['opens_at'] || !$hour['closes_at']) {
                continue;
            }
            $data['hours'][] = [
                'day' => $days[(int) $hour['day_of_week']] ?? '',
                'opens' => substr((string) $hour['opens_at'], 0, 5),
                'closes' => substr((string) $hour['closes_at'], 0, 5),
            ];
        }

        foreach (db()->query('SELECT url FROM social_links WHERE is_visible = 1 AND url <> "" ORDER BY sort_order')->fetchAll() as $social) {
            $data['socials'][] = (string) $social['url'];
        }

        $data['branches'] = db()->query(
            'SELECT slug, name, address_line, city, district, country, phone, latitude, longitude
             FROM clinic_branches
             WHERE deleted_at IS NULL AND is_visible = 1
             ORDER BY is_primary DESC, sort_order ASC, name ASC'
        )->fetchAll();
    } catch (Throwable $exception) {
        // Missing tables or no database: keep the built-in clinic details.
    }

    if ($data['hours'] === []) {
        foreach (['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'] as $day) {
            $data['hours'][] = ['day' => $day, 'opens' => '08:00', 'closes' => '20:00'];
        }
        $data['hours'][] = ['day' => 'Saturday', 'opens' => '09:00', 'closes' => '14:00'];
    }

    return $data;
}

function public_schema_address(array $parts): array
{
    return array_filter([
        '@type' => 'PostalAddress',
        'streetAddress' => $parts['street'] ?? '',
        'addressLocality' => $parts['city'] ?? '',
        'addressRegion' => $parts['region'] ?? '',
        'addressCountry' => $parts['country'] ?? 'NP',
    ], static function ($value): bool {
        return $value !== '';
    });
}

function public_schema_hours(array $hours): array
{
    // Group days that share the same hours into one specification.
    $groups = [];
    foreach ($hours as $hour) {
        $key = $hour['opens'] . '-' . $hour['closes'];
        $groups[$key]['opens'] = $hour['opens'];
        $groups[$key]['closes'] = $hour['closes'];
        $groups[$key]['days'][] = $hour['day'];
    }

    $specs = [];
    foreach ($groups as $group) {
        $specs[] = [
            '@type' => 'OpeningHoursSpecification',
            'dayOfWeek' => $group['days'],
            'opens' => $group['opens'],
            'closes' => $group['closes'],
        ];
    }

    return $specs;
}

/**
 * One @graph per page: the clinic (with each branch as a department), the website,
 * this page, and a breadcrumb trail on inner pages.
 */
function public_schema_graph(array $seo): array
{
    $base = public_base_url();
    $clinicId = $base . '/#clinic';
    $websiteId = $base . '/#website';
    $pageType = (string) ($seo['page_type'] ?? 'home');
    $data = public_schema_clinic_data();

    $clinic = [
        '@type' => 'MedicalClinic',
        '@id' => $clinicId,
        'name' => $data['name'],
        'url' => $base . '/',
        'logo' => $base . '/uploads/logo.jpeg',
        'image' => $base . '/' . PUBLIC_DEFAULT_OG_IMAGE,
        'telephone' => $data['phone'],
        'email' => $data['email'],
        'address' => public_schema_address($data['address']),
        'geo' => ['@type' => 'GeoCoordinates', 'latitude' => (float) $data['geo']['lat'], 'longitude' => (float) $data['geo']['lng']],
        'openingHoursSpecification' => public_schema_hours($data['hours']),
        'areaServed' => ['Butwal', 'Bhairahawa', 'Rupandehi'],
        'availableService' => array_map(static function (string $name): array {
            return ['@type' => 'MedicalProcedure', 'name' => $name];
        }, ['Specialist consultation', 'Laboratory services', 'Diagnostic imaging', 'Physiotherapy and rehabilitation', 'Obstetrics and gynecology', 'Nephrology and kidney care']),
    ];
    if ($data['socials'] !== []) {
        $clinic['sameAs'] = $data['socials'];
    }

    $departments = [];
    foreach ($data['branches'] as $branch) {
        $department = [
            '@type' => 'MedicalClinic',
            '@id' => $base . '/#branch-' . $branch['slug'],
            'name' => 'NMC Clinic, ' . $branch['name'],
            'address' => public_schema_address([
                'street' => (string) $branch['address_line'],
                'city' => (string) $branch['city'],
                'region' => (string) ($branch['district'] ?? ''),
                'country' => strtolower((string) $branch['country']) === 'nepal' ? 'NP' : (string) $branch['country'],
            ]),
            'parentOrganization' => ['@id' => $clinicId],
        ];
        if (!empty($branch['phone'])) {
            $department['telephone'] = (string) $branch['phone'];
        }
        if ($branch['latitude'] !== null && $branch['longitude'] !== null) {
            $department['geo'] = ['@type' => 'GeoCoordinates', 'latitude' => (float) $branch['latitude'], 'longitude' => (float) $branch['longitude']];
            $department['hasMap'] = 'https://www.google.com/maps?q=' . $branch['latitude'] . ',' . $branch['longitude'];
        }
        $departments[] = $department;
    }
    if ($departments !== []) {
        $clinic['department'] = array_map(static function (array $department): array {
            return ['@id' => $department['@id']];
        }, $departments);
    }

    $page = [
        '@type' => $pageType === 'about' ? 'AboutPage' : ($pageType === 'contact' ? 'ContactPage' : ($pageType === 'blog_index' ? 'CollectionPage' : 'WebPage')),
        '@id' => $seo['canonical'] . '#webpage',
        'url' => $seo['canonical'],
        'name' => $seo['title'],
        'description' => $seo['description'],
        'isPartOf' => ['@id' => $websiteId],
        'about' => ['@id' => $clinicId],
        'inLanguage' => 'en',
    ];
    if (!empty($seo['og_image'])) {
        $page['primaryImageOfPage'] = ['@type' => 'ImageObject', 'url' => $seo['og_image']];
    }

    $graph = [
        $clinic,
        [
            '@type' => 'WebSite',
            '@id' => $websiteId,
            'url' => $base . '/',
            'name' => 'NMC Clinic',
            'publisher' => ['@id' => $clinicId],
            'inLanguage' => 'en',
        ],
        $page,
    ];

    if ($pageType !== 'home') {
        $map = public_page_file_map();
        $breadcrumbId = $seo['canonical'] . '#breadcrumb';
        $page['breadcrumb'] = ['@id' => $breadcrumbId];
        $graph[2] = $page;
        $graph[] = [
            '@type' => 'BreadcrumbList',
            '@id' => $breadcrumbId,
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $base . '/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => $map[$pageType]['name'] ?? $seo['title'], 'item' => $seo['canonical']],
            ],
        ];
    }

    return ['@context' => 'https://schema.org', '@graph' => array_merge($graph, $departments)];
}

function public_render_schema(array $seo): void
{
    // JSON_HEX_TAG keeps "</script>" sequences from ever closing the tag early.
    echo '<script type="application/ld+json">'
        . json_encode(public_schema_graph($seo), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_THROW_ON_ERROR)
        . "</script>\n";
}

/** Everything a public page needs in <head> for search and sharing. */
function public_render_page_head(string $pageType): void
{
    $seo = public_seo_for_page_type($pageType);
    public_render_seo_meta($seo);
    public_render_head_assets();
    public_render_schema($seo);
}

function public_sitemap_urls(): array
{
    $baseUrl = public_base_url();
    $urls = [];

    foreach (public_page_file_map() as $pageType => $page) {
        $file = $page['file'];
        $route = $page['route'];
        if (is_file(dirname(__DIR__) . '/' . $file)) {
            $urls[$route] = [
                'loc' => $baseUrl . '/' . $route,
                'lastmod' => date('Y-m-d', filemtime(dirname(__DIR__) . '/' . $file)),
                'priority' => $pageType === 'home' ? '1.0' : '0.8',
            ];
        }
    }

    try {
        $stmt = db()->query(
            'SELECT page_type, updated_at
             FROM pages
             WHERE deleted_at IS NULL AND status = "published"'
        );
        foreach ($stmt->fetchAll() as $row) {
            $route = public_route_for_page_type((string) $row['page_type']);
            if (isset($urls[$route])) {
                $urls[$route]['lastmod'] = substr((string) $row['updated_at'], 0, 10);
            }
        }
    } catch (Throwable $exception) {
    }

    if (is_file(dirname(__DIR__) . '/blog-detail.php')) {
        try {
            $stmt = db()->query(
                'SELECT slug, updated_at
                 FROM blog_posts
                 WHERE deleted_at IS NULL AND status = "published"
                 ORDER BY published_at DESC, sort_order ASC'
            );
            foreach ($stmt->fetchAll() as $row) {
                $slug = (string) $row['slug'];
                if ($slug === '') {
                    continue;
                }
                $urls['blog/' . $slug] = [
                    'loc' => $baseUrl . '/blog/' . rawurlencode($slug),
                    'lastmod' => substr((string) $row['updated_at'], 0, 10),
                    'priority' => '0.7',
                ];
            }
        } catch (Throwable $exception) {
        }
    }

    return array_values($urls);
}
