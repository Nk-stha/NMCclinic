<?php
declare(strict_types=1);

function specialty_default(): array
{
    return [
        'id' => null,
        'slug' => '',
        'title' => '',
        'short_description' => '',
        'image_id' => '',
        'status' => 'draft',
        'sort_order' => '0',
        'is_visible' => 1,
    ];
}

function specialty_slug(string $title): string
{
    $slug = strtolower(trim($title));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
    $slug = trim($slug, '-');

    return $slug !== '' ? $slug : 'specialty';
}

function specialty_media_options(): array
{
    $stmt = db()->query(
        "SELECT id, file_path, alt_text
         FROM media_assets
         WHERE deleted_at IS NULL AND is_public = 1 AND mime_type LIKE 'image/%'
         ORDER BY id DESC"
    );

    return $stmt->fetchAll();
}

function specialty_find(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM specialties WHERE id = :id AND deleted_at IS NULL LIMIT 1');
    $stmt->execute([':id' => $id]);
    $specialty = $stmt->fetch();

    return $specialty ?: null;
}

function specialty_points(int $specialtyId): array
{
    $stmt = db()->prepare('SELECT id, body, is_visible FROM specialty_points WHERE specialty_id = :id ORDER BY sort_order ASC, id ASC');
    $stmt->execute([':id' => $specialtyId]);

    return $stmt->fetchAll();
}

function specialty_list(): array
{
    $stmt = db()->query(
        'SELECT s.*, m.file_path AS image_path
         FROM specialties s
         LEFT JOIN media_assets m ON m.id = s.image_id
         WHERE s.deleted_at IS NULL
         ORDER BY s.sort_order ASC, s.title ASC'
    );

    return $stmt->fetchAll();
}

function specialty_from_post(): array
{
    $specialty = specialty_default();
    $specialty['title'] = trim((string) ($_POST['title'] ?? ''));
    $specialty['slug'] = trim((string) ($_POST['slug'] ?? ''));
    $specialty['short_description'] = trim((string) ($_POST['short_description'] ?? ''));
    $specialty['image_id'] = trim((string) ($_POST['image_id'] ?? ''));
    $specialty['status'] = trim((string) ($_POST['status'] ?? 'draft'));
    $specialty['sort_order'] = trim((string) ($_POST['sort_order'] ?? '0'));
    $specialty['is_visible'] = isset($_POST['is_visible']) ? 1 : 0;

    if ($specialty['slug'] === '') {
        $specialty['slug'] = specialty_slug($specialty['title']);
    } else {
        $specialty['slug'] = specialty_slug($specialty['slug']);
    }

    return $specialty;
}

function specialty_points_from_post(): array
{
    $bodies = $_POST['point_body'] ?? [];
    $visible = $_POST['point_visible'] ?? [];
    $points = [];

    if (!is_array($bodies)) {
        return $points;
    }

    foreach ($bodies as $index => $body) {
        $body = trim((string) $body);
        if ($body === '') {
            continue;
        }

        $points[] = [
            'body' => $body,
            'is_visible' => isset($visible[$index]) ? 1 : 0,
        ];
    }

    return $points;
}

function specialty_validate(array $specialty, array $points, ?int $currentId = null): array
{
    $errors = [];

    if ($specialty['title'] === '') {
        $errors[] = 'Specialty title is required.';
    } elseif (strlen($specialty['title']) > 150) {
        $errors[] = 'Specialty title must be 150 characters or less.';
    }

    if ($specialty['slug'] === '') {
        $errors[] = 'URL slug is required.';
    } elseif (strlen($specialty['slug']) > 191) {
        $errors[] = 'URL slug must be 191 characters or less.';
    }

    if ($specialty['short_description'] !== '' && strlen($specialty['short_description']) > 500) {
        $errors[] = 'Short description must be 500 characters or less.';
    }

    if (!in_array($specialty['status'], ['draft', 'published', 'archived'], true)) {
        $errors[] = 'Publishing status is invalid.';
    }

    if (!ctype_digit((string) $specialty['sort_order'])) {
        $errors[] = 'Sort order must be a whole number.';
    }

    foreach ($points as $point) {
        if (strlen($point['body']) > 255) {
            $errors[] = 'Each highlight must be 255 characters or less.';
            break;
        }
    }

    if ($currentId === null) {
        $stmt = db()->prepare('SELECT id FROM specialties WHERE slug = :slug AND deleted_at IS NULL LIMIT 1');
        $stmt->execute([':slug' => $specialty['slug']]);
    } else {
        $stmt = db()->prepare('SELECT id FROM specialties WHERE slug = :slug AND deleted_at IS NULL AND id <> :id LIMIT 1');
        $stmt->execute([
            ':slug' => $specialty['slug'],
            ':id' => $currentId,
        ]);
    }

    if ($stmt->fetch()) {
        $errors[] = 'This URL slug is already used by another specialty.';
    }

    if ($specialty['image_id'] !== '') {
        $stmt = db()->prepare("SELECT id FROM media_assets WHERE id = :id AND deleted_at IS NULL AND is_public = 1 AND mime_type LIKE 'image/%' LIMIT 1");
        $stmt->execute([':id' => (int) $specialty['image_id']]);
        if (!$stmt->fetch()) {
            $errors[] = 'Selected image is invalid.';
        }
    }

    return $errors;
}

