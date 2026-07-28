<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/public_seo.php';

$slug = trim((string) ($_GET['slug'] ?? ''));
if (!preg_match('/^[a-z0-9-]+$/', $slug)) {
    public_blog_not_found();
}

$post = public_blog_find_post($slug);
if ($post === null) {
    public_blog_not_found();
}

$relatedPosts = public_blog_related_posts((int) $post['id'], $post['category_id'] !== null ? (int) $post['category_id'] : null);
$baseUrl = public_base_url();
$canonical = $baseUrl . '/blog/' . rawurlencode((string) $post['slug']);
$title = (string) $post['title'];
$description = (string) $post['excerpt'];
$category = (string) ($post['category_name'] ?? 'Health Guides');
$reviewer = (string) ($post['reviewer_name'] ?: 'NMC medical team');
$reviewedAt = public_blog_month($post['reviewed_at'] ?: $post['published_at']);
$readTime = $post['reading_time_minutes'] !== null ? (int) $post['reading_time_minutes'] . ' min read' : 'Health guide';
$imageUrl = $post['image_path'] ? $baseUrl . '/' . ltrim((string) $post['image_path'], '/') : null;
$bodyHtml = trim((string) ($post['body_html_sanitized'] ?? ''));
if ($bodyHtml === '') {
    $bodyHtml = '<p>' . public_e($description) . '</p>';
}

