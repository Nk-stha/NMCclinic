<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';

$admin = require_admin();

$stats = [
    'doctors' => 0,
    'specialties' => 0,
    'blog_posts' => 0,
    'appointments' => 0,
    'inquiries' => 0,
    'subscribers' => 0,
];
$appointments = [];
$inquiries = [];
$dashboardError = '';

try {
    $stats = [
        'doctors' => cms_count('doctors', "status = 'published'"),
        'specialties' => cms_count('specialties', "status = 'published'"),
        'blog_posts' => cms_count('blog_posts', "status = 'published'"),
        'appointments' => cms_count('appointment_requests', "status = 'new'"),
        'inquiries' => cms_count('contact_inquiries', "status = 'new'"),
        'subscribers' => cms_count('newsletter_subscribers', "status = 'active'"),
    ];
    $appointments = cms_recent_appointments();
    $inquiries = cms_recent_inquiries();
} catch (Throwable $exception) {
    $dashboardError = 'Dashboard data is not available. Please confirm the database schema has been imported.';
}

cms_header('Dashboard', $admin, 'dashboard');
?>
<div class="content">
  <section class="page-head">
    <div>
      <h1>Welcome back, <?= e((string) $admin['full_name']) ?>.</h1>
      <p>Overview of NMC Clinic CMS content, appointment requests, and contact inquiries.</p>
    </div>
  </section>

  <?php if ($dashboardError !== ''): ?>
    <div class="card empty"><?= e($dashboardError) ?></div>
  <?php endif; ?>

  <section class="grid stats" aria-label="CMS overview">
    <article class="card stat">
      <div class="stat-top"><div class="icon"><span class="material-symbols-outlined">medical_services</span></div><span class="tag">Published</span></div>
      <div class="stat-label">Total Doctors</div>
      <div class="stat-value"><?= e((string) $stats['doctors']) ?></div>
    </article>
    <article class="card stat">
      <div class="stat-top"><div class="icon"><span class="material-symbols-outlined">category</span></div><span class="tag">Published</span></div>
      <div class="stat-label">Total Specialties</div>
      <div class="stat-value"><?= e((string) $stats['specialties']) ?></div>
    </article>
    <article class="card stat">
      <div class="stat-top"><div class="icon"><span class="material-symbols-outlined">edit_note</span></div><span class="tag">Published</span></div>
      <div class="stat-label">Blog Posts</div>
      <div class="stat-value"><?= e((string) $stats['blog_posts']) ?></div>
    </article>
    <article class="card stat">
      <div class="stat-top"><div class="icon"><span class="material-symbols-outlined">event_available</span></div><span class="tag">New</span></div>
      <div class="stat-label">Appointment Requests</div>
      <div class="stat-value"><?= e((string) $stats['appointments']) ?></div>
    </article>
  </section>

  <section class="grid panel-grid">
    <article class="card">
      <div class="panel-head">
        <h2>Recent Appointment Requests</h2>
        <a class="panel-link" href="submissions.php">View All</a>
      </div>
      <?php if ($appointments === []): ?>
        <div class="empty">No appointment requests found.</div>
      <?php else: ?>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Patient Name</th>
                <th>Specialty</th>
                <th>Preferred Date</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($appointments as $appointment): ?>
                <tr>
                  <td><?= e((string) $appointment['full_name']) ?></td>
                  <td><?= e((string) ($appointment['specialty'] ?? 'Not selected')) ?></td>
                  <td><?= e((string) ($appointment['preferred_date'] ?? 'Any date')) ?><?= $appointment['preferred_time_slot'] ? ' · ' . e((string) $appointment['preferred_time_slot']) : '' ?></td>
                  <td><span class="status <?= e(cms_status_class((string) $appointment['status'])) ?>"><?= e((string) $appointment['status']) ?></span></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </article>

    <aside class="card">
      <div class="panel-head">
        <h2>New Inquiries</h2>
        <span class="tag"><?= e((string) $stats['inquiries']) ?> New</span>
      </div>
      <?php if ($inquiries === []): ?>
        <div class="empty">No contact inquiries found.</div>
      <?php else: ?>
        <div class="inquiries">
          <?php foreach ($inquiries as $inquiry): ?>
            <article class="inquiry">
              <div class="initials"><?= e(cms_initials((string) $inquiry['full_name'])) ?></div>
              <div>
                <h3><?= e((string) $inquiry['full_name']) ?></h3>
                <p><strong><?= e((string) ($inquiry['subject'] ?? 'General inquiry')) ?></strong></p>
                <p><?= e(cms_excerpt((string) $inquiry['message'])) ?></p>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </aside>
  </section>

  <section>
    <div class="actions-title">
      <h2>Quick Actions</h2>
      <div class="rule"></div>
    </div>
    <div class="grid actions">
      <a class="card action" href="settings.php">
        <div class="icon"><span class="material-symbols-outlined">settings</span></div>
        <div><h3>Global Settings</h3><p>Clinic identity and contact details</p></div>
      </a>
      <a class="card action" href="specialties.php">
        <div class="icon"><span class="material-symbols-outlined">grid_view</span></div>
        <div><h3>Specialties</h3><p>Manage service records</p></div>
      </a>
      <a class="card action" href="doctors.php">
        <div class="icon"><span class="material-symbols-outlined">person_add</span></div>
        <div><h3>Doctors</h3><p>Manage doctor listings</p></div>
      </a>
      <a class="card action" href="blog.php">
        <div class="icon"><span class="material-symbols-outlined">edit_document</span></div>
        <div><h3>Blog Posts</h3><p>Manage health articles</p></div>
      </a>
    </div>
  </section>
</div>
<?php cms_footer(); ?>
