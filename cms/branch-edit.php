<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/branch_form.php';

$admin = require_admin();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    $_SESSION['branch_toast'] = ['type' => 'error', 'message' => 'Invalid branch selected.'];
    header('Location: branches.php');
    exit;
}

$errors = [];
$toast = $_SESSION['branch_toast'] ?? null;
unset($_SESSION['branch_toast']);

try {
    $branch = branch_find((int) $id);
    if (!$branch) {
        $_SESSION['branch_toast'] = ['type' => 'error', 'message' => 'Branch was not found.'];
        header('Location: branches.php');
        exit;
    }
} catch (Throwable $exception) {
    $_SESSION['branch_toast'] = ['type' => 'error', 'message' => 'Branch data is not available. Run the branches migration first.'];
    header('Location: branches.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid request. Please reload the page and try again.';
    } elseif (($_POST['action'] ?? '') === 'delete') {
        // Soft delete, matching the rest of the CMS; the row stays recoverable in the database.
        try {
            $stmt = db()->prepare('UPDATE clinic_branches SET deleted_at = NOW(), is_primary = 0 WHERE id = :id');
            $stmt->execute([':id' => (int) $id]);
            $_SESSION['branch_toast'] = ['type' => 'success', 'message' => 'Branch removed from the website.'];
            header('Location: branches.php');
            exit;
        } catch (Throwable $exception) {
            $errors[] = 'Unable to remove branch. Please try again.';
        }
    }

    if ($errors === [] && ($_POST['action'] ?? '') !== 'delete') {
        $branch = array_merge($branch, branch_from_post());

        try {
            $errors = branch_validate($branch, (int) $id);

            if ($errors === []) {
                branch_save($branch, (int) $id);
                $_SESSION['branch_toast'] = ['type' => 'success', 'message' => 'Branch updated successfully.'];
                header('Location: branch-edit.php?id=' . $id);
                exit;
            }
        } catch (Throwable $exception) {
            $errors[] = 'Unable to save branch. Please try again.';
        }
    }
}

// Decimal columns come back as "27.7000000"; trim trailing zeros for editing.
foreach (['latitude', 'longitude'] as $field) {
    if (isset($branch[$field]) && $branch[$field] !== null && $branch[$field] !== '' && is_numeric($branch[$field])) {
        $branch[$field] = rtrim(rtrim((string) $branch[$field], '0'), '.');
    }
}

cms_header('Edit Branch', $admin, 'branches');
?>
<section class="content">
  <div class="page-head">
    <div>
      <h1>Edit Branch</h1>
      <p>Update the address, phone, and map pin for this location.</p>
    </div>
    <div style="display:flex;gap:12px;flex-wrap:wrap">
      <form method="post" action="branch-edit.php?id=<?= (int) $id ?>" onsubmit="return confirm('Remove this branch from the website?');">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" value="delete">
        <button class="secondary-btn" type="submit" style="border-color:var(--danger);color:var(--danger)">Remove Branch</button>
      </form>
      <a class="secondary-btn" href="branches.php">Back to List</a>
    </div>
  </div>

  <?php if ($toast): ?>
    <div class="toast <?= e((string) $toast['type']) ?>" role="status"><?= e((string) $toast['message']) ?></div>
  <?php endif; ?>

  <?php branch_render_form($branch, 'branch-edit.php?id=' . (int) $id, 'edit', $errors); ?>
</section>
<?php cms_footer(); ?>
