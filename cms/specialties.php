<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/specialty_form.php';

$admin = require_admin();
$toast = $_SESSION['specialty_toast'] ?? null;
unset($_SESSION['specialty_toast']);

try {
    $specialties = specialty_list();
} catch (Throwable $exception) {
    $specialties = [];
    $toast = ['type' => 'error', 'message' => 'Specialties are not available. Please confirm the latest schema has been imported.'];
}

cms_header('Specialties', $admin, 'specialties');
?>
<section class="content">
  <div class="page-head">
    <div>
      <h1>Specialties</h1>
      <p>Manage clinical specialties, public visibility, image selection, and key highlights.</p>
    </div>
    <a class="primary-btn" style="width:auto;padding-left:18px;padding-right:18px" href="specialty-create.php">
      <span class="material-symbols-outlined">add</span>Add Specialty
    </a>
  </div>

  <?php if ($toast): ?>
    <div class="toast <?= e((string) $toast['type']) ?>" role="status"><?= e((string) $toast['message']) ?></div>
  <?php endif; ?>

  <section class="card">
    <div class="panel-head">
      <h2>Specialty Records</h2>
      <span class="tag"><?= count($specialties) ?> total</span>
    </div>
    <?php if ($specialties === []): ?>
      <div class="empty">No specialties found. Add the first specialty when the schema is imported and ready.</div>
    <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Specialty</th>
              <th>Slug</th>
              <th>Status</th>
              <th>Visible</th>
              <th>Sort</th>
              <th>Updated</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($specialties as $specialty): ?>
              <tr>
                <td>
                  <strong><?= e((string) $specialty['title']) ?></strong>
                  <?php if (!empty($specialty['short_description'])): ?>
                    <p class="hint"><?= e(cms_excerpt((string) $specialty['short_description'], 90)) ?></p>
                  <?php endif; ?>
                </td>
                <td><?= e((string) $specialty['slug']) ?></td>
                <td><span class="status <?= e(cms_status_class((string) $specialty['status'])) ?>"><?= e((string) $specialty['status']) ?></span></td>
                <td><?= (int) $specialty['is_visible'] === 1 ? 'Yes' : 'No' ?></td>
                <td><?= e((string) $specialty['sort_order']) ?></td>
                <td><?= e((string) $specialty['updated_at']) ?></td>
                <td>
                  <a class="secondary-btn" style="padding:8px 12px" href="specialty-edit.php?id=<?= e((string) $specialty['id']) ?>">Edit</a>
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
