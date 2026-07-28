<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/contact_helpers.php';

$admin = require_admin();
$errors = [];
$toast = $_SESSION['contact_toast'] ?? null;
unset($_SESSION['contact_toast']);

try {
    $pageOptions = contact_page_options();
    $settings = contact_page_settings();
    $subjects = contact_subjects();
} catch (Throwable $exception) {
    $pageOptions = [];
    $settings = contact_page_default();
    $subjects = [];
    $toast = ['type' => 'error', 'message' => 'Contact data is not available. Please confirm the latest schema has been imported.'];
}

if ($subjects === []) {
    $subjects = [
        ['label' => '', 'value' => '', 'recipient_email' => '', 'is_active' => 1],
        ['label' => '', 'value' => '', 'recipient_email' => '', 'is_active' => 1],
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings = contact_settings_from_post();
    $subjects = contact_subjects_from_post();

    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid request. Please reload the page and try again.';
    }

    try {
        $errors = array_merge($errors, contact_validate($settings, $subjects));
        if ($errors === []) {
            contact_save($settings, $subjects);
            $_SESSION['contact_toast'] = ['type' => 'success', 'message' => 'Contact settings saved successfully.'];
            header('Location: contact.php');
            exit;
        }
    } catch (Throwable $exception) {
        $errors[] = 'Unable to save contact settings. Please confirm the database schema is available and try again.';
    }
}

cms_header('Contact', $admin, 'contact');
?>
<section class="content">
  <div class="page-head">
    <div>
      <h1>Contact</h1>
      <p>Manage contact-page emergency content and contact form subject routing.</p>
    </div>
    <a class="secondary-btn" href="submissions.php">View Submissions</a>
  </div>

  <?php if ($toast): ?>
    <div class="toast <?= e((string) $toast['type']) ?>" role="status"><?= e((string) $toast['message']) ?></div>
  <?php endif; ?>
  <?php if ($errors !== []): ?>
    <div class="toast error" role="alert"><?= e(implode(' ', $errors)) ?></div>
  <?php endif; ?>

  <form method="post" action="contact.php">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <div class="grid settings-grid">
      <section class="card form-card">
        <h2><span class="material-symbols-outlined">contact_mail</span>Contact Page Settings</h2>
        <div class="form-grid">
          <div class="field full">
            <label for="page_id">Contact Page</label>
            <select class="select" id="page_id" name="page_id" required>
              <option value="">Select contact page</option>
              <?php foreach ($pageOptions as $page): ?>
                <option value="<?= e((string) $page['id']) ?>" <?= (string) $settings['page_id'] === (string) $page['id'] ? 'selected' : '' ?>>
                  <?= e((string) $page['slug']) ?> (<?= e((string) $page['status']) ?>)
                </option>
              <?php endforeach; ?>
            </select>
            <p class="hint">Uses the existing `pages` table. No raw page ID entry.</p>
          </div>
          <div class="field full">
            <label for="response_time_note">Response Time Note</label>
            <input class="input" id="response_time_note" name="response_time_note" type="text" maxlength="255" value="<?= e((string) $settings['response_time_note']) ?>">
          </div>
          <div class="field">
            <label for="emergency_title">Emergency Title</label>
            <input class="input" id="emergency_title" name="emergency_title" type="text" maxlength="150" value="<?= e((string) $settings['emergency_title']) ?>">
          </div>
          <div class="field">
            <label for="emergency_phone_label">Emergency Phone Label</label>
            <input class="input" id="emergency_phone_label" name="emergency_phone_label" type="text" maxlength="100" value="<?= e((string) $settings['emergency_phone_label']) ?>">
          </div>
          <div class="field">
            <label for="emergency_phone_number">Emergency Phone Number</label>
            <input class="input" id="emergency_phone_number" name="emergency_phone_number" type="text" maxlength="30" value="<?= e((string) $settings['emergency_phone_number']) ?>">
          </div>
          <div class="field">
            <label>Visibility</label>
            <label class="hint"><input class="checkbox" type="checkbox" name="is_visible" value="1" <?= (int) $settings['is_visible'] === 1 ? 'checked' : '' ?>> Show contact emergency block</label>
          </div>
          <div class="field full">
            <label for="emergency_description">Emergency Description</label>
            <textarea class="textarea" id="emergency_description" name="emergency_description" rows="5"><?= e((string) $settings['emergency_description']) ?></textarea>
          </div>
        </div>
      </section>

      <section class="card form-card">
        <h2><span class="material-symbols-outlined">route</span>Contact Subjects</h2>
        <div class="grid" id="subjects">
          <?php foreach ($subjects as $index => $subject): ?>
            <div class="form-grid subject-row">
              <input type="hidden" name="subject_id[<?= $index ?>]" value="<?= e((string) ($subject['id'] ?? '')) ?>">
              <div class="field">
                <label for="subject_label_<?= $index ?>">Label</label>
                <input class="input" id="subject_label_<?= $index ?>" name="subject_label[<?= $index ?>]" type="text" maxlength="100" value="<?= e((string) $subject['label']) ?>">
              </div>
              <div class="field">
                <label for="subject_value_<?= $index ?>">Value</label>
                <input class="input" id="subject_value_<?= $index ?>" name="subject_value[<?= $index ?>]" type="text" maxlength="100" value="<?= e((string) $subject['value']) ?>">
              </div>
              <div class="field full">
                <label for="subject_email_<?= $index ?>">Recipient Email</label>
                <input class="input" id="subject_email_<?= $index ?>" name="subject_email[<?= $index ?>]" type="email" maxlength="191" value="<?= e((string) $subject['recipient_email']) ?>">
              </div>
              <div class="field full">
                <label class="hint"><input class="checkbox" type="checkbox" name="subject_active[<?= $index ?>]" value="1" <?= (int) $subject['is_active'] === 1 ? 'checked' : '' ?>> Active subject</label>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <button class="secondary-btn" type="button" id="addSubject">Add Subject</button>
      </section>
    </div>

    <div class="sticky-actions">
      <span class="hint">Changes are saved only when you press Save Contact Settings.</span>
      <button class="primary-btn" style="width:auto" type="submit">Save Contact Settings</button>
    </div>
  </form>
</section>
<script>
(function () {
  var subjects = document.getElementById('subjects');
  var addSubject = document.getElementById('addSubject');
  if (!subjects || !addSubject) return;
  addSubject.addEventListener('click', function () {
    var index = subjects.querySelectorAll('.subject-row').length;
    var row = document.createElement('div');
    row.className = 'form-grid subject-row';
    row.innerHTML =
      '<input type="hidden" name="subject_id[' + index + ']" value="">' +
      '<div class="field"><label for="subject_label_' + index + '">Label</label><input class="input" id="subject_label_' + index + '" name="subject_label[' + index + ']" type="text" maxlength="100"></div>' +
      '<div class="field"><label for="subject_value_' + index + '">Value</label><input class="input" id="subject_value_' + index + '" name="subject_value[' + index + ']" type="text" maxlength="100"></div>' +
      '<div class="field full"><label for="subject_email_' + index + '">Recipient Email</label><input class="input" id="subject_email_' + index + '" name="subject_email[' + index + ']" type="email" maxlength="191"></div>' +
      '<div class="field full"><label class="hint"><input class="checkbox" type="checkbox" name="subject_active[' + index + ']" value="1" checked> Active subject</label></div>';
    subjects.appendChild(row);
  });
})();
</script>
<?php cms_footer(); ?>
