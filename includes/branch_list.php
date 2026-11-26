<?php
declare(strict_types=1);

require_once __DIR__ . '/content.php';

/** Map search term for a branch: coordinates when set, else the address. */
function site_branch_query(array $b): string
{
    return $b['lat'] !== '' && $b['lng'] !== '' ? $b['lat'] . ',' . $b['lng'] : $b['address'];
}

/**
 * The branch picker list, shared by the home and contact pages (script.js drives it via data-branch-*).
 * $labels holds the page's editable strings: map_show_label, map_showing_label.
 */
function render_branch_list(array $branches, array $labels, string $ariaLabel): void
{
    ?>
        <div class="branch-list" data-branch-list aria-label="<?= public_e($ariaLabel) ?>">
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
                <span class="branch-cta" data-branch-cta><?= c($labels, $on ? 'map_showing_label' : 'map_show_label') ?></span>
              </span>
            </button>
<?php if (($b['phone'] !== '' && $b['phone_href'] !== '') || $b['hours'] !== ''): ?>
            <div class="branch-meta"><?php if ($b['phone'] !== '' && $b['phone_href'] !== ''): ?><a href="<?= public_e($b['phone_href']) ?>"><?= public_e($b['phone']) ?></a><?php endif; ?><?php if ($b['hours'] !== ''): ?><span><?= public_e($b['hours']) ?></span><?php endif; ?></div>
<?php endif; ?>
          </div>
<?php endforeach; ?>
        </div>
<?php
}
