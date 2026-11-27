<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/public_seo.php';
require_once __DIR__ . '/includes/site_footer.php';

$slug = trim((string) ($_GET['slug'] ?? ''));
if (!preg_match('/^[a-z0-9-]+$/', $slug)) {
    public_blog_not_found();
}

$post = null;
try {
    $post = public_blog_find_post($slug);
} catch (Throwable $exception) {
    public_blog_unavailable();
}
if ($post === null) {
    public_blog_not_found();
}

$relatedPosts = public_blog_related_posts((int) $post['id'], $post['category_id'] !== null ? (int) $post['category_id'] : null);
$baseUrl = public_base_url();
$basePath = public_base_path();
$p = page_content('blog_detail');
[$b, $dc, $sb, $rl] = [$p['banner'], $p['disclaimer'], $p['sidebar'], $p['related']];
$canonical = $baseUrl . '/blog/' . rawurlencode((string) $post['slug']);
$title = (string) $post['title'];
$description = (string) $post['excerpt'];
$category = (string) ($post['category_name'] ?? $b['default_category']);
$reviewer = (string) ($post['reviewer_name'] ?: $b['default_reviewer']);
$reviewedAt = public_blog_month($post['reviewed_at'] ?: $post['published_at']);
$readTime = $post['reading_time_minutes'] !== null ? (int) $post['reading_time_minutes'] . ' ' . $b['read_time_suffix'] : $b['no_read_time'];
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
<?php if (!empty($post['published_at'])): ?>
<meta property="article:published_time" content="<?= public_e(public_blog_iso_datetime((string) $post['published_at'])) ?>">
<?php endif; ?>
<?php if (!empty($post['updated_at'])): ?>
<meta property="article:modified_time" content="<?= public_e(public_blog_iso_datetime((string) $post['updated_at'])) ?>">
<?php endif; ?>
<meta name="twitter:image" content="<?= public_e((string) $seo['og_image']) ?>">
<?php if (!empty($seo['og_image'])): ?>
<meta property="og:image" content="<?= public_e($seo['og_image']) ?>">
<?php endif; ?>
<meta name="twitter:card" content="<?= public_e($seo['twitter_card']) ?>">
<meta name="twitter:title" content="<?= public_e($seo['og_title']) ?>">
<meta name="twitter:description" content="<?= public_e($seo['og_description']) ?>">
<meta property="og:site_name" content="NMC Clinic">
<?php public_render_head_assets(); ?>
<script type="application/ld+json"><?= public_json_ld($schema) ?></script>
<?php if (!empty($seo['schema_json']) && ($override = public_json_ld_override((string) $seo['schema_json'])) !== ''): ?>
<script type="application/ld+json"><?= $override ?></script>
<?php endif; ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:ital,wght@0,400..800;1,500..700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= public_e($basePath) ?>/assets/style.css?v=20261127a">
</head>
<body>
<a class="skip-link" href="#top"><?= c(page_content('global')['header'], 'skip_label') ?></a>
<div class="page">

  <?php site_header('blog'); ?>

  <section class="banner article-banner" id="top">
    <div class="banner-veil is-strong"></div>
    <div class="banner-in narrow">
      <div class="crumb">
        <a href="<?= public_e($basePath) ?>/"><?= c($b, 'crumb_home') ?></a>
        <span class="sep">/</span>
        <a href="<?= public_e($basePath) ?>/blog"><?= c($b, 'crumb_blog') ?></a>
        <span class="sep">/</span>
        <span class="here"><?= public_e($category) ?></span>
      </div>
      <span class="tag-chip"><?= public_e($category) ?></span>
      <h1><?= public_e($title) ?></h1>
      <div class="article-meta">
        <span><?= c($b, 'reviewed_by') ?> <?= public_e($reviewer) ?></span>
        <?php if ($reviewedAt !== ''): ?><span><?= c($b, 'reviewed_date') ?> <?= public_e($reviewedAt) ?></span><?php endif; ?>
        <span><?= public_e($readTime) ?></span>
      </div>
    </div>
  </section>

  <div class="wrap-narrow featured-img-wrap">
    <div class="photo-ph ratio-16x8">
      <?php if ($imageUrl): ?>
        <img src="<?= public_e($imageUrl) ?>" alt="<?= public_e((string) ($post['image_alt'] ?: $title)) ?>">
      <?php else: ?>
        <span><?= c($b, 'image_placeholder') ?></span>
      <?php endif; ?>
    </div>
  </div>

  <section class="wrap article-layout">
    <article class="article-body">
      <?= $bodyHtml ?>

      <div class="notice notice-warn disclaimer">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#124E4A" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
        <?= c_text($dc, 'body') ?>
      </div>
    </article>

    <aside class="article-side">
      <div class="side-card">
        <div class="reviewer">
          <span class="reviewer-avatar"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg></span>
          <div>
            <div class="reviewer-name"><?= public_e($reviewer) ?></div>
            <div class="reviewer-role"><?= c($sb, 'reviewer_role') ?></div>
          </div>
        </div>
        <?= c_text($sb, 'reviewer_note') ?>
      </div>
      <div class="side-card">
        <h3><?= c($sb, 'cta_heading') ?></h3>
        <?= c_text($sb, 'cta_body') ?>
        <a class="btn btn-primary btn-block" href="<?= c_href($sb['cta_url']) ?>"><?= c($sb, 'cta_label') ?></a>
      </div>
    </aside>
  </section>

  <?php if ($relatedPosts !== []): ?>
  <section class="band-light">
    <div class="wrap section-sm">
      <div class="center head-block">
        <span class="eyebrow"><?= c($rl, 'eyebrow') ?></span>
        <h2 class="section-title"><?= c_heading($rl, 'heading', 'accent') ?></h2>
      </div>
      <div class="grid grid-300 fill-300">
        <?php foreach ($relatedPosts as $related): ?>
          <a class="post-card" href="<?= public_e($basePath) ?>/blog/<?= public_e((string) $related['slug']) ?>">
            <div class="post-photo">
              <?php if (!empty($related['image_path'])): ?>
                <img src="<?= public_e($basePath . '/' . ltrim((string) $related['image_path'], '/')) ?>" alt="<?= public_e((string) ($related['image_alt'] ?: $related['title'])) ?>" loading="lazy">
              <?php else: ?>
                <span class="ph-label"><?= c($b, 'image_placeholder') ?></span>
              <?php endif; ?>
              <span class="doc-tag"><?= public_e((string) ($related['category_name'] ?? $b['default_category'])) ?></span>
            </div>
            <div class="post-body">
              <h3><?= public_e((string) $related['title']) ?></h3>
              <p><?= public_e((string) $related['excerpt']) ?></p>
              <span class="link-cta inline"><?= c($rl, 'card_link_label') ?></span>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <?php site_footer(true); ?>

