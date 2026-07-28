<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/specialty_form.php';

$admin = require_admin();
$specialty = specialty_default();
$points = [];
$errors = [];
$toast = null;

try {
    $mediaOptions = specialty_media_options();
} catch (Throwable $exception) {
    $mediaOptions = [];
    $toast = ['type' => 'error', 'message' => 'Media options are not available. You can still save the specialty without an image.'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $specialty = specialty_from_post();
    $points = specialty_points_from_post();

    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid request. Please reload the page and try again.';
    }

    try {
        $errors = array_merge($errors, specialty_validate($specialty, $points));

        if ($errors === []) {
            $id = specialty_save($specialty, $points);
            $_SESSION['specialty_toast'] = ['type' => 'success', 'message' => 'Specialty created successfully.'];
            header('Location: specialty-edit.php?id=' . $id);
            exit;
        }
    } catch (Throwable $exception) {
        $errors[] = 'Unable to save specialty. Please confirm the database schema is available and try again.';
    }
}

cms_header('Create Specialty', $admin, 'specialties');
?>
<section class="content">
  <div class="page-head">
    <div>
      <h1>Create Specialty</h1>
      <p>Add the clinical details needed for public specialty pages and internal CMS listing.</p>
    </div>
    <a class="secondary-btn" href="specialties.php">Back to List</a>
  </div>

  <?php if ($toast): ?>
    <div class="toast <?= e((string) $toast['type']) ?>" role="status"><?= e((string) $toast['message']) ?></div>
  <?php endif; ?>

  <?php specialty_render_form($specialty, $points, $mediaOptions, 'specialty-create.php', 'create', $errors); ?>
</section>
<?php cms_footer(); ?>
