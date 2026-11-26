<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/public_seo.php';
require_once __DIR__ . '/includes/site_footer.php';
require_once __DIR__ . '/includes/doctor_card.php';
require_once __DIR__ . '/includes/blog_card.php';

$p = page_content('home');
$ct = site_contact_defaults();
$specialties = array_slice(site_specialties(), 0, 6);
$doctors = array_slice(site_doctors(), 0, 4); // mirrors the old JS home list (first 4 in CMS order)
$posts = array_slice(site_blog_posts(), 0, 3);
$branches = site_branches();
$serviceOptions = site_form_options_filled()['specialties'];
// Contact details are edited in CMS > Settings; the shipped values are only a fallback.
$settings = $ct['settings']['settings'];
$email = (string) ($settings['email'] ?? '') !== '' ? (string) $settings['email'] : 'info@nmcbutwal.com.np';
$hoursLines = $settings !== null ? hours_lines($ct['settings']['hours']) : [];
// Shown until script.js computes the live open/closed status.
$statusHours = $hoursLines ? implode(', ', $hoursLines) : 'Sun-Fri 8 AM-8 PM, Sat 9 AM-2 PM';
$hoursLines = $hoursLines ?: ['Sun-Fri: 8:00 AM - 8:00 PM', 'Saturday: 9:00 AM - 2:00 PM'];

