<?php
declare(strict_types=1);

function blog_category_default(): array
{
    return [
        'id' => null,
        'name' => '',
        'slug' => '',
        'description' => '',
        'sort_order' => '0',
        'is_visible' => 1,
    ];
}

function blog_category_slug(string $value): string
{
    $slug = strtolower(trim($value));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
    $slug = trim($slug, '-');

    return $slug !== '' ? $slug : 'category';
}

function blog_category_list(): array
{
    return db()->query(
        'SELECT bc.*, COUNT(bp.id) AS post_count
         FROM blog_categories bc
         LEFT JOIN blog_posts bp ON bp.category_id = bc.id AND bp.deleted_at IS NULL
         WHERE bc.deleted_at IS NULL
         GROUP BY bc.id
         ORDER BY bc.sort_order ASC, bc.name ASC'
    )->fetchAll();
}

function blog_category_find(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM blog_categories WHERE id = :id AND deleted_at IS NULL LIMIT 1');
    $stmt->execute([':id' => $id]);
    $category = $stmt->fetch();

    return $category ?: null;
}

function blog_category_from_post(): array
{
    $category = blog_category_default();
    $category['name'] = trim((string) ($_POST['name'] ?? ''));
    $category['slug'] = trim((string) ($_POST['slug'] ?? ''));
    $category['description'] = trim((string) ($_POST['description'] ?? ''));
    $category['sort_order'] = trim((string) ($_POST['sort_order'] ?? '0'));
    $category['is_visible'] = isset($_POST['is_visible']) ? 1 : 0;

    if ($category['slug'] === '') {
        $category['slug'] = blog_category_slug($category['name']);
    } else {
        $category['slug'] = blog_category_slug($category['slug']);
    }

    return $category;
}

function blog_category_validate(array $category, ?int $currentId = null): array
{
    $errors = [];

    if ($category['name'] === '') {
        $errors[] = 'Category name is required.';
    } elseif (strlen($category['name']) > 120) {
        $errors[] = 'Category name must be 120 characters or less.';
    }

    if ($category['slug'] === '') {
        $errors[] = 'Category slug is required.';
    } elseif (strlen($category['slug']) > 191) {
        $errors[] = 'Category slug must be 191 characters or less.';
    }

    if (!ctype_digit((string) $category['sort_order'])) {
        $errors[] = 'Sort order must be a whole number.';
    }

    if ($currentId === null) {
        $stmt = db()->prepare('SELECT id FROM blog_categories WHERE slug = :slug AND deleted_at IS NULL LIMIT 1');
        $stmt->execute([':slug' => $category['slug']]);
    } else {
        $stmt = db()->prepare('SELECT id FROM blog_categories WHERE slug = :slug AND deleted_at IS NULL AND id <> :id LIMIT 1');
        $stmt->execute([
            ':slug' => $category['slug'],
            ':id' => $currentId,
        ]);
    }

    if ($stmt->fetch()) {
        $errors[] = 'This category slug is already used.';
    }

    return $errors;
}

function blog_category_save(array $category, ?int $id = null): int
{
    if ($id === null) {
        $stmt = db()->prepare(
            'INSERT INTO blog_categories (name, slug, description, sort_order, is_visible)
             VALUES (:name, :slug, :description, :sort_order, :is_visible)'
        );
    } else {
        $stmt = db()->prepare(
            'UPDATE blog_categories
             SET name = :name,
                 slug = :slug,
                 description = :description,
                 sort_order = :sort_order,
                 is_visible = :is_visible
             WHERE id = :id'
        );
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    }

    $stmt->bindValue(':name', $category['name']);
    $stmt->bindValue(':slug', $category['slug']);
    $stmt->bindValue(':description', $category['description'] !== '' ? $category['description'] : null);
    $stmt->bindValue(':sort_order', (int) $category['sort_order'], PDO::PARAM_INT);
    $stmt->bindValue(':is_visible', (int) $category['is_visible'], PDO::PARAM_INT);
    $stmt->execute();

    return $id ?? (int) db()->lastInsertId();
}

function blog_category_render_form(array $category, string $action, string $mode, array $errors = []): void
{
    ?>
  <?php if ($errors !== []): ?>
    <div class="toast error" role="alert"><?= e(implode(' ', $errors)) ?></div>
  <?php endif; ?>

  <section class="card form-card">
    <h2><span class="material-symbols-outlined">category</span>Category Details</h2>
    <form method="post" action="<?= e($action) ?>">
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
      <div class="form-grid">
        <div class="field">
          <label for="name">Category Name</label>
          <input class="input" id="name" name="name" type="text" maxlength="120" value="<?= e((string) $category['name']) ?>" required>
        </div>
        <div class="field">
          <label for="slug">URL Slug</label>
          <input class="input" id="slug" name="slug" type="text" maxlength="191" value="<?= e((string) $category['slug']) ?>" required>
        </div>
        <div class="field full">
          <label for="description">Description</label>
          <textarea class="textarea" id="description" name="description" rows="5"><?= e((string) $category['description']) ?></textarea>
        </div>
        <div class="field">
          <label for="sort_order">Sort Order</label>
          <input class="input" id="sort_order" name="sort_order" type="number" min="0" step="1" value="<?= e((string) $category['sort_order']) ?>">
        </div>
        <div class="field">
          <label>Visibility</label>
          <label class="hint"><input class="checkbox" type="checkbox" name="is_visible" value="1" <?= (int) $category['is_visible'] === 1 ? 'checked' : '' ?>> Show in blog category lists</label>
        </div>
      </div>
      <div class="sticky-actions">
        <span class="hint"><?= $mode === 'create' ? 'New category will be available in the blog post category dropdown.' : 'Changes are saved only when you press Save Category.' ?></span>
        <div style="display:flex;gap:12px;flex-wrap:wrap">
          <a class="secondary-btn" href="blog-categories.php">Cancel</a>
          <button class="primary-btn" type="submit">Save Category</button>
        </div>
      </div>
    </form>
  </section>

  <script>
  (function () {
    var name = document.getElementById('name');
    var slug = document.getElementById('slug');
    if (name && slug) {
      name.addEventListener('blur', function () {
        if (slug.value.trim() !== '') return;
        slug.value = name.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
      });
    }
  })();
  </script>
    <?php
}
