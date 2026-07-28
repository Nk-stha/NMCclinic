<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/doctor_form.php';

$admin = require_admin();
$toast = $_SESSION['doctor_toast'] ?? null;
unset($_SESSION['doctor_toast']);

try {
    $doctors = doctor_list();
} catch (Throwable $exception) {
    $doctors = [];
    $toast = ['type' => 'error', 'message' => 'Doctors are not available. Please confirm the latest schema has been imported.'];
}

cms_header('Doctors', $admin, 'doctors');
?>
<section class="content">
  <div class="page-head">
    <div>
      <h1>Doctors</h1>
      <p>Manage doctor profiles, specialties, appointment availability, and public publishing status.</p>
    </div>
    <a class="primary-btn" style="width:auto;padding-left:18px;padding-right:18px" href="doctor-create.php">
      <span class="material-symbols-outlined">add</span>Add Doctor
    </a>
  </div>

  <?php if ($toast): ?>
    <div class="toast <?= e((string) $toast['type']) ?>" role="status"><?= e((string) $toast['message']) ?></div>
  <?php endif; ?>

  <section class="card">
    <div class="panel-head">
      <h2>Doctor Records</h2>
      <span class="tag"><?= count($doctors) ?> total</span>
    </div>
    <?php if ($doctors === []): ?>
      <div class="empty">No doctors found. Add the first doctor when the schema is imported and ready.</div>
    <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Doctor</th>
              <th>Specialty</th>
              <th>Status</th>
              <th>Availability</th>
              <th>Featured</th>
              <th>Sort</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($doctors as $doctor): ?>
              <tr>
                <td>
                  <strong><?= e((string) $doctor['full_name']) ?></strong>
                  <p class="hint"><?= e((string) $doctor['slug']) ?></p>
                </td>
                <td><?= e((string) ($doctor['specialty_title'] ?? 'Not selected')) ?></td>
                <td><span class="status <?= e(cms_status_class((string) $doctor['status'])) ?>"><?= e((string) $doctor['status']) ?></span></td>
                <td><span class="status <?= e(cms_status_class((string) $doctor['availability_status'])) ?>"><?= e((string) $doctor['availability_status']) ?></span></td>
                <td><?= (int) $doctor['is_featured'] === 1 ? 'Yes' : 'No' ?></td>
                <td><?= e((string) $doctor['sort_order']) ?></td>
                <td>
                  <a class="secondary-btn" style="padding:8px 12px" href="doctor-edit.php?id=<?= e((string) $doctor['id']) ?>">Edit</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </section>
</section>
<?php cms_footer(); ?>
