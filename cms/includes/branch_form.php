<?php
declare(strict_types=1);

function branch_default(): array
{
    return [
        'id' => null,
        'slug' => '',
        'name' => '',
        'tag_label' => '',
        'address_line' => '',
        'city' => '',
        'district' => '',
        'country' => 'Nepal',
        'phone' => '',
        'email' => '',
        'hours_note' => '',
        'latitude' => '',
        'longitude' => '',
        'map_zoom' => '15',
        'is_primary' => 0,
        'is_visible' => 1,
        'sort_order' => '0',
    ];
}

function branch_slug(string $name): string
{
    $slug = strtolower(trim($name));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
    $slug = trim($slug, '-');

    return $slug !== '' ? $slug : 'branch';
}

function branch_find(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM clinic_branches WHERE id = :id AND deleted_at IS NULL LIMIT 1');
    $stmt->execute([':id' => $id]);
    $branch = $stmt->fetch();

    return $branch ?: null;
}

function branch_list(): array
{
    $stmt = db()->query(
        'SELECT * FROM clinic_branches
         WHERE deleted_at IS NULL
         ORDER BY is_primary DESC, sort_order ASC, name ASC'
    );

    return $stmt->fetchAll();
}

function branch_from_post(): array
{
    $branch = branch_default();
    foreach (['name', 'slug', 'tag_label', 'address_line', 'city', 'district', 'country', 'phone', 'email', 'hours_note', 'latitude', 'longitude', 'map_zoom', 'sort_order'] as $field) {
        $branch[$field] = trim((string) ($_POST[$field] ?? ''));
    }
    $branch['is_primary'] = isset($_POST['is_primary']) ? 1 : 0;
    $branch['is_visible'] = isset($_POST['is_visible']) ? 1 : 0;
    $branch['slug'] = branch_slug($branch['slug'] !== '' ? $branch['slug'] : $branch['name']);
    if ($branch['map_zoom'] === '') {
        $branch['map_zoom'] = '15';
    }
    if ($branch['sort_order'] === '') {
        $branch['sort_order'] = '0';
    }

    return $branch;
}

function branch_validate(array $branch, ?int $currentId = null): array
{
    // Database rows carry NULL for empty optional columns; treat them as empty strings.
    $branch = array_map(static function ($value) {
        return $value ?? '';
    }, $branch);
    $errors = [];
    $lengths = [
        'name' => [150, 'Branch name'],
        'tag_label' => [60, 'Tag'],
        'address_line' => [255, 'Street or area'],
        'city' => [100, 'City'],
        'district' => [100, 'District'],
        'country' => [100, 'Country'],
        'phone' => [30, 'Phone'],
        'email' => [191, 'Email'],
        'hours_note' => [255, 'Opening hours note'],
    ];

    foreach (['name' => 'Branch name', 'address_line' => 'Street or area', 'city' => 'City', 'country' => 'Country'] as $field => $label) {
        if ($branch[$field] === '') {
            $errors[] = $label . ' is required.';
        }
    }
    foreach ($lengths as $field => [$max, $label]) {
        if (strlen((string) $branch[$field]) > $max) {
            $errors[] = $label . ' must be ' . $max . ' characters or less.';
        }
    }

    if ($branch['phone'] !== '' && !preg_match('/^[\d+\-\s()]{6,30}$/', $branch['phone'])) {
        $errors[] = 'Phone may only contain digits, spaces, +, - and brackets.';
    }
    if ($branch['email'] !== '' && filter_var($branch['email'], FILTER_VALIDATE_EMAIL) === false) {
        $errors[] = 'Email address is invalid.';
    }

    // Coordinates are optional, but must come as a valid pair.
    $hasLat = $branch['latitude'] !== '';
    $hasLng = $branch['longitude'] !== '';
    if ($hasLat !== $hasLng) {
        $errors[] = 'Enter both latitude and longitude, or leave both empty to place the pin by address.';
    } elseif ($hasLat) {
        if (!is_numeric($branch['latitude']) || abs((float) $branch['latitude']) > 90) {
            $errors[] = 'Latitude must be a number between -90 and 90.';
        }
        if (!is_numeric($branch['longitude']) || abs((float) $branch['longitude']) > 180) {
            $errors[] = 'Longitude must be a number between -180 and 180.';
        }
    }

    if (!ctype_digit((string) $branch['map_zoom']) || (int) $branch['map_zoom'] < 3 || (int) $branch['map_zoom'] > 20) {
        $errors[] = 'Map zoom must be a whole number from 3 to 20.';
    }
    if (!ctype_digit((string) $branch['sort_order'])) {
        $errors[] = 'Sort order must be a whole number.';
    }

    $sql = 'SELECT id FROM clinic_branches WHERE slug = :slug AND deleted_at IS NULL';
    $params = [':slug' => $branch['slug']];
    if ($currentId !== null) {
        $sql .= ' AND id <> :id';
        $params[':id'] = $currentId;
    }
    $stmt = db()->prepare($sql . ' LIMIT 1');
    $stmt->execute($params);
    if ($stmt->fetch()) {
        $errors[] = 'This URL slug is already used by another branch.';
    }

    return $errors;
}

