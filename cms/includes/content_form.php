<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/content.php';

/** Message is safe to show to the admin; every other exception is logged and hidden. */
class ContentValidationException extends RuntimeException
{
}

/** Same query as doctor_media_options(); duplicated so this file does not pull in the doctor form. */
function content_media_options(): array
{
    $stmt = db()->query(
        "SELECT id, file_path, alt_text
         FROM media_assets
         WHERE deleted_at IS NULL AND is_public = 1 AND mime_type LIKE 'image/%'
         ORDER BY id DESC"
    );

    return $stmt->fetchAll();
}

/** Validate one field; returns [cleanValue, error|null]. */
function content_validate_field(array $f, mixed $raw): array
{
    $v = is_string($raw) ? trim(str_replace(["\r\n", "\r"], "\n", $raw)) : '';
    $type = $f['type'];
    if ($v === '') {
        // An empty image means "default image", so it is never required.
        return [$v, (!empty($f['required']) && $type !== 'image') ? 'This field is required.' : null];
    }
    if ($type === 'image') {
        return [$v, ctype_digit($v) && (int) $v > 0 ? null : 'Choose an image from the list.'];
    }
    if ($type === 'icon') {
        return [$v, isset(content_icons()[$v]) ? null : 'Choose an icon from the list.'];
    }
    if (($f['max'] ?? 0) > 0 && mb_strlen($v) > $f['max']) {
        return [$v, 'Must be ' . $f['max'] . ' characters or fewer.'];
    }
    if ($type === 'url' && content_safe_url($v) !== $v) {
        return [$v, 'Use a page name (e.g. contact), #section, https://…, tel: or mailto:.'];
    }

    return [$v, null];
}

/** @return array{values: array, items: list<array>, errors: array<string,string>} */
function content_validate_section(array $sectionSpec, array $input): array
{
    $errors = [];
    $values = [];
    $postedFields = is_array($input['fields'] ?? null) ? $input['fields'] : [];
    foreach ($sectionSpec['fields'] as $name => $f) {
        [$values[$name], $err] = content_validate_field($f, $postedFields[$name] ?? '');
        if ($err !== null) {
            $errors[$name] = $err;
        }
    }

    $items = [];
    $itemsSpec = $sectionSpec['items'] ?? null;
    if ($itemsSpec !== null) {
        $posted = is_array($input['items'] ?? null) ? array_values(array_filter($input['items'], 'is_array')) : [];
        foreach ($posted as $i => $row) {
            $item = [];
            foreach ($itemsSpec['fields'] as $name => $f) {
                [$item[$name], $err] = content_validate_field($f, $row[$name] ?? '');
                if ($err !== null) {
                    $errors["items.$i.$name"] = $err;
                }
            }
            $items[] = $item;
        }
        $n = count($items);
        if ($n < $itemsSpec['min'] || $n > $itemsSpec['max']) {
            $errors['items'] = 'Add between ' . $itemsSpec['min'] . ' and ' . $itemsSpec['max'] . ' entries.';
        }
    }

    return ['values' => $values, 'items' => $items, 'errors' => $errors];
}

