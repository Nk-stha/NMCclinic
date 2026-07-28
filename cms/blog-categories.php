<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/blog_category_form.php';

$admin = require_admin();
$toast = $_SESSION['blog_category_toast'] ?? null;
unset($_SESSION['blog_category_toast']);

try {
    $categories = blog_category_list();
} catch (Throwable $exception) {
    $categories = [];
    $toast = ['type' => 'error', 'message' => 'Blog categories are not available. Please confirm the latest schema has been imported.'];
}

cms_header('Blog Categories', $admin, 'blog_categories');
?>
<section class="content">
  <div class="page-head">
    <div>
      <h1>Blog Categories</h1>
      <p>Manage article categories used by the Blog CMS dropdown.</p>
    </div>
    <div style="display:flex;gap:12px;flex-wrap:wrap">
      <a class="secondary-btn" href="blog.php">Blog Posts</a>
      <a class="primary-btn" style="width:auto;padding-left:18px;padding-right:18px" href="blog-category-create.php">
        <span class="material-symbols-outlined">add</span>Add Category
      </a>
    </div>
  </div>

  <?php if ($toast): ?>
    <div class="toast <?= e((string) $toast['type']) ?>" role="status"><?= e((string) $toast['message']) ?></div>
  <?php endif; ?>

  <section class="card">
    <div class="panel-head">
      <h2>Category Records</h2>
      <span class="tag"><?= count($categories) ?> total</span>
    </div>
    <?php if ($categories === []): ?>
      <div class="empty">No blog categories found.</div>
    <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Category</th>
              <th>Slug</th>
              <th>Posts</th>
              <th>Visible</th>
              <th>Sort</th>
              <th>Updated</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($categories as $category): ?>
              <tr>
                <td>
                  <strong><?= e((string) $category['name']) ?></strong>
                  <?php if (!empty($category['description'])): ?><p class="hint"><?= e(cms_excerpt((string) $category['description'], 90)) ?></p><?php endif; ?>
                </td>
                <td><?= e((string) $category['slug']) ?></td>
                <td><?= e((string) $category['post_count']) ?></td>
                <td><?= (int) $category['is_visible'] === 1 ? 'Yes' : 'No' ?></td>
                <td><?= e((string) $category['sort_order']) ?></td>
                <td><?= e((string) $category['updated_at']) ?></td>
                <td><a class="secondary-btn" style="padding:8px 12px" href="blog-category-edit.php?id=<?= e((string) $category['id']) ?>">Edit</a></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </section>
</section>
<?php cms_footer(); ?>
