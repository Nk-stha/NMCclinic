<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/seo_form.php';

$admin = require_admin();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    $_SESSION['seo_toast'] = ['type' => 'error', 'message' => 'Invalid SEO metadata selected.'];
    header('Location: seo.php');
    exit;
}

$errors = [];
$toast = $_SESSION['seo_toast'] ?? null;
unset($_SESSION['seo_toast']);

try {
    $targetOptions = seo_target_options();
    $mediaOptions = seo_media_options();
    $seo = seo_find((int) $id);
    if (!$seo) {
        $_SESSION['seo_toast'] = ['type' => 'error', 'message' => 'SEO metadata was not found.'];
        header('Location: seo.php');
        exit;
    }
} catch (Throwable $exception) {
    $_SESSION['seo_toast'] = ['type' => 'error', 'message' => 'SEO metadata is not available. Please confirm the latest schema has been imported.'];
    header('Location: seo.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $seo = array_merge($seo, seo_from_post());

    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid request. Please reload the page and try again.';
    }

    try {
        $errors = array_merge($errors, seo_validate($seo, (int) $id));
        if ($errors === []) {
            seo_save($seo, (int) $id);
            $_SESSION['seo_toast'] = ['type' => 'success', 'message' => 'SEO metadata updated successfully.'];
            header('Location: seo-edit.php?id=' . $id);
            exit;
        }
    } catch (Throwable $exception) {
        $errors[] = 'Unable to save SEO metadata. Please confirm the database schema is available and try again.';
    }
}

cms_header('Edit SEO Metadata', $admin, 'seo');
?>
<section class="content">
  <div class="page-head">
    <div>
      <h1>Edit SEO Metadata</h1>
      <p>Update metadata, canonical, robots, social preview, and schema override fields.</p>
    </div>
    <a class="secondary-btn" href="seo.php">Back to List</a>
  </div>

  <?php if ($toast): ?>
    <div class="toast <?= e((string) $toast['type']) ?>" role="status"><?= e((string) $toast['message']) ?></div>
  <?php endif; ?>

  <?php seo_render_form($seo, $targetOptions, $mediaOptions, 'seo-edit.php?id=' . (int) $id, 'edit', $errors); ?>
</section>
<?php cms_footer(); ?>
