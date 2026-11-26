<?php
declare(strict_types=1);

require_once __DIR__ . '/content.php';
require_once __DIR__ . '/site_data.php';

/** Phone/WhatsApp from Settings, falling back to the values the site shipped with. */
function site_contact_defaults(): array
{
    $s = site_settings();
    $m = $s['settings'] ?? [];

    return [
        'settings' => $s,
        'phone' => ($m['primary_phone'] ?? '') !== '' ? $m['primary_phone'] : '+977 71-000000',
        'phone_href' => ($m['primary_phone_href'] ?? '') !== '' ? $m['primary_phone_href'] : 'tel:+97771000000',
        'whatsapp' => ($m['whatsapp_url'] ?? '') !== '' ? $m['whatsapp_url'] : 'https://wa.me/9779800000000',
        // Shipped fallbacks; the real values are edited in CMS > Settings.
        'email' => ($m['email'] ?? '') !== '' ? (string) $m['email'] : 'info@nmcbutwal.com.np',
        'address' => ($m['address'] ?? '') !== '' ? (string) $m['address'] : 'Traffic Chowk, Butwal, Rupandehi, Nepal',
        'hours_fallback' => ['Sun-Fri: 8 AM-8 PM', 'Saturday: 9 AM-2 PM'],
    ];
}

/** $active: home|about|specialties|doctors|blog|contact. */
function site_header(string $active): void
{
    $g = page_content('global');
    $h = $g['header'];
    $ct = site_contact_defaults();
    $logo = c_img($h, 'logo');
    $phoneSvg = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#286963" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>';
    $links = static function (string $indent) use ($g, $active): string {
        $out = [];
        foreach ($g['nav']['items'] as $item) {
            $url = (string) $item['link_url'];
            $current = strtok($url, '#?') === $active ? ' aria-current="page"' : '';
            $out[] = $indent . '<a href="' . c_href($url) . '"' . $current . '>' . c($item, 'label') . '</a>';
        }

        return implode("\n", $out);
    };
    ?>
  <header class="site-header">
    <div class="header-bar">
      <a class="brand" href="<?= c_href('home') ?>"<?= $active === 'home' ? ' aria-current="page"' : '' ?>>
        <img src="<?= c_href((string) ($h['logo']['src'] ?? '')) ?>" alt="<?= $logo['alt'] ?>" width="48" height="48">
        <span class="brand-text">
          <span class="brand-name"><?= c($h, 'brand_name') ?></span>
          <span class="brand-sub"><?= c($h, 'brand_sub') ?></span>
        </span>
      </a>

      <nav class="nav-main" aria-label="Primary">
<?= $links('        ') ?>

      </nav>

      <div class="header-actions">
        <a class="header-phone" href="<?= public_e($ct['phone_href']) ?>">
          <?= $phoneSvg ?>

          <?= public_e($ct['phone']) ?>

        </a>
        <a class="btn btn-primary" href="<?= c_href((string) $h['cta_url']) ?>"><?= c($h, 'cta_label') ?></a>
      </div>

      <button class="nav-toggle" type="button" aria-label="<?= c($h, 'menu_open_label') ?>" aria-expanded="false" data-label-open="<?= c($h, 'menu_open_label') ?>" data-label-close="<?= c($h, 'menu_close_label') ?>">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="4" y1="7" x2="20" y2="7"></line><line x1="4" y1="12" x2="20" y2="12"></line><line x1="4" y1="17" x2="20" y2="17"></line></svg>
      </button>
    </div>

    <div class="nav-mobile">
<?= $links('      ') ?>

      <div class="nav-mobile-cta">
        <a class="m-book" href="<?= c_href((string) $h['cta_url']) ?>"><?= c($h, 'cta_label') ?></a>
        <a class="m-call" href="<?= public_e($ct['phone_href']) ?>"><?= c($g['mobile_bar'], 'call_label') ?> <?= public_e($ct['phone']) ?></a>
      </div>
    </div>
  </header>
<?php
}
