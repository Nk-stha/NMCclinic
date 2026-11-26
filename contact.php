<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/public_seo.php';
require_once __DIR__ . '/includes/site_footer.php';

$p = page_content('contact');
[$b, $ic, $f, $br, $tk] = [$p['banner'], $p['info_cards'], $p['form'], $p['branches'], $p['talk']];
$ct = site_contact_defaults();
$branches = site_branches();
$first = $branches[0] ?? null;
$subjects = site_form_options_filled()['subjects'];
$page = site_contact_page();
// Contact details are edited in CMS > Settings and Branches; the shipped values are only a fallback.
$settings = $ct['settings']['settings'];
$email = (string) ($settings['email'] ?? '') !== '' ? (string) $settings['email'] : 'info@nmcbutwal.com.np';
$hoursLines = $settings !== null ? hours_lines($ct['settings']['hours']) : [];
$hoursLines = $hoursLines ?: ['Sun-Fri: 8 AM-8 PM', 'Saturday: 9 AM-2 PM'];
$address = (string) ($settings['address'] ?? '') !== '' ? (string) $settings['address'] : 'Traffic Chowk, Butwal, Rupandehi, Nepal';
$cardName = $first['name'] ?? 'Butwal Branch';
$cardAddress = $first['address'] ?? $address;
$cardPhone = ($first['phone'] ?? '') !== '' && ($first['phone_href'] ?? '') !== '' ? $first : ['phone' => $ct['phone'], 'phone_href' => $ct['phone_href']];
$responseNote = trim((string) ($page['response_time_note'] ?? '')) !== '' ? (string) $page['response_time_note'] : 'Our reception team usually responds within clinic hours.';
$emTitle = trim((string) ($page['emergency_title'] ?? '')) !== '' ? (string) $page['emergency_title'] : 'In an emergency';
$emText = trim((string) ($page['emergency_description'] ?? '')) !== '' ? (string) $page['emergency_description'] : 'This form is not monitored around the clock. For urgent or life-threatening symptoms, contact the nearest emergency service or hospital immediately.';
$emShow = (int) ($page['is_visible'] ?? 1) === 1;
$emNumber = trim((string) ($page['emergency_phone_number'] ?? ''));
$emHref = phone_href($emNumber);
$emLabel = trim((string) ($page['emergency_phone_label'] ?? ''));
$branchQuery = static fn (array $b): string => $b['lat'] !== '' && $b['lng'] !== '' ? $b['lat'] . ',' . $b['lng'] : $b['address'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php public_render_page_head('contact'); ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:ital,wght@0,400..800;1,500..700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css?v=20261126b">
</head>
<body>
<a class="skip-link" href="#top"><?= c(page_content('global')['header'], 'skip_label') ?></a>
<div class="page">

  <?php site_header('contact'); ?>

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

  <!-- ============ CONTACT INFO CARDS ============ -->
  <section class="wrap info-wrap" aria-label="<?= c($ic, 'section_label') ?>">
    <div class="grid grid-230 gap-16">
      <div class="info-card">
        <span class="icon-badge md"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#286963" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg></span>
        <div class="info-title" data-branch-field="name"><?= public_e($cardName) ?></div>
        <div class="info-text" data-branch-field="address"><?= public_e($cardAddress) ?></div>
      </div>
      <div class="info-card">
        <span class="icon-badge md"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#286963" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"></path></svg></span>
        <div class="info-title"><?= c($ic, 'phone_title') ?></div>
        <div class="info-text">
          <a data-branch-field="phone" href="<?= public_e($cardPhone['phone_href']) ?>"><?= public_e($cardPhone['phone']) ?></a>
        </div>
      </div>
      <div class="info-card">
        <span class="icon-badge md"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#286963" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"></rect><path d="m22 7-10 6L2 7"></path></svg></span>
        <div class="info-title"><?= c($ic, 'email_title') ?></div>
        <div class="info-text"><a href="mailto:<?= public_e($email) ?>"><?= public_e($email) ?></a></div>
      </div>
      <div class="info-card">
        <span class="icon-badge md is-warm"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#124E4A" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"></circle><polyline points="12 7 12 12 15 14"></polyline></svg></span>
        <div class="info-title"><?= c($ic, 'hours_title') ?></div>
        <div class="info-text"><?= implode('<br>', array_map('public_e', $hoursLines)) ?></div>
      </div>
    </div>
  </section>

  <!-- ============ FORM + MAP ============ -->
  <section class="wrap contact-split" id="contact-form" data-branch-picker data-label-show="<?= c($br, 'map_show_label') ?>" data-label-showing="<?= c($br, 'map_showing_label') ?>" data-caption-prefix="<?= c($br, 'map_caption_prefix') ?>" data-title-prefix="<?= c($br, 'map_title_prefix') ?>">
    <div class="contact-form-col">
      <span class="eyebrow"><?= c($f, 'eyebrow') ?></span>
      <h2 class="section-title contact"><?= c_heading($f, 'heading') ?></h2>
      <p class="section-lead"><?= c($f, 'subheading') ?></p>

      <form class="form-card" data-success="contact-ok" action="submit-contact.php" method="post">
        <div class="grid grid-200 form-grid">
          <div class="field">
            <label for="c-name"><?= c($f, 'name_label') ?></label>
            <input class="input" id="c-name" name="name" type="text" required placeholder="<?= c($f, 'name_placeholder') ?>">
          </div>
          <div class="field">
            <label for="c-phone"><?= c($f, 'phone_label') ?></label>
            <input class="input" id="c-phone" name="phone" type="tel" required placeholder="<?= c($f, 'phone_placeholder') ?>">
          </div>
          <div class="field">
            <label for="c-email"><?= c($f, 'email_label') ?> <span class="optional"><?= c($f, 'optional_label') ?></span></label>
            <input class="input" id="c-email" name="email" type="email" placeholder="<?= c($f, 'email_placeholder') ?>">
          </div>
          <div class="field">
            <label for="c-subject"><?= c($f, 'subject_label') ?></label>
            <select class="select" id="c-subject" name="subject">
              <option value=""><?= c($f, 'subject_placeholder') ?></option>
<?php foreach ($subjects as $o): ?>
              <option value="<?= public_e($o['value']) ?>"><?= public_e($o['label']) ?></option>
<?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="field">
          <label for="c-msg"><?= c($f, 'message_label') ?></label>
          <textarea class="textarea" id="c-msg" name="message" rows="4" required placeholder="<?= c($f, 'message_placeholder') ?>"></textarea>
        </div>
        <label class="consent">
          <input type="checkbox" name="consent_accepted" value="1" required>
          <span><?= c($f, 'consent_text') ?></span>
        </label>
        <button class="btn btn-primary btn-block" type="submit"><?= c($f, 'submit_label') ?></button>
        <div class="form-ok" id="contact-ok" role="status" hidden>
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#286963" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
          <?= c($f, 'success_message') ?>
        </div>
        <p class="form-note center"><?= public_e($responseNote) ?></p>
      </form>
    </div>

    <div class="contact-side">
      <h3 class="side-title"><?= c($br, 'heading') ?></h3>
<?php if ($branches): ?>
      <div class="branch-list" data-branch-list aria-label="<?= c($br, 'list_label') ?>">
<?php foreach ($branches as $i => $bn): $on = $i === 0; ?>
        <div class="branch-item<?= $on ? ' is-active' : '' ?>">
          <button type="button" class="branch-select" aria-pressed="<?= $on ? 'true' : 'false' ?>"
                  data-branch-name="<?= public_e($bn['name']) ?>" data-branch-address="<?= public_e($bn['address']) ?>"
                  data-branch-lat="<?= public_e($bn['lat']) ?>" data-branch-lng="<?= public_e($bn['lng']) ?>" data-branch-zoom="<?= (int) ($bn['zoom'] ?: 15) ?>"
                  data-branch-phone="<?= public_e($bn['phone']) ?>" data-branch-phone-href="<?= public_e($bn['phone_href']) ?>">
            <span class="icon-badge md"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg></span>
            <span class="branch-body">
              <span class="branch-head"><span class="branch-name"><?= public_e($bn['name']) ?></span><?php if ($bn['tag'] !== ''): ?><span class="branch-tag<?= $bn['is_primary'] ? '' : ' is-soft' ?>"><?= public_e($bn['tag']) ?></span><?php endif; ?></span>
              <span class="branch-text"><?= public_e($bn['address']) ?></span>
              <span class="branch-cta" data-branch-cta><?= c($br, $on ? 'map_showing_label' : 'map_show_label') ?></span>
            </span>
          </button>
<?php if (($bn['phone'] !== '' && $bn['phone_href'] !== '') || $bn['hours'] !== ''): ?>
          <div class="branch-meta"><?php if ($bn['phone'] !== '' && $bn['phone_href'] !== ''): ?><a href="<?= public_e($bn['phone_href']) ?>"><?= public_e($bn['phone']) ?></a><?php endif; ?><?php if ($bn['hours'] !== ''): ?><span><?= public_e($bn['hours']) ?></span><?php endif; ?></div>
<?php endif; ?>
        </div>
<?php endforeach; ?>
      </div>
<?php $q = rawurlencode($branchQuery($first)); ?>
      <p class="map-caption" data-branch-caption aria-live="polite"><?= c($br, 'map_caption_prefix') ?> <?= public_e($first['name']) ?></p>
      <div class="map-frame">
        <iframe data-branch-map title="<?= c($br, 'map_title_prefix') ?> <?= public_e($first['name']) ?>" loading="lazy"
                src="<?= public_e('https://www.google.com/maps?q=' . $q . '&z=' . ($first['zoom'] ?: 15) . '&output=embed') ?>"></iframe>
      </div>
      <a class="btn btn-outline btn-block" data-branch-directions href="<?= public_e('https://www.google.com/maps/dir/?api=1&destination=' . $q) ?>" target="_blank" rel="noopener">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="3 11 22 2 13 21 11 13 3 11"></polygon></svg> <?= c($br, 'directions_label') ?>
      </a>
<?php else: ?>
      <div class="empty-state"><?= c_text($br, 'empty_message') ?></div>
<?php endif; ?>
      <div class="talk-card">
        <h3><?= c($tk, 'heading') ?></h3>
        <a class="talk-row" href="<?= public_e($ct['phone_href']) ?>">
          <span class="talk-icon is-yellow"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"></path></svg></span>
          <span><span class="talk-label"><?= c($tk, 'call_label') ?></span><span class="talk-value"><?= public_e($ct['phone']) ?></span></span>
        </a>
        <a class="talk-row" href="<?= public_e($ct['whatsapp']) ?>" target="_blank" rel="noopener">
          <span class="talk-icon is-green"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2 22l5.25-1.38c1.45.79 3.08 1.21 4.79 1.21h.01c5.46 0 9.91-4.45 9.91-9.91C21.96 6.45 17.5 2 12.04 2z"></path></svg></span>
          <span><span class="talk-label"><?= c($tk, 'whatsapp_label') ?></span><span class="talk-value"><?= c($tk, 'whatsapp_text') ?></span></span>
        </a>
      </div>
    </div>
  </section>

  <!-- ============ EMERGENCY NOTE ============ -->
<?php if ($emShow): ?>
  <section class="band-light">
    <div class="wrap-narrow emergency-wrap">
      <div class="emergency-note">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#124E4A" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
        <div>
          <div class="emergency-title"><?= public_e($emTitle) ?></div>
          <p><?= public_e($emText) ?></p>
<?php if ($emNumber !== ''): ?>
          <p><?= $emLabel !== '' ? public_e($emLabel) . ' ' : '' ?><?php if ($emHref !== ''): ?><a href="<?= public_e($emHref) ?>"><?= public_e($emNumber) ?></a><?php else: ?><?= public_e($emNumber) ?><?php endif; ?></p>
<?php endif; ?>
        </div>
      </div>
    </div>
  </section>
<?php endif; ?>

  <?php site_footer(false); ?>

</div>
<script src="assets/script.js?v=20261126"></script>
</body>
</html>
