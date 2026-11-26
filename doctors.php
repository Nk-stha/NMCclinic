<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/public_seo.php';
require_once __DIR__ . '/includes/site_footer.php';
require_once __DIR__ . '/includes/doctor_card.php';

$p = page_content('doctors');
$ct = site_contact_defaults();
[$b, $l, $cta] = [$p['banner'], $p['listing'], $p['cta_band']];
$doctors = site_doctors();
$specialties = site_distinct($doctors, 'specialty');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php public_render_page_head('doctors'); ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:ital,wght@0,400..800;1,500..700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css?v=20261126b">
</head>
<body>
<a class="skip-link" href="#top"><?= c(page_content('global')['header'], 'skip_label') ?></a>
<div class="page">

  <?php site_header('doctors'); ?>

  <!-- ============ PAGE BANNER ============ -->
  <section class="banner" id="top">
    <div class="banner-veil"></div>
    <div class="banner-in">
      <div class="crumb">
        <a href="<?= c_href('home') ?>"><?= c($b, 'crumb_home') ?></a>
        <span class="sep">/</span>
        <span class="here"><?= c($b, 'crumb_here') ?></span>
      </div>
      <span class="pill"><span class="dot"></span><?= c($b, 'eyebrow') ?></span>
      <h1><?= c_heading($b, 'heading', 'accent') ?></h1>
      <p class="lead"><?= c($b, 'subheading') ?></p>
    </div>
  </section>

  <!-- ============ FILTER + DOCTOR GRID ============ -->
  <section class="wrap listing">
<?php if ($doctors): ?>
    <div class="tabs" data-filter-group="doctors" aria-label="Filter doctors by specialty">
        <button type="button" class="tab lg is-active" data-filter="All" aria-pressed="true"><?= c($l, 'all_label') ?></button>
<?php foreach ($specialties as $name): ?>
        <button type="button" class="tab lg" data-filter="<?= public_e($name) ?>" aria-pressed="false"><?= public_e($name) ?></button>
<?php endforeach; ?>
    </div>

    <div class="grid grid-280 fill-280 doc-grid" data-filter-target="doctors">
<?php foreach ($doctors as $d) { render_doctor_card($d, $l, false); } ?>
    </div>
    <p class="listing-note" data-filter-empty="doctors" hidden><?= c($l, 'no_match_message') ?></p>
<?php else: ?>
    <div class="empty-state"><?= c_text($l, 'empty_message') ?></div>
<?php endif; ?>
<?php if ((string) $l['note'] !== ''): ?>
    <p class="listing-note"><?= c($l, 'note') ?></p>
<?php endif; ?>
  </section>

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
<script src="assets/script.js?v=20261126b"></script>
</body>
</html>
