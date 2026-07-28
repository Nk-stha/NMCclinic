<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/contact_helpers.php';

$admin = require_admin();
$toast = $_SESSION['submission_toast'] ?? null;
unset($_SESSION['submission_toast']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = trim((string) ($_POST['type'] ?? ''));
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
    $status = trim((string) ($_POST['status'] ?? ''));

    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $_SESSION['submission_toast'] = ['type' => 'error', 'message' => 'Invalid request. Please reload the page and try again.'];
    } elseif (!$id || !update_submission_status($type, (int) $id, $status)) {
        $_SESSION['submission_toast'] = ['type' => 'error', 'message' => 'Submission status could not be updated.'];
    } else {
        $_SESSION['submission_toast'] = ['type' => 'success', 'message' => 'Submission status updated.'];
    }

    header('Location: submissions.php');
    exit;
}

try {
    $inquiries = contact_inquiries_list();
    $appointments = appointment_requests_list();
} catch (Throwable $exception) {
    $inquiries = [];
    $appointments = [];
    $toast = ['type' => 'error', 'message' => 'Submissions are not available. Please confirm the latest schema has been imported.'];
}

cms_header('Submissions', $admin, 'submissions');
?>
<section class="content">
  <div class="page-head">
    <div>
      <h1>Submissions</h1>
      <p>Review contact inquiries and appointment requests without editing the submitted patient message.</p>
    </div>
    <a class="secondary-btn" href="contact.php">Contact Settings</a>
  </div>

  <?php if ($toast): ?>
    <div class="toast <?= e((string) $toast['type']) ?>" role="status"><?= e((string) $toast['message']) ?></div>
  <?php endif; ?>

  <div class="grid">
    <section class="card">
      <div class="panel-head">
        <h2>Contact Inquiries</h2>
        <span class="tag"><?= count($inquiries) ?> total</span>
      </div>
      <?php if ($inquiries === []): ?>
        <div class="empty">No contact inquiries found.</div>
      <?php else: ?>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Name</th>
                <th>Contact</th>
                <th>Subject</th>
                <th>Message</th>
                <th>Status</th>
                <th>Received</th>
                <th>Update</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($inquiries as $row): ?>
                <tr>
                  <td><?= e((string) $row['full_name']) ?></td>
                  <td>
                    <?= e((string) $row['phone']) ?>
                    <?php if (!empty($row['email'])): ?><p class="hint"><?= e((string) $row['email']) ?></p><?php endif; ?>
                  </td>
                  <td><?= e((string) ($row['subject_label'] ?? 'Not selected')) ?></td>
                  <td><?= e(cms_excerpt((string) $row['message'], 100)) ?></td>
                  <td><span class="status <?= e(cms_status_class((string) $row['status'])) ?>"><?= e((string) $row['status']) ?></span></td>
                  <td><?= e((string) $row['created_at']) ?></td>
                  <td>
                    <form method="post" action="submissions.php" style="display:flex;gap:8px;align-items:center">
                      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                      <input type="hidden" name="type" value="contact">
                      <input type="hidden" name="id" value="<?= e((string) $row['id']) ?>">
                      <select class="select" name="status" aria-label="Contact inquiry status">
                        <?php foreach (['new', 'contacted', 'closed', 'spam'] as $status): ?>
                          <option value="<?= e($status) ?>" <?= $row['status'] === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option>
                        <?php endforeach; ?>
                      </select>
                      <button class="secondary-btn" style="padding:8px 12px" type="submit">Save</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </section>

    <section class="card">
      <div class="panel-head">
        <h2>Appointment Requests</h2>
        <span class="tag"><?= count($appointments) ?> total</span>
      </div>
      <?php if ($appointments === []): ?>
        <div class="empty">No appointment requests found.</div>
      <?php else: ?>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Name</th>
                <th>Phone</th>
                <th>Specialty</th>
                <th>Preferred</th>
                <th>Status</th>
                <th>Received</th>
                <th>Update</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($appointments as $row): ?>
                <tr>
                  <td>
                    <?= e((string) $row['full_name']) ?>
                    <?php if (!empty($row['message'])): ?><p class="hint"><?= e(cms_excerpt((string) $row['message'], 80)) ?></p><?php endif; ?>
                  </td>
                  <td><?= e((string) $row['phone']) ?></td>
                  <td><?= e((string) ($row['specialty_title'] ?? 'Not selected')) ?></td>
                  <td><?= e(trim((string) ($row['preferred_date'] ?? '') . ' ' . (string) ($row['preferred_time_slot'] ?? ''))) ?></td>
                  <td><span class="status <?= e(cms_status_class((string) $row['status'])) ?>"><?= e((string) $row['status']) ?></span></td>
                  <td><?= e((string) $row['created_at']) ?></td>
                  <td>
                    <form method="post" action="submissions.php" style="display:flex;gap:8px;align-items:center">
                      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                      <input type="hidden" name="type" value="appointment">
                      <input type="hidden" name="id" value="<?= e((string) $row['id']) ?>">
                      <select class="select" name="status" aria-label="Appointment request status">
                        <?php foreach (['new', 'contacted', 'scheduled', 'closed', 'spam'] as $status): ?>
                          <option value="<?= e($status) ?>" <?= $row['status'] === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option>
                        <?php endforeach; ?>
                      </select>
                      <button class="secondary-btn" style="padding:8px 12px" type="submit">Save</button>
                    </form>
                  </td>
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