function specialty_save(array $specialty, array $points, ?int $id = null): int
{
    $pdo = db();
    $pdo->beginTransaction();

    try {
        if ($id === null) {
            $stmt = $pdo->prepare(
                'INSERT INTO specialties (slug, title, short_description, image_id, status, sort_order, is_visible)
                 VALUES (:slug, :title, :short_description, :image_id, :status, :sort_order, :is_visible)'
            );
        } else {
            $stmt = $pdo->prepare(
                'UPDATE specialties
                 SET slug = :slug,
                     title = :title,
                     short_description = :short_description,
                     image_id = :image_id,
                     status = :status,
                     sort_order = :sort_order,
                     is_visible = :is_visible
                 WHERE id = :id'
            );
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        }

        $stmt->bindValue(':slug', $specialty['slug']);
        $stmt->bindValue(':title', $specialty['title']);
        $stmt->bindValue(':short_description', $specialty['short_description'] !== '' ? $specialty['short_description'] : null);
        if ($specialty['image_id'] === '') {
            $stmt->bindValue(':image_id', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':image_id', (int) $specialty['image_id'], PDO::PARAM_INT);
        }
        $stmt->bindValue(':status', $specialty['status']);
        $stmt->bindValue(':sort_order', (int) $specialty['sort_order'], PDO::PARAM_INT);
        $stmt->bindValue(':is_visible', (int) $specialty['is_visible'], PDO::PARAM_INT);
        $stmt->execute();

        $specialtyId = $id ?? (int) $pdo->lastInsertId();

        $delete = $pdo->prepare('DELETE FROM specialty_points WHERE specialty_id = :id');
        $delete->execute([':id' => $specialtyId]);

        $insert = $pdo->prepare(
            'INSERT INTO specialty_points (specialty_id, body, sort_order, is_visible)
             VALUES (:specialty_id, :body, :sort_order, :is_visible)'
        );
        foreach ($points as $index => $point) {
            $insert->execute([
                ':specialty_id' => $specialtyId,
                ':body' => $point['body'],
                ':sort_order' => $index,
                ':is_visible' => $point['is_visible'],
            ]);
        }

        $pdo->commit();
        return $specialtyId;
    } catch (Throwable $exception) {
        $pdo->rollBack();
        throw $exception;
    }
}

