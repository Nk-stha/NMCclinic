<?php
declare(strict_types=1);

// CLI-only checks for includes/content*.php. Run: php cms/tools/content_check.php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../../includes/content.php';
require_once __DIR__ . '/../../includes/site_footer.php';

$failures = 0;
function check(string $name, bool $ok): void
{
    global $failures;
    if (!$ok) {
        $failures++;
    }
    echo ($ok ? 'PASS ' : 'FAIL ') . $name . PHP_EOL;
}

// db_down runs first: db() caches its PDO only on success, and this must hit a dead host before any other db() call.
putenv('NMC_DB_HOST=nonexistent.invalid');
$errLog = ini_set('error_log', '/dev/null');
$globalSpec = content_page_spec('global');
check('db_down_returns_defaults', page_content('global')['footer']['about'] === $globalSpec['sections']['footer']['fields']['about']['default']);
require_once __DIR__ . '/../../includes/site_data.php';
check('site_data_db_down_shapes', site_doctors() === [] && site_settings()['settings'] === null && site_branches() === [] && site_form_options() === ['specialties' => [], 'subjects' => []] && site_contact_page() === [] && !site_data_ok());
check('form_options_fallback', site_form_options_filled()['subjects'] !== [] && site_form_options_filled()['specialties'] !== []);
$footerOk = false;
if (function_exists('site_footer')) {
    ob_start();
    site_footer();
    $html = (string) ob_get_clean();
    $footerOk = !str_contains($html, 'Privacy Policy') && !str_contains($html, 'footer-legal') && str_contains($html, 'wa.me/9779800000000') && str_contains($html, 'Traffic Chowk');
}
check('footer_hides_legal_without_url', $footerOk);
ini_set('error_log', (string) $errLog);

$spec = ['label' => 'T', 'sections' => ['s' => [
    'label' => 'S',
    'fields' => [
        'heading' => ['type' => 'heading', 'label' => 'H', 'max' => 50, 'required' => true, 'default' => 'Def *h*'],
        'eyebrow' => ['type' => 'text', 'label' => 'E', 'max' => 50, 'required' => false, 'default' => 'Eb'],
        'tagline' => ['type' => 'text', 'label' => 'T', 'max' => 50, 'required' => false, 'default' => 'Tg'],
        'primary_media_id' => ['type' => 'image', 'label' => 'I', 'max' => 0, 'required' => false, 'default' => ['src' => 'a.jpg', 'alt' => 'Default alt']],
    ],
    'items' => ['item_type' => 'card', 'label' => 'Cards', 'min' => 0, 'max' => 5,
        'fields' => ['title' => ['type' => 'text', 'label' => 'T', 'max' => 50, 'required' => true, 'default' => 'IT']],
        'default' => [['title' => 'D1'], ['title' => 'D2']]],
]]];
$defaults = ['s' => ['heading' => 'Def *h*', 'eyebrow' => 'Eb', 'tagline' => 'Tg', 'primary_media_id' => ['src' => 'a.jpg', 'alt' => 'Default alt'], 'items' => [['title' => 'D1'], ['title' => 'D2']]]];

check('escapes_html_text', c(['x' => '<script>a</script>'], 'x') === '&lt;script&gt;a&lt;/script&gt;');
check('escapes_html_heading_accent',
    c_heading(['h' => '<b>Hi</b> *one visit*'], 'h') === '&lt;b&gt;Hi&lt;/b&gt; <em>one visit</em>'
    && str_contains(c_heading(['h' => '<b>Hi</b> *one visit*'], 'h', 'accent'), '<span class="accent">one visit</span>'));
check('paragraphs', content_paragraphs("a\nb\n\n<c>") === "<p>a<br>b</p><p>&lt;c&gt;</p>");
$bad = true;
foreach (['javascript:alert(1)', '  JaVaScRiPt:alert(1)', 'data:text/html,x', '//evil.com', 'vbscript:x'] as $u) {
    $bad = $bad && content_safe_url($u) === '#';
}
check('url_blocks_scripts', $bad);
$good = true;
foreach (['https://x.com/a', 'tel:+97771000000', 'mailto:a@b.c', '#appointment', 'contact', 'specialties#kidney'] as $u) {
    $good = $good && content_safe_url($u) === $u;
}
check('url_allows_safe', $good);
$bs = true;
foreach (["/\\evil.com", "\\\\evil.com", "/\t/evil.com", "java\nscript:alert(1)"] as $u) {
    $bs = $bs && content_safe_url($u) === '#';
}
check('url_blocks_backslash_and_control', $bs && content_safe_url('specialties#kidney') === 'specialties#kidney');
check('c_url_escapes', c_url(['u' => 'a?x=1&y="2"'], 'u') === 'a?x=1&amp;y=&quot;2&quot;');
check('c_icon_unknown_empty', c_icon(['i' => 'nope'], 'i') === '');