$seo = public_blog_seo((int) $post['id'], $title, $description, $canonical, $imageUrl);
$schema = public_blog_schema($post, $canonical, $imageUrl);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= public_e($seo['title']) ?></title>
<meta name="description" content="<?= public_e($seo['description']) ?>">
<link rel="canonical" href="<?= public_e($seo['canonical']) ?>">
<meta name="robots" content="<?= public_e($seo['robots']) ?>">
<meta property="og:title" content="<?= public_e($seo['og_title']) ?>">
<meta property="og:description" content="<?= public_e($seo['og_description']) ?>">
<meta property="og:url" content="<?= public_e($seo['canonical']) ?>">
<meta property="og:type" content="article">
<?php if (!empty($seo['og_image'])): ?>
<meta property="og:image" content="<?= public_e($seo['og_image']) ?>">
<?php endif; ?>
<meta name="twitter:card" content="<?= public_e($seo['twitter_card']) ?>">
<meta name="twitter:title" content="<?= public_e($seo['og_title']) ?>">
<meta name="twitter:description" content="<?= public_e($seo['og_description']) ?>">
<script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) ?></script>
<?php if (!empty($seo['schema_json'])): ?>
<script type="application/ld+json"><?= $seo['schema_json'] ?></script>
<?php endif; ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= public_e($baseUrl) ?>/assets/style.css">
</head>
<body>
<div class="page">

  <header class="site-header">
    <div class="topbar">
      <div class="topbar-in">
        <span class="topbar-item">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#FDCA00" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
          <span class="topbar-label">Emergency:</span> <a class="topbar-em" href="tel:+97771000000">+977 71-000000</a>
        </span>
        <span class="topbar-item">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#7FD4CA" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"></rect><path d="m22 7-10 6L2 7"></path></svg>
          <a class="topbar-mail" href="mailto:info@nmcbutwal.com.np">info@nmcbutwal.com.np</a>
        </span>
        <span class="topbar-item">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#7FD4CA" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"></circle><polyline points="12 7 12 12 15 14"></polyline></svg>
          <span class="topbar-hours">Sun-Fri 8 AM-8 PM · Sat 9 AM-2 PM</span>
        </span>
        <div class="topbar-social">
          <a class="social-btn" href="#" aria-label="Facebook"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg></a>
          <a class="social-btn" href="#" aria-label="Instagram"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5"></rect><circle cx="12" cy="12" r="4"></circle><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg></a>
          <a class="social-btn" href="https://wa.me/9779800000000" aria-label="WhatsApp"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2 22l5.25-1.38c1.45.79 3.08 1.21 4.79 1.21h.01c5.46 0 9.91-4.45 9.91-9.91C21.96 6.45 17.5 2 12.04 2z"></path></svg></a>
        </div>
      </div>
    </div>

    <div class="header-bar">
      <a class="brand" href="<?= public_e($baseUrl) ?>/index.html">
        <img src="<?= public_e($baseUrl) ?>/uploads/logo.jpeg" alt="NMC clinic logo" width="48" height="48">
        <span class="brand-text"><span class="brand-name">NMC</span><span class="brand-sub">Clinic ·&nbsp;</span></span>
      </a>
      <nav class="nav-main" aria-label="Primary">
        <a href="<?= public_e($baseUrl) ?>/index.html">Home</a>
        <a href="<?= public_e($baseUrl) ?>/about.html">About</a>
        <a href="<?= public_e($baseUrl) ?>/specialties.html">Specialties</a>
        <a href="<?= public_e($baseUrl) ?>/doctors.html">Doctors</a>
        <a href="<?= public_e($baseUrl) ?>/blog.html" aria-current="page">Blog</a>
        <a href="<?= public_e($baseUrl) ?>/contact.html">Contact</a>
      </nav>
      <div class="header-actions">
        <a class="header-phone" href="tel:+97771000000">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#287772" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
          +977 71-000000
        </a>
        <a class="btn btn-primary" href="<?= public_e($baseUrl) ?>/index.html#appointment">Book Appointment</a>
      </div>
      <button class="nav-toggle" type="button" aria-label="Open menu" aria-expanded="false">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="4" y1="7" x2="20" y2="7"></line><line x1="4" y1="12" x2="20" y2="12"></line><line x1="4" y1="17" x2="20" y2="17"></line></svg>
      </button>
    </div>
    <div class="nav-mobile">
      <a href="<?= public_e($baseUrl) ?>/index.html">Home</a>
      <a href="<?= public_e($baseUrl) ?>/about.html">About</a>
      <a href="<?= public_e($baseUrl) ?>/specialties.html">Specialties</a>
      <a href="<?= public_e($baseUrl) ?>/doctors.html">Doctors</a>
      <a href="<?= public_e($baseUrl) ?>/blog.html" aria-current="page">Blog</a>
      <a href="<?= public_e($baseUrl) ?>/contact.html">Contact</a>
      <div class="nav-mobile-cta">
        <a class="m-book" href="<?= public_e($baseUrl) ?>/index.html#appointment">Book Appointment</a>
        <a class="m-call" href="tel:+97771000000">Call +977 71-000000</a>
      </div>
    </div>
  </header>

  <section class="banner article-banner" id="top">
    <div class="banner-veil is-strong"></div>
    <div class="banner-in narrow">
      <div class="crumb">
        <a href="<?= public_e($baseUrl) ?>/index.html">Home</a>
        <span class="sep">/</span>
        <a href="<?= public_e($baseUrl) ?>/blog.html">Blog</a>
        <span class="sep">/</span>
        <span class="here"><?= public_e($category) ?></span>
      </div>
      <span class="tag-chip"><?= public_e($category) ?></span>
      <h1><?= public_e($title) ?></h1>
      <div class="article-meta">
        <span>Reviewed by <?= public_e($reviewer) ?></span>
        <?php if ($reviewedAt !== ''): ?><span>Reviewed <?= public_e($reviewedAt) ?></span><?php endif; ?>
        <span><?= public_e($readTime) ?></span>
      </div>
    </div>
  </section>

  <div class="wrap-narrow featured-img-wrap">
    <div class="photo-ph ratio-16x8">
      <?php if ($imageUrl): ?>
        <img src="<?= public_e($imageUrl) ?>" alt="<?= public_e((string) ($post['image_alt'] ?: $title)) ?>">
      <?php else: ?>
        <span>article image</span>
      <?php endif; ?>
    </div>
  </div>

  <section class="wrap article-layout">
    <article class="article-body">
      <?= $bodyHtml ?>

      <div class="notice notice-warn disclaimer">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#124E4A" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
        <p>This article is general health information and does not replace professional medical advice. Please speak with a qualified doctor about your own symptoms and care.</p>
      </div>
    </article>

    <aside class="article-side">
      <div class="side-card">
        <div class="reviewer">
          <span class="reviewer-avatar"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg></span>
          <div>
            <div class="reviewer-name"><?= public_e($reviewer) ?></div>
            <div class="reviewer-role">Reviewed for accuracy</div>
          </div>
        </div>
        <p>Our health guides are written in plain language and reviewed by NMC clinicians before publishing.</p>
      </div>
      <div class="side-card">
        <h3>Have a concern?</h3>
        <p>Book a consultation and talk it through with one of our doctors.</p>
        <a class="btn btn-primary btn-block" href="<?= public_e($baseUrl) ?>/index.html#appointment">Book an Appointment</a>
      </div>
    </aside>
  </section>

  <?php if ($relatedPosts !== []): ?>
  <section class="band-light">
    <div class="wrap section-sm">
      <div class="center head-block">
        <span class="eyebrow">Keep reading</span>
        <h2 class="section-title">Related Articles</h2>
      </div>
      <div class="grid grid-300 fill-300">
        <?php foreach ($relatedPosts as $related): ?>
          <a class="post-card" href="<?= public_e($baseUrl) ?>/blog/<?= public_e((string) $related['slug']) ?>">
            <div class="post-photo">
              <?php if (!empty($related['image_path'])): ?>
                <img src="<?= public_e($baseUrl . '/' . ltrim((string) $related['image_path'], '/')) ?>" alt="<?= public_e((string) ($related['image_alt'] ?: $related['title'])) ?>" loading="lazy">
              <?php else: ?>
                <span class="ph-label">article image</span>
              <?php endif; ?>
              <span class="doc-tag"><?= public_e((string) ($related['category_name'] ?? 'Health Guides')) ?></span>
            </div>
            <div class="post-body">
              <h3><?= public_e((string) $related['title']) ?></h3>
              <p><?= public_e((string) $related['excerpt']) ?></p>
              <span class="link-cta inline">Read article</span>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <footer class="site-footer">
    <div class="footer-in">
      <div class="footer-grid">
        <div>
          <div class="footer-brand">
            <img src="<?= public_e($baseUrl) ?>/uploads/logo.jpeg" alt="NMC clinic logo" width="44" height="44" loading="lazy">
            <span>NMC</span>
          </div>
          <p class="footer-about">A community clinic in Butwal offering specialist consultation, diagnostics, physiotherapy, women's health, and kidney care under one roof.</p>
          <a class="footer-phone" href="tel:+97771000000">+977 71-000000</a>
        </div>
        <div>
          <h4>Quick Links</h4>
          <div class="footer-list">
            <a href="<?= public_e($baseUrl) ?>/index.html">Home</a>
            <a href="<?= public_e($baseUrl) ?>/about.html">About</a>
            <a href="<?= public_e($baseUrl) ?>/specialties.html">Specialties</a>
            <a href="<?= public_e($baseUrl) ?>/doctors.html">Doctors</a>
            <a href="<?= public_e($baseUrl) ?>/blog.html">Blog</a>
          </div>
        </div>
        <div>
          <h4>Services</h4>
          <div class="footer-list">
            <a href="<?= public_e($baseUrl) ?>/specialties.html#consultation">Specialist Consultation</a>
            <a href="<?= public_e($baseUrl) ?>/specialties.html#diagnostics">Advanced Diagnostics</a>
            <a href="<?= public_e($baseUrl) ?>/specialties.html#laboratory">Laboratory Services</a>
            <a href="<?= public_e($baseUrl) ?>/specialties.html#physiotherapy">Physiotherapy</a>
            <a href="<?= public_e($baseUrl) ?>/specialties.html#kidney">Kidney Care</a>
          </div>
        </div>
        <div>
          <h4>Contact</h4>
          <div class="footer-list footer-contact">
            <span>Traffic Chowk, Butwal, Rupandehi, Nepal</span>
            <a href="mailto:info@nmcbutwal.com.np">info@nmcbutwal.com.np</a>
            <span>Sun-Fri: 8 AM - 8 PM</span>
            <span>Saturday: 9 AM - 2 PM</span>
          </div>
        </div>
      </div>
      <div class="footer-emergency">
        <p>For medical emergencies, contact the nearest emergency service or hospital immediately.</p>
      </div>
      <div class="footer-bottom">
        <span>© <span data-year>2026</span> NMC Clinic, Butwal. All rights reserved.</span>
        <div class="footer-legal">
          <a href="#">Privacy Policy</a>
          <a href="#">Terms &amp; Conditions</a>
          <a href="#">Accessibility</a>
        </div>
      </div>
    </div>
  </footer>

  <a class="wa-float" href="https://wa.me/9779800000000" target="_blank" rel="noopener" aria-label="Chat with us on WhatsApp">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="#ffffff" aria-hidden="true"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2 22l5.25-1.38c1.45.79 3.08 1.21 4.79 1.21h.01c5.46 0 9.91-4.45 9.91-9.91C21.96 6.45 17.5 2 12.04 2zm5.8 14.04c-.24.68-1.42 1.32-1.95 1.37-.5.05-1.13.07-1.83-.11-.42-.11-.96-.29-1.66-.59-2.92-1.26-4.83-4.2-4.98-4.4-.15-.2-1.19-1.58-1.19-3.01 0-1.43.75-2.13 1.02-2.42.27-.29.58-.36.78-.36.19 0 .39 0 .56.01.18.01.42-.07.66.5.24.59.82 2.02.89 2.17.07.15.12.32.02.51-.09.2-.14.32-.28.49-.14.17-.29.38-.42.51-.14.14-.28.29-.12.57.16.28.72 1.19 1.55 1.93 1.06.95 1.96 1.24 2.24 1.38.28.14.44.12.6-.07.16-.19.69-.8.87-1.08.18-.28.36-.23.61-.14.25.09 1.61.76 1.89.9.28.14.46.21.53.32.07.12.07.66-.17 1.34z"></path></svg>
    WhatsApp us
  </a>
