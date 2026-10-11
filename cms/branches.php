<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/branch_form.php';

$admin = require_admin();
$toast = $_SESSION['branch_toast'] ?? null;
unset($_SESSION['branch_toast']);

try {
    $branches = branch_list();
} catch (Throwable $exception) {
    $branches = [];
    $toast = ['type' => 'error', 'message' => 'Branches are not available. Run migrations/2026-10-11-clinic-branches.sql on the database first.'];
}

cms_header('Branches', $admin, 'branches');
?>
<section class="content">
  <div class="page-head">
    <div>
      <h1>Branches</h1>
      <p>Manage clinic locations shown in the branch list and map on the Home and Contact pages.</p>
    </div>
    <a class="primary-btn" style="width:auto;padding-left:18px;padding-right:18px" href="branch-create.php">
      <span class="material-symbols-outlined">add</span>Add Branch
    </a>
  </div>

  <?php if ($toast): ?>
    <div class="toast <?= e((string) $toast['type']) ?>" role="status"><?= e((string) $toast['message']) ?></div>
  <?php endif; ?>

  <section class="card">
    <div class="panel-head">
      <h2>Branch Records</h2>
      <span class="tag"><?= count($branches) ?> total</span>
    </div>
    <?php if ($branches === []): ?>
      <div class="empty">No branches yet. Add the first branch, or run the migration to load the existing Butwal and Bhairahawa branches.</div>
    <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Branch</th>
              <th>Address</th>
              <th>Phone</th>
              <th>Map pin</th>
              <th>Visible</th>
              <th>Sort</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($branches as $branch): ?>
              <tr>
                <td>
                  <strong><?= e((string) $branch['name']) ?></strong>
                  <?php if ((int) $branch['is_primary'] === 1): ?>
                    <span class="status published">Main clinic</span>
                  <?php endif; ?>
                </td>
                <td><?= e(cms_excerpt((string) $branch['address_line'] . ', ' . (string) $branch['city'], 60)) ?></td>
                <td><?= e((string) ($branch['phone'] ?? '')) ?></td>
                <td><?= $branch['latitude'] !== null ? 'Coordinates' : 'By address' ?></td>
                <td><?= (int) $branch['is_visible'] === 1 ? 'Yes' : 'No' ?></td>
                <td><?= e((string) $branch['sort_order']) ?></td>
                <td>
                  <a class="secondary-btn" style="padding:8px 12px" href="branch-edit.php?id=<?= e((string) $branch['id']) ?>">Edit</a>
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
