<?php
declare(strict_types=1);

function blog_default(): array
{
    return [
        'id' => null,
        'category_id' => '',
        'slug' => '',
        'title' => '',
        'excerpt' => '',
        'body_text' => '',
        'body_html_sanitized' => '',
        'featured_image_id' => '',
        'author_name' => '',
        'reviewer_name' => '',
        'reviewed_at' => '',
        'published_at' => '',
        'status' => 'draft',
        'is_featured' => 0,
        'sort_order' => '0',
        'reading_time_minutes' => '',
    ];
}

function blog_slug(string $title): string
{
    $slug = strtolower(trim($title));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
    $slug = trim($slug, '-');

    return $slug !== '' ? $slug : 'blog-post';
}

function blog_html_to_text(?string $html): string
{
    if ($html === null || $html === '') {
        return '';
    }

    $html = preg_replace('/<br\s*\/?>/i', "\n", $html) ?? $html;
    $html = preg_replace('/<\/p>/i', "\n\n", $html) ?? $html;

    return trim(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
}

function blog_text_to_sanitized_html(string $text): ?string
{
    $text = trim($text);
    if ($text === '') {
        return null;
    }

    $paragraphs = preg_split('/\R{2,}/', $text) ?: [];
    $html = [];
    foreach ($paragraphs as $paragraph) {
        $paragraph = trim($paragraph);
        if ($paragraph !== '') {
            $html[] = '<p>' . nl2br(e($paragraph), false) . '</p>';
        }
    }

    return implode("\n", $html);
}

function blog_category_options(): array
{
    $stmt = db()->query(
        'SELECT id, name
         FROM blog_categories
         WHERE deleted_at IS NULL
         ORDER BY sort_order ASC, name ASC'
    );

    return $stmt->fetchAll();
}

function blog_media_options(): array
{
    $stmt = db()->query(
        "SELECT id, file_path, alt_text
         FROM media_assets
         WHERE deleted_at IS NULL AND is_public = 1 AND mime_type LIKE 'image/%'
         ORDER BY id DESC"
    );

    return $stmt->fetchAll();
}

function blog_find(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM blog_posts WHERE id = :id AND deleted_at IS NULL LIMIT 1');
    $stmt->execute([':id' => $id]);
    $post = $stmt->fetch();

    if (!$post) {
        return null;
    }

    $post['body_text'] = blog_html_to_text($post['body_html_sanitized'] ?? null);
    $post['published_at'] = $post['published_at'] ? str_replace(' ', 'T', substr((string) $post['published_at'], 0, 16)) : '';
    $post['reviewed_at'] = $post['reviewed_at'] ? str_replace(' ', 'T', substr((string) $post['reviewed_at'], 0, 16)) : '';

    return $post;
}

function blog_list(): array
{
    $stmt = db()->query(
        'SELECT p.*, c.name AS category_name, m.file_path AS image_path
         FROM blog_posts p
         LEFT JOIN blog_categories c ON c.id = p.category_id
         LEFT JOIN media_assets m ON m.id = p.featured_image_id
         WHERE p.deleted_at IS NULL
         ORDER BY p.sort_order ASC, p.published_at DESC, p.updated_at DESC'
    );

    return $stmt->fetchAll();
}

function blog_from_post(): array
{
    $post = blog_default();
    $post['category_id'] = trim((string) ($_POST['category_id'] ?? ''));
    $post['slug'] = trim((string) ($_POST['slug'] ?? ''));
    $post['title'] = trim((string) ($_POST['title'] ?? ''));
    $post['excerpt'] = trim((string) ($_POST['excerpt'] ?? ''));
    $post['body_text'] = trim((string) ($_POST['body_text'] ?? ''));
    $post['body_html_sanitized'] = blog_text_to_sanitized_html($post['body_text']);
    $post['featured_image_id'] = trim((string) ($_POST['featured_image_id'] ?? ''));
    $post['author_name'] = trim((string) ($_POST['author_name'] ?? ''));
    $post['reviewer_name'] = trim((string) ($_POST['reviewer_name'] ?? ''));
    $post['reviewed_at'] = trim((string) ($_POST['reviewed_at'] ?? ''));
    $post['published_at'] = trim((string) ($_POST['published_at'] ?? ''));
    $post['status'] = trim((string) ($_POST['status'] ?? 'draft'));
    $post['is_featured'] = isset($_POST['is_featured']) ? 1 : 0;
    $post['sort_order'] = trim((string) ($_POST['sort_order'] ?? '0'));
    $post['reading_time_minutes'] = trim((string) ($_POST['reading_time_minutes'] ?? ''));

    if ($post['slug'] === '') {
        $post['slug'] = blog_slug($post['title']);
    } else {
        $post['slug'] = blog_slug($post['slug']);
    }

    return $post;
}

function blog_datetime_for_db(string $value): ?string
{
    if ($value === '') {
        return null;
    }

    return str_replace('T', ' ', $value) . ':00';
}

function blog_validate(array $post, ?int $currentId = null): array
{
    $errors = [];

    if ($post['title'] === '') {
        $errors[] = 'Post title is required.';
    } elseif (strlen($post['title']) > 255) {
        $errors[] = 'Post title must be 255 characters or less.';
    }

    if ($post['slug'] === '') {
        $errors[] = 'URL slug is required.';
    } elseif (strlen($post['slug']) > 191) {
        $errors[] = 'URL slug must be 191 characters or less.';
    }

    if ($post['excerpt'] === '') {
        $errors[] = 'Excerpt is required.';
    } elseif (strlen($post['excerpt']) > 500) {
        $errors[] = 'Excerpt must be 500 characters or less.';
    }

    foreach (['author_name' => 150, 'reviewer_name' => 150] as $field => $limit) {
        if ($post[$field] !== '' && strlen($post[$field]) > $limit) {
            $errors[] = str_replace('_', ' ', ucfirst($field)) . ' must be ' . $limit . ' characters or less.';
        }
    }

    if (!in_array($post['status'], ['draft', 'review', 'published', 'archived'], true)) {
        $errors[] = 'Publishing status is invalid.';
    }

    if (!ctype_digit((string) $post['sort_order'])) {
        $errors[] = 'Sort order must be a whole number.';
    }

    if ($post['reading_time_minutes'] !== '' && (!ctype_digit($post['reading_time_minutes']) || (int) $post['reading_time_minutes'] > 999)) {
        $errors[] = 'Reading time must be a whole number under 1000.';
    }

    foreach (['published_at' => 'Published date', 'reviewed_at' => 'Reviewed date'] as $field => $label) {
        if ($post[$field] !== '') {
            $date = DateTime::createFromFormat('Y-m-d\TH:i', $post[$field]);
            if (!$date) {
                $errors[] = $label . ' is invalid.';
            }
        }
    }

    if ($currentId === null) {
        $stmt = db()->prepare('SELECT id FROM blog_posts WHERE slug = :slug AND deleted_at IS NULL LIMIT 1');
        $stmt->execute([':slug' => $post['slug']]);
    } else {
        $stmt = db()->prepare('SELECT id FROM blog_posts WHERE slug = :slug AND deleted_at IS NULL AND id <> :id LIMIT 1');
        $stmt->execute([':slug' => $post['slug'], ':id' => $currentId]);
    }

    if ($stmt->fetch()) {
        $errors[] = 'This URL slug is already used by another blog post.';
    }

    if ($post['category_id'] !== '') {
        $stmt = db()->prepare('SELECT id FROM blog_categories WHERE id = :id AND deleted_at IS NULL LIMIT 1');
        $stmt->execute([':id' => (int) $post['category_id']]);
        if (!$stmt->fetch()) {
            $errors[] = 'Selected category is invalid.';
        }
    }

    if ($post['featured_image_id'] !== '') {
        $stmt = db()->prepare("SELECT id FROM media_assets WHERE id = :id AND deleted_at IS NULL AND is_public = 1 AND mime_type LIKE 'image/%' LIMIT 1");
        $stmt->execute([':id' => (int) $post['featured_image_id']]);
        if (!$stmt->fetch()) {
            $errors[] = 'Selected featured image is invalid.';
        }
    }

    return $errors;
}

function blog_save(array $post, ?int $id = null): int
{
    $pdo = db();

    if ($id === null) {
        $stmt = $pdo->prepare(
            'INSERT INTO blog_posts
             (category_id, slug, title, excerpt, body_html_sanitized, featured_image_id, author_name, reviewer_name, reviewed_at, published_at, status, is_featured, sort_order, reading_time_minutes)
             VALUES
             (:category_id, :slug, :title, :excerpt, :body_html_sanitized, :featured_image_id, :author_name, :reviewer_name, :reviewed_at, :published_at, :status, :is_featured, :sort_order, :reading_time_minutes)'
        );
    } else {
        $stmt = $pdo->prepare(
            'UPDATE blog_posts
             SET category_id = :category_id,
                 slug = :slug,
                 title = :title,
                 excerpt = :excerpt,
                 body_html_sanitized = :body_html_sanitized,
                 featured_image_id = :featured_image_id,
                 author_name = :author_name,
                 reviewer_name = :reviewer_name,
                 reviewed_at = :reviewed_at,
                 published_at = :published_at,
                 status = :status,
                 is_featured = :is_featured,
                 sort_order = :sort_order,
                 reading_time_minutes = :reading_time_minutes
             WHERE id = :id'
        );
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    }

    foreach (['category_id', 'featured_image_id'] as $field) {
        if ($post[$field] === '') {
            $stmt->bindValue(':' . $field, null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':' . $field, (int) $post[$field], PDO::PARAM_INT);
        }
    }
    $stmt->bindValue(':slug', $post['slug']);
    $stmt->bindValue(':title', $post['title']);
    $stmt->bindValue(':excerpt', $post['excerpt']);
    $stmt->bindValue(':body_html_sanitized', $post['body_html_sanitized']);
    $stmt->bindValue(':author_name', $post['author_name'] !== '' ? $post['author_name'] : null);
    $stmt->bindValue(':reviewer_name', $post['reviewer_name'] !== '' ? $post['reviewer_name'] : null);
    $stmt->bindValue(':reviewed_at', blog_datetime_for_db($post['reviewed_at']));
    $stmt->bindValue(':published_at', blog_datetime_for_db($post['published_at']));
    $stmt->bindValue(':status', $post['status']);
    $stmt->bindValue(':is_featured', (int) $post['is_featured'], PDO::PARAM_INT);
    $stmt->bindValue(':sort_order', (int) $post['sort_order'], PDO::PARAM_INT);
    if ($post['reading_time_minutes'] === '') {
        $stmt->bindValue(':reading_time_minutes', null, PDO::PARAM_NULL);
    } else {
        $stmt->bindValue(':reading_time_minutes', (int) $post['reading_time_minutes'], PDO::PARAM_INT);
    }
    $stmt->execute();

    return $id ?? (int) $pdo->lastInsertId();
}

function blog_render_form(array $post, array $categoryOptions, array $mediaOptions, string $action, string $mode, array $errors = []): void
{
    ?>
  <?php if ($errors !== []): ?>
    <div class="toast error" role="alert"><?= e(implode(' ', $errors)) ?></div>
  <?php endif; ?>

  <form method="post" action="<?= e($action) ?>">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

    <div class="grid settings-grid">
      <div class="grid">
        <section class="card form-card">
          <h2><span class="material-symbols-outlined">edit_note</span>Article Content</h2>
          <div class="form-grid">
            <div class="field full">
              <label for="title">Post Title</label>
              <input class="input" id="title" name="title" type="text" maxlength="255" value="<?= e((string) $post['title']) ?>" required>
            </div>
            <div class="field full">
              <label for="slug">URL Slug</label>
              <input class="input" id="slug" name="slug" type="text" maxlength="191" value="<?= e((string) $post['slug']) ?>" required>
            </div>
            <div class="field full">
              <label for="excerpt">Excerpt</label>
              <textarea class="textarea" id="excerpt" name="excerpt" rows="3" maxlength="500" required><?= e((string) $post['excerpt']) ?></textarea>
              <p class="hint">Required by the current schema and useful for listing summaries and meta descriptions.</p>
            </div>
            <div class="field full">
              <label for="body_text">Article Body</label>
              <textarea class="textarea" id="body_text" name="body_text" rows="12"><?= e((string) ($post['body_text'] ?? '')) ?></textarea>
              <p class="hint">Enter plain text. The CMS stores it as sanitized paragraph HTML.</p>
            </div>
          </div>
        </section>

        <section class="card form-card">
          <h2><span class="material-symbols-outlined">verified_user</span>Medical Review</h2>
          <div class="form-grid">
            <div class="field">
              <label for="author_name">Author Name</label>
              <input class="input" id="author_name" name="author_name" type="text" maxlength="150" value="<?= e((string) $post['author_name']) ?>">
            </div>
            <div class="field">
              <label for="reviewer_name">Reviewer Name</label>
              <input class="input" id="reviewer_name" name="reviewer_name" type="text" maxlength="150" value="<?= e((string) $post['reviewer_name']) ?>">
            </div>
            <div class="field">
              <label for="reviewed_at">Reviewed Date</label>
              <input class="input" id="reviewed_at" name="reviewed_at" type="datetime-local" value="<?= e((string) $post['reviewed_at']) ?>">
            </div>
            <div class="field">
              <label for="published_at">Published Date</label>
              <input class="input" id="published_at" name="published_at" type="datetime-local" value="<?= e((string) $post['published_at']) ?>">
            </div>
          </div>
        </section>
      </div>

      <aside class="grid">
        <section class="card form-card">
          <h2><span class="material-symbols-outlined">category</span>Category</h2>
          <div class="field">
            <label for="category_id">Blog Category</label>
            <select class="select" id="category_id" name="category_id">
              <option value="">No category selected</option>
              <?php foreach ($categoryOptions as $category): ?>
                <option value="<?= e((string) $category['id']) ?>" <?= (string) $post['category_id'] === (string) $category['id'] ? 'selected' : '' ?>>
                  <?= e((string) $category['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </section>

        <section class="card form-card">
          <h2><span class="material-symbols-outlined">image</span>Featured Image</h2>
          <div class="field">
            <label for="featured_image_id">Image</label>
            <select class="select" id="featured_image_id" name="featured_image_id">
              <option value="">No image selected</option>
              <?php foreach ($mediaOptions as $media): ?>
                <option value="<?= e((string) $media['id']) ?>" <?= (string) $post['featured_image_id'] === (string) $media['id'] ? 'selected' : '' ?>>
                  <?= e((string) ($media['alt_text'] ?: $media['file_path'])) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </section>

        <section class="card form-card">
          <h2><span class="material-symbols-outlined">settings_suggest</span>Publication Settings</h2>
          <div class="form-grid">
            <div class="field">
              <label for="status">Status</label>
              <select class="select" id="status" name="status">
                <?php foreach (['draft' => 'Draft', 'review' => 'Review', 'published' => 'Published', 'archived' => 'Archived'] as $value => $label): ?>
                  <option value="<?= e($value) ?>" <?= $post['status'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="field">
              <label for="reading_time_minutes">Reading Time</label>
              <input class="input" id="reading_time_minutes" name="reading_time_minutes" type="number" min="0" max="999" value="<?= e((string) $post['reading_time_minutes']) ?>">
            </div>
            <div class="field">
              <label for="sort_order">Sort Order</label>
              <input class="input" id="sort_order" name="sort_order" type="number" min="0" step="1" value="<?= e((string) $post['sort_order']) ?>">
            </div>
            <div class="field">
              <label>Featured Post</label>
              <label class="hint"><input class="checkbox" type="checkbox" name="is_featured" value="1" <?= (int) $post['is_featured'] === 1 ? 'checked' : '' ?>> Promote in featured blog areas</label>
            </div>
          </div>
        </section>
      </aside>
    </div>

    <div class="sticky-actions">
      <span class="hint"><?= $mode === 'create' ? 'New blog post will be saved with the selected status.' : 'Changes are saved only when you press Save Post.' ?></span>
      <div style="display:flex;gap:12px;flex-wrap:wrap">
        <a class="secondary-btn" href="blog.php">Cancel</a>
        <button class="primary-btn" type="submit">Save Post</button>
      </div>
    </div>
  </form>

  <script>
  (function () {
    var title = document.getElementById('title');
    var slug = document.getElementById('slug');
    if (title && slug) {
      title.addEventListener('blur', function () {
        if (slug.value.trim() !== '') return;
        slug.value = title.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
      });
    }
  })();
  </script>
    <?php
}
