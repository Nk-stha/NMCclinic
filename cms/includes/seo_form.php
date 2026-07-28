<?php
declare(strict_types=1);

function seo_default(): array
{
    return [
        'id' => null,
        'target_type' => 'page',
        'target_id' => '',
        'page_id' => '',
        'doctor_id' => '',
        'blog_post_id' => '',
        'meta_title' => '',
        'meta_description' => '',
        'canonical_url' => '',
        'robots_index' => 1,
        'robots_follow' => 1,
        'og_title' => '',
        'og_description' => '',
        'og_image_id' => '',
        'twitter_card' => 'summary_large_image',
        'schema_json_override' => '',
    ];
}

function seo_target_options(): array
{
    return [
        'page' => db()->query(
            'SELECT id, slug AS label, status
             FROM pages
             WHERE deleted_at IS NULL
             ORDER BY page_type ASC, slug ASC'
        )->fetchAll(),
        'doctor' => db()->query(
            'SELECT id, full_name AS label, status
             FROM doctors
             WHERE deleted_at IS NULL
             ORDER BY full_name ASC'
        )->fetchAll(),
        'blog_post' => db()->query(
            'SELECT id, title AS label, status
             FROM blog_posts
             WHERE deleted_at IS NULL
             ORDER BY title ASC'
        )->fetchAll(),
    ];
}

function seo_media_options(): array
{
    $stmt = db()->query(
        "SELECT id, file_path, alt_text
         FROM media_assets
         WHERE deleted_at IS NULL AND is_public = 1 AND mime_type LIKE 'image/%'
         ORDER BY id DESC"
    );

    return $stmt->fetchAll();
}

function seo_find(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM seo_metadata WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    $seo = $stmt->fetch();

    if (!$seo) {
        return null;
    }

    if ($seo['page_id'] !== null) {
        $seo['target_type'] = 'page';
        $seo['target_id'] = (string) $seo['page_id'];
    } elseif ($seo['doctor_id'] !== null) {
        $seo['target_type'] = 'doctor';
        $seo['target_id'] = (string) $seo['doctor_id'];
    } else {
        $seo['target_type'] = 'blog_post';
        $seo['target_id'] = (string) $seo['blog_post_id'];
    }

    return $seo;
}

function seo_list(): array
{
    $stmt = db()->query(
        'SELECT sm.*,
                p.slug AS page_slug,
                d.full_name AS doctor_name,
                bp.title AS blog_title,
                m.file_path AS og_image_path
         FROM seo_metadata sm
         LEFT JOIN pages p ON p.id = sm.page_id
         LEFT JOIN doctors d ON d.id = sm.doctor_id
         LEFT JOIN blog_posts bp ON bp.id = sm.blog_post_id
         LEFT JOIN media_assets m ON m.id = sm.og_image_id
         ORDER BY sm.updated_at DESC, sm.id DESC'
    );

    return $stmt->fetchAll();
}

function seo_from_post(): array
{
    $seo = seo_default();
    $seo['target_type'] = trim((string) ($_POST['target_type'] ?? 'page'));
    $seo['target_id'] = trim((string) ($_POST['target_id'] ?? ''));
    $seo['meta_title'] = trim((string) ($_POST['meta_title'] ?? ''));
    $seo['meta_description'] = trim((string) ($_POST['meta_description'] ?? ''));
    $seo['canonical_url'] = trim((string) ($_POST['canonical_url'] ?? ''));
    $seo['robots_index'] = isset($_POST['robots_index']) ? 1 : 0;
    $seo['robots_follow'] = isset($_POST['robots_follow']) ? 1 : 0;
    $seo['og_title'] = trim((string) ($_POST['og_title'] ?? ''));
    $seo['og_description'] = trim((string) ($_POST['og_description'] ?? ''));
    $seo['og_image_id'] = trim((string) ($_POST['og_image_id'] ?? ''));
    $seo['twitter_card'] = trim((string) ($_POST['twitter_card'] ?? 'summary_large_image'));
    $seo['schema_json_override'] = trim((string) ($_POST['schema_json_override'] ?? ''));

    if ($seo['target_type'] === 'page') {
        $seo['page_id'] = $seo['target_id'];
    } elseif ($seo['target_type'] === 'doctor') {
        $seo['doctor_id'] = $seo['target_id'];
    } elseif ($seo['target_type'] === 'blog_post') {
        $seo['blog_post_id'] = $seo['target_id'];
    }

    return $seo;
}

