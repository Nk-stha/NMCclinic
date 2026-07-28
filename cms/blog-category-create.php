<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/blog_category_form.php';

$admin = require_admin();
$category = blog_category_default();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = blog_category_from_post();
    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid request. Please reload the page and try again.';
    }
    try {
        $errors = array_merge($errors, blog_category_validate($category));
        if ($errors === []) {
            $id = blog_category_save($category);
            $_SESSION['blog_category_toast'] = ['type' => 'success', 'message' => 'Blog category created successfully.'];
            header('Location: blog-category-edit.php?id=' . $id);
            exit;
        }
    } catch (Throwable $exception) {
        $errors[] = 'Unable to save blog category. Please confirm the database schema is available and try again.';
    }
}

cms_header('Create Blog Category', $admin, 'blog_categories');
?>
<section class="content">
  <div class="page-head">
    <div>
      <h1>Create Blog Category</h1>
      <p>Add a category for organizing blog posts.</p>
    </div>
    <a class="secondary-btn" href="blog-categories.php">Back to List</a>
  </div>
  <?php blog_category_render_form($category, 'blog-category-create.php', 'create', $errors); ?>
</section>
<?php cms_footer(); ?>