check('merge_no_rows_uses_defaults', content_merge($spec, [], []) === $defaults);
$m = content_merge($spec, ['s' => ['id' => 1, 'heading' => '', 'eyebrow' => '', 'settings_json' => null]], []);
check('required_empty_uses_default', $m['s']['heading'] === 'Def *h*' && $m['s']['eyebrow'] === '');
$m = content_merge($spec, ['s' => ['id' => 1, 'heading' => 'X', 'settings_json' => '{"tagline":"Hello"}']], []);
check('settings_json_field', $m['s']['tagline'] === 'Hello');
check('saved_empty_items_stay_empty', $m['s']['items'] === []);
$m = content_merge($spec, ['s' => ['id' => 1, 'heading' => 'X']], [1 => [['title' => ''], ['title' => 'Saved']]]);
check('saved_items_required_default', $m['s']['items'] === [['title' => 'IT'], ['title' => 'Saved']]);

$r = content_resolve_images($spec, ['s' => ['primary_media_id' => 99] + $defaults['s']], [5 => ['file_path' => 'uploads/x.jpg', 'alt_text' => 'X alt']]);
$r2 = content_resolve_images($spec, ['s' => ['primary_media_id' => 5] + $defaults['s']], [5 => ['file_path' => 'uploads/x.jpg', 'alt_text' => '']]);
$r3 = content_resolve_images($spec, ['s' => ['primary_media_id' => 5] + $defaults['s']], [5 => ['file_path' => 'uploads/x.jpg', 'alt_text' => 'X alt']]);
check('image_falls_back_when_media_missing',
    $r['s']['primary_media_id'] === ['src' => 'a.jpg', 'alt' => 'Default alt']
    && $r3['s']['primary_media_id'] === ['src' => 'uploads/x.jpg', 'alt' => 'X alt']
    && $r2['s']['primary_media_id'] === ['src' => 'uploads/x.jpg', 'alt' => 'Default alt']);

$ok = true;
$check = static function (array $fields) use (&$ok): void {
    foreach ($fields as $f) {
        if (!array_key_exists('default', $f)) { $ok = false; continue; }
        if ($f['type'] === 'image') {
            $ok = $ok && is_array($f['default']) && isset($f['default']['src'], $f['default']['alt']);
            continue;
        }
        if (!is_string($f['default'])) { $ok = false; continue; }
        if (!empty($f['required']) && $f['default'] === '') { $ok = false; }
        if ($f['type'] !== 'icon' && $f['max'] > 0 && mb_strlen($f['default']) > $f['max']) { $ok = false; }
    }
};
foreach (content_registry() as $page) {
    foreach ($page['sections'] as $s) {
        $check($s['fields']);
        foreach (($s['items']['default'] ?? []) as $row) {
            foreach ($row as $name => $v) {
                $f = $s['items']['fields'][$name] ?? null;
                if ($f === null || !is_string($v) && $f['type'] !== 'image') { $ok = false; }
                elseif (is_string($v) && $f['max'] > 0 && mb_strlen($v) > $f['max']) { $ok = false; }
            }
        }
        if (isset($s['items'])) {
            $check($s['items']['fields']);
            foreach (array_keys($s['items']['fields']) as $n) {
                $ok = $ok && in_array($n, CONTENT_ITEM_COLUMNS, true);
            }
        }
    }
}
check('registry_defaults_complete', $ok);

// R10: a field's max must fit its DB column (body / settings_json fields are unlimited).
$secLimits = ['eyebrow' => 100, 'heading' => 255, 'subheading' => 255, 'primary_cta_label' => 100, 'secondary_cta_label' => 100, 'primary_cta_url' => 500, 'secondary_cta_url' => 500];
$itemLimits = ['label' => 100, 'title' => 255, 'subtitle' => 255, 'value_text' => 100, 'icon_key' => 100, 'link_label' => 100, 'link_url' => 500];
$tooLong = [];
foreach (content_registry() as $pSlug => $pg) {
    foreach ($pg['sections'] as $sKey => $sec) {
        foreach ($sec['fields'] as $fName => $fSpec) {
            if (isset($secLimits[$fName]) && (int) $fSpec['max'] > $secLimits[$fName]) {
                $tooLong[] = "$pSlug.$sKey.$fName";
            }
        }
        foreach ($sec['items']['fields'] ?? [] as $fName => $fSpec) {
            if (isset($itemLimits[$fName]) && (int) $fSpec['max'] > $itemLimits[$fName]) {
                $tooLong[] = "$pSlug.$sKey.items.$fName";
            }
        }
    }
}
if ($tooLong) {
    fwrite(STDERR, 'max exceeds column: ' . implode(', ', $tooLong) . "\n");
}
check('registry_max_fits_columns', $tooLong === []);

