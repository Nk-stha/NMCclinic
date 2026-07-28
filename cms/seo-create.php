<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/seo_form.php';

$admin = require_admin();
$seo = seo_default();
$errors = [];
$toast = null;

try {
    $targetOptions = seo_target_options();
    $mediaOptions = seo_media_options();
} catch (Throwable $exception) {
    $targetOptions = ['page' => [], 'doctor' => [], 'blog_post' => []];
    $mediaOptions = [];
    $toast = ['type' => 'error', 'message' => 'SEO dropdown options are not available. Please confirm the latest schema has been imported.'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $seo = seo_from_post();

    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid request. Please reload the page and try again.';
    }

    try {
        $errors = array_merge($errors, seo_validate($seo));
        if ($errors === []) {
            $id = seo_save($seo);
            $_SESSION['seo_toast'] = ['type' => 'success', 'message' => 'SEO metadata created successfully.'];
            header('Location: seo-edit.php?id=' . $id);
            exit;
        }
    } catch (Throwable $exception) {
        $errors[] = 'Unable to save SEO metadata. Please confirm the database schema is available and try again.';
    }
}

cms_header('Create SEO Metadata', $admin, 'seo');
?>
<section class="content">
  <div class="page-head">
    <div>
      <h1>Create SEO Metadata</h1>
      <p>Add metadata for one page, doctor, or blog post.</p>
    </div>
    <a class="secondary-btn" href="seo.php">Back to List</a>
  </div>

  <?php if ($toast): ?>
    <div class="toast <?= e((string) $toast['type']) ?>" role="status"><?= e((string) $toast['message']) ?></div>
  <?php endif; ?>

  <?php seo_render_form($seo, $targetOptions, $mediaOptions, 'seo-create.php', 'create', $errors); ?>
</section>
<?php cms_footer(); ?>
