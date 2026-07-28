<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/specialty_form.php';

$admin = require_admin();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    $_SESSION['specialty_toast'] = ['type' => 'error', 'message' => 'Invalid specialty selected.'];
    header('Location: specialties.php');
    exit;
}

$errors = [];
$toast = $_SESSION['specialty_toast'] ?? null;
unset($_SESSION['specialty_toast']);

try {
    $mediaOptions = specialty_media_options();
    $specialty = specialty_find((int) $id);
    if (!$specialty) {
        $_SESSION['specialty_toast'] = ['type' => 'error', 'message' => 'Specialty was not found.'];
        header('Location: specialties.php');
        exit;
    }
    $points = specialty_points((int) $id);
} catch (Throwable $exception) {
    $_SESSION['specialty_toast'] = ['type' => 'error', 'message' => 'Specialty data is not available. Please confirm the latest schema has been imported.'];
    header('Location: specialties.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $specialty = array_merge($specialty, specialty_from_post());
    $points = specialty_points_from_post();

    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid request. Please reload the page and try again.';
    }

    try {
        $errors = array_merge($errors, specialty_validate($specialty, $points, (int) $id));

        if ($errors === []) {
            specialty_save($specialty, $points, (int) $id);
            $_SESSION['specialty_toast'] = ['type' => 'success', 'message' => 'Specialty updated successfully.'];
            header('Location: specialty-edit.php?id=' . $id);
            exit;
        }
    } catch (Throwable $exception) {
        $errors[] = 'Unable to save specialty. Please confirm the database schema is available and try again.';
    }
}

cms_header('Edit Specialty', $admin, 'specialties');
?>
<section class="content">
  <div class="page-head">
    <div>
      <h1>Edit Specialty</h1>
      <p>Update clinical details, media selection, visibility, and highlights.</p>
    </div>
    <a class="secondary-btn" href="specialties.php">Back to List</a>
  </div>

  <?php if ($toast): ?>
    <div class="toast <?= e((string) $toast['type']) ?>" role="status"><?= e((string) $toast['message']) ?></div>
  <?php endif; ?>

  <?php specialty_render_form($specialty, $points, $mediaOptions, 'specialty-edit.php?id=' . (int) $id, 'edit', $errors); ?>
</section>
<?php cms_footer(); ?>
