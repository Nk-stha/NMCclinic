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
    ?>
    <div class="field<?= in_array($type, ['textarea', 'heading'], true) ? ' full' : '' ?>">
      <label for="<?= e($id) ?>"><?= e($f['label']) ?><?= !empty($f['required']) ? ' *' : '' ?></label>
      <?php if ($type === 'image'):
        $sel = is_numeric($value) ? (int) $value : 0;
        $thumb = is_array($value) ? (string) ($value['src'] ?? '') : (string) ($f['default']['src'] ?? '');
        foreach ($mediaOptions as $m) {
            if ((int) $m['id'] === $sel) {
                $thumb = (string) $m['file_path'];
            }
        }
        ?>
        <select class="select" id="<?= e($id) ?>" name="<?= e($name) ?>">
          <option value="">Default image</option>
          <?php foreach ($mediaOptions as $m): ?>
            <option value="<?= (int) $m['id'] ?>"<?= (int) $m['id'] === $sel ? ' selected' : '' ?>><?= e('#' . $m['id'] . ' ' . basename((string) $m['file_path'])) ?></option>
          <?php endforeach; ?>
        </select>
        <?php if ($thumb !== ''): ?><img src="../<?= e($thumb) ?>" alt="" width="64" height="64" style="object-fit:contain;border:1px solid var(--border);border-radius:8px;background:#fff"><?php endif; ?>
      <?php elseif ($type === 'icon'): ?>
        <select class="select" id="<?= e($id) ?>" name="<?= e($name) ?>">
          <option value="">No icon</option>
          <?php foreach (array_keys(content_icons()) as $key): ?>
            <option value="<?= e($key) ?>"<?= (string) $value === $key ? ' selected' : '' ?>><?= e($key) ?></option>
          <?php endforeach; ?>
        </select>
      <?php elseif ($type === 'textarea' || $type === 'heading'): ?>
        <textarea class="textarea" id="<?= e($id) ?>" name="<?= e($name) ?>" rows="<?= $type === 'heading' ? 2 : 4 ?>"<?= $max > 0 ? ' maxlength="' . $max . '"' : '' ?>><?= e((string) $value) ?></textarea>
        <?php if ($type === 'heading'): ?><p class="hint">Wrap words in *asterisks* to highlight them.</p><?php endif; ?>
      <?php else: ?>
        <input class="input" type="text" id="<?= e($id) ?>" name="<?= e($name) ?>" value="<?= e((string) $value) ?>"<?= $max > 0 ? ' maxlength="' . $max . '"' : '' ?>>
        <?php if ($type === 'url'): ?><p class="hint">Use a page name (e.g. contact), #section, https://…, tel: or mailto:</p><?php endif; ?>
      <?php endif; ?>
      <?php if ($error): ?><p class="hint" style="color:var(--danger)" role="alert"><?= e($error) ?></p><?php endif; ?>
    </div>
    <?php
}

