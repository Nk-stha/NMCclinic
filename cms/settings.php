<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';

$admin = require_admin();

$days = [
    0 => 'Sunday',
    1 => 'Monday',
    2 => 'Tuesday',
    3 => 'Wednesday',
    4 => 'Thursday',
    5 => 'Friday',
    6 => 'Saturday',
];

$settings = [
    'clinic_name' => 'NMC Clinic',
    'address_line' => 'Traffic Chowk',
    'city' => 'Butwal',
    'district' => 'Rupandehi',
    'country' => 'Nepal',
    'primary_phone' => '+977 71-000000',
    'secondary_phone' => '+977 98-0000000',
    'whatsapp_number' => '+9779800000000',
    'email' => 'info@nmcbutwal.com.np',
    'latitude' => '27.7000000',
    'longitude' => '83.4480000',
    'map_zoom' => '14',
    'privacy_url' => '',
    'terms_url' => '',
    'accessibility_url' => '',
];
$hours = [];
$socials = [
    'facebook' => ['label' => 'Facebook', 'url' => '', 'is_visible' => 1],
    'instagram' => ['label' => 'Instagram', 'url' => '', 'is_visible' => 1],
    'linkedin' => ['label' => 'LinkedIn', 'url' => '', 'is_visible' => 0],
];
$toast = $_SESSION['settings_toast'] ?? null;
unset($_SESSION['settings_toast']);

foreach ($days as $dayNumber => $dayName) {
    $hours[$dayNumber] = [
        'day_of_week' => $dayNumber,
        'opens_at' => $dayNumber === 6 ? '09:00' : '08:00',
        'closes_at' => $dayNumber === 6 ? '14:00' : '20:00',
        'label' => '',
        'is_closed' => 0,
        'sort_order' => $dayNumber,
    ];
}

function posted_string(string $key): string
{
    return trim((string) ($_POST[$key] ?? ''));
}

function optional_decimal(string $value, float $min, float $max, string $label, array &$errors): ?string
{
    if ($value === '') {
        return null;
    }

    if (!is_numeric($value) || (float) $value < $min || (float) $value > $max) {
        $errors[] = $label . ' is invalid.';
        return null;
    }

    return number_format((float) $value, 7, '.', '');
}

function optional_url(string $value, string $label, array &$errors): ?string
{
    if ($value === '') {
        return null;
    }

    if (!filter_var($value, FILTER_VALIDATE_URL)) {
        $errors[] = $label . ' must be a valid URL.';
        return null;
    }

    return $value;
}