function seo_target_label(array $row): string
{
    if ($row['page_id'] !== null) {
        return 'Page: ' . (string) ($row['page_slug'] ?? ('#' . $row['page_id']));
    }
    if ($row['doctor_id'] !== null) {
        return 'Doctor: ' . (string) ($row['doctor_name'] ?? ('#' . $row['doctor_id']));
    }

    return 'Blog: ' . (string) ($row['blog_title'] ?? ('#' . $row['blog_post_id']));
}

function seo_validate(array $seo, ?int $currentId = null): array
{
    $errors = [];
    $targetTables = [
        'page' => ['pages', 'page_id'],
        'doctor' => ['doctors', 'doctor_id'],
        'blog_post' => ['blog_posts', 'blog_post_id'],
    ];

    if (!isset($targetTables[$seo['target_type']])) {
        $errors[] = 'SEO target type is invalid.';
    } elseif ($seo['target_id'] === '' || !ctype_digit($seo['target_id'])) {
        $errors[] = 'SEO target is required.';
    } else {
        [$table, $field] = $targetTables[$seo['target_type']];
        $deletedClause = $table === 'seo_metadata' ? '' : ' AND deleted_at IS NULL';
        $stmt = db()->prepare('SELECT id FROM ' . $table . ' WHERE id = :id' . $deletedClause . ' LIMIT 1');
        $stmt->execute([':id' => (int) $seo['target_id']]);
        if (!$stmt->fetch()) {
            $errors[] = 'Selected SEO target is invalid.';
        }

        if ($currentId === null) {
            $stmt = db()->prepare('SELECT id FROM seo_metadata WHERE ' . $field . ' = :target_id LIMIT 1');
            $stmt->execute([':target_id' => (int) $seo['target_id']]);
        } else {
            $stmt = db()->prepare('SELECT id FROM seo_metadata WHERE ' . $field . ' = :target_id AND id <> :id LIMIT 1');
            $stmt->execute([':target_id' => (int) $seo['target_id'], ':id' => $currentId]);
        }
        if ($stmt->fetch()) {
            $errors[] = 'SEO metadata already exists for the selected target.';
        }
    }

    if ($seo['meta_title'] === '') {
        $errors[] = 'Meta title is required.';
    } elseif (strlen($seo['meta_title']) > 255) {
        $errors[] = 'Meta title must be 255 characters or less.';
    }

    if ($seo['meta_description'] === '') {
        $errors[] = 'Meta description is required.';
    } elseif (strlen($seo['meta_description']) > 500) {
        $errors[] = 'Meta description must be 500 characters or less.';
    }

    if ($seo['canonical_url'] !== '' && !filter_var($seo['canonical_url'], FILTER_VALIDATE_URL)) {
        $errors[] = 'Canonical URL must be a valid absolute URL.';
    }

    foreach (['og_title' => 255, 'og_description' => 500] as $field => $limit) {
        if ($seo[$field] !== '' && strlen($seo[$field]) > $limit) {
            $errors[] = str_replace('_', ' ', strtoupper($field)) . ' must be ' . $limit . ' characters or less.';
        }
    }

    if ($seo['og_image_id'] !== '') {
        $stmt = db()->prepare("SELECT id FROM media_assets WHERE id = :id AND deleted_at IS NULL AND is_public = 1 AND mime_type LIKE 'image/%' LIMIT 1");
        $stmt->execute([':id' => (int) $seo['og_image_id']]);
        if (!$stmt->fetch()) {
            $errors[] = 'Selected OG image is invalid.';
        }
    }

    if (!in_array($seo['twitter_card'], ['summary', 'summary_large_image'], true)) {
        $errors[] = 'Twitter card type is invalid.';
    }

    if ($seo['schema_json_override'] !== '') {
        json_decode($seo['schema_json_override'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $errors[] = 'Schema JSON override must be valid JSON.';
        }
    }

    return $errors;
}

function seo_save(array $seo, ?int $id = null): int
{
    if ($id === null) {
        $stmt = db()->prepare(
            'INSERT INTO seo_metadata
             (page_id, doctor_id, blog_post_id, meta_title, meta_description, canonical_url, robots_index, robots_follow, og_title, og_description, og_image_id, twitter_card, schema_json_override)
             VALUES
             (:page_id, :doctor_id, :blog_post_id, :meta_title, :meta_description, :canonical_url, :robots_index, :robots_follow, :og_title, :og_description, :og_image_id, :twitter_card, :schema_json_override)'
        );
    } else {
        $stmt = db()->prepare(
            'UPDATE seo_metadata
             SET page_id = :page_id,
                 doctor_id = :doctor_id,
                 blog_post_id = :blog_post_id,
                 meta_title = :meta_title,
                 meta_description = :meta_description,
                 canonical_url = :canonical_url,
                 robots_index = :robots_index,
                 robots_follow = :robots_follow,
                 og_title = :og_title,
                 og_description = :og_description,
                 og_image_id = :og_image_id,
                 twitter_card = :twitter_card,
                 schema_json_override = :schema_json_override
             WHERE id = :id'
        );
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    }

    foreach (['page_id', 'doctor_id', 'blog_post_id', 'og_image_id'] as $field) {
        if ($seo[$field] === '' || $seo[$field] === null) {
            $stmt->bindValue(':' . $field, null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':' . $field, (int) $seo[$field], PDO::PARAM_INT);
        }
    }
    $stmt->bindValue(':meta_title', $seo['meta_title']);
    $stmt->bindValue(':meta_description', $seo['meta_description']);
    $stmt->bindValue(':canonical_url', $seo['canonical_url'] !== '' ? $seo['canonical_url'] : null);
    $stmt->bindValue(':robots_index', (int) $seo['robots_index'], PDO::PARAM_INT);
    $stmt->bindValue(':robots_follow', (int) $seo['robots_follow'], PDO::PARAM_INT);
    $stmt->bindValue(':og_title', $seo['og_title'] !== '' ? $seo['og_title'] : null);
    $stmt->bindValue(':og_description', $seo['og_description'] !== '' ? $seo['og_description'] : null);
    $stmt->bindValue(':twitter_card', $seo['twitter_card']);
    $stmt->bindValue(':schema_json_override', $seo['schema_json_override'] !== '' ? $seo['schema_json_override'] : null);
    $stmt->execute();

    return $id ?? (int) db()->lastInsertId();
}

function seo_render_form(array $seo, array $targetOptions, array $mediaOptions, string $action, string $mode, array $errors = []): void
{
    ?>
  <?php if ($errors !== []): ?>
    <div class="toast error" role="alert"><?= e(implode(' ', $errors)) ?></div>
  <?php endif; ?>

  <form method="post" action="<?= e($action) ?>">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <div class="grid settings-grid">
      <section class="card form-card">
        <h2><span class="material-symbols-outlined">travel_explore</span>SEO Metadata</h2>
        <div class="form-grid">
          <div class="field">
            <label for="target_type">Target Type</label>
            <select class="select" id="target_type" name="target_type">
              <option value="page" <?= $seo['target_type'] === 'page' ? 'selected' : '' ?>>Page</option>
              <option value="doctor" <?= $seo['target_type'] === 'doctor' ? 'selected' : '' ?>>Doctor</option>
              <option value="blog_post" <?= $seo['target_type'] === 'blog_post' ? 'selected' : '' ?>>Blog Post</option>
            </select>
          </div>
          <div class="field">
            <label for="target_id">Target</label>
            <select class="select" id="target_id" name="target_id" required></select>
            <p class="hint">The selected target can have only one SEO metadata record.</p>
          </div>
          <div class="field full">
            <label for="meta_title">Meta Title</label>
            <input class="input" id="meta_title" name="meta_title" type="text" maxlength="255" value="<?= e((string) $seo['meta_title']) ?>" required>
          </div>
          <div class="field full">
            <label for="meta_description">Meta Description</label>
            <textarea class="textarea" id="meta_description" name="meta_description" rows="4" maxlength="500" required><?= e((string) $seo['meta_description']) ?></textarea>
          </div>
          <div class="field full">
            <label for="canonical_url">Canonical URL</label>
            <input class="input" id="canonical_url" name="canonical_url" type="url" maxlength="500" value="<?= e((string) $seo['canonical_url']) ?>">
          </div>
        </div>
      </section>

      <aside class="grid">
        <section class="card form-card">
          <h2><span class="material-symbols-outlined">share</span>Social Preview</h2>
          <div class="form-grid">
            <div class="field full">
              <label for="og_title">OG Title</label>
              <input class="input" id="og_title" name="og_title" type="text" maxlength="255" value="<?= e((string) $seo['og_title']) ?>">
            </div>
            <div class="field full">
              <label for="og_description">OG Description</label>
              <textarea class="textarea" id="og_description" name="og_description" rows="3" maxlength="500"><?= e((string) $seo['og_description']) ?></textarea>
            </div>
            <div class="field full">
              <label for="og_image_id">OG Image</label>
              <select class="select" id="og_image_id" name="og_image_id">
                <option value="">No image selected</option>
                <?php foreach ($mediaOptions as $media): ?>
                  <option value="<?= e((string) $media['id']) ?>" <?= (string) $seo['og_image_id'] === (string) $media['id'] ? 'selected' : '' ?>>
                    <?= e((string) ($media['alt_text'] ?: $media['file_path'])) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="field full">
              <label for="twitter_card">Twitter Card</label>
              <select class="select" id="twitter_card" name="twitter_card">
                <option value="summary_large_image" <?= $seo['twitter_card'] === 'summary_large_image' ? 'selected' : '' ?>>Summary Large Image</option>
                <option value="summary" <?= $seo['twitter_card'] === 'summary' ? 'selected' : '' ?>>Summary</option>
              </select>
            </div>
          </div>
        </section>

        <section class="card form-card">
          <h2><span class="material-symbols-outlined">rule</span>Indexing</h2>
          <div class="form-grid">
            <div class="field">
              <label>Robots Index</label>
              <label class="hint"><input class="checkbox" type="checkbox" name="robots_index" value="1" <?= (int) $seo['robots_index'] === 1 ? 'checked' : '' ?>> Allow indexing</label>
            </div>
            <div class="field">
              <label>Robots Follow</label>
              <label class="hint"><input class="checkbox" type="checkbox" name="robots_follow" value="1" <?= (int) $seo['robots_follow'] === 1 ? 'checked' : '' ?>> Allow link following</label>
            </div>
          </div>
        </section>
      </aside>

      <section class="card form-card settings-wide">
        <h2><span class="material-symbols-outlined">data_object</span>Schema JSON Override</h2>
        <div class="field">
          <label for="schema_json_override">Schema JSON Override</label>
          <textarea class="textarea" id="schema_json_override" name="schema_json_override" rows="8"><?= e((string) $seo['schema_json_override']) ?></textarea>
          <p class="hint">Optional. Must be valid JSON when filled.</p>
        </div>
      </section>
    </div>

    <div class="sticky-actions">
      <span class="hint"><?= $mode === 'create' ? 'New SEO metadata will be assigned to the selected target.' : 'Changes are saved only when you press Save SEO.' ?></span>
      <div style="display:flex;gap:12px;flex-wrap:wrap">
        <a class="secondary-btn" href="seo.php">Cancel</a>
        <button class="primary-btn" type="submit">Save SEO</button>
      </div>
    </div>
  </form>

  <script>
  (function () {
    var options = <?php echo json_encode($targetOptions, JSON_THROW_ON_ERROR); ?>;
    var selectedType = <?= json_encode((string) $seo['target_type'], JSON_THROW_ON_ERROR) ?>;
    var selectedId = <?= json_encode((string) $seo['target_id'], JSON_THROW_ON_ERROR) ?>;
    var type = document.getElementById('target_type');
    var target = document.getElementById('target_id');

    function renderTargets() {
      var currentType = type.value || selectedType;
      var rows = options[currentType] || [];
      target.innerHTML = '<option value="">Select target</option>';
      rows.forEach(function (row) {
        var option = document.createElement('option');
        option.value = row.id;
        option.textContent = row.label + ' (' + row.status + ')';
        if (currentType === selectedType && String(row.id) === String(selectedId)) {
          option.selected = true;
        }
        target.appendChild(option);
      });
    }

    if (type && target) {
      type.addEventListener('change', function () {
        selectedId = '';
        renderTargets();
      });
      renderTargets();
    }
  })();
  </script>
    <?php
}