function branch_save(array $branch, ?int $id = null): int
{
    $pdo = db();
    $pdo->beginTransaction();

    try {
        // Only one branch can be the main clinic.
        if ((int) $branch['is_primary'] === 1) {
            $pdo->exec('UPDATE clinic_branches SET is_primary = 0 WHERE is_primary = 1');
        }

        $columns = 'slug = :slug, name = :name, tag_label = :tag_label, address_line = :address_line, city = :city,
                    district = :district, country = :country, phone = :phone, email = :email, hours_note = :hours_note,
                    latitude = :latitude, longitude = :longitude, map_zoom = :map_zoom, is_primary = :is_primary,
                    is_visible = :is_visible, sort_order = :sort_order';
        if ($id === null) {
            $stmt = $pdo->prepare('INSERT INTO clinic_branches SET ' . $columns);
        } else {
            $stmt = $pdo->prepare('UPDATE clinic_branches SET ' . $columns . ' WHERE id = :id');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        }

        foreach (['slug', 'name', 'address_line', 'city', 'country'] as $field) {
            $stmt->bindValue(':' . $field, $branch[$field]);
        }
        foreach (['tag_label', 'district', 'phone', 'email', 'hours_note', 'latitude', 'longitude'] as $field) {
            $value = (string) $branch[$field];
            $stmt->bindValue(':' . $field, $value !== '' ? $value : null, $value !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
        }
        foreach (['map_zoom', 'is_primary', 'is_visible', 'sort_order'] as $field) {
            $stmt->bindValue(':' . $field, (int) $branch[$field], PDO::PARAM_INT);
        }
        $stmt->execute();

        $branchId = $id ?? (int) $pdo->lastInsertId();
        $pdo->commit();

        return $branchId;
    } catch (Throwable $exception) {
        $pdo->rollBack();
        throw $exception;
    }
}

function branch_render_form(array $branch, string $action, string $mode, array $errors = []): void
{
    $text = static function (string $field) use ($branch): string {
        return e((string) ($branch[$field] ?? ''));
    };
    ?>
  <?php if ($errors !== []): ?>
    <div class="toast error" role="alert"><?= e(implode(' ', $errors)) ?></div>
  <?php endif; ?>

  <form method="post" action="<?= e($action) ?>">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

    <div class="grid settings-grid">
      <div class="grid">
        <section class="card form-card">
          <h2><span class="material-symbols-outlined">storefront</span>Branch Details</h2>
          <div class="form-grid">
            <div class="field">
              <label for="name">Branch Name</label>
              <input class="input" id="name" name="name" type="text" maxlength="150" value="<?= $text('name') ?>" required>
              <p class="hint">Shown on the branch list, for example Butwal Branch.</p>
            </div>
            <div class="field">
              <label for="tag_label">Tag <span class="hint">(optional)</span></label>
              <input class="input" id="tag_label" name="tag_label" type="text" maxlength="60" value="<?= $text('tag_label') ?>">
              <p class="hint">Small label beside the name, for example Main clinic.</p>
            </div>
            <div class="field full">
              <label for="slug">URL Slug</label>
              <input class="input" id="slug" name="slug" type="text" maxlength="191" value="<?= $text('slug') ?>">
              <p class="hint">Filled from the name if left empty. Lowercase letters, numbers, and hyphens only.</p>
            </div>
          </div>
        </section>

        <section class="card form-card">
          <h2><span class="material-symbols-outlined">location_on</span>Address</h2>
          <div class="form-grid">
            <div class="field full">
              <label for="address_line">Street or Area</label>
              <input class="input" id="address_line" name="address_line" type="text" maxlength="255" value="<?= $text('address_line') ?>" required>
              <p class="hint">Add a landmark patients recognise, for example Traffic Chowk, opposite the bus park.</p>
            </div>
            <div class="field">
              <label for="city">City</label>
              <input class="input" id="city" name="city" type="text" maxlength="100" value="<?= $text('city') ?>" required>
            </div>
            <div class="field">
              <label for="district">District</label>
              <input class="input" id="district" name="district" type="text" maxlength="100" value="<?= $text('district') ?>">
            </div>
            <div class="field">
              <label for="country">Country</label>
              <input class="input" id="country" name="country" type="text" maxlength="100" value="<?= $text('country') ?>" required>
            </div>
          </div>
        </section>

        <section class="card form-card">
          <h2><span class="material-symbols-outlined">call</span>Contact</h2>
          <div class="form-grid">
            <div class="field">
              <label for="phone">Phone</label>
              <input class="input" id="phone" name="phone" type="tel" maxlength="30" value="<?= $text('phone') ?>">
            </div>
            <div class="field">
              <label for="email">Email <span class="hint">(optional)</span></label>
              <input class="input" id="email" name="email" type="email" maxlength="191" value="<?= $text('email') ?>">
            </div>
            <div class="field full">
              <label for="hours_note">Opening Hours Note <span class="hint">(optional)</span></label>
              <input class="input" id="hours_note" name="hours_note" type="text" maxlength="255" value="<?= $text('hours_note') ?>">
              <p class="hint">Only if this branch keeps different hours, for example Sun-Fri 9 AM-5 PM.</p>
            </div>
          </div>
        </section>
      </div>

      <aside class="grid">
        <section class="card form-card">
          <h2><span class="material-symbols-outlined">map</span>Map Pin</h2>
          <div class="form-grid">
            <div class="field">
              <label for="latitude">Latitude</label>
              <input class="input" id="latitude" name="latitude" type="text" inputmode="decimal" value="<?= $text('latitude') ?>">
            </div>
            <div class="field">
              <label for="longitude">Longitude</label>
              <input class="input" id="longitude" name="longitude" type="text" inputmode="decimal" value="<?= $text('longitude') ?>">
            </div>
            <div class="field full">
              <label for="map_zoom">Map Zoom</label>
              <input class="input" id="map_zoom" name="map_zoom" type="number" min="3" max="20" step="1" value="<?= $text('map_zoom') ?>">
              <p class="hint">In Google Maps, right-click the clinic entrance and click the coordinates to copy them, then paste latitude and longitude here. Leave both empty to place the pin by address. 15 to 17 is street level.</p>
            </div>
          </div>
        </section>

        <section class="card form-card">
          <h2><span class="material-symbols-outlined">settings_suggest</span>Configuration</h2>
          <div class="form-grid">
            <div class="field">
              <label for="sort_order">Sort Order</label>
              <input class="input" id="sort_order" name="sort_order" type="number" min="0" step="1" value="<?= $text('sort_order') ?>">
            </div>
            <div class="field full">
              <label>Main Clinic</label>
              <label class="hint"><input class="checkbox" type="checkbox" name="is_primary" value="1" <?= (int) $branch['is_primary'] === 1 ? 'checked' : '' ?>> List first and show on the map by default</label>
            </div>
            <div class="field full">
              <label>Public Visibility</label>
              <label class="hint"><input class="checkbox" type="checkbox" name="is_visible" value="1" <?= (int) $branch['is_visible'] === 1 ? 'checked' : '' ?>> Show on the website</label>
            </div>
          </div>
        </section>
      </aside>
    </div>

    <div class="sticky-actions">
      <span class="hint"><?= $mode === 'create' ? 'The branch appears on the Home and Contact pages once saved as visible.' : 'Changes are saved only when you press Save Branch.' ?></span>
      <div style="display:flex;gap:12px;flex-wrap:wrap">
        <a class="secondary-btn" href="branches.php">Cancel</a>
        <button class="primary-btn" type="submit">Save Branch</button>
      </div>
    </div>
  </form>

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
