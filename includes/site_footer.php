<?php
declare(strict_types=1);

require_once __DIR__ . '/site_header.php';

function site_hours_line(array $hour): string
{
    return $hour['day'] . ': ' . ($hour['is_closed'] ? 'Closed' : ($hour['label'] ?: $hour['opens_at'] . '-' . $hour['closes_at']));
}

/** $details: also list email and first/last opening hours under Contact (home and contact pages show the address only). */
function site_footer(bool $details = true): void
{
    $g = page_content('global');
    $f = $g['footer'];
    $m = $g['mobile_bar'];
    $ct = site_contact_defaults();
    $set = $ct['settings']['settings'] ?? null;
    $hours = $ct['settings']['hours'];
    $contact = ['<span>' . public_e($ct['address']) . '</span>'];
    if ($details) {
        $email = $set === null ? $ct['email'] : (string) $set['email'];
        if ($email !== '') {
            $contact[] = '<a href="mailto:' . public_e($email) . '">' . public_e($email) . '</a>';
        }
        if ($set === null) {
            foreach ($ct['hours_fallback'] as $line) {
                $contact[] = '<span>' . public_e($line) . '</span>';
            }
        } elseif ($hours) {
            $contact[] = '<span>' . public_e(site_hours_line($hours[0])) . '</span>';
            if (count($hours) > 1) {
                $contact[] = '<span>' . public_e(site_hours_line($hours[count($hours) - 1])) . '</span>';
            }
        }
    }
    $legal = [];
    foreach (['privacy', 'terms', 'accessibility'] as $k) {
        $url = (string) ($set[$k . '_url'] ?? '');
        if ($url !== '') {
            $legal[] = '<a href="' . c_href($url) . '">' . c($f, 'legal_' . $k . '_label') . '</a>';
        }
    }
    $logo = c_img($f, 'logo');
    ?>
  <footer class="site-footer">
    <div class="footer-in">
      <div class="footer-grid">
        <div>
          <div class="footer-brand">
            <img src="<?= c_href((string) ($f['logo']['src'] ?? '')) ?>" alt="<?= $logo['alt'] ?>" width="44" height="44" loading="lazy">
            <span><?= c($g['header'], 'brand_name') ?></span>
          </div>
          <p class="footer-about"><?= c($f, 'about') ?></p>
          <a class="footer-phone" href="<?= public_e($ct['phone_href']) ?>"><?= public_e($ct['phone']) ?></a>
        </div>
        <div>
          <h4><?= c($f, 'services_heading') ?></h4>
          <div class="footer-list">
<?php foreach ($g['footer_services']['items'] as $item): ?>
            <a href="<?= c_href((string) $item['link_url']) ?>"><?= c($item, 'label') ?></a>
<?php endforeach; ?>
          </div>
        </div>
        <div>
          <h4><?= c($f, 'contact_heading') ?></h4>
          <div class="footer-list footer-contact">
            <?= implode("\n            ", $contact) ?>

          </div>
        </div>
      </div>

      <div class="footer-bottom">
        <span>© <span data-year>2026</span> <?= c($f, 'copyright') ?></span>
<?php if ($legal): ?>
        <div class="footer-legal">
          <?= implode("\n          ", $legal) ?>

        </div>
<?php endif; ?>
      </div>
    </div>
  </footer>

  <a class="wa-float" href="<?= public_e($ct['whatsapp']) ?>" target="_blank" rel="noopener" aria-label="<?= c($m, 'float_aria_label') ?>">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="#ffffff" aria-hidden="true"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2 22l5.25-1.38c1.45.79 3.08 1.21 4.79 1.21h.01c5.46 0 9.91-4.45 9.91-9.91C21.96 6.45 17.5 2 12.04 2zm5.8 14.04c-.24.68-1.42 1.32-1.95 1.37-.5.05-1.13.07-1.83-.11-.42-.11-.96-.29-1.66-.59-2.92-1.26-4.83-4.2-4.98-4.4-.15-.2-1.19-1.58-1.19-3.01 0-1.43.75-2.13 1.02-2.42.27-.29.58-.36.78-.36.19 0 .39 0 .56.01.18.01.42-.07.66.5.24.59.82 2.02.89 2.17.07.15.12.32.02.51-.09.2-.14.32-.28.49-.14.17-.29.38-.42.51-.14.14-.28.29-.12.57.16.28.72 1.19 1.55 1.93 1.06.95 1.96 1.24 2.24 1.38.28.14.44.12.6-.07.16-.19.69-.8.87-1.08.18-.28.36-.23.61-.14.25.09 1.61.76 1.89.9.28.14.46.21.53.32.07.12.07.66-.17 1.34z"></path></svg>
    <?= c($m, 'float_label') ?>

  </a>

  <nav class="mobile-bar" aria-label="Quick contact">
    <a href="<?= public_e($ct['phone_href']) ?>"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"></path></svg><?= c($m, 'call_label') ?></a>
    <a href="<?= public_e($ct['whatsapp']) ?>" target="_blank" rel="noopener"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2 22l5.25-1.38c1.45.79 3.08 1.21 4.79 1.21h.01c5.46 0 9.91-4.45 9.91-9.91C21.96 6.45 17.5 2 12.04 2z"></path></svg><?= c($m, 'whatsapp_label') ?></a>
    <a class="is-book" href="<?= c_href((string) $m['book_url']) ?>"><?= c($m, 'book_label') ?></a>
  </nav>
<?php
}