function specialty_render_form(array $specialty, array $points, array $mediaOptions, string $action, string $mode, array $errors = []): void
{
    if ($points === []) {
        $points = [
            ['body' => '', 'is_visible' => 1],
            ['body' => '', 'is_visible' => 1],
            ['body' => '', 'is_visible' => 1],
        ];
    }
    ?>
  <?php if ($errors !== []): ?>
    <div class="toast error" role="alert"><?= e(implode(' ', $errors)) ?></div>
  <?php endif; ?>

  <form method="post" action="<?= e($action) ?>">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

    <div class="grid settings-grid">
      <div class="grid">
        <section class="card form-card">
          <h2><span class="material-symbols-outlined">info</span>Basic Information</h2>
          <div class="form-grid">
            <div class="field full">
              <label for="title">Specialty Title</label>
              <input class="input" id="title" name="title" type="text" maxlength="150" value="<?= e((string) $specialty['title']) ?>" required>
              <p class="hint">Use the public service name, for example Physiotherapy & Rehabilitation.</p>
            </div>
            <div class="field full">
              <label for="slug">URL Slug</label>
              <input class="input" id="slug" name="slug" type="text" maxlength="191" value="<?= e((string) $specialty['slug']) ?>" required>
              <p class="hint">Used for anchors and future clean URLs. Lowercase letters, numbers, and hyphens only.</p>
            </div>
            <div class="field full">
              <label for="short_description">Short Description</label>
              <textarea class="textarea" id="short_description" name="short_description" rows="4" maxlength="500"><?= e((string) $specialty['short_description']) ?></textarea>
            </div>
          </div>
        </section>

        <section class="card form-card">
          <h2><span class="material-symbols-outlined">list</span>Key Highlights</h2>
          <div class="grid" id="points">
            <?php foreach ($points as $index => $point): ?>
              <div class="form-grid point-row">
                <div class="field">
                  <label for="point_<?= $index ?>">Highlight <?= $index + 1 ?></label>
                  <input class="input" id="point_<?= $index ?>" name="point_body[<?= $index ?>]" type="text" maxlength="255" value="<?= e((string) $point['body']) ?>">
                </div>
                <div class="field">
                  <label>Visibility</label>
                  <label class="hint"><input class="checkbox" type="checkbox" name="point_visible[<?= $index ?>]" value="1" <?= (int) $point['is_visible'] === 1 ? 'checked' : '' ?>> Show this highlight</label>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <button class="secondary-btn" type="button" id="addPoint">Add Highlight</button>
        </section>
      </div>

      <aside class="grid">
        <section class="card form-card">
          <h2><span class="material-symbols-outlined">image</span>Media</h2>
          <div class="field">
            <label for="image_id">Specialty Image</label>
            <select class="select" id="image_id" name="image_id">
              <option value="">No image selected</option>
              <?php foreach ($mediaOptions as $media): ?>
                <option value="<?= e((string) $media['id']) ?>" <?= (string) $specialty['image_id'] === (string) $media['id'] ? 'selected' : '' ?>>
                  <?= e((string) ($media['alt_text'] ?: $media['file_path'])) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <p class="hint">Images come from the media library. If none exist yet, leave this empty.</p>
          </div>
        </section>

        <section class="card form-card">
          <h2><span class="material-symbols-outlined">settings_suggest</span>Configuration</h2>
          <div class="form-grid">
            <div class="field">
              <label for="sort_order">Sort Order</label>
              <input class="input" id="sort_order" name="sort_order" type="number" min="0" step="1" value="<?= e((string) $specialty['sort_order']) ?>">
            </div>
            <div class="field">
              <label for="status">Publishing Status</label>
              <select class="select" id="status" name="status">
                <?php foreach (['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived'] as $value => $label): ?>
                  <option value="<?= e($value) ?>" <?= $specialty['status'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="field full">
              <label>Public Visibility</label>
              <label class="hint"><input class="checkbox" type="checkbox" name="is_visible" value="1" <?= (int) $specialty['is_visible'] === 1 ? 'checked' : '' ?>> Show on website when published</label>
            </div>
          </div>
        </section>
      </aside>
    </div>

    <div class="sticky-actions">
      <span class="hint"><?= $mode === 'create' ? 'New specialty will be saved as the selected status.' : 'Changes are saved only when you press Save Specialty.' ?></span>
      <div style="display:flex;gap:12px;flex-wrap:wrap">
        <a class="secondary-btn" href="specialties.php">Cancel</a>
        <button class="primary-btn" type="submit">Save Specialty</button>
      </div>
    </div>
  </form>

  <script>
  (function () {
    var title = document.getElementById('title');
    var slug = document.getElementById('slug');
    var points = document.getElementById('points');
    var addPoint = document.getElementById('addPoint');

    if (title && slug) {
      title.addEventListener('blur', function () {
        if (slug.value.trim() !== '') return;
        slug.value = title.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
      });
    }

    if (points && addPoint) {
      addPoint.addEventListener('click', function () {
        var index = points.querySelectorAll('.point-row').length;
        var row = document.createElement('div');
        row.className = 'form-grid point-row';
        row.innerHTML =
          '<div class="field">' +
          '<label for="point_' + index + '">Highlight ' + (index + 1) + '</label>' +
          '<input class="input" id="point_' + index + '" name="point_body[' + index + ']" type="text" maxlength="255">' +
          '</div>' +
          '<div class="field">' +
          '<label>Visibility</label>' +
          '<label class="hint"><input class="checkbox" type="checkbox" name="point_visible[' + index + ']" value="1" checked> Show this highlight</label>' +
          '</div>';
        points.appendChild(row);
      });
    }
  })();
  </script>
    <?php
}
