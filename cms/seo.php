<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/seo_form.php';

$admin = require_admin();
$toast = $_SESSION['seo_toast'] ?? null;
unset($_SESSION['seo_toast']);

try {
    $seoRows = seo_list();
} catch (Throwable $exception) {
    $seoRows = [];
    $toast = ['type' => 'error', 'message' => 'SEO metadata is not available. Please confirm the latest schema has been imported.'];
}

cms_header('SEO Metadata', $admin, 'seo');
?>
<section class="content">
  <div class="page-head">
    <div>
      <h1>SEO Metadata</h1>
      <p>Manage metadata, canonical URLs, robots settings, social preview fields, and schema JSON overrides.</p>
    </div>
    <a class="primary-btn" style="width:auto;padding-left:18px;padding-right:18px" href="seo-create.php">
      <span class="material-symbols-outlined">add</span>Add SEO
    </a>
  </div>

  <?php if ($toast): ?>
    <div class="toast <?= e((string) $toast['type']) ?>" role="status"><?= e((string) $toast['message']) ?></div>
  <?php endif; ?>

  <section class="card">
    <div class="panel-head">
      <h2>SEO Records</h2>
      <span class="tag"><?= count($seoRows) ?> total</span>
    </div>
    <?php if ($seoRows === []): ?>
      <div class="empty">No SEO metadata records found.</div>
    <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Target</th>
              <th>Meta Title</th>
              <th>Robots</th>
              <th>Twitter</th>
              <th>Updated</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($seoRows as $row): ?>
              <tr>
                <td><?= e(seo_target_label($row)) ?></td>
                <td>
                  <strong><?= e((string) $row['meta_title']) ?></strong>
                  <p class="hint"><?= e(cms_excerpt((string) $row['meta_description'], 92)) ?></p>
                </td>
                <td><?= (int) $row['robots_index'] === 1 ? 'index' : 'noindex' ?>, <?= (int) $row['robots_follow'] === 1 ? 'follow' : 'nofollow' ?></td>
                <td><?= e((string) $row['twitter_card']) ?></td>
                <td><?= e((string) $row['updated_at']) ?></td>
                <td><a class="secondary-btn" style="padding:8px 12px" href="seo-edit.php?id=<?= e((string) $row['id']) ?>">Edit</a></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </section>
</section>
<?php cms_footer(); ?>
