<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/blog_form.php';

$admin = require_admin();
$toast = $_SESSION['blog_toast'] ?? null;
unset($_SESSION['blog_toast']);

try {
    $posts = blog_list();
} catch (Throwable $exception) {
    $posts = [];
    $toast = ['type' => 'error', 'message' => 'Blog posts are not available. Please confirm the latest schema has been imported.'];
}

cms_header('Blog', $admin, 'blog');
?>
<section class="content">
  <div class="page-head">
    <div>
      <h1>Blog</h1>
      <p>Manage medical articles, review metadata, publishing status, and featured placement.</p>
    </div>
    <a class="primary-btn" style="width:auto;padding-left:18px;padding-right:18px" href="blog-create.php">
      <span class="material-symbols-outlined">add</span>Add Post
    </a>
  </div>

  <?php if ($toast): ?>
    <div class="toast <?= e((string) $toast['type']) ?>" role="status"><?= e((string) $toast['message']) ?></div>
  <?php endif; ?>

  <section class="card">
    <div class="panel-head">
      <h2>Blog Posts</h2>
      <span class="tag"><?= count($posts) ?> total</span>
    </div>
    <?php if ($posts === []): ?>
      <div class="empty">No blog posts found. Add the first post when the schema is imported and ready.</div>
    <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Post</th>
              <th>Category</th>
              <th>Status</th>
              <th>Published</th>
              <th>Featured</th>
              <th>Sort</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($posts as $post): ?>
              <tr>
                <td>
                  <strong><?= e((string) $post['title']) ?></strong>
                  <p class="hint"><?= e(cms_excerpt((string) $post['excerpt'], 90)) ?></p>
                </td>
                <td><?= e((string) ($post['category_name'] ?? 'Not selected')) ?></td>
                <td><span class="status <?= e(cms_status_class((string) $post['status'])) ?>"><?= e((string) $post['status']) ?></span></td>
                <td><?= e((string) ($post['published_at'] ?? '')) ?></td>
                <td><?= (int) $post['is_featured'] === 1 ? 'Yes' : 'No' ?></td>
                <td><?= e((string) $post['sort_order']) ?></td>
                <td>
                  <a class="secondary-btn" style="padding:8px 12px" href="blog-edit.php?id=<?= e((string) $post['id']) ?>">Edit</a>
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
