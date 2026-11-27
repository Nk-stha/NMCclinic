<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/seo_form.php';

$admin = require_admin();
$toast = $_SESSION['seo_toast'] ?? null;
unset($_SESSION['seo_toast']);

$auditIssues = [
    'missing_desc' => 0,
    'missing_og' => 0,
    'noindex' => 0,
    'long_title' => 0,
];

try {
    $seoRows = seo_list();

    // Calculate audit metrics
    foreach ($seoRows as $row) {
        if (empty(trim((string) $row['meta_description']))) {
            $auditIssues['missing_desc']++;
        }
        if (empty($row['og_image_id'])) {
            $auditIssues['missing_og']++;
        }
        if ((int) $row['robots_index'] === 0) {
            $auditIssues['noindex']++;
        }
        if (mb_strlen((string) $row['meta_title']) > 65) {
            $auditIssues['long_title']++;
        }
    }

    $totalRecords = count($seoRows);
    $healthScore = $totalRecords > 0
        ? max(0, min(100, (int) round((($totalRecords * 4 - array_sum($auditIssues)) / ($totalRecords * 4)) * 100)))
        : 100;
} catch (Throwable $exception) {
    $seoRows = [];
    $totalRecords = 0;
    $healthScore = 0;
    $toast = ['type' => 'error', 'message' => 'SEO audit data is not available. Please confirm the database schema is active.'];
}

$typeFilter = trim((string) ($_GET['type'] ?? 'all'));
if ($typeFilter !== 'all' && $seoRows !== []) {
    $seoRows = array_filter($seoRows, static function (array $row) use ($typeFilter): bool {
        return match ($typeFilter) {
            'page' => !empty($row['page_id']),
            'blog_post' => !empty($row['blog_post_id']),
            'doctor' => !empty($row['doctor_id']),
            default => true,
        };
    });
}