try {
    $row = db()->query('SELECT * FROM business_contact_settings ORDER BY id ASC LIMIT 1')->fetch();
    if ($row) {
        foreach ($settings as $key => $value) {
            $settings[$key] = (string) ($row[$key] ?? $value);
        }
    }

    $stmt = db()->query('SELECT * FROM opening_hours ORDER BY sort_order ASC, day_of_week ASC');
    foreach ($stmt->fetchAll() as $row) {
        $day = (int) $row['day_of_week'];
        if (isset($hours[$day])) {
            $hours[$day] = [
                'day_of_week' => $day,
                'opens_at' => $row['opens_at'] ? substr((string) $row['opens_at'], 0, 5) : '',
                'closes_at' => $row['closes_at'] ? substr((string) $row['closes_at'], 0, 5) : '',
                'label' => (string) ($row['label'] ?? ''),
                'is_closed' => (int) $row['is_closed'],
                'sort_order' => (int) $row['sort_order'],
            ];
        }
    }

    $stmt = db()->query('SELECT * FROM social_links ORDER BY sort_order ASC');
    foreach ($stmt->fetchAll() as $row) {
        $platform = (string) $row['platform'];
        if (isset($socials[$platform])) {
            $socials[$platform] = [
                'label' => (string) $row['label'],
                'url' => (string) $row['url'],
                'is_visible' => (int) $row['is_visible'],
            ];
        }
    }
} catch (Throwable $exception) {
    $toast = ['type' => 'error', 'message' => 'Settings data is not available. Please confirm the latest schema has been imported.'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];

    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid request. Please reload the page and try again.';
    }

    $settings = [
        'clinic_name' => posted_string('clinic_name'),
        'address_line' => posted_string('address_line'),
        'city' => posted_string('city'),
        'district' => posted_string('district'),
        'country' => posted_string('country'),
        'primary_phone' => posted_string('primary_phone'),
        'secondary_phone' => posted_string('secondary_phone'),
        'whatsapp_number' => posted_string('whatsapp_number'),
        'email' => posted_string('email'),
        'latitude' => posted_string('latitude'),
        'longitude' => posted_string('longitude'),
        'map_zoom' => posted_string('map_zoom') ?: '14',
        'privacy_url' => posted_string('privacy_url'),
        'terms_url' => posted_string('terms_url'),
        'accessibility_url' => posted_string('accessibility_url'),
    ];

    foreach (['clinic_name', 'address_line', 'city', 'country', 'primary_phone', 'email'] as $required) {
        if ($settings[$required] === '') {
            $errors[] = str_replace('_', ' ', ucfirst($required)) . ' is required.';
        }
    }

    if ($settings['email'] !== '' && !filter_var($settings['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email address is invalid.';
    }

    $settings['latitude'] = optional_decimal($settings['latitude'], -90, 90, 'Latitude', $errors);
    $settings['longitude'] = optional_decimal($settings['longitude'], -180, 180, 'Longitude', $errors);
    $settings['map_zoom'] = (string) max(1, min(20, (int) $settings['map_zoom']));
    $settings['privacy_url'] = optional_url($settings['privacy_url'], 'Privacy URL', $errors);
    $settings['terms_url'] = optional_url($settings['terms_url'], 'Terms URL', $errors);
    $settings['accessibility_url'] = optional_url($settings['accessibility_url'], 'Accessibility URL', $errors);

    foreach ($days as $dayNumber => $dayName) {
        $closed = isset($_POST['hours_closed'][$dayNumber]) ? 1 : 0;
        $open = trim((string) ($_POST['hours_open'][$dayNumber] ?? ''));
        $close = trim((string) ($_POST['hours_close'][$dayNumber] ?? ''));

        if (!$closed && ($open === '' || $close === '')) {
            $errors[] = $dayName . ' opening and closing times are required unless closed.';
        }

        $hours[$dayNumber] = [
            'day_of_week' => $dayNumber,
            'opens_at' => $closed ? null : $open,
            'closes_at' => $closed ? null : $close,
            'label' => trim((string) ($_POST['hours_label'][$dayNumber] ?? '')),
            'is_closed' => $closed,
            'sort_order' => $dayNumber,
        ];
    }

    foreach ($socials as $platform => $social) {
        $url = trim((string) ($_POST['social_url'][$platform] ?? ''));
        if ($url !== '' && !filter_var($url, FILTER_VALIDATE_URL)) {
            $errors[] = ucfirst($platform) . ' URL must be valid.';
        }
        $socials[$platform] = [
            'label' => $social['label'],
            'url' => $url,
            'is_visible' => isset($_POST['social_visible'][$platform]) ? 1 : 0,
        ];
    }

    if ($errors === []) {
        try {
            $pdo = db();
            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                'INSERT INTO business_contact_settings
                 (id, clinic_name, address_line, city, district, country, primary_phone, secondary_phone, whatsapp_number, email, latitude, longitude, map_zoom, privacy_url, terms_url, accessibility_url)
                 VALUES
                 (1, :clinic_name, :address_line, :city, :district, :country, :primary_phone, :secondary_phone, :whatsapp_number, :email, :latitude, :longitude, :map_zoom, :privacy_url, :terms_url, :accessibility_url)
                 ON DUPLICATE KEY UPDATE
                 clinic_name = VALUES(clinic_name),
                 address_line = VALUES(address_line),
                 city = VALUES(city),
                 district = VALUES(district),
                 country = VALUES(country),
                 primary_phone = VALUES(primary_phone),
                 secondary_phone = VALUES(secondary_phone),
                 whatsapp_number = VALUES(whatsapp_number),
                 email = VALUES(email),
                 latitude = VALUES(latitude),
                 longitude = VALUES(longitude),
                 map_zoom = VALUES(map_zoom),
                 privacy_url = VALUES(privacy_url),
                 terms_url = VALUES(terms_url),
                 accessibility_url = VALUES(accessibility_url)'
            );
            $stmt->execute([
                ':clinic_name' => $settings['clinic_name'],
                ':address_line' => $settings['address_line'],
                ':city' => $settings['city'],
                ':district' => $settings['district'] ?: null,
                ':country' => $settings['country'],
                ':primary_phone' => $settings['primary_phone'],
                ':secondary_phone' => $settings['secondary_phone'] ?: null,
                ':whatsapp_number' => $settings['whatsapp_number'] ?: null,
                ':email' => $settings['email'],
                ':latitude' => $settings['latitude'],
                ':longitude' => $settings['longitude'],
                ':map_zoom' => (int) $settings['map_zoom'],
                ':privacy_url' => $settings['privacy_url'],
                ':terms_url' => $settings['terms_url'],
                ':accessibility_url' => $settings['accessibility_url'],
            ]);

            $stmt = $pdo->prepare(
                'INSERT INTO opening_hours (day_of_week, opens_at, closes_at, label, is_closed, sort_order)
                 VALUES (:day_of_week, :opens_at, :closes_at, :label, :is_closed, :sort_order)
                 ON DUPLICATE KEY UPDATE
                 opens_at = VALUES(opens_at),
                 closes_at = VALUES(closes_at),
                 label = VALUES(label),
                 is_closed = VALUES(is_closed),
                 sort_order = VALUES(sort_order)'
            );
            foreach ($hours as $hour) {
                $stmt->execute([
                    ':day_of_week' => $hour['day_of_week'],
                    ':opens_at' => $hour['opens_at'],
                    ':closes_at' => $hour['closes_at'],
                    ':label' => $hour['label'] ?: null,
                    ':is_closed' => $hour['is_closed'],
                    ':sort_order' => $hour['sort_order'],
                ]);
            }

            $stmt = $pdo->prepare(
                'INSERT INTO social_links (platform, label, url, is_visible, sort_order)
                 VALUES (:platform, :label, :url, :is_visible, :sort_order)
                 ON DUPLICATE KEY UPDATE
                 label = VALUES(label),
                 url = VALUES(url),
                 is_visible = VALUES(is_visible),
                 sort_order = VALUES(sort_order)'
            );
            $sort = 0;
            foreach ($socials as $platform => $social) {
                $stmt->execute([
                    ':platform' => $platform,
                    ':label' => $social['label'],
                    ':url' => $social['url'],
                    ':is_visible' => $social['is_visible'],
                    ':sort_order' => $sort++,
                ]);
            }

            $pdo->commit();
            $_SESSION['settings_toast'] = ['type' => 'success', 'message' => 'Global settings saved successfully.'];
            header('Location: settings.php');
            exit;
        } catch (Throwable $exception) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $toast = ['type' => 'error', 'message' => 'Settings could not be saved. Please check the database schema.'];
        }
    } else {
        $toast = ['type' => 'error', 'message' => implode(' ', $errors)];
    }
}