$h = static fn (string $d, string $o, string $c, bool $x = false, string $l = ''): array => ['day' => $d, 'opens_at' => $o, 'closes_at' => $c, 'label' => $l, 'is_closed' => $x];
check('hours_lines_groups_days', hours_lines([$h('Sunday', '08:00', '20:00'), $h('Monday', '08:00', '20:00'), $h('Friday', '08:00', '20:00'), $h('Saturday', '09:00', '14:00')]) === ['Sun-Fri: 8:00 AM - 8:00 PM', 'Saturday: 9:00 AM - 2:00 PM']
    && hours_lines([$h('Sunday', '', '', true), $h('Monday', '', '', false, 'By appointment'), $h('Tuesday', '', '')]) === ['Sunday: Closed', 'Monday: By appointment']);

$_SERVER['HTTP_HOST'] = 'example.test';
$base = public_base_url();
check('abs_url_prefixes_relative_only',
    content_abs_url('about') === $base . '/about' && content_abs_url('home') === $base . '/' && content_abs_url('home#appointment') === $base . '/#appointment' && content_abs_url('homepage') === $base . '/homepage' && content_abs_url('specialties#kidney') === $base . '/specialties#kidney'
    && content_abs_url('#x') === '#x' && content_abs_url('https://a.b/c') === 'https://a.b/c' && content_abs_url('tel:+977') === 'tel:+977'
    && content_abs_url('mailto:a@b.c') === 'mailto:a@b.c' && content_abs_url('/x') === '/x' && c_href('javascript:alert(1)') === '#'
    && c_href('a?x=1&y=2') === public_e($base . '/a?x=1&y=2'));

$headerOk = false;
if (function_exists('site_header')) {
    ob_start();
    site_header('about');
    $html = (string) ob_get_clean();
    $headerOk = substr_count($html, 'aria-current="page"') === 2 && preg_match_all('/<a href="[^"]*about" aria-current="page">About<\/a>/', $html) === 2;
}
check('header_marks_active', $headerOk);

$seo = (string) file_get_contents(__DIR__ . '/../../includes/public_seo.php') . (string) file_get_contents(__DIR__ . '/../includes/seo_form.php');
check('sitemap_excludes_generic', substr_count($seo, "page_type <> \"generic\"") >= 2);

// content_validate_section (cms/includes/content_form.php)
require_once __DIR__ . '/../includes/content_form.php';
$vs = $spec['sections']['s'];
$vs['fields']['heading']['max'] = 10;
$vs['fields']['cta'] = ['type' => 'url', 'label' => 'U', 'max' => 50, 'required' => false, 'default' => ''];
$vs['fields']['ic'] = ['type' => 'icon', 'label' => 'I', 'max' => 0, 'required' => false, 'default' => ''];
$vs['items']['min'] = 1;
$vs['items']['max'] = 3;
$vs['items']['fields']['title']['required'] = true;
$goodItems = [['title' => 'a']];
$val = static fn (array $f, ?array $items = null): array => content_validate_section($vs, ['fields' => $f + ['heading' => 'ok'], 'items' => $items ?? $goodItems]);
check('validate_required', isset($val(['heading' => '  '])['errors']['heading']) && $val([])['errors'] === []);
check('validate_counts_multibyte', !isset($val(['heading' => str_repeat('क', 10)])['errors']['heading']) && isset($val(['heading' => str_repeat('क', 11)])['errors']['heading']));
check('validate_rejects_bad_url', isset($val(['cta' => 'javascript:x'])['errors']['cta']) && !isset($val(['cta' => 'contact'])['errors']['cta']) && !isset($val(['cta' => ''])['errors']['cta']));
check('validate_items_bounds', isset($val([], [])['errors']['items']) && isset($val([], [['title' => 'a'], ['title' => 'a'], ['title' => 'a'], ['title' => 'a']])['errors']['items']) && !isset($val([], $goodItems)['errors']['items']));
check('validate_item_field_path', isset($val([], [['title' => 'a'], ['title' => 'b'], ['title' => '']])['errors']['items.2.title']));
check('validate_icon_whitelist', isset($val(['ic' => 'nope'])['errors']['ic']) && !isset($val(['ic' => 'kidney'])['errors']['ic']) && !isset($val(['ic' => ''])['errors']['ic']));
check('validate_image_rejects_non_numeric', isset($val(['primary_media_id' => 'abc'])['errors']['primary_media_id']) && isset($val(['primary_media_id' => '-3'])['errors']['primary_media_id']) && isset($val(['primary_media_id' => '1.5'])['errors']['primary_media_id']));
check('validate_image_and_normalise', isset($val(['primary_media_id' => 'abc'])['errors']['primary_media_id']) && isset($val(['primary_media_id' => '0'])['errors']['primary_media_id'])
    && $val(['primary_media_id' => '7'])['values']['primary_media_id'] === '7' && $val(['heading' => " a\r\nb "])['values']['heading'] === "a\nb");

exit($failures ? 1 : 0);