</div>
<script src="<?= public_e($baseUrl) ?>/assets/script.js"></script>
</body>
</html>
<?php

function public_blog_find_post(string $slug): ?array
{
    $stmt = db()->prepare(
        'SELECT p.*, c.name AS category_name, m.file_path AS image_path, m.alt_text AS image_alt
         FROM blog_posts p
         LEFT JOIN blog_categories c ON c.id = p.category_id AND c.deleted_at IS NULL
         LEFT JOIN media_assets m ON m.id = p.featured_image_id AND m.deleted_at IS NULL
         WHERE p.slug = :slug AND p.deleted_at IS NULL AND p.status = "published"
         LIMIT 1'
    );
    $stmt->execute([':slug' => $slug]);
    $post = $stmt->fetch();

    return $post ?: null;
}

function public_blog_related_posts(int $postId, ?int $categoryId): array
{
    if ($categoryId !== null) {
        $stmt = db()->prepare(
            'SELECT p.slug, p.title, p.excerpt, c.name AS category_name, m.file_path AS image_path, m.alt_text AS image_alt
             FROM blog_posts p
             LEFT JOIN blog_categories c ON c.id = p.category_id AND c.deleted_at IS NULL
             LEFT JOIN media_assets m ON m.id = p.featured_image_id AND m.deleted_at IS NULL
             WHERE p.deleted_at IS NULL AND p.status = "published" AND p.id <> :id AND p.category_id = :category_id
             ORDER BY p.sort_order ASC, p.published_at DESC, p.title ASC
             LIMIT 3'
        );
        $stmt->execute([':id' => $postId, ':category_id' => $categoryId]);
        $rows = $stmt->fetchAll();
        if ($rows !== []) {
            return $rows;
        }
    }

    $stmt = db()->prepare(
        'SELECT p.slug, p.title, p.excerpt, c.name AS category_name, m.file_path AS image_path, m.alt_text AS image_alt
         FROM blog_posts p
         LEFT JOIN blog_categories c ON c.id = p.category_id AND c.deleted_at IS NULL
         LEFT JOIN media_assets m ON m.id = p.featured_image_id AND m.deleted_at IS NULL
         WHERE p.deleted_at IS NULL AND p.status = "published" AND p.id <> :id
         ORDER BY p.sort_order ASC, p.published_at DESC, p.title ASC
         LIMIT 3'
    );
    $stmt->execute([':id' => $postId]);

    return $stmt->fetchAll();
}

