<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/public_seo.php';
require_once __DIR__ . '/includes/content.php';

http_response_code(404);
$base = public_base_path();
$p = page_content('404');
$b = $p['banner'];
$h = page_content('global')['header'];
$logo = c_img($h, 'logo');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Page not found | NMC Clinic</title>
<meta name="robots" content="noindex, follow">
<?php public_render_head_assets(false); ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:ital,wght@0,400..800;1,500..700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= public_e($base) ?>/assets/style.css?v=20261127b">
</head>
<body>
<a class="skip-link" href="#top"><?= c($h, 'skip_label') ?></a>
<main class="page">
  <section class="banner" id="top">
    <div class="banner-veil"></div>
    <div class="banner-in">
      <a class="brand" href="<?= public_e($base) ?>/" style="--hdr-brand:#fff;--hdr-brand-sub:rgba(255,255,255,.75);margin-bottom:28px">
        <img src="<?= c_href((string) ($h['logo']['src'] ?? '')) ?>" alt="<?= $logo['alt'] ?>" width="48" height="48">
        <span class="brand-text"><span class="brand-name"><?= c($h, 'brand_name') ?></span><span class="brand-sub"><?= c($h, 'brand_sub') ?></span></span>
      </a>
      <span class="pill"><span class="dot"></span><?= c($b, 'eyebrow') ?></span>
      <h1><?= c_heading($b, 'heading') ?></h1>
      <p class="lead"><?= c($b, 'subheading') ?></p>
      <div class="hero-actions">
        <a class="btn btn-yellow btn-hero" href="<?= c_href((string) $b['primary_cta_url']) ?>"><?= c($b, 'primary_cta_label') ?></a>
      </div>
    </div>
  </section>
  <section class="wrap section">
    <div class="grid grid-240 gap-16">
<?php foreach ($p['links']['items'] as $item): ?>
      <a class="card card-hover" href="<?= c_href((string) $item['link_url']) ?>"><h3><?= c($item, 'title') ?></h3><p><?= c($item, 'body') ?></p></a>
<?php endforeach; ?>
    </div>
  </section>
</main>
</body>
</html>
