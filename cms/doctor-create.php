<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/doctor_form.php';

$admin = require_admin();
$doctor = doctor_default();
$qualifications = [];
$schedules = [];
$errors = [];
$toast = null;

try {
    $specialtyOptions = doctor_specialty_options();
    $mediaOptions = doctor_media_options();
} catch (Throwable $exception) {
    $specialtyOptions = [];
    $mediaOptions = [];
    $toast = ['type' => 'error', 'message' => 'Dropdown options are not available. Please confirm the latest schema has been imported.'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctor = doctor_from_post();
    $qualifications = doctor_qualifications_from_post();
    $schedules = doctor_schedules_from_post();

    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid request. Please reload the page and try again.';
    }

    try {
        $errors = array_merge($errors, doctor_validate($doctor, $qualifications, $schedules));

        if ($errors === []) {
            $id = doctor_save($doctor, $qualifications, $schedules);
            $_SESSION['doctor_toast'] = ['type' => 'success', 'message' => 'Doctor profile created successfully.'];
            header('Location: doctor-edit.php?id=' . $id);
            exit;
        }
    } catch (Throwable $exception) {
        $errors[] = 'Unable to save doctor profile. Please confirm the database schema is available and try again.';
    }
}

cms_header('Create Doctor', $admin, 'doctors');
?>
<section class="content">
  <div class="page-head">
    <div>
      <h1>Create Doctor</h1>
      <p>Add doctor profile content, specialty assignment, publication status, qualifications, and schedule.</p>
    </div>
    <a class="secondary-btn" href="doctors.php">Back to List</a>
  </div>

  <?php if ($toast): ?>
    <div class="toast <?= e((string) $toast['type']) ?>" role="status"><?= e((string) $toast['message']) ?></div>
  <?php endif; ?>

  <?php doctor_render_form($doctor, $qualifications, $schedules, $specialtyOptions, $mediaOptions, 'doctor-create.php', 'create', $errors); ?>
</section>
<?php cms_footer(); ?>
