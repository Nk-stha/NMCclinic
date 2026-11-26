<?php
declare(strict_types=1);

require_once __DIR__ . '/public_seo.php';
require_once __DIR__ . '/content_registry.php';
require_once __DIR__ . '/content_icons.php';

/** Value of one field from a DB row (column or settings), applying the required-default rule. */
function content_field_value(array $field, string $name, array $row, array $columns, array $settings): mixed
{
    $source = in_array($name, $columns, true) ? $row : $settings;
    if (!array_key_exists($name, $source)) {
        return $field['default'];
    }
    $value = $source[$name];
    if ($field['type'] === 'image') {
        return ($value === null || $value === '') ? $field['default'] : $value;
    }
    $value = $value === null ? '' : (string) $value;

    return ($value === '' && !empty($field['required'])) ? $field['default'] : $value;
}

function content_merge(array $pageSpec, array $sectionRows, array $itemRowsBySectionId): array
{
    $out = [];
    foreach ($pageSpec['sections'] as $key => $section) {
        $row = $sectionRows[$key] ?? null;
        $items = $section['items'] ?? null;
        if ($row === null) {
            $values = array_map(static fn (array $f) => $f['default'], $section['fields']);
            if ($items !== null) {
                $values['items'] = array_map(static function (array $d) use ($items): array {
                    $r = [];
                    foreach ($items['fields'] as $n => $f) {
                        $r[$n] = $d[$n] ?? $f['default'];
                    }
                    return $r;
                }, $items['default']);
            }
            $out[$key] = $values;
            continue;
        }

        $decoded = json_decode((string) ($row['settings_json'] ?? ''), true);
        $settings = is_array($decoded) ? $decoded : [];
        $values = [];
        foreach ($section['fields'] as $n => $f) {
            $values[$n] = content_field_value($f, $n, $row, CONTENT_SECTION_COLUMNS, $settings);
        }
        if ($items !== null) {
            $values['items'] = [];
            foreach ($itemRowsBySectionId[$row['id'] ?? 0] ?? [] as $itemRow) {
                $item = [];
                foreach ($items['fields'] as $n => $f) {
                    $item[$n] = content_field_value($f, $n, $itemRow, CONTENT_ITEM_COLUMNS, []);
                }
                $values['items'][] = $item;
            }
        }
        $out[$key] = $values;
    }

    return $out;
}

function content_resolve_image(array $field, mixed $value, array $mediaById): array
{
    if (is_array($value)) {
        return ['src' => (string) ($value['src'] ?? ''), 'alt' => (string) ($value['alt'] ?? '')];
    }
    $default = $field['default'];
    if (is_numeric($value) && isset($mediaById[(int) $value]) && (string) ($mediaById[(int) $value]['file_path'] ?? '') !== '') {
        $m = $mediaById[(int) $value];
        $alt = trim((string) ($m['alt_text'] ?? ''));
        return ['src' => (string) $m['file_path'], 'alt' => $alt !== '' ? $alt : $default['alt']];
    }

    return $default;
}

function content_resolve_images(array $pageSpec, array $merged, array $mediaById): array
{
    foreach ($pageSpec['sections'] as $key => $section) {
        foreach ($section['fields'] as $n => $f) {
            if ($f['type'] === 'image' && isset($merged[$key][$n])) {
                $merged[$key][$n] = content_resolve_image($f, $merged[$key][$n], $mediaById);
            }
        }
        foreach ($merged[$key]['items'] ?? [] as $i => $item) {
            foreach ($section['items']['fields'] as $n => $f) {
                if ($f['type'] === 'image') {
                    $merged[$key]['items'][$i][$n] = content_resolve_image($f, $item[$n] ?? null, $mediaById);
                }
            }
        }
    }

    return $merged;
}

