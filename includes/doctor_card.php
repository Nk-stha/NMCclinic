<?php
declare(strict_types=1);

require_once __DIR__ . '/content.php';

/**
 * One doctor card, shared by the home page (compact) and the doctors page.
 * $labels holds the page's editable strings: card_photo_placeholder, card_reg_prefix, card_cta_label.
 */
function render_doctor_card(array $d, array $labels, bool $home): void
{
    $hours = implode(', ', array_map(static fn (array $r): string => $r['day']
        . ($r['start_time'] !== '' && $r['end_time'] !== '' ? ' ' . $r['start_time'] . '-' . $r['end_time'] : '')
        . ($r['location'] !== '' ? ' · ' . $r['location'] : ''), $d['schedules']));
    $calendar = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#286963" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>';
    ?>
        <article class="doc-card<?= $home ? ' is-home' : '' ?>" data-category="<?= public_e($d['specialty']) ?>">
          <div class="doc-photo"><?php if ($d['photo_path']): ?><img src="<?= public_e(content_safe_url($d['photo_path'])) ?>" alt="<?= public_e($d['photo_alt']) ?>" loading="lazy"><?php else: ?><span class="ph-label"><?= c($labels, 'card_photo_placeholder') ?></span><?php endif; ?><?php if (!$home): ?><span class="doc-tag"><?= public_e($d['specialty']) ?></span><?php endif; ?></div>
          <div class="doc-body">
<?php if ($home): ?>
            <span class="doc-chip"><?= public_e($d['specialty']) ?></span>
<?php endif; ?>
            <h3><?= public_e($d['full_name']) ?></h3>
<?php if ($d['qualifications']): ?>
            <p class="doc-qual"><?= public_e(implode(', ', $d['qualifications'])) ?></p>
<?php endif; ?>
<?php if ($d['registration_number'] !== ''): ?>
            <p class="doc-reg"><?= c($labels, 'card_reg_prefix') ?> <?= public_e($d['registration_number']) ?></p>
<?php endif; ?>
<?php if ($hours !== ''): ?>
            <div class="doc-hours"><?= $calendar ?> <?= public_e($hours) ?></div>
<?php endif; ?>
            <div class="doc-actions">
              <a class="<?= $home ? 'doc-view' : 'doc-view-solid' ?>" href="<?= $home ? '#appointment' : 'home#appointment' ?>"><?= c($labels, 'card_cta_label') ?></a>
            </div>
          </div>
        </article>
<?php
}