/** Form for one section. $values is in content_merge() shape (items under 'items'). */
function content_render_section(string $slug, string $key, array $section, array $values, array $errors, array $mediaOptions, string $viewUrl): void
{
    $items = $section['items'] ?? null;
    ?>
    <form class="card form-card content-section" method="post" action="content.php?page=<?= e(rawurlencode($slug)) ?>#section-<?= e($key) ?>" id="section-<?= e($key) ?>" style="margin-bottom:24px">
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
      <input type="hidden" name="page" value="<?= e($slug) ?>">
      <input type="hidden" name="section" value="<?= e($key) ?>">
      <h2><?= e($section['label']) ?></h2>
      <?php if ($errors): ?><p class="hint" style="color:var(--danger)" role="alert">Please fix the highlighted fields.</p><?php endif; ?>
      <?php if ($section['fields']): ?>
        <div class="form-grid">
          <?php foreach ($section['fields'] as $n => $f) {
              content_render_field($f, "fields[$n]", $values[$n] ?? '', $mediaOptions, $errors[$n] ?? null, "f-$key-$n");
          } ?>
        </div>
      <?php endif; ?>
      <?php if ($items !== null): ?>
        <h3 style="margin:24px 0 8px"><?= e($items['label']) ?> <span class="hint">(<?= (int) $items['min'] ?>–<?= (int) $items['max'] ?>)</span></h3>
        <?php if (isset($errors['items'])): ?><p class="hint" style="color:var(--danger)" role="alert"><?= e($errors['items']) ?></p><?php endif; ?>
        <div class="content-items" data-max="<?= (int) $items['max'] ?>">
          <?php foreach ($values['items'] ?? [] as $i => $row): ?>
            <?php content_render_item_row($key, $items, (string) $i, $row, $errors, $mediaOptions); ?>
          <?php endforeach; ?>
        </div>
        <template class="content-item-template"><?php content_render_item_row($key, $items, '__i__', array_map(static fn (array $f) => $f['type'] === 'image' ? $f['default'] : '', $items['fields']), [], $mediaOptions); ?></template>
        <button type="button" class="secondary-btn content-add" style="margin-top:12px">Add <?= e(strtolower($items['label'])) ?> entry</button>
      <?php endif; ?>
      <div class="sticky-actions" style="position:static;margin:24px 0 0;padding:16px 0 0;background:none;backdrop-filter:none">
        <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:center">
          <button class="primary-btn" style="width:auto" type="submit">Save <?= e($section['label']) ?></button>
          <?php if ($viewUrl !== ''): ?><a class="secondary-btn" href="<?= e($viewUrl) ?>" target="_blank" rel="noopener">View on site</a><?php endif; ?>
        </div>
        <button class="secondary-btn" type="submit" name="action" value="reset" formnovalidate style="color:var(--danger);border-color:var(--danger)" onclick="return confirm('Reset this section to its default content? Your edits will be lost.')">Reset to default</button>
      </div>
    </form>
    <?php
}

function content_render_item_row(string $key, array $items, string $i, array $row, array $errors, array $mediaOptions): void
{
    ?>
    <fieldset class="content-item" style="border:1px solid var(--border);border-radius:10px;padding:14px;margin:0 0 12px">
      <div class="form-grid">
        <?php foreach ($items['fields'] as $n => $f) {
            content_render_field($f, "items[$i][$n]", $row[$n] ?? '', $mediaOptions, $errors["items.$i.$n"] ?? null, "i-$key-$i-$n");
        } ?>
      </div>
      <div style="display:flex;gap:8px;margin-top:12px">
        <button type="button" class="secondary-btn content-up" aria-label="Move up">Up</button>
        <button type="button" class="secondary-btn content-down" aria-label="Move down">Down</button>
        <button type="button" class="secondary-btn content-remove" style="color:var(--danger);border-color:var(--danger)">Remove</button>
      </div>
    </fieldset>
    <?php
}

function content_render_script(): void
{
    ?>
<script>
document.querySelectorAll('.content-section').forEach(function (form) {
  var list = form.querySelector('.content-items');
  if (!list) return;
  var tpl = form.querySelector('.content-item-template');
  var add = form.querySelector('.content-add');
  var max = parseInt(list.dataset.max, 10);
  function sync() { add.disabled = list.children.length >= max; }
  function renumber() {
    Array.prototype.forEach.call(list.children, function (row, i) {
      row.querySelectorAll('[name]').forEach(function (el) {
        el.name = el.name.replace(/^items\[[^\]]*\]/, 'items[' + i + ']');
      });
    });
  }
  add.addEventListener('click', function () {
    if (list.children.length >= max) return;
    var row = tpl.content.firstElementChild.cloneNode(true);
    row.querySelectorAll('[id]').forEach(function (el) { el.id = el.id + '-n' + Date.now(); });
    list.appendChild(row);
    renumber(); sync();
  });
  list.addEventListener('click', function (ev) {
    var row = ev.target.closest('.content-item');
    if (!row) return;
    if (ev.target.closest('.content-remove')) row.remove();
    else if (ev.target.closest('.content-up') && row.previousElementSibling) list.insertBefore(row, row.previousElementSibling);
    else if (ev.target.closest('.content-down') && row.nextElementSibling) list.insertBefore(row.nextElementSibling, row);
    else return;
    renumber(); sync();
  });
  form.addEventListener('submit', renumber);
  sync();
});
</script>
    <?php
}
