<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/blog_form.php';
require_once __DIR__ . '/includes/seo_metadata_form.php';

$admin = require_admin();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    $_SESSION['blog_toast'] = ['type' => 'error', 'message' => 'Invalid blog post selected.'];
    header('Location: blog.php');
    exit;
}

$errors = [];
$toast = $_SESSION['blog_toast'] ?? null;
unset($_SESSION['blog_toast']);

try {
    $categoryOptions = blog_category_options();
    $mediaOptions = blog_media_options();
    $post = blog_find((int) $id);
    if (!$post) {
        $_SESSION['blog_toast'] = ['type' => 'error', 'message' => 'Blog post was not found.'];
        header('Location: blog.php');
        exit;
    }
    $post['seo'] = seo_find_by_target('blog_post', (int) $id) ?? [];
} catch (Throwable $exception) {
    $_SESSION['blog_toast'] = ['type' => 'error', 'message' => 'Blog data is not available. Please confirm the latest schema has been imported.'];
    header('Location: blog.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid request. Please reload the page and try again.';
    } elseif (($_POST['action'] ?? '') === 'delete') {
        try {
            $stmt = db()->prepare('UPDATE blog_posts SET deleted_at = NOW() WHERE id = :id AND deleted_at IS NULL');
            $stmt->execute([':id' => (int) $id]);
            $_SESSION['blog_toast'] = ['type' => 'success', 'message' => 'Blog post removed from the website.'];
            header('Location: blog.php');
            exit;
        } catch (Throwable $exception) {
            $errors[] = 'Unable to remove blog post. Please try again.';
        }
    }

    if ($errors === [] && ($_POST['action'] ?? '') !== 'delete') {
        $post = array_merge($post, blog_from_post());

        try {
            $errors = array_merge($errors, blog_validate($post, (int) $id));

            if ($errors === []) {
                $pdo = db();
                $pdo->beginTransaction();
                try {
                    blog_save($post, (int) $id);
                    seo_save_for_target('blog_post', (int) $id, $_POST);
                    $pdo->commit();
                    $_SESSION['blog_toast'] = ['type' => 'success', 'message' => 'Blog post and SEO metadata updated successfully.'];
                    header('Location: blog-edit.php?id=' . $id);
                    exit;
                } catch (Throwable $saveException) {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                    throw $saveException;
                }
            }
        } catch (Throwable $exception) {
            $errors[] = 'Unable to save blog post. Please confirm the database schema is available and try again.';
        }
    }
}

cms_header('Edit Blog Post', $admin, 'blog');
?>
<section class="content">
  <div class="page-head">
    <div>
      <h1>Edit Blog Post</h1>
      <p>Update article content, review metadata, category, and publication settings.</p>
    </div>
    <div style="display:flex;gap:12px;flex-wrap:wrap">
      <form method="post" action="blog-edit.php?id=<?= (int) $id ?>" onsubmit="return confirm('Remove this blog post from the website?');">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" value="delete">
        <button class="secondary-btn" type="submit" style="border-color:var(--danger);color:var(--danger)">Remove Post</button>
      </form>
      <a class="secondary-btn" href="blog.php">Back to List</a>
    </div>
  </div>

  <?php if ($toast): ?>
    <div class="toast <?= e((string) $toast['type']) ?>" role="status"><?= e((string) $toast['message']) ?></div>
  <?php endif; ?>

  <?php blog_render_form($post, $categoryOptions, $mediaOptions, 'blog-edit.php?id=' . (int) $id, 'edit', $errors); ?>
</section>
<?php cms_footer(); ?>
