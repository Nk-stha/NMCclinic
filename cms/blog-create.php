<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/blog_form.php';

$admin = require_admin();
$post = blog_default();
$errors = [];
$toast = null;

try {
    $categoryOptions = blog_category_options();
    $mediaOptions = blog_media_options();
} catch (Throwable $exception) {
    $categoryOptions = [];
    $mediaOptions = [];
    $toast = ['type' => 'error', 'message' => 'Dropdown options are not available. Please confirm the latest schema has been imported.'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post = blog_from_post();

    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid request. Please reload the page and try again.';
    }

    try {
        $errors = array_merge($errors, blog_validate($post));

        if ($errors === []) {
            $id = blog_save($post);
            $_SESSION['blog_toast'] = ['type' => 'success', 'message' => 'Blog post created successfully.'];
            header('Location: blog-edit.php?id=' . $id);
            exit;
        }
    } catch (Throwable $exception) {
        $errors[] = 'Unable to save blog post. Please confirm the database schema is available and try again.';
    }
}

cms_header('Create Blog Post', $admin, 'blog');
?>
<section class="content">
  <div class="page-head">
    <div>
      <h1>Create Blog Post</h1>
      <p>Add medical article content, review metadata, category, and publication settings.</p>
    </div>
    <a class="secondary-btn" href="blog.php">Back to List</a>
  </div>

  <?php if ($toast): ?>
    <div class="toast <?= e((string) $toast['type']) ?>" role="status"><?= e((string) $toast['message']) ?></div>
  <?php endif; ?>

  <?php blog_render_form($post, $categoryOptions, $mediaOptions, 'blog-create.php', 'create', $errors); ?>
</section>
<?php cms_footer(); ?>