/** Raw DB fetch: sections keyed by section_key, items grouped by section_id. Throws on DB error. */
function content_load_rows(string $slug): array
{
    $stmt = db()->prepare(
        'SELECT ps.* FROM page_sections ps JOIN pages p ON p.id = ps.page_id
         WHERE p.slug = :slug AND p.deleted_at IS NULL AND ps.deleted_at IS NULL'
    );
    $stmt->execute([':slug' => $slug]);
    $sections = [];
    foreach ($stmt->fetchAll() as $row) {
        $sections[$row['section_key']] = $row;
    }

    $items = [];
    if ($sections) {
        $ids = array_map(static fn (array $r) => (int) $r['id'], array_values($sections));
        $in = implode(',', array_fill(0, count($ids), '?'));
        $stmt = db()->prepare("SELECT * FROM section_items WHERE section_id IN ($in) AND deleted_at IS NULL ORDER BY sort_order ASC, id ASC");
        $stmt->execute($ids);
        foreach ($stmt->fetchAll() as $row) {
            $items[(int) $row['section_id']][] = $row;
        }
    }

    return ['sections' => $sections, 'items' => $items];
}

function page_content(string $slug): array
{
    static $cache = [];
    if (isset($cache[$slug])) {
        return $cache[$slug];
    }

    $spec = content_page_spec($slug);
    try {
        $rows = content_load_rows($slug);
        $merged = content_merge($spec, $rows['sections'], $rows['items']);

        $mediaIds = [];
        $collect = static function (array $fields, array $values) use (&$mediaIds): void {
            foreach ($fields as $n => $f) {
                if ($f['type'] === 'image' && is_numeric($values[$n] ?? null)) {
                    $mediaIds[] = (int) $values[$n];
                }
            }
        };
        foreach ($spec['sections'] as $key => $section) {
            $collect($section['fields'], $merged[$key]);
            foreach ($merged[$key]['items'] ?? [] as $item) {
                $collect($section['items']['fields'], $item);
            }
        }
        $media = [];
        if ($mediaIds) {
            $mediaIds = array_values(array_unique($mediaIds));
            $in = implode(',', array_fill(0, count($mediaIds), '?'));
            $stmt = db()->prepare("SELECT id, file_path, alt_text FROM media_assets WHERE id IN ($in) AND deleted_at IS NULL");
            $stmt->execute($mediaIds);
            foreach ($stmt->fetchAll() as $m) {
                $media[(int) $m['id']] = $m;
            }
        }
        $content = content_resolve_images($spec, $merged, $media);
    } catch (Throwable $e) {
        error_log('page_content(' . $slug . '): ' . $e->getMessage());
        $content = content_resolve_images($spec, content_merge($spec, [], []), []);
    }

    return $cache[$slug] = $content;
}

function content_safe_url(string $url): string
{
    $url = trim($url);
    // Browsers treat backslashes as slashes and strip tabs/newlines, so these can escape to another host.
    if (preg_match('/[\x00-\x1F\x7F\\\\]/', $url)) {
        return '#';
    }
    if (preg_match('/^(https?:|tel:|mailto:|#)/i', $url)) {
        return $url;
    }
    if ($url !== '' && !str_starts_with($url, '//') && !preg_match('/^[^\/]*:/', $url)) {
        return $url;
    }

    return '#';
}

function content_paragraphs(string $text): string
{
    $html = '';
    foreach (preg_split('/\R{2,}/', trim($text)) ?: [] as $p) {
        $p = trim($p);
        if ($p !== '') {
            $html .= '<p>' . preg_replace('/\R/', '<br>', public_e($p)) . '</p>';
        }
    }

    return $html;
}

function c(array $values, string $field): string
{
    return public_e((string) ($values[$field] ?? ''));
}

function c_heading(array $values, string $field, string $accent = 'em'): string
{
    $open = $accent === 'accent' ? '<span class="accent">' : '<em>';
    $close = $accent === 'accent' ? '</span>' : '</em>';

    return preg_replace('/\*([^*]+)\*/', $open . '$1' . $close, c($values, $field)) ?? '';
}

function c_text(array $values, string $field): string
{
    return content_paragraphs((string) ($values[$field] ?? ''));
}

function c_url(array $values, string $field): string
{
    return public_e(content_safe_url((string) ($values[$field] ?? '')));
}

function c_img(array $values, string $field): array
{
    $img = is_array($values[$field] ?? null) ? $values[$field] : [];

    return ['src' => public_e((string) ($img['src'] ?? '')), 'alt' => public_e((string) ($img['alt'] ?? ''))];
}

function c_icon(array $values, string $field): string
{
    return content_icons()[(string) ($values[$field] ?? '')] ?? '';
}