</div>
<script src="<?= public_e($basePath) ?>/assets/script.js?v=20261126b"></script>
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
            'robots' => 'index, follow, max-image-preview:large',
            'og_title' => $title,
            'og_description' => $description,
            'og_image' => $imageUrl ?? public_base_url() . '/' . PUBLIC_DEFAULT_OG_IMAGE,
            'twitter_card' => 'summary_large_image',
            'schema_json' => null,
        ];
    }

    $ogImage = $row['og_image_path'] ? public_base_url() . '/' . ltrim((string) $row['og_image_path'], '/') : ($imageUrl ?? public_base_url() . '/' . PUBLIC_DEFAULT_OG_IMAGE);

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
        'inLanguage' => 'en',
        'publisher' => [
            '@type' => 'MedicalClinic',
            '@id' => public_base_url() . '/#clinic',
            'name' => 'NMC Clinic',
            'url' => public_base_url() . '/',
            'logo' => ['@type' => 'ImageObject', 'url' => public_base_url() . '/uploads/logo.jpeg'],
        ],
        // Articles need an author; fall back to the clinic when no named author is set.
        'author' => ['@type' => 'Organization', 'name' => 'NMC Clinic', 'url' => public_base_url() . '/'],
        'image' => [$imageUrl ?? public_base_url() . '/' . PUBLIC_DEFAULT_OG_IMAGE],
    ];

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
    require __DIR__ . '/404.php';
    exit;
}

function public_blog_unavailable(): void
{
    http_response_code(503);
    echo 'Blog post is temporarily unavailable.';
    exit;
}
