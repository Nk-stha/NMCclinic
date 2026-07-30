<?php
declare(strict_types=1);

require_once __DIR__ . '/../cms/config/database.php';

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
    return [
        'home' => ['file' => 'index.html', 'route' => 'home'],
        'about' => ['file' => 'about.html', 'route' => 'about'],
        'specialties' => ['file' => 'specialties.html', 'route' => 'specialties'],
        'doctors' => ['file' => 'doctors.html', 'route' => 'doctors'],
        'blog_index' => ['file' => 'blog.html', 'route' => 'blog'],
        'contact' => ['file' => 'contact.html', 'route' => 'contact'],
        'privacy' => ['file' => 'privacy.html', 'route' => 'privacy'],
        'terms' => ['file' => 'terms.html', 'route' => 'terms'],
        'accessibility' => ['file' => 'accessibility.html', 'route' => 'accessibility'],
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

function public_static_seo_defaults(string $pageType): array
{
    $defaults = [
        'home' => [
            'title' => 'NMC Clinic Butwal | Specialist Care, Diagnostics & Physiotherapy',
            'description' => "Specialist consultations, in-house diagnostics, laboratory services, physiotherapy, women's healthcare and kidney care at NMC Clinic, Traffic Chowk, Butwal.",
        ],
        'about' => [
            'title' => 'About NMC Clinic Butwal | Our Mission, Values and Story',
            'description' => "NMC is a community clinic in Butwal bringing specialist consultation, diagnostics, physiotherapy, women's health and kidney care together under one roof.",
        ],
        'specialties' => [
            'title' => 'Specialties at NMC Clinic Butwal | Consultation, Diagnostics & More',
            'description' => 'Specialist consultation, advanced diagnostics, laboratory services, physiotherapy, obstetrics & gynecology and kidney care at NMC Clinic, Butwal.',
        ],
        'doctors' => [
            'title' => 'Our Doctors | NMC Clinic Butwal Medical Team',
            'description' => 'Meet the doctors at NMC Clinic Butwal. View specialties, qualifications and consultation schedules across general medicine, gynecology, nephrology and physiotherapy.',
        ],
        'blog_index' => [
            'title' => 'Health Blog | NMC Clinic Butwal',
            'description' => "Practical health articles from the NMC Clinic team in Butwal, covering diagnostics, women's health, physiotherapy, kidney care and everyday wellness.",
        ],
        'contact' => [
            'title' => 'Contact NMC Clinic Butwal | Address, Phone & Opening Hours',
            'description' => 'Find NMC Clinic at Traffic Chowk, Butwal. Phone, email, opening hours, map directions, and a message form for appointments and enquiries.',
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
    $canonical = public_base_url() . '/' . public_route_for_page_type($pageType);

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
            'title' => $default['title'],
            'description' => $default['description'],
            'canonical' => $canonical,
            'robots' => 'index, follow',
            'og_title' => $default['title'],
            'og_description' => $default['description'],
            'og_image' => null,
            'twitter_card' => 'summary_large_image',
            'schema_json' => null,
        ];
    }

    return [
        'title' => (string) $row['meta_title'],
        'description' => (string) $row['meta_description'],
        'canonical' => (string) ($row['canonical_url'] ?: $canonical),
        'robots' => ((int) $row['robots_index'] === 1 ? 'index' : 'noindex') . ', ' . ((int) $row['robots_follow'] === 1 ? 'follow' : 'nofollow'),
        'og_title' => (string) ($row['og_title'] ?: $row['meta_title']),
        'og_description' => (string) ($row['og_description'] ?: $row['meta_description']),
        'og_image' => $row['og_image_path'] ? public_base_url() . '/' . ltrim((string) $row['og_image_path'], '/') : null,
        'twitter_card' => (string) $row['twitter_card'],
        'schema_json' => $row['schema_json_override'] ?: null,
    ];
}

function public_render_seo_meta(array $seo): void
{
    ?>
<title><?= public_e($seo['title']) ?></title>
<meta name="description" content="<?= public_e($seo['description']) ?>">
<link rel="canonical" href="<?= public_e($seo['canonical']) ?>">
<meta name="robots" content="<?= public_e($seo['robots']) ?>">
<meta property="og:title" content="<?= public_e($seo['og_title']) ?>">
<meta property="og:description" content="<?= public_e($seo['og_description']) ?>">
<meta property="og:url" content="<?= public_e($seo['canonical']) ?>">
<meta property="og:type" content="website">
<?php if (!empty($seo['og_image'])): ?>
<meta property="og:image" content="<?= public_e($seo['og_image']) ?>">
<?php endif; ?>
<meta name="twitter:card" content="<?= public_e($seo['twitter_card']) ?>">
<meta name="twitter:title" content="<?= public_e($seo['og_title']) ?>">
<meta name="twitter:description" content="<?= public_e($seo['og_description']) ?>">
<?php if (!empty($seo['schema_json'])): ?>
<script type="application/ld+json"><?= $seo['schema_json'] ?></script>
<?php endif; ?>
    <?php
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
