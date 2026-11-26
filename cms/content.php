<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/content_form.php';

$admin = require_admin();
$registry = content_registry();

$slug = (string) ($_POST['page'] ?? $_GET['page'] ?? '');
if ($slug !== '' && !isset($registry[$slug])) {
    http_response_code(404);
    exit('Page not found.');
}

$errors = [];     // section key => [path => message]
$posted = [];     // section key => values in merge shape, for re-render
$toast = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $spec = $registry[$slug] ?? null;
    $key = (string) ($_POST['section'] ?? '');
    if ($spec === null || !isset($spec['sections'][$key])) {
        http_response_code(400);
        exit('Unknown section.');
    }
    $section = $spec['sections'][$key];
    $fields = is_array($_POST['fields'] ?? null) ? $_POST['fields'] : [];
    $items = is_array($_POST['items'] ?? null) ? $_POST['items'] : [];

    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $toast = ['error', 'Invalid request. Please reload the page and try again.'];
        $norm = content_validate_section($section, ['fields' => $fields, 'items' => $items]);
        $posted[$key] = $norm['values'] + ['items' => $norm['items']];
    } elseif (($_POST['action'] ?? '') === 'reset') {
        try {
            content_reset_section($slug, $key);
            header('Location: content.php?page=' . rawurlencode($slug) . '&reset=' . rawurlencode($key) . '#section-' . $key);
            exit;
        } catch (Throwable $exception) {
            error_log('content reset: ' . $exception->getMessage());
            $toast = ['error', 'Unable to reset this section.'];
        }
    } else {
        $result = content_validate_section($section, ['fields' => $fields, 'items' => $items]);
        $posted[$key] = $result['values'] + ['items' => $result['items']];
        if ($result['errors'] === []) {
            try {
                content_save_section($slug, $key, $result['values'], $result['items']);
                header('Location: content.php?page=' . rawurlencode($slug) . '&saved=' . rawurlencode($key) . '#section-' . $key);
                exit;
            } catch (ContentValidationException $exception) {
                $toast = ['error', $exception->getMessage()];
            } catch (Throwable $exception) {
                error_log('content save: ' . $exception->getMessage());
                $toast = ['error', 'Unable to save. Please try again.'];
            }
        } else {
            $errors[$key] = $result['errors'];
            $toast = ['error', 'Please fix the highlighted fields.'];
        }
    }
}

foreach (['saved' => ' saved.', 'reset' => ' reset to default.'] as $param => $suffix) {
    $k = (string) ($_GET[$param] ?? '');
    if ($slug !== '' && $toast === null && isset($registry[$slug]['sections'][$k])) {
        $toast = ['success', $registry[$slug]['sections'][$k]['label'] . $suffix];
    }
}

// Public route per registry slug; no section anchors yet (public pages have none), '' hides the link.
$viewUrl = match ($slug) {
    'home', 'global' => '../',
    'blog', 'blog_detail' => '../blog',
    '404' => '',
    default => '../' . $slug,
};

cms_header('Page Content', $admin, 'content');
?>
<section class="content">
  <div class="page-head">
    <div>
      <h1>Page Content</h1>
      <p><?= $slug === '' ? 'Edit the text, images and links shown on each public page.' : e($registry[$slug]['label']) ?></p>
    </div>
    <?php if ($slug !== ''): ?><a class="secondary-btn" href="content.php">All pages</a><?php endif; ?>
  </div>

  <?php if ($toast): ?>
    <div class="toast <?= e($toast[0]) ?>" role="status"><?= e($toast[1]) ?></div>
  <?php endif; ?>

  <?php if ($slug === ''): ?>
    <?php
    try {
        $updated = content_last_updated();
    } catch (Throwable $exception) {
        $updated = [];
    }
    ?>
    <div class="card table-wrap">
      <table>
        <thead><tr><th>Page</th><th>Last updated</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($registry as $pageSlug => $pageSpec): ?>
            <tr>
              <td><?= e($pageSpec['label']) ?></td>
              <td><?= e($updated[$pageSlug] ?? 'Using defaults') ?></td>
              <td><a class="secondary-btn" href="content.php?page=<?= e(rawurlencode($pageSlug)) ?>">Edit</a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <?php
    $spec = $registry[$slug];
    try {
        $rows = content_load_rows($slug);
        $stored = content_merge($spec, $rows['sections'], $rows['items']);
        $mediaOptions = content_media_options();
    } catch (Throwable $exception) {
        error_log('content load: ' . $exception->getMessage());
        $stored = content_merge($spec, [], []);
        $mediaOptions = [];
        echo '<div class="toast error" role="status">Stored content could not be loaded; showing defaults.</div>';
    }
    foreach ($spec['sections'] as $key => $section) {
        content_render_section($slug, $key, $section, $posted[$key] ?? $stored[$key], $errors[$key] ?? [], $mediaOptions, $viewUrl);
    }
    content_render_script();
    ?>
  <?php endif; ?>
</section>
<?php cms_footer(); ?>