function content_save_section(string $slug, string $sectionKey, array $values, array $items): void
{
    $spec = content_page_spec($slug);
    $section = $spec['sections'][$sectionKey] ?? throw new InvalidArgumentException('Unknown section: ' . $sectionKey);
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('SELECT id FROM pages WHERE slug = :s AND deleted_at IS NULL LIMIT 1');
        $stmt->execute([':s' => $slug]);
        $pageId = $stmt->fetchColumn();
        if ($pageId === false) {
            throw new RuntimeException('Page record not found. Run the content migration.');
        }

        $mediaIds = [];
        foreach ($section['fields'] as $n => $f) {
            if ($f['type'] === 'image' && ($values[$n] ?? '') !== '') {
                $mediaIds[] = (int) $values[$n];
            }
        }
        foreach ($items as $row) {
            foreach ($section['items']['fields'] ?? [] as $n => $f) {
                if ($f['type'] === 'image' && ($row[$n] ?? '') !== '') {
                    $mediaIds[] = (int) $row[$n];
                }
            }
        }
        $mediaIds = array_values(array_unique($mediaIds));
        if ($mediaIds) {
            $in = implode(',', array_fill(0, count($mediaIds), '?'));
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM media_assets WHERE id IN ($in) AND deleted_at IS NULL AND is_public = 1 AND mime_type LIKE 'image/%'");
            $stmt->execute($mediaIds);
            if ((int) $stmt->fetchColumn() !== count($mediaIds)) {
                throw new ContentValidationException('Selected image no longer exists.');
            }
        }

        $cols = [];
        $settings = [];
        foreach ($section['fields'] as $n => $f) {
            $v = $values[$n] ?? '';
            if (in_array($n, CONTENT_SECTION_COLUMNS, true)) {
                $cols[$n] = ($f['type'] === 'image') ? ($v === '' ? null : (int) $v) : $v;
            } else {
                $settings[$n] = ($f['type'] === 'image') ? ($v === '' ? '' : (int) $v) : $v;
            }
        }
        $params = [':page_id' => $pageId, ':key' => $sectionKey, ':settings' => $settings ? json_encode($settings, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) : null];
        $set = '';
        foreach (CONTENT_SECTION_COLUMNS as $c) {
            $params[":$c"] = $cols[$c] ?? null;
            $set .= ", $c = VALUES($c)";
        }
        $colList = implode(', ', CONTENT_SECTION_COLUMNS);
        $colParams = ':' . implode(', :', CONTENT_SECTION_COLUMNS);
        // Ruling R4: always published and visible; the loader ignores those flags.
        $pdo->prepare(
            "INSERT INTO page_sections (page_id, section_key, section_type, status, is_visible, settings_json, $colList)
             VALUES (:page_id, :key, :key2, 'published', 1, :settings, $colParams)
             ON DUPLICATE KEY UPDATE section_type = VALUES(section_type), status = 'published', is_visible = 1,
               deleted_at = NULL, settings_json = VALUES(settings_json)$set"
        )->execute($params + [':key2' => $sectionKey]);

        $stmt = $pdo->prepare('SELECT id FROM page_sections WHERE page_id = :p AND section_key = :k');
        $stmt->execute([':p' => $pageId, ':k' => $sectionKey]);
        $sectionId = (int) $stmt->fetchColumn();

        $pdo->prepare('DELETE FROM section_items WHERE section_id = :id')->execute([':id' => $sectionId]);
        if ($section['items'] !== null && $items) {
            $itemCols = CONTENT_ITEM_COLUMNS;
            $ins = $pdo->prepare(
                'INSERT INTO section_items (section_id, item_type, sort_order, is_visible, ' . implode(', ', $itemCols) . ')
                 VALUES (:sid, :type, :sort, 1, :' . implode(', :', $itemCols) . ')'
            );
            foreach (array_values($items) as $i => $row) {
                $p = [':sid' => $sectionId, ':type' => $section['items']['item_type'], ':sort' => $i];
                foreach ($itemCols as $c) {
                    $v = $row[$c] ?? null;
                    $p[":$c"] = ($c === 'media_id') ? (($v === null || $v === '') ? null : (int) $v) : $v;
                }
                $ins->execute($p);
            }
        }
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function content_reset_section(string $slug, string $sectionKey): void
{
    db()->prepare(
        'DELETE ps FROM page_sections ps JOIN pages p ON p.id = ps.page_id WHERE p.slug = :s AND ps.section_key = :k'
    )->execute([':s' => $slug, ':k' => $sectionKey]);
}

/** @return array<string, ?string> */
function content_last_updated(): array
{
    $out = [];
    foreach (db()->query(
        'SELECT p.slug, MAX(ps.updated_at) AS u FROM pages p JOIN page_sections ps ON ps.page_id = p.id AND ps.deleted_at IS NULL GROUP BY p.slug'
    )->fetchAll() as $r) {
        $out[$r['slug']] = $r['u'];
    }

    return $out;
}

/** One input/select/textarea for a field. $name is the full POST name; $value is the editor value. */
function content_render_field(array $f, string $name, mixed $value, array $mediaOptions, ?string $error, string $idPrefix): void
{
    $id = $idPrefix;
    $type = $f['type'];
    $max = (int) ($f['max'] ?? 0);
    $hint = match ($type) {
        'heading' => 'Wrap words in *asterisks* to highlight them.',
        'url' => 'Use a page name (e.g. contact), #section, https://…, tel: or mailto:',
        default => '',
    };
    $describedBy = trim(($hint !== '' ? $id . '-hint ' : '') . ($error ? $id . '-err' : ''));
    // Ties the hint and inline error to the control for screen readers.
    $aria = ($error ? ' aria-invalid="true"' : '') . ($describedBy !== '' ? ' aria-describedby="' . e($describedBy) . '"' : '')
        . (!empty($f['required']) && $type !== 'image' ? ' aria-required="true"' : '');
    $wide = in_array($type, ['textarea', 'heading'], true);
    ?>
    <div class="field field-<?= e($type) ?><?= $wide ? ' full' : '' ?><?= $error ? ' has-error' : '' ?>">
      <label for="<?= e($id) ?>"><?= e($f['label']) ?><?= !empty($f['required']) ? ' *' : '' ?></label>
      <?php if ($type === 'image'):
        $sel = is_numeric($value) ? (int) $value : 0;
        $default = (string) ($f['default']['src'] ?? '');
        $thumb = is_array($value) ? (string) ($value['src'] ?? '') : $default;
        foreach ($mediaOptions as $m) {
            if ((int) $m['id'] === $sel) {
                $thumb = (string) $m['file_path'];
            }
        }
        $custom = $sel > 0 && $thumb !== $default;
        ?>
        <div class="content-media">
          <select class="select" id="<?= e($id) ?>" name="<?= e($name) ?>"<?= $aria ?>>
            <option value="">Default image</option>
            <?php foreach ($mediaOptions as $m): ?>
              <option value="<?= (int) $m['id'] ?>" data-src="../<?= e((string) $m['file_path']) ?>"<?= (int) $m['id'] === $sel ? ' selected' : '' ?>><?= e('#' . $m['id'] . ' ' . basename((string) $m['file_path'])) ?></option>
            <?php endforeach; ?>
          </select>
          <div class="content-thumbs">
            <figure class="content-thumb" data-thumb="current"<?= $thumb === '' ? ' hidden' : '' ?>>
              <img src="<?= $thumb !== '' ? '../' . e($thumb) : '' ?>" alt="" width="72" height="72" loading="lazy">
              <figcaption><?= $custom ? 'Selected' : 'Default image' ?></figcaption>
            </figure>
            <?php if ($default !== ''): ?>
              <figure class="content-thumb" data-thumb="default"<?= $custom ? '' : ' hidden' ?>>
                <img src="../<?= e($default) ?>" alt="" width="72" height="72" loading="lazy">
                <figcaption>Default</figcaption>
              </figure>
            <?php endif; ?>
          </div>
        </div>
      <?php elseif ($type === 'icon'): $svg = content_icons()[(string) $value] ?? ''; ?>
        <div class="content-icon-pick">
          <span class="content-icon-preview" aria-hidden="true"><?= $svg /* trusted static SVG from content_icons() */ ?></span>
          <select class="select" id="<?= e($id) ?>" name="<?= e($name) ?>"<?= $aria ?>>
            <option value="">No icon</option>
            <?php foreach (array_keys(content_icons()) as $key): ?>
              <option value="<?= e($key) ?>"<?= (string) $value === $key ? ' selected' : '' ?>><?= e($key) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      <?php elseif ($type === 'textarea' || $type === 'heading'): ?>
        <textarea class="textarea" autocomplete="off" id="<?= e($id) ?>" name="<?= e($name) ?>" rows="<?= $type === 'heading' ? 2 : 4 ?>"<?= $aria ?><?= $max > 0 ? ' maxlength="' . $max . '"' : '' ?>><?= e((string) $value) ?></textarea>
      <?php elseif ($type === 'url'): ?>
        <div class="content-input-icon">
          <span class="material-symbols-outlined" aria-hidden="true">link</span>
          <input class="input" type="text" autocomplete="off" inputmode="url" autocapitalize="none" autocorrect="off" spellcheck="false" id="<?= e($id) ?>" name="<?= e($name) ?>" value="<?= e((string) $value) ?>"<?= $aria ?><?= $max > 0 ? ' maxlength="' . $max . '"' : '' ?>>
        </div>
      <?php else: ?>
        <input class="input" type="text" autocomplete="off" id="<?= e($id) ?>" name="<?= e($name) ?>" value="<?= e((string) $value) ?>"<?= $aria ?><?= $max > 0 ? ' maxlength="' . $max . '"' : '' ?>>
      <?php endif; ?>
      <?php if ($hint !== ''): ?><p class="hint" id="<?= e($id) ?>-hint"><?= e($hint) ?></p><?php endif; ?>
      <?php if ($error): ?><p class="content-field-error" id="<?= e($id) ?>-err"><span class="material-symbols-outlined" aria-hidden="true">error</span><?= e($error) ?></p><?php endif; ?>
    </div>
    <?php
}

/** Form for one section, as a collapsible panel. $values is in content_merge() shape (items under 'items'). */
function content_render_section(string $slug, string $key, array $section, array $values, array $errors, array $mediaOptions, string $viewUrl, bool $open = false): void
{
    $items = $section['items'] ?? null;
    $rows = $values['items'] ?? [];
    $meta = count($section['fields']) . (count($section['fields']) === 1 ? ' field' : ' fields');
    if ($items !== null) {
        $meta .= ' · ' . count($rows) . (count($rows) === 1 ? ' entry' : ' entries');
    }
    ?>
    <details class="card content-panel" id="section-<?= e($key) ?>"<?= $open || $errors ? ' open' : '' ?>>
      <summary class="content-panel-head">
        <span class="content-panel-title">
          <span class="content-panel-name"><?= e($section['label']) ?></span>
          <span class="content-panel-meta"><?= e($meta) ?></span>
        </span>
        <?php if ($errors): ?><span class="content-badge">Needs fixing</span><?php endif; ?>
        <span class="material-symbols-outlined content-chevron" aria-hidden="true">expand_more</span>
      </summary>
      <form class="content-section" method="post" action="content.php?page=<?= e(rawurlencode($slug)) ?>#section-<?= e($key) ?>">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="page" value="<?= e($slug) ?>">
        <input type="hidden" name="section" value="<?= e($key) ?>">
        <?php if ($errors): ?><p class="content-alert" role="alert"><span class="material-symbols-outlined" aria-hidden="true">error</span>Please fix the highlighted fields.</p><?php endif; ?>
        <?php if ($section['fields']): ?>
          <div class="form-grid">
            <?php foreach ($section['fields'] as $n => $f) {
                content_render_field($f, "fields[$n]", $values[$n] ?? '', $mediaOptions, $errors[$n] ?? null, "f-$key-$n");
            } ?>
          </div>
        <?php endif; ?>
        <?php if ($items !== null): ?>
          <div class="content-items-block">
            <div class="content-items-head">
              <h3><?= e($items['label']) ?></h3>
              <span class="content-count" data-min="<?= (int) $items['min'] ?>"><?= count($rows) ?> of <?= (int) $items['max'] ?> max<?= (int) $items['min'] > 0 ? ' · at least ' . (int) $items['min'] : '' ?></span>
            </div>
            <?php if (isset($errors['items'])): ?><p class="content-field-error" role="alert"><span class="material-symbols-outlined" aria-hidden="true">error</span><?= e($errors['items']) ?></p><?php endif; ?>
            <div class="content-items" data-max="<?= (int) $items['max'] ?>">
              <?php foreach ($rows as $i => $row): ?>
                <?php content_render_item_row($key, $items, (string) $i, $row, $errors, $mediaOptions); ?>
              <?php endforeach; ?>
            </div>
            <p class="content-empty"<?= $rows ? ' hidden' : '' ?>>No entries yet. Add one below.</p>
            <template class="content-item-template"><?php content_render_item_row($key, $items, '__i__', array_map(static fn (array $f) => $f['type'] === 'image' ? $f['default'] : '', $items['fields']), [], $mediaOptions); ?></template>
            <button type="button" class="secondary-btn content-add"><span class="material-symbols-outlined" aria-hidden="true">add</span>Add <?= e(strtolower($items['label'])) ?> entry</button>
            <p class="content-live sr-only" aria-live="polite"></p>
          </div>
        <?php endif; ?>
        <div class="content-actions">
          <button class="primary-btn content-save" type="submit">Save <?= e($section['label']) ?></button>
          <?php if ($viewUrl !== ''): ?><a class="content-view" href="<?= e($viewUrl) ?>" target="_blank" rel="noopener"><span class="content-label">View on site</span><span class="material-symbols-outlined" aria-hidden="true">open_in_new</span><span class="sr-only"> (opens in a new tab)</span></a><?php endif; ?>
          <button class="content-reset" type="submit" name="action" value="reset" formnovalidate onclick="return confirm('Reset this section to its default content? Your edits will be lost.')"><span class="material-symbols-outlined" aria-hidden="true">restart_alt</span><span class="content-label">Reset to default</span></button>
        </div>
      </form>
    </details>
    <?php
}

function content_render_item_row(string $key, array $items, string $i, array $row, array $errors, array $mediaOptions): void
{
    // Server rows number from 1; the template ('__i__') is numbered by the script.
    $n = ctype_digit($i) ? (string) ((int) $i + 1) : '';
    ?>
    <fieldset class="content-item">
      <legend class="sr-only content-item-name">Item <?= e($n) ?></legend>
      <div class="content-item-head">
        <span class="content-item-num" aria-hidden="true">Item <?= e($n) ?></span>
        <span class="content-item-tools">
          <button type="button" class="content-icon-btn content-up" aria-label="Move item <?= e($n) ?> up" title="Move up"><span class="material-symbols-outlined" aria-hidden="true">arrow_upward</span></button>
          <button type="button" class="content-icon-btn content-down" aria-label="Move item <?= e($n) ?> down" title="Move down"><span class="material-symbols-outlined" aria-hidden="true">arrow_downward</span></button>
          <button type="button" class="content-icon-btn content-remove" aria-label="Remove item <?= e($n) ?>" title="Remove"><span class="material-symbols-outlined" aria-hidden="true">delete</span></button>
        </span>
      </div>
      <div class="form-grid">
        <?php foreach ($items['fields'] as $fn => $f) {
            content_render_field($f, "items[$i][$fn]", $row[$fn] ?? '', $mediaOptions, $errors["items.$i.$fn"] ?? null, "i-$key-$i-$fn");
        } ?>
      </div>
    </fieldset>
    <?php
}

function content_render_script(): void
{
    ?>
<template id="content-icon-set"><?php foreach (content_icons() as $iconKey => $svg): ?><span data-icon="<?= e($iconKey) ?>"><?= $svg /* trusted static SVG */ ?></span><?php endforeach; ?></template>
<script>
document.querySelectorAll('.content-section').forEach(function (form) {
  var list = form.querySelector('.content-items');
  if (!list) return;
  var tpl = form.querySelector('.content-item-template');
  var add = form.querySelector('.content-add');
  var empty = form.querySelector('.content-empty');
  var count = form.querySelector('.content-count');
  var live = form.querySelector('.content-live');
  var max = parseInt(list.dataset.max, 10);
  var min = parseInt(count.dataset.min, 10);
  function sync() {
    var n = list.children.length;
    add.disabled = n >= max;
    empty.hidden = n > 0;
    count.textContent = n + ' of ' + max + ' max' + (min > 0 ? ' · at least ' + min : '');
  }
  function renumber() {
    Array.prototype.forEach.call(list.children, function (row, i) {
      var n = i + 1;
      row.querySelectorAll('[name]').forEach(function (el) {
        el.name = el.name.replace(/^items\[[^\]]*\]/, 'items[' + i + ']');
      });
      row.querySelector('.content-item-name').textContent = 'Item ' + n;
      row.querySelector('.content-item-num').textContent = 'Item ' + n;
      row.querySelector('.content-up').setAttribute('aria-label', 'Move item ' + n + ' up');
      row.querySelector('.content-down').setAttribute('aria-label', 'Move item ' + n + ' down');
      row.querySelector('.content-remove').setAttribute('aria-label', 'Remove item ' + n);
      row.querySelector('.content-up').disabled = i === 0;
      row.querySelector('.content-down').disabled = i === list.children.length - 1;
    });
  }
  add.addEventListener('click', function () {
    if (list.children.length >= max) return;
    var row = tpl.content.firstElementChild.cloneNode(true);
    var suffix = '-n' + Date.now();
    row.querySelectorAll('[id]').forEach(function (el) { el.id = el.id + suffix; });
    row.querySelectorAll('label[for]').forEach(function (el) { el.htmlFor = el.htmlFor + suffix; });
    row.querySelectorAll('[aria-describedby]').forEach(function (el) {
      el.setAttribute('aria-describedby', el.getAttribute('aria-describedby').split(' ').map(function (id) { return id + suffix; }).join(' '));
    });
    list.appendChild(row);
    renumber(); sync();
    live.textContent = 'Item ' + list.children.length + ' added.';
    var first = row.querySelector('input, select, textarea');
    if (first) first.focus();
  });
  list.addEventListener('click', function (ev) {
    var btn = ev.target.closest('button');
    var row = ev.target.closest('.content-item');
    if (!btn || !row) return;
    // Keep keyboard focus on the form after the focused row is removed.
    if (btn.classList.contains('content-remove')) { row.remove(); renumber(); sync(); live.textContent = 'Item removed.'; add.focus(); return; }
    else if (btn.classList.contains('content-up') && row.previousElementSibling) list.insertBefore(row, row.previousElementSibling);
    else if (btn.classList.contains('content-down') && row.nextElementSibling) list.insertBefore(row.nextElementSibling, row);
    else return;
    renumber(); sync();
    live.textContent = 'Moved to position ' + (Array.prototype.indexOf.call(list.children, row) + 1) + '.';
    // Moving the row can disable the pressed button at the list edge; keep focus inside the row.
    (btn.disabled ? row.querySelector('.content-up:not([disabled]), .content-down:not([disabled]), .content-remove') : btn).focus();
  });
  form.addEventListener('submit', renumber);
  renumber(); sync();
});
// Live previews for the image and icon pickers (rows added later included).
document.addEventListener('change', function (ev) {
  var el = ev.target;
  var media = el.closest('.content-media');
  if (media && el.tagName === 'SELECT') {
    var opt = el.options[el.selectedIndex];
    var cur = media.querySelector('[data-thumb="current"]');
    var def = media.querySelector('[data-thumb="default"]');
    var src = opt.dataset.src || (def ? def.querySelector('img').getAttribute('src') : '');
    cur.hidden = !src;
    if (src) cur.querySelector('img').src = src;
    cur.querySelector('figcaption').textContent = opt.value ? 'Selected' : 'Default image';
    if (def) def.hidden = !opt.value;
  }
  var pick = el.closest('.content-icon-pick');
  if (pick && el.tagName === 'SELECT') {
    var box = pick.querySelector('.content-icon-preview');
    var src2 = document.querySelector('#content-icon-set').content.querySelector('[data-icon="' + CSS.escape(el.value) + '"]');
    box.replaceChildren(src2 ? src2.firstElementChild.cloneNode(true) : '');
  }
});
// Each section is its own form: warn before a save, reset or link drops edits made in another section.
var dirty = new Set();
function markDirty(ev) { var f = ev.target.closest && ev.target.closest('.content-section'); if (f) dirty.add(f); }
document.addEventListener('input', markDirty);
document.addEventListener('change', markDirty);
document.querySelectorAll('.content-section').forEach(function (form) {
  form.addEventListener('click', function (ev) { if (ev.target.closest('.content-items button, .content-add')) dirty.add(form); });
  form.addEventListener('submit', function () { dirty.delete(form); });
});
window.addEventListener('beforeunload', function (ev) { if (dirty.size) { ev.preventDefault(); ev.returnValue = ''; } });
// After a failed save, put focus on the first invalid field.
var firstBad = document.querySelector('.content-editor [aria-invalid="true"]');
if (firstBad) firstBad.focus();
document.querySelectorAll('.content-toast-close').forEach(function (btn) {
  btn.addEventListener('click', function () { btn.closest('.toast').remove(); });
});
</script>
    <?php
}
