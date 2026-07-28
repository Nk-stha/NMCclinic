<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/doctor_form.php';

$admin = require_admin();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    $_SESSION['doctor_toast'] = ['type' => 'error', 'message' => 'Invalid doctor selected.'];
    header('Location: doctors.php');
    exit;
}

$errors = [];
$toast = $_SESSION['doctor_toast'] ?? null;
unset($_SESSION['doctor_toast']);

try {
    $specialtyOptions = doctor_specialty_options();
    $mediaOptions = doctor_media_options();
    $doctor = doctor_find((int) $id);
    if (!$doctor) {
        $_SESSION['doctor_toast'] = ['type' => 'error', 'message' => 'Doctor profile was not found.'];
        header('Location: doctors.php');
        exit;
    }
    $qualifications = doctor_qualifications((int) $id);
    $schedules = doctor_schedules((int) $id);
} catch (Throwable $exception) {
    $_SESSION['doctor_toast'] = ['type' => 'error', 'message' => 'Doctor data is not available. Please confirm the latest schema has been imported.'];
    header('Location: doctors.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctor = array_merge($doctor, doctor_from_post());
    $qualifications = doctor_qualifications_from_post();
    $schedules = doctor_schedules_from_post();

    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid request. Please reload the page and try again.';
    }

    try {
        $errors = array_merge($errors, doctor_validate($doctor, $qualifications, $schedules, (int) $id));

        if ($errors === []) {
            doctor_save($doctor, $qualifications, $schedules, (int) $id);
            $_SESSION['doctor_toast'] = ['type' => 'success', 'message' => 'Doctor profile updated successfully.'];
            header('Location: doctor-edit.php?id=' . $id);
            exit;
        }
    } catch (Throwable $exception) {
        $errors[] = 'Unable to save doctor profile. Please confirm the database schema is available and try again.';
    }
}

cms_header('Edit Doctor', $admin, 'doctors');
?>
<section class="content">
  <div class="page-head">
    <div>
      <h1>Edit Doctor</h1>
      <p>Update profile details, specialty assignment, photo, qualifications, and schedule.</p>
    </div>
    <a class="secondary-btn" href="doctors.php">Back to List</a>
  </div>

  <?php if ($toast): ?>
    <div class="toast <?= e((string) $toast['type']) ?>" role="status"><?= e((string) $toast['message']) ?></div>
  <?php endif; ?>

  <?php doctor_render_form($doctor, $qualifications, $schedules, $specialtyOptions, $mediaOptions, 'doctor-edit.php?id=' . (int) $id, 'edit', $errors); ?>
</section>
<?php cms_footer(); ?>