$token = csrf_token();

cms_header('Global Settings', $admin, 'settings');
?>
<div class="content">
  <?php if (is_array($toast)): ?>
    <div class="toast <?= e((string) $toast['type']) ?>" role="status"><?= e((string) $toast['message']) ?></div>
  <?php endif; ?>

  <section class="page-head">
    <div>
      <h1>Global Settings</h1>
      <p>Manage shared clinic identity, contact details, opening hours, map data, and legal links.</p>
    </div>
    <span class="tag">NMC Clinic</span>
  </section>

  <form method="post" action="settings.php">
    <input type="hidden" name="csrf_token" value="<?= e($token) ?>">

    <div class="grid settings-grid">
      <section class="card form-card">
        <h2><span class="material-symbols-outlined">info</span>Clinic Identity</h2>
        <div class="form-grid">
          <div class="field">
            <label for="clinic_name">Clinic Name</label>
            <input class="input" id="clinic_name" name="clinic_name" type="text" value="<?= e($settings['clinic_name']) ?>" required>
          </div>
          <div class="field">
            <label for="email">Email Address</label>
            <input class="input" id="email" name="email" type="email" value="<?= e($settings['email']) ?>" required>
          </div>
          <div class="field full">
            <div class="logo-preview">
              <img src="../uploads/logo.jpeg" alt="NMC Clinic logo">
              <div>
                <strong>Clinic Logo</strong>
                <p class="hint">Using the current site logo. Upload/replace will be handled from the Media Library when that module is built.</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="card form-card">
        <h2><span class="material-symbols-outlined">contact_support</span>Contact Details</h2>
        <div class="form-grid">
          <div class="field">
            <label for="primary_phone">Primary Phone</label>
            <input class="input" id="primary_phone" name="primary_phone" type="tel" value="<?= e($settings['primary_phone']) ?>" required>
          </div>
          <div class="field">
            <label for="secondary_phone">Secondary Phone</label>
            <input class="input" id="secondary_phone" name="secondary_phone" type="tel" value="<?= e($settings['secondary_phone']) ?>">
          </div>
          <div class="field full">
            <label for="whatsapp_number">WhatsApp Number</label>
            <input class="input" id="whatsapp_number" name="whatsapp_number" type="tel" value="<?= e($settings['whatsapp_number']) ?>">
          </div>
        </div>
      </section>

      <section class="card form-card settings-wide">
        <h2><span class="material-symbols-outlined">location_on</span>Physical Location</h2>
        <div class="form-grid">
          <div class="field full">
            <label for="address_line">Address Line</label>
            <textarea class="textarea" id="address_line" name="address_line" rows="3" required><?= e($settings['address_line']) ?></textarea>
          </div>
          <div class="field">
            <label for="city">City</label>
            <input class="input" id="city" name="city" type="text" value="<?= e($settings['city']) ?>" required>
          </div>
          <div class="field">
            <label for="district">District</label>
            <input class="input" id="district" name="district" type="text" value="<?= e($settings['district']) ?>">
          </div>
          <div class="field">
            <label for="country">Country</label>
            <input class="input" id="country" name="country" type="text" value="<?= e($settings['country']) ?>" required>
          </div>
          <div class="field">
            <label for="map_zoom">Map Zoom</label>
            <input class="input" id="map_zoom" name="map_zoom" type="number" min="1" max="20" value="<?= e($settings['map_zoom']) ?>">
          </div>
          <div class="field">
            <label for="latitude">Latitude</label>
            <input class="input" id="latitude" name="latitude" type="text" value="<?= e($settings['latitude']) ?>">
          </div>
          <div class="field">
            <label for="longitude">Longitude</label>
            <input class="input" id="longitude" name="longitude" type="text" value="<?= e($settings['longitude']) ?>">
          </div>
        </div>
      </section>

      <section class="card form-card settings-wide">
        <h2><span class="material-symbols-outlined">schedule</span>Opening Hours</h2>
        <div class="table-wrap">
          <table class="hours-table">
            <thead>
              <tr>
                <th>Day</th>
                <th>Closed</th>
                <th>Opening</th>
                <th>Closing</th>
                <th>Display Label</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($days as $dayNumber => $dayName): ?>
                <tr>
                  <td><?= e($dayName) ?></td>
                  <td class="check-cell"><input class="checkbox" type="checkbox" name="hours_closed[<?= $dayNumber ?>]" value="1" <?= $hours[$dayNumber]['is_closed'] ? 'checked' : '' ?>></td>
                  <td><input class="input" type="time" name="hours_open[<?= $dayNumber ?>]" value="<?= e((string) $hours[$dayNumber]['opens_at']) ?>"></td>
                  <td><input class="input" type="time" name="hours_close[<?= $dayNumber ?>]" value="<?= e((string) $hours[$dayNumber]['closes_at']) ?>"></td>
                  <td><input class="input" type="text" name="hours_label[<?= $dayNumber ?>]" value="<?= e((string) $hours[$dayNumber]['label']) ?>" placeholder="Optional"></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>

      <section class="card form-card">
        <h2><span class="material-symbols-outlined">link</span>Social Links</h2>
        <div class="form-grid">
          <?php foreach ($socials as $platform => $social): ?>
            <div class="field full">
              <label for="social_<?= e($platform) ?>"><?= e($social['label']) ?> URL</label>
              <input class="input" id="social_<?= e($platform) ?>" name="social_url[<?= e($platform) ?>]" type="url" value="<?= e($social['url']) ?>">
              <label class="hint"><input class="checkbox" type="checkbox" name="social_visible[<?= e($platform) ?>]" value="1" <?= $social['is_visible'] ? 'checked' : '' ?>> Visible</label>
            </div>
          <?php endforeach; ?>
        </div>
      </section>

      <section class="card form-card">
        <h2><span class="material-symbols-outlined">policy</span>Legal Links</h2>
        <div class="form-grid">
          <div class="field full">
            <label for="privacy_url">Privacy Policy URL</label>
            <input class="input" id="privacy_url" name="privacy_url" type="url" value="<?= e((string) $settings['privacy_url']) ?>">
          </div>
          <div class="field full">
            <label for="terms_url">Terms URL</label>
            <input class="input" id="terms_url" name="terms_url" type="url" value="<?= e((string) $settings['terms_url']) ?>">
          </div>
          <div class="field full">
            <label for="accessibility_url">Accessibility URL</label>
            <input class="input" id="accessibility_url" name="accessibility_url" type="url" value="<?= e((string) $settings['accessibility_url']) ?>">
          </div>
        </div>
      </section>
    </div>

    <div class="sticky-actions">
      <span class="hint">Changes are saved only when you press Save Settings.</span>
      <div style="display:flex;gap:12px;flex-wrap:wrap">
        <a class="secondary-btn" href="dashboard.php">Cancel</a>
        <button class="primary-btn" type="submit">Save Settings</button>
      </div>
    </div>
  </form>
</div>
<?php cms_footer(); ?>