cms_header('Central SEO Dashboard', $admin, 'seo');
?>
<section class="content">
  <div class="page-head">
    <div>
      <h1>Central SEO & Health Dashboard</h1>
      <p>Audit search engine optimization health, social sharing metadata, canonical URLs, and indexing directives.</p>
    </div>
    <div style="display:flex;gap:12px;flex-wrap:wrap">
      <a class="secondary-btn" style="width:auto;padding-left:16px;padding-right:16px" href="sitemap.php">
        <span class="material-symbols-outlined">map</span>Sitemap
      </a>
      <a class="primary-btn" style="width:auto;padding-left:18px;padding-right:18px" href="seo-create.php">
        <span class="material-symbols-outlined">add</span>Add SEO Target
      </a>
    </div>
  </div>

  <?php if ($toast): ?>
    <div class="toast <?= e((string) $toast['type']) ?>" role="status"><?= e((string) $toast['message']) ?></div>
  <?php endif; ?>

  <!-- SEO Health Overview Cards -->
  <div class="grid stats" style="margin-bottom:28px">
    <div class="card stat">
      <div class="stat-top">
        <div class="icon" style="background:#e8f5e9;color:#2e7d32"><span class="material-symbols-outlined">verified</span></div>
        <span class="tag">Overall</span>
      </div>
      <div class="stat-label">SEO Health Score</div>
      <div class="stat-value" style="color:<?= $healthScore >= 80 ? '#2e7d32' : ($healthScore >= 50 ? '#f57c00' : '#d32f2f') ?>">
        <?= $healthScore ?>%
      </div>
    </div>

    <div class="card stat">
      <div class="stat-top">
        <div class="icon" style="background:#e3f2fd;color:#1976d2"><span class="material-symbols-outlined">inventory_2</span></div>
        <span class="tag">Active</span>
      </div>
      <div class="stat-label">Total SEO Records</div>
      <div class="stat-value"><?= $totalRecords ?></div>
    </div>

    <div class="card stat">
      <div class="stat-top">
        <div class="icon" style="background:#fff3e0;color:#ef6c00"><span class="material-symbols-outlined">broken_image</span></div>
        <span class="tag">Social</span>
      </div>
      <div class="stat-label">Missing OG Images</div>
      <div class="stat-value" style="color:<?= $auditIssues['missing_og'] > 0 ? '#ef6c00' : 'inherit' ?>">
        <?= $auditIssues['missing_og'] ?>
      </div>
    </div>

    <div class="card stat">
      <div class="stat-top">
        <div class="icon" style="background:#ffebee;color:#c62828"><span class="material-symbols-outlined">visibility_off</span></div>
        <span class="tag">Indexing</span>
      </div>
      <div class="stat-label">Noindex Directives</div>
      <div class="stat-value" style="color:<?= $auditIssues['noindex'] > 0 ? '#c62828' : 'inherit' ?>">
        <?= $auditIssues['noindex'] ?>
      </div>
    </div>
  </div>

  <section class="card">
    <div class="panel-head" style="flex-wrap:wrap;gap:12px">
      <div style="display:flex;align-items:center;gap:12px">
        <h2>Content Inventory & SEO Status</h2>
        <span class="tag"><?= count($seoRows) ?> showing</span>
      </div>
      <!-- Type Filter Tabs -->
      <div style="display:flex;gap:8px;flex-wrap:wrap">
        <a class="secondary-btn" style="padding:6px 14px;font-size:13px;<?= $typeFilter === 'all' ? 'background:var(--primary);color:#fff;' : '' ?>" href="seo.php?type=all">All</a>
        <a class="secondary-btn" style="padding:6px 14px;font-size:13px;<?= $typeFilter === 'page' ? 'background:var(--primary);color:#fff;' : '' ?>" href="seo.php?type=page">Pages</a>
        <a class="secondary-btn" style="padding:6px 14px;font-size:13px;<?= $typeFilter === 'blog_post' ? 'background:var(--primary);color:#fff;' : '' ?>" href="seo.php?type=blog_post">Blog Posts</a>
        <a class="secondary-btn" style="padding:6px 14px;font-size:13px;<?= $typeFilter === 'doctor' ? 'background:var(--primary);color:#fff;' : '' ?>" href="seo.php?type=doctor">Doctors</a>
      </div>
    </div>

    <?php if ($seoRows === []): ?>
      <div class="empty">No SEO records found matching this filter.</div>
    <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Target Content</th>
              <th>Meta Title & Snippet</th>
              <th>Social OG Image</th>
              <th>Indexing</th>
              <th>Health Check</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($seoRows as $row): ?>
              <?php
              $hasOg = !empty($row['og_image_id']);
              $hasDesc = !empty(trim((string) $row['meta_description']));
              $titleLen = mb_strlen((string) $row['meta_title']);
              $isNoindex = (int) $row['robots_index'] === 0;

              // Edit URL helper
              $editUrl = 'seo-edit.php?id=' . e((string) $row['id']);
              if (!empty($row['blog_post_id'])) {
                  $contentEditUrl = 'blog-edit.php?id=' . e((string) $row['blog_post_id']);
              } elseif (!empty($row['doctor_id'])) {
                  $contentEditUrl = 'doctor-edit.php?id=' . e((string) $row['doctor_id']);
              } else {
                  $contentEditUrl = null;
              }
              ?>
              <tr>
                <td>
                  <strong><?= e(seo_target_label($row)) ?></strong>
                  <div class="hint" style="font-size:12px;margin-top:2px">
                    Updated <?= substr((string) $row['updated_at'], 0, 10) ?>
                  </div>
                </td>
                <td style="max-width:320px">
                  <div style="font-weight:700;color:var(--primary);word-break:break-word"><?= e((string) $row['meta_title']) ?></div>
                  <p class="hint" style="margin:4px 0 0;font-size:13px;line-height:1.4"><?= e(cms_excerpt((string) $row['meta_description'], 90)) ?></p>
                  <?php if (!empty($row['canonical_url'])): ?>
                    <span class="hint" style="font-size:11px;word-break:break-all;color:var(--outline);display:block;margin-top:2px">
                      🔗 <?= e((string) $row['canonical_url']) ?>
                    </span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if (!empty($row['og_image_path'])): ?>
                    <div style="width:48px;height:48px;border-radius:6px;overflow:hidden;border:1px solid var(--border);display:inline-block">
                      <img src="../<?= e(ltrim((string) $row['og_image_path'], '/')) ?>" alt="OG" style="width:100%;height:100%;object-fit:cover">
                    </div>
                  <?php else: ?>
                    <span class="tag" style="background:#fff3e0;color:#ef6c00">No Image</span>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="status <?= $isNoindex ? 'danger' : 'active' ?>" style="font-size:12px">
                    <?= $isNoindex ? 'noindex' : 'index, follow' ?>
                  </span>
                </td>
                <td>
                  <?php if ($titleLen > 65): ?>
                    <span class="tag" style="background:#fff3e0;color:#e65100;display:inline-block;margin-bottom:2px">Long Title (<?= $titleLen ?>c)</span>
                  <?php endif; ?>
                  <?php if (!$hasDesc): ?>
                    <span class="tag" style="background:#ffebee;color:#c62828;display:inline-block;margin-bottom:2px">Missing Desc</span>
                  <?php endif; ?>
                  <?php if ($titleLen <= 65 && $hasDesc && $hasOg && !$isNoindex): ?>
                    <span class="tag" style="background:#e8f5e9;color:#2e7d32">Optimized</span>
                  <?php endif; ?>
                </td>
                <td>
                  <div style="display:flex;gap:6px;flex-wrap:wrap">
                    <a class="secondary-btn" style="padding:6px 10px;font-size:12px" href="<?= $editUrl ?>">Edit SEO</a>
                    <?php if ($contentEditUrl): ?>
                      <a class="secondary-btn" style="padding:6px 10px;font-size:12px;background:var(--surface-low)" href="<?= $contentEditUrl ?>">Edit Content</a>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </section>
</section>
<?php cms_footer(); ?>