function public_blog_seo(int $postId, string $title, string $description, string $canonical, ?string $imageUrl): array
{
    try {
        $stmt = db()->prepare(
            'SELECT sm.*, m.file_path AS og_image_path
             FROM seo_metadata sm
             LEFT JOIN media_assets m ON m.id = sm.og_image_id AND m.deleted_at IS NULL
             WHERE sm.blog_post_id = :post_id
             LIMIT 1'
        );
        $stmt->execute([':post_id' => $postId]);
        $row = $stmt->fetch();
    } catch (Throwable $exception) {
        $row = null;
    }

    if (!$row) {
        return [
            'title' => $title . ' | NMC Clinic Butwal',
            'description' => $description,
            'canonical' => $canonical,
            'robots' => 'index, follow',
            'og_title' => $title,
            'og_description' => $description,
            'og_image' => $imageUrl,
            'twitter_card' => 'summary_large_image',
            'schema_json' => null,
        ];
    }

    $ogImage = $row['og_image_path'] ? public_base_url() . '/' . ltrim((string) $row['og_image_path'], '/') : $imageUrl;

    return [
        'title' => (string) $row['meta_title'],
        'description' => (string) $row['meta_description'],
        'canonical' => (string) ($row['canonical_url'] ?: $canonical),
        'robots' => ((int) $row['robots_index'] === 1 ? 'index' : 'noindex') . ', ' . ((int) $row['robots_follow'] === 1 ? 'follow' : 'nofollow'),
        'og_title' => (string) ($row['og_title'] ?: $row['meta_title']),
        'og_description' => (string) ($row['og_description'] ?: $row['meta_description']),
        'og_image' => $ogImage,
        'twitter_card' => (string) $row['twitter_card'],
        'schema_json' => $row['schema_json_override'] ?: null,
    ];
}

