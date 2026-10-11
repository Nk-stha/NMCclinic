<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/branch_form.php';

$admin = require_admin();
$branch = branch_default();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $branch = branch_from_post();

    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid request. Please reload the page and try again.';
    }

    try {
        $errors = array_merge($errors, branch_validate($branch));

        if ($errors === []) {
            $id = branch_save($branch);
            $_SESSION['branch_toast'] = ['type' => 'success', 'message' => 'Branch created successfully.'];
            header('Location: branch-edit.php?id=' . $id);
            exit;
        }
    } catch (Throwable $exception) {
        $errors[] = 'Unable to save branch. Please confirm the branches migration has been run and try again.';
    }
}

cms_header('Add Branch', $admin, 'branches');
?>
<section class="content">
  <div class="page-head">
    <div>
      <h1>Add Branch</h1>
      <p>Add a clinic location. It appears in the branch list and map on the Home and Contact pages.</p>
    </div>
    <a class="secondary-btn" href="branches.php">Back to List</a>
  </div>

  <?php branch_render_form($branch, 'branch-create.php', 'create', $errors); ?>
</section>
<?php cms_footer(); ?>