$hero = $p['hero'];
// Only plain path characters reach the inline style, so an image path cannot break out of url().
$heroBg = content_abs_url(content_safe_url((string) ($hero['primary_media_id']['src'] ?? '')));
$heroBg = preg_match('#^[\w/.:%~-]+$#', $heroBg) ? $heroBg : content_abs_url('uploads/backgound.webp');
$aboutImg = c_img($p['about'], 'primary_media_id');
$arrow = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>';
$phoneSvg = static fn (string $stroke): string => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="' . $stroke . '" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>';
$branchQuery = static fn (array $b): string => $b['lat'] !== '' && $b['lng'] !== '' ? $b['lat'] . ',' . $b['lng'] : $b['address'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php public_render_page_head('home'); ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:ital,wght@0,400..800;1,500..700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css?v=20261126">
</head>
<body>
<a class="skip-link" href="#top"><?= c(page_content('global')['header'], 'skip_label') ?></a>
<div class="page">

  <?php site_header('home'); ?>

  <!-- ============ HERO ============ -->
  <section class="hero" id="top" style="--hero-bg:url('<?= public_e($heroBg) ?>')">
    <div class="hero-veil"></div>
    <div class="hero-in">
      <div class="hero-copy">
        <span class="pill"><span class="dot"></span><?= c($hero, 'eyebrow') ?></span>
        <h1><?= c_heading($hero, 'heading', 'em') ?></h1>
        <p class="lead"><?= c($hero, 'subheading') ?></p>
        <div class="hero-actions">
          <a class="btn btn-yellow btn-hero" href="<?= c_url($hero, 'primary_cta_url') ?>"><?= c($hero, 'primary_cta_label') ?></a>
        </div>
      </div>
    </div>
  </section>

  <!-- ============ QUICK ACTIONS ============ -->
  <section class="wrap info-strip-wrap" aria-label="Clinic status and quick contact">
    <div class="info-strip">
      <div class="info-item" role="status">
        <span class="status-dot" data-open-dot></span>
        <span><span class="info-k"><?= c($p['info_strip'], 'status_label') ?></span><span class="info-v" data-open-status data-hours='<?= public_e((string) json_encode(site_settings()['hours'])) ?>'><?= public_e($statusHours) ?></span></span>
      </div>
      <a class="info-item is-emergency" href="<?= public_e($ct['phone_href']) ?>" data-phone-href>
        <span class="icon-badge"><?= $phoneSvg('currentColor') ?></span>
        <span><span class="info-k"><?= c($p['info_strip'], 'emergency_label') ?></span><span class="info-v" data-phone><?= public_e($ct['phone']) ?></span></span>
      </a>
    </div>
  </section>

  <!-- ============ SERVICES ============ -->
  <section class="wrap home-pad-svc" id="services">
    <div class="head-stack">
      <h2 class="section-title"><?= c_heading($p['services'], 'heading') ?></h2>
      <p class="section-lead"><?= c($p['services'], 'subheading') ?></p>
    </div>
<?php if ($specialties): ?>
    <div class="bento" data-reveal>
<?php foreach ($specialties as $s): ?>
        <article class="card card-hover svc-card">
          <span class="icon-badge"><?= content_icons()['generic'] ?></span>
          <h3><?= public_e($s['title']) ?></h3>
          <p><?= public_e($s['short_description']) ?></p>
          <a class="link-cta inline" href="specialties#<?= public_e(rawurlencode($s['slug'])) ?>"><?= c($p['services'], 'card_link_label') ?> <?= $arrow ?></a>
        </article>
<?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="empty-state"><?= c_text($p['services'], 'empty_message') ?></div>
<?php endif; ?>
  </section>

  <!-- ============ ABOUT ============ -->
  <section id="about">
    <div class="wrap split pad-about">
      <div class="split-media">
        <div class="about-photo">
          <img src="<?= $aboutImg['src'] ?>" loading="lazy" width="640" height="480"
               alt="<?= $aboutImg['alt'] ?>">
        </div>
        <div class="stat-badge">
          <div class="stat-badge-num"><?= c($p['about'], 'stat_value') ?></div>
          <div class="stat-badge-txt"><?= nl2br(c($p['about'], 'stat_label'), false) ?></div>
        </div>
      </div>
      <div class="split-body">
        <h2 class="section-title"><?= c_heading($p['about'], 'heading') ?></h2>
        <p class="section-lead"><?= c($p['about'], 'subheading') ?></p>
        <a class="link-cta inline" href="<?= c_url($p['about'], 'primary_cta_url') ?>"><?= c($p['about'], 'primary_cta_label') ?> <?= $arrow ?></a>
      </div>
    </div>
  </section>

  <!-- ============ DIAGNOSTICS ============ -->
  <section class="band-light" id="diagnostics">
    <div class="wrap home-pad-80">
      <div class="head-stack">
        <h2 class="section-title"><?= c_heading($p['diagnostics'], 'heading') ?></h2>
        <p class="section-lead"><?= c($p['diagnostics'], 'subheading') ?></p>
      </div>
      <ol class="journey" data-reveal>
<?php foreach ($p['diagnostics']['items'] as $item): ?>
        <li>
          <span class="journey-icon"><?= c_icon($item, 'icon_key') ?></span>
          <h3><?= c($item, 'title') ?></h3>
          <p><?= c($item, 'body') ?></p>
        </li>
<?php endforeach; ?>
      </ol>
      <a class="btn btn-outline" href="<?= c_url($p['diagnostics'], 'primary_cta_url') ?>"><?= c($p['diagnostics'], 'primary_cta_label') ?></a>
    </div>
  </section>

  <!-- ============ DOCTORS ============ -->
  <section class="wrap home-pad-88" id="doctors">
    <div class="head-row">
      <div class="head-stack">
        <h2 class="section-title"><?= c_heading($p['doctors'], 'heading') ?></h2>
        <p class="section-lead"><?= c($p['doctors'], 'subheading') ?></p>
      </div>
      <a class="link-cta inline" href="<?= c_url($p['doctors'], 'primary_cta_url') ?>"><?= c($p['doctors'], 'primary_cta_label') ?> <?= $arrow ?></a>
    </div>
<?php if ($doctors): ?>
    <div class="grid grid-260 gap-22">
<?php foreach ($doctors as $d) { render_doctor_card($d, $p['doctors'], true); } ?>
    </div>
<?php else: ?>
    <div class="empty-state"><?= c_text($p['doctors'], 'empty_message') ?></div>
<?php endif; ?>
  </section>

  <!-- ============ WHY CHOOSE US ============ -->
  <section class="wrap home-pad-top24">
    <div class="why-panel lattice">
      <div>
        <h2 class="section-title on-dark"><?= c_heading($p['why_choose'], 'heading') ?></h2>
        <p class="section-lead"><?= c($p['why_choose'], 'subheading') ?></p>
      </div>
      <div class="why-list" data-reveal>
<?php foreach ($p['why_choose']['items'] as $item): ?>
        <div class="why-item">
          <?= c_icon($item, 'icon_key') ?>

          <h3><?= c($item, 'title') ?></h3>
          <p><?= c($item, 'body') ?></p>
        </div>
<?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- ============ APPOINTMENT ============ -->
<?php $a = $p['appointment']; ?>
  <section class="appt-band lattice" id="appointment">
    <div class="wrap home-pad-80 appointment">
      <div class="appointment-copy">
        <span class="eyebrow"><?= c($a, 'eyebrow') ?></span>
        <h2 class="section-title"><?= c_heading($a, 'heading') ?></h2>
        <p class="section-lead"><?= c($a, 'subheading') ?></p>
        <div class="dept-help">
          <span class="icon-badge"><?= $phoneSvg('#286963') ?></span>
          <div>
            <div class="dept-help-title"><?= c($a, 'help_title') ?></div>
            <div class="dept-help-text"><?= c($a, 'help_text') ?> <a href="<?= public_e($ct['phone_href']) ?>"><?= public_e($ct['phone']) ?></a></div>
          </div>
        </div>
      </div>

      <div class="appointment-form-col">
        <form class="form-card is-home" data-success="appointment-ok" action="submit-appointment.php" method="post">
          <div class="grid grid-200 form-grid">
            <div class="field">
              <label for="f-name"><?= c($a, 'name_label') ?></label>
              <input class="input" id="f-name" name="name" type="text" required placeholder="<?= c($a, 'name_placeholder') ?>">
            </div>
            <div class="field">
              <label for="f-phone"><?= c($a, 'phone_label') ?></label>
              <input class="input" id="f-phone" name="phone" type="tel" required placeholder="<?= c($a, 'phone_placeholder') ?>">
            </div>
            <div class="field">
              <label for="f-service"><?= c($a, 'service_label') ?></label>
              <select class="select" id="f-service" name="service">
                <option value=""><?= c($a, 'service_placeholder') ?></option>
<?php foreach ($serviceOptions as $o): ?>
                <option value="<?= public_e($o['value']) ?>"><?= public_e($o['label']) ?></option>
<?php endforeach; ?>
              </select>
            </div>
            <div class="field">
              <label for="f-date"><?= c($a, 'date_label') ?></label>
              <input class="input" id="f-date" name="date" type="date">
            </div>
            <div class="field">
              <label for="f-time"><?= c($a, 'time_label') ?></label>
              <select class="select" id="f-time" name="time">
                <option value=""><?= c($a, 'time_any') ?></option>
                <option><?= c($a, 'time_morning') ?></option>
                <option><?= c($a, 'time_afternoon') ?></option>
                <option><?= c($a, 'time_evening') ?></option>
              </select>
            </div>
          </div>
          <div class="field">
            <label for="f-msg"><?= c($a, 'message_label') ?> <span class="optional"><?= c($a, 'optional_label') ?></span></label>
            <textarea class="textarea" id="f-msg" name="message" rows="3" placeholder="<?= c($a, 'message_placeholder') ?>"></textarea>
          </div>

          <label class="consent">
            <input type="checkbox" name="consent_accepted" value="1" required>
            <span><?= c($a, 'consent_text') ?></span>
          </label>
          <button class="btn btn-primary btn-block" type="submit"><?= c($a, 'submit_label') ?></button>
          <div class="form-ok" id="appointment-ok" role="status" hidden>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#286963" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            <?= c($a, 'success_message') ?>

          </div>
          <p class="form-note"><?= c($a, 'form_note') ?></p>
        </form>
      </div>
    </div>
  </section>

  <!-- ============ PATIENT INFORMATION ============ -->
  <section class="wrap home-pad-88">
    <div class="patient-grid">
      <div>
        <div class="head-stack">
          <h2 class="section-title"><?= c_heading($p['before_visit'], 'heading') ?></h2>
          <p class="section-lead"><?= c($p['before_visit'], 'subheading') ?></p>
        </div>
        <a class="link-cta inline" href="<?= c_url($p['before_visit'], 'primary_cta_url') ?>"><?= c($p['before_visit'], 'primary_cta_label') ?> <?= $arrow ?></a>
      </div>
      <div class="checklist" data-reveal>
<?php foreach ($p['before_visit']['items'] as $item): ?>
        <div class="check-item">
          <span class="icon-badge"><?= c_icon($item, 'icon_key') ?></span>
          <div>
            <h3><?= c($item, 'title') ?></h3>
            <p><?= c($item, 'body') ?></p>
          </div>
        </div>
<?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- Patient stories: add a section here once verified, permitted testimonials exist.
       An empty "coming soon" box reads as unfinished, so it is left out for now. -->

  <!-- ============ HEALTH ARTICLES ============ -->
  <section class="wrap home-pad-top24" id="articles">
    <div class="head-stack">
      <h2 class="section-title"><?= c_heading($p['articles'], 'heading') ?></h2>
      <p class="section-lead"><?= c($p['articles'], 'subheading') ?></p>
    </div>
<?php $ar = $p['articles']; if ($posts): ?>
    <div class="grid grid-280 gap-22">
<?php foreach ($posts as $post) { render_blog_card($post, $ar); } ?>
    </div>
<?php else: ?>
    <div class="empty-state"><?= c_text($p['articles'], 'empty_message') ?></div>
<?php endif; ?>
    <div class="section-foot">
      <a class="btn btn-outline" href="<?= c_url($p['articles'], 'primary_cta_url') ?>"><?= c($p['articles'], 'primary_cta_label') ?> <?= $arrow ?></a>
    </div>
  </section>

  <!-- ============ LOCATION & CONTACT ============ -->
<?php $v = $p['visit']; $first = $branches[0] ?? null; ?>
  <section class="wrap home-pad-88" id="contact" data-branch-picker data-label-show="<?= c($v, 'map_show_label') ?>" data-label-showing="<?= c($v, 'map_showing_label') ?>" data-caption-prefix="<?= c($v, 'map_caption_prefix') ?>" data-title-prefix="<?= c($v, 'map_title_prefix') ?>">
    <div class="head-stack">
      <span class="eyebrow"><?= c($v, 'eyebrow') ?></span>
      <h2 class="section-title"><?= c_heading($v, 'heading') ?></h2>
      <p class="section-lead"><?= c($v, 'subheading') ?></p>
    </div>
    <div class="contact-split">
      <div class="contact-form-col">
<?php if ($branches): ?>
        <div class="branch-list" data-branch-list aria-label="Clinic branches">
<?php foreach ($branches as $i => $b): $on = $i === 0; ?>
          <div class="branch-item<?= $on ? ' is-active' : '' ?>">
            <button type="button" class="branch-select" aria-pressed="<?= $on ? 'true' : 'false' ?>"
                    data-branch-name="<?= public_e($b['name']) ?>" data-branch-address="<?= public_e($b['address']) ?>"
                    data-branch-lat="<?= public_e($b['lat']) ?>" data-branch-lng="<?= public_e($b['lng']) ?>" data-branch-zoom="<?= (int) ($b['zoom'] ?: 15) ?>"
                    data-branch-phone="<?= public_e($b['phone']) ?>" data-branch-phone-href="<?= public_e($b['phone_href']) ?>">
              <span class="icon-badge md"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg></span>
              <span class="branch-body">
                <span class="branch-head"><span class="branch-name"><?= public_e($b['name']) ?></span><?php if ($b['tag'] !== ''): ?><span class="branch-tag<?= $b['is_primary'] ? '' : ' is-soft' ?>"><?= public_e($b['tag']) ?></span><?php endif; ?></span>
                <span class="branch-text"><?= public_e($b['address']) ?></span>
                <span class="branch-cta" data-branch-cta><?= c($v, $on ? 'map_showing_label' : 'map_show_label') ?></span>
              </span>
            </button>
<?php if (($b['phone'] !== '' && $b['phone_href'] !== '') || $b['hours'] !== ''): ?>
            <div class="branch-meta"><?php if ($b['phone'] !== '' && $b['phone_href'] !== ''): ?><a href="<?= public_e($b['phone_href']) ?>"><?= public_e($b['phone']) ?></a><?php endif; ?><?php if ($b['hours'] !== ''): ?><span><?= public_e($b['hours']) ?></span><?php endif; ?></div>
<?php endif; ?>
          </div>
<?php endforeach; ?>
        </div>
<?php else: ?>
        <div class="empty-state"><?= c_text($v, 'empty_message') ?></div>
<?php endif; ?>
        <div class="branch-list branch-extra">
          <div class="branch">
            <span class="icon-badge md"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#286963" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"></rect><path d="m22 7-10 6L2 7"></path></svg></span>
            <div>
              <div class="branch-name"><?= c($v, 'email_label') ?></div>
              <div class="branch-text"><a href="mailto:<?= public_e($email) ?>"><?= public_e($email) ?></a></div>
            </div>
          </div>
          <div class="branch">
            <span class="icon-badge md is-warm"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#124E4A" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"></circle><polyline points="12 7 12 12 15 14"></polyline></svg></span>
            <div>
              <div class="branch-name"><?= c($v, 'hours_label') ?></div>
              <div class="branch-text"><?= implode('<br>', array_map('public_e', $hoursLines)) ?></div>
            </div>
          </div>
        </div>
      </div>
<?php if ($first): $q = rawurlencode($branchQuery($first)); ?>
      <div class="contact-side">
        <p class="map-caption" data-branch-caption aria-live="polite"><?= c($v, 'map_caption_prefix') ?> <?= public_e($first['name']) ?></p>
        <div class="map-frame">
          <iframe data-branch-map title="<?= c($v, 'map_title_prefix') ?> <?= public_e($first['name']) ?>" loading="lazy"
                  src="<?= public_e('https://www.google.com/maps?q=' . $q . '&z=' . ($first['zoom'] ?: 15) . '&output=embed') ?>"></iframe>
        </div>
        <a class="btn btn-outline btn-block" data-branch-directions href="<?= public_e('https://www.google.com/maps/dir/?api=1&destination=' . $q) ?>" target="_blank" rel="noopener">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="3 11 22 2 13 21 11 13 3 11"></polygon></svg> <?= c($v, 'directions_label') ?>

        </a>
      </div>
<?php endif; ?>
    </div>
  </section>

  <?php site_footer(false); ?>

</div>
<script src="assets/script.js?v=20261126"></script>
</body>
</html>
