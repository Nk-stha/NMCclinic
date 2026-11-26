<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/public_seo.php';
require_once __DIR__ . '/includes/site_footer.php';

$p = page_content('specialties');
$ct = site_contact_defaults();
[$b, $l, $cta] = [$p['banner'], $p['list'], $p['cta_band']];
$specialties = site_specialties();
$arrow = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php public_render_page_head('specialties'); ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:ital,wght@0,400..800;1,500..700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css?v=20261126b">
</head>
<body>
<a class="skip-link" href="#top"><?= c(page_content('global')['header'], 'skip_label') ?></a>
<div class="page">

  <?php site_header('specialties'); ?>

  <!-- ============ PAGE BANNER ============ -->
  <section class="banner" id="top">
    <div class="banner-veil"></div>
    <div class="banner-in">
      <div class="crumb">
        <a href="home"><?= c($b, 'crumb_home') ?></a>
        <span class="sep">/</span>
        <span class="here"><?= c($b, 'crumb_here') ?></span>
      </div>
      <span class="pill"><span class="dot"></span><?= c($b, 'eyebrow') ?></span>
      <h1><?= c_heading($b, 'heading', 'accent') ?></h1>
      <p class="lead"><?= c($b, 'subheading') ?></p>
    </div>
  </section>
<?php if ($specialties): ?>

  <!-- ============ SPECIALTY QUICK NAV ============ -->
  <section class="wrap quick-nav-wrap" aria-label="Specialty quick links">
    <div class="quick-nav">
<?php foreach ($specialties as $s): ?>
        <a href="#<?= public_e($s['slug']) ?>">
          <span class="icon-badge sm"><?= spec_icon($s['slug']) ?></span>
          <span class="quick-label"><?= public_e($s['title']) ?></span>
        </a>
<?php endforeach; ?>
    </div>
  </section>

  <!-- ============ SPECIALTY DETAIL SECTIONS ============ -->
  <section class="wrap spec-list">
<?php foreach ($specialties as $s): ?>
      <div class="spec-row" id="<?= public_e($s['slug']) ?>">
        <div class="spec-media">
          <div class="photo-ph ratio-4x3">
<?php if ($s['image_path']): ?>
            <img src="<?= public_e($s['image_path']) ?>" alt="<?= public_e($s['image_alt']) ?>" loading="lazy">
<?php else: ?>
            <span><?= public_e($s['title']) ?> <?= c($l, 'photo_label') ?></span>
<?php endif; ?>
          </div>
        </div>
        <div class="spec-body">
          <span class="eyebrow with-icon" aria-hidden="true"><span class="icon-badge sm"><?= spec_icon($s['slug']) ?></span></span>
          <h2 class="section-title"><?= public_e($s['title']) ?></h2>
<?php if ($s['short_description'] !== ''): ?>
          <p class="section-lead"><?= public_e($s['short_description']) ?></p>
<?php endif; ?>
<?php if ($s['points']): ?>
          <ul class="tick-list compact">
<?php foreach ($s['points'] as $point): ?>
          <li><span class="tick sm"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></span><?= public_e($point) ?></li>
<?php endforeach; ?>
          </ul>
<?php endif; ?>
          <a class="link-cta inline" href="<?= c_href((string) $l['book_url']) ?>"><?= c($l, 'book_label') ?> <?= $arrow ?></a>
        </div>
      </div>
<?php endforeach; ?>
  </section>
<?php else: ?>

  <section class="wrap spec-list">
    <div class="empty-state"><?= c_text($l, 'empty_message') ?></div>
  </section>
<?php endif; ?>

  <!-- ============ CTA BAND ============ -->
  <section class="band-light">
    <div class="wrap-narrow section-sm center">
      <h2 class="section-title sm"><?= c_heading($cta, 'heading') ?></h2>
      <p class="section-lead"><?= c($cta, 'subheading') ?></p>
      <div class="cta-row">
        <a class="btn btn-primary btn-lg" href="<?= c_href((string) $cta['primary_cta_url']) ?>"><?= c($cta, 'primary_cta_label') ?></a>
        <a class="btn btn-light btn-lg" href="<?= public_e($ct['phone_href']) ?>"><?= c($cta, 'call_label') ?> <?= public_e($ct['phone']) ?></a>
      </div>
    </div>
  </section>

  <?php site_footer(true); ?>

</div>
<script src="assets/script.js?v=20261126"></script>
</body>
</html>