function public_blog_schema(array $post, string $canonical, ?string $imageUrl): array
{
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'BlogPosting',
        'headline' => (string) $post['title'],
        'description' => (string) $post['excerpt'],
        'url' => $canonical,
        'mainEntityOfPage' => $canonical,
        'publisher' => [
            '@type' => 'MedicalOrganization',
            'name' => 'NMC Clinic',
        ],
    ];

    if ($imageUrl !== null) {
        $schema['image'] = [$imageUrl];
    }
    if (!empty($post['author_name'])) {
        $schema['author'] = ['@type' => 'Person', 'name' => (string) $post['author_name']];
    }
    if (!empty($post['published_at'])) {
        $schema['datePublished'] = public_blog_iso_datetime((string) $post['published_at']);
    }
    if (!empty($post['updated_at'])) {
        $schema['dateModified'] = public_blog_iso_datetime((string) $post['updated_at']);
    }
    if (!empty($post['reviewer_name'])) {
        $schema['reviewedBy'] = ['@type' => 'Person', 'name' => (string) $post['reviewer_name']];
    }

    return $schema;
}

function public_blog_month(?string $value): string
{
    if ($value === null || $value === '') {
        return '';
    }

    $timestamp = strtotime($value);
    if ($timestamp === false) {
        return '';
    }

    return date('F Y', $timestamp);
}

function public_blog_iso_datetime(string $value): string
{
    $date = new DateTimeImmutable($value, new DateTimeZone('Asia/Kathmandu'));

    return $date->format(DATE_ATOM);
}

function public_blog_not_found(): void
{
    http_response_code(404);
    echo 'Blog post not found.';
    exit;
}
