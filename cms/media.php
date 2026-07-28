<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/media_helpers.php';

$admin = require_admin();
$toast = $_SESSION['media_toast'] ?? null;
unset($_SESSION['media_toast']);
$errors = [];
$editMedia = null;
$metadata = [
    'alt_text' => '',
    'title_text' => '',
    'caption' => '',
    'focal_point_x' => '',
    'focal_point_y' => '',
    'is_public' => 1,
];

if (isset($_GET['edit'])) {
    $editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
    if ($editId) {
        try {
            $editMedia = media_find((int) $editId);
            if ($editMedia) {
                $metadata = [
                    'alt_text' => (string) ($editMedia['alt_text'] ?? ''),
                    'title_text' => (string) ($editMedia['title_text'] ?? ''),
                    'caption' => (string) ($editMedia['caption'] ?? ''),
                    'focal_point_x' => (string) ($editMedia['focal_point_x'] ?? ''),
                    'focal_point_y' => (string) ($editMedia['focal_point_y'] ?? ''),
                    'is_public' => (int) $editMedia['is_public'],
                ];
            }
        } catch (Throwable $exception) {
            $toast = ['type' => 'error', 'message' => 'Selected media item is not available.'];
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim((string) ($_POST['action'] ?? ''));

    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid request. Please reload the page and try again.';
    }

    try {
        if ($action === 'upload') {
            $metadata = media_metadata_from_post();
            $errors = array_merge($errors, media_validate_metadata($metadata));
            if ($errors === []) {
                $id = media_upload($_FILES['image'] ?? [], $metadata);
                $_SESSION['media_toast'] = ['type' => 'success', 'message' => 'Image uploaded successfully.'];
                header('Location: media.php?edit=' . $id);
                exit;
            }
        } elseif ($action === 'update') {
            $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
            $metadata = media_metadata_from_post();
            $errors = array_merge($errors, media_validate_metadata($metadata));
            if (!$id) {
                $errors[] = 'Invalid media item selected.';
            }
            if ($errors === []) {
                media_save_metadata((int) $id, $metadata);
                $_SESSION['media_toast'] = ['type' => 'success', 'message' => 'Media metadata saved successfully.'];
                header('Location: media.php?edit=' . $id);
                exit;
            }
        } elseif ($action === 'delete') {
            $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
            if (!$id || !media_soft_delete((int) $id)) {
                $errors[] = 'Media item could not be deleted.';
            } else {
                $_SESSION['media_toast'] = ['type' => 'success', 'message' => 'Media item removed from CMS lists.'];
                header('Location: media.php');
                exit;
            }
        }
    } catch (Throwable $exception) {
        $errors[] = $exception->getMessage();
    }
}

try {
    $mediaItems = media_list();
} catch (Throwable $exception) {
    $mediaItems = [];
    $toast = ['type' => 'error', 'message' => 'Media library is not available. Please confirm the latest schema has been imported.'];
}

cms_header('Media', $admin, 'media');
?>
<section class="content">
  <div class="page-head">
    <div>
      <h1>Media</h1>
      <p>Upload clinic images and manage image metadata used by specialties, doctors, blog posts, settings, and SEO.</p>
    </div>
    <?php if ($editMedia): ?>
      <a class="secondary-btn" href="media.php">New Upload</a>
    <?php endif; ?>
  </div>

  <?php if ($toast): ?>
    <div class="toast <?= e((string) $toast['type']) ?>" role="status"><?= e((string) $toast['message']) ?></div>
  <?php endif; ?>
  <?php if ($errors !== []): ?>
    <div class="toast error" role="alert"><?= e(implode(' ', $errors)) ?></div>
  <?php endif; ?>

  <div class="grid settings-grid">
    <section class="card form-card">
      <h2><span class="material-symbols-outlined"><?= $editMedia ? 'edit' : 'upload' ?></span><?= $editMedia ? 'Edit Image Metadata' : 'Upload Image' ?></h2>
      <form method="post" action="<?= $editMedia ? 'media.php?edit=' . e((string) $editMedia['id']) : 'media.php' ?>" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" value="<?= $editMedia ? 'update' : 'upload' ?>">
        <?php if ($editMedia): ?>
          <input type="hidden" name="id" value="<?= e((string) $editMedia['id']) ?>">
          <div class="logo-preview" style="margin-bottom:18px">
            <img src="../<?= e((string) $editMedia['file_path']) ?>" alt="<?= e((string) ($editMedia['alt_text'] ?: 'Selected media image')) ?>">
            <div>
              <strong><?= e((string) $editMedia['file_path']) ?></strong>
              <p class="hint"><?= e((string) $editMedia['mime_type']) ?> · <?= e(media_size_label((int) $editMedia['file_size'])) ?> · <?= e((string) $editMedia['width']) ?>x<?= e((string) $editMedia['height']) ?></p>
            </div>
          </div>
        <?php else: ?>
          <div class="field" style="margin-bottom:18px">
            <label for="image">Image File</label>
            <input class="input" id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" required>
            <p class="hint">Allowed: JPG, PNG, WebP. Maximum file size: 2MB.</p>
          </div>
        <?php endif; ?>

        <div class="form-grid">
          <div class="field full">
            <label for="alt_text">Alt Text</label>
            <input class="input" id="alt_text" name="alt_text" type="text" maxlength="255" value="<?= e((string) $metadata['alt_text']) ?>">
            <p class="hint">Describe the image for accessibility and image SEO.</p>
          </div>
          <div class="field">
            <label for="title_text">Title Text</label>
            <input class="input" id="title_text" name="title_text" type="text" maxlength="255" value="<?= e((string) $metadata['title_text']) ?>">
          </div>
          <div class="field">
            <label for="caption">Caption</label>
            <input class="input" id="caption" name="caption" type="text" maxlength="255" value="<?= e((string) $metadata['caption']) ?>">
          </div>
          <div class="field">
            <label for="focal_point_x">Focal Point X</label>
            <input class="input" id="focal_point_x" name="focal_point_x" type="number" min="0" max="100" step="0.01" value="<?= e((string) $metadata['focal_point_x']) ?>">
          </div>
          <div class="field">
            <label for="focal_point_y">Focal Point Y</label>
            <input class="input" id="focal_point_y" name="focal_point_y" type="number" min="0" max="100" step="0.01" value="<?= e((string) $metadata['focal_point_y']) ?>">
          </div>
          <div class="field full">
            <label class="hint"><input class="checkbox" type="checkbox" name="is_public" value="1" <?= (int) $metadata['is_public'] === 1 ? 'checked' : '' ?>> Available in CMS image dropdowns</label>
          </div>
        </div>

        <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:22px">
          <button class="primary-btn" style="width:auto" type="submit"><?= $editMedia ? 'Save Metadata' : 'Upload Image' ?></button>
          <?php if ($editMedia): ?>
            <a class="secondary-btn" href="media.php">Cancel</a>
          <?php endif; ?>
        </div>
      </form>

      <?php if ($editMedia): ?>
        <form method="post" action="media.php" onsubmit="return confirm('Remove this media item from CMS lists? The physical file will be kept.');" style="margin-top:18px">
          <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= e((string) $editMedia['id']) ?>">
          <button class="secondary-btn" style="border-color:var(--danger);color:var(--danger)" type="submit">Remove From CMS</button>
        </form>
      <?php endif; ?>
    </section>

    <section class="card">
      <div class="panel-head">
        <h2>Media Library</h2>
        <span class="tag"><?= count($mediaItems) ?> total</span>
      </div>
      <?php if ($mediaItems === []): ?>
        <div class="empty">No media records found.</div>
      <?php else: ?>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Preview</th>
                <th>Metadata</th>
                <th>Size</th>
                <th>Public</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($mediaItems as $item): ?>
                <tr>
                  <td>
                    <img src="../<?= e((string) $item['file_path']) ?>" alt="<?= e((string) ($item['alt_text'] ?: 'Media preview')) ?>" style="width:72px;height:54px;object-fit:cover;border-radius:8px;border:1px solid var(--border)">
                  </td>
                  <td>
                    <strong><?= e((string) ($item['alt_text'] ?: $item['file_path'])) ?></strong>
                    <p class="hint"><?= e((string) $item['mime_type']) ?> · <?= e((string) $item['file_path']) ?></p>
                  </td>
                  <td><?= e(media_size_label((int) $item['file_size'])) ?><p class="hint"><?= e((string) $item['width']) ?>x<?= e((string) $item['height']) ?></p></td>
                  <td><?= (int) $item['is_public'] === 1 ? 'Yes' : 'No' ?></td>
                  <td><a class="secondary-btn" style="padding:8px 12px" href="media.php?edit=<?= e((string) $item['id']) ?>">Edit</a></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </section>
  </div>
</section>
<?php cms_footer(); ?>
