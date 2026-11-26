<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/public_seo.php';
require_once __DIR__ . '/includes/site_footer.php';

$p = page_content('about');
$ct = site_contact_defaults();
[$b, $in, $mv, $v, $st, $cta] = [$p['banner'], $p['intro'], $p['mission_vision'], $p['values'], $p['story'], $p['cta_band']];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php public_render_page_head('about'); ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:ital,wght@0,400..800;1,500..700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css?v=20261011">
</head>
<body>
<a class="skip-link" href="#top">Skip to content</a>
<div class="page">

  <?php site_header('about'); ?>

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

  <!-- ============ INTRO ============ -->
  <section class="wrap split">
    <div class="split-media">
      <div class="photo-ph ratio-4x3"><span><?= c($in, 'photo_placeholder') ?></span></div>
      <div class="stat-badge">
        <div class="stat-badge-num"><?= c($in, 'stat_value') ?></div>
        <div class="stat-badge-txt"><?= nl2br(c($in, 'stat_label'), false) ?></div>
      </div>
    </div>
    <div class="split-body">
      <h2 class="section-title"><?= c_heading($in, 'heading') ?></h2>
      <p class="section-lead"><?= c($in, 'body') ?></p>
<?php if ($in['items']): ?>
      <ul class="tick-list">
<?php foreach ($in['items'] as $item): ?>
        <li><span class="tick"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></span><?= c($item, 'title') ?></li>
<?php endforeach; ?>
      </ul>
<?php else: ?>
      <div class="empty-state"><?= c_text($in, 'empty_message') ?></div>
<?php endif; ?>
      <a class="btn btn-primary" href="<?= c_url($in, 'primary_cta_url') ?>"><?= c($in, 'primary_cta_label') ?></a>
    </div>
  </section>

  <!-- ============ MISSION & VISION ============ -->
  <section class="band-light">
    <div class="wrap section-80 grid grid-320">
<?php foreach ($mv['items'] as $i => $item): ?>
      <div class="mv-card<?= $i === 1 ? ' is-dark' : '' ?>">
        <span class="mv-icon"><?= c_icon($item, 'icon_key') ?></span>
        <h3><?= c($item, 'title') ?></h3>
        <p><?= c($item, 'body') ?></p>
      </div>
<?php endforeach; ?>
<?php if (!$mv['items']): ?>
      <div class="empty-state"><?= c_text($mv, 'empty_message') ?></div>
<?php endif; ?>
    </div>
  </section>

  <!-- ============ CORE VALUES ============ -->
  <section class="wrap section">
    <div class="center head-block">
      <h2 class="section-title"><?= c_heading($v, 'heading') ?></h2>
      <p class="section-lead"><?= c($v, 'subheading') ?></p>
    </div>
<?php if ($v['items']): ?>
    <div class="grid grid-250 gap-22">
<?php foreach ($v['items'] as $item): ?>
      <div class="card value-card">
        <span class="icon-badge lg"><?= c_icon($item, 'icon_key') ?></span>
        <h3><?= c($item, 'title') ?></h3>
        <p><?= c($item, 'body') ?></p>
      </div>
<?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="empty-state"><?= c_text($v, 'empty_message') ?></div>
<?php endif; ?>
  </section>

  <!-- ============ STORY / TIMELINE ============ -->
  <section class="band-cream">
    <div class="wrap-narrow section">
      <div class="center head-block">
        <span class="eyebrow"><?= c($st, 'eyebrow') ?></span>
        <h2 class="section-title"><?= c_heading($st, 'heading') ?></h2>
      </div>
<?php if ($st['items']): ?>
      <ol class="timeline">
<?php $last = count($st['items']) - 1; foreach ($st['items'] as $i => $item): $isLast = $i === $last; ?>
        <li>
          <div class="tl-rail"><span class="tl-dot<?= $isLast ? ' is-last' : '' ?>"><?= sprintf('%02d', $i + 1) ?></span><?= $isLast ? '' : '<span class="tl-line"></span>' ?></div>
          <div class="tl-body<?= $isLast ? ' is-last' : '' ?>">
            <h3><?= c($item, 'title') ?></h3>
            <p><?= c($item, 'body') ?></p>
          </div>
        </li>
<?php endforeach; ?>
      </ol>
<?php else: ?>
      <div class="empty-state"><?= c_text($st, 'empty_message') ?></div>
<?php endif; ?>
    </div>
  </section>

  <!-- ============ CTA BAND ============ -->
  <section class="band-light">
    <div class="wrap-narrow section-sm center">
      <h2 class="section-title sm"><?= c_heading($cta, 'heading') ?></h2>
      <p class="section-lead"><?= c($cta, 'subheading') ?></p>
      <div class="cta-row">
        <a class="btn btn-primary btn-lg" href="<?= c_url($cta, 'primary_cta_url') ?>"><?= c($cta, 'primary_cta_label') ?></a>
        <a class="btn btn-light btn-lg" href="<?= public_e($ct['phone_href']) ?>"><?= c($cta, 'call_label') ?> <?= public_e($ct['phone']) ?></a>
      </div>
    </div>
  </section>

  <?php site_footer(true); ?>

</div>
<script src="assets/script.js?v=20261011"></script>
</body>
</html>
