<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/blog_category_form.php';

$admin = require_admin();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    $_SESSION['blog_category_toast'] = ['type' => 'error', 'message' => 'Invalid blog category selected.'];
    header('Location: blog-categories.php');
    exit;
}

$toast = $_SESSION['blog_category_toast'] ?? null;
unset($_SESSION['blog_category_toast']);
$errors = [];

try {
    $category = blog_category_find((int) $id);
    if (!$category) {
        $_SESSION['blog_category_toast'] = ['type' => 'error', 'message' => 'Blog category was not found.'];
        header('Location: blog-categories.php');
        exit;
    }
} catch (Throwable $exception) {
    $_SESSION['blog_category_toast'] = ['type' => 'error', 'message' => 'Blog category data is not available. Please confirm the latest schema has been imported.'];
    header('Location: blog-categories.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = array_merge($category, blog_category_from_post());
    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid request. Please reload the page and try again.';
    }
    try {
        $errors = array_merge($errors, blog_category_validate($category, (int) $id));
        if ($errors === []) {
            blog_category_save($category, (int) $id);
            $_SESSION['blog_category_toast'] = ['type' => 'success', 'message' => 'Blog category updated successfully.'];
            header('Location: blog-category-edit.php?id=' . $id);
            exit;
        }
    } catch (Throwable $exception) {
        $errors[] = 'Unable to save blog category. Please confirm the database schema is available and try again.';
    }
}

cms_header('Edit Blog Category', $admin, 'blog_categories');
?>
<section class="content">
  <div class="page-head">
    <div>
      <h1>Edit Blog Category</h1>
      <p>Update category name, slug, description, visibility, and sort order.</p>
    </div>
    <a class="secondary-btn" href="blog-categories.php">Back to List</a>
  </div>
  <?php if ($toast): ?><div class="toast <?= e((string) $toast['type']) ?>" role="status"><?= e((string) $toast['message']) ?></div><?php endif; ?>
  <?php blog_category_render_form($category, 'blog-category-edit.php?id=' . (int) $id, 'edit', $errors); ?>
</section>
<?php cms_footer(); ?>
