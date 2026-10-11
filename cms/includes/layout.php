<?php
declare(strict_types=1);

function cms_count(string $table, ?string $where = null): int
{
    $allowedTables = [
        'specialties',
        'doctors',
        'blog_posts',
        'appointment_requests',
        'contact_inquiries',
        'newsletter_subscribers',
    ];
    $allowedWhere = [
        null,
        "status = 'published'",
        "status = 'new'",
        "status = 'active'",
    ];

    if (!in_array($table, $allowedTables, true) || !in_array($where, $allowedWhere, true)) {
        throw new InvalidArgumentException('Unsupported dashboard count query.');
    }

    $sql = 'SELECT COUNT(*) FROM ' . $table;
    if ($where !== null) {
        $sql .= ' WHERE ' . $where;
    }

    return (int) db()->query($sql)->fetchColumn();
}

function cms_recent_appointments(int $limit = 5): array
{
    $stmt = db()->prepare(
        'SELECT ar.full_name, ar.phone, ar.preferred_date, ar.preferred_time_slot, ar.status, s.title AS specialty
         FROM appointment_requests ar
         LEFT JOIN specialties s ON s.id = ar.specialty_id
         ORDER BY ar.created_at DESC
         LIMIT :limit'
    );
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

function cms_recent_inquiries(int $limit = 4): array
{
    $stmt = db()->prepare(
        'SELECT ci.full_name, ci.message, ci.status, ci.created_at, cs.label AS subject
         FROM contact_inquiries ci
         LEFT JOIN contact_subjects cs ON cs.id = ci.subject_id
         ORDER BY ci.created_at DESC
         LIMIT :limit'
    );
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

function cms_header(string $title, array $admin, string $active = 'dashboard'): void
{
    $nav = [
        'dashboard' => ['Dashboard', 'dashboard.php', 'dashboard'],
        'settings' => ['Global Settings', 'settings.php', 'settings'],
        'specialties' => ['Specialties', 'specialties.php', 'category'],
        'doctors' => ['Doctors', 'doctors.php', 'medical_services'],
        'blog' => ['Blog', 'blog.php', 'edit_note'],
        'blog_categories' => ['Blog Categories', 'blog-categories.php', 'category'],
        'contact' => ['Contact', 'contact.php', 'contact_mail'],
        'branches' => ['Branches', 'branches.php', 'location_on'],
        'submissions' => ['Submissions', 'submissions.php', 'inbox'],
        'media' => ['Media', 'media.php', 'image'],
        'seo' => ['SEO Metadata', 'seo.php', 'travel_explore'],
        'sitemap' => ['Sitemap', 'sitemap.php', 'account_tree'],
    ];
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> | NMC Clinic CMS</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Public+Sans:wght@400;500;600;700&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
<style>
:root{--bg:#f8fafa;--surface:#fff;--surface-low:#f2f4f4;--surface-mid:#e6e8e9;--border:#bec9c8;--outline:#6f7979;--text:#191c1d;--muted:#3f4948;--primary:#005051;--primary2:#136a6a;--yellow:#fdbc13;--danger:#ba1a1a;--danger-bg:#ffdad6;--success:#287772;--shadow:0 2px 8px rgba(19,106,106,.05);--shadow-hover:0 4px 12px rgba(19,106,106,.12)}
*{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--text);font-family:"Public Sans",Arial,sans-serif}.material-symbols-outlined{font-variation-settings:'FILL' 0,'wght' 400,'GRAD' 0,'opsz' 24}
a{text-decoration:none;color:inherit}.cms-shell{display:flex;min-height:100vh}.sidebar{position:fixed;inset:0 auto 0 0;width:264px;display:flex;flex-direction:column;border-right:1px solid var(--border);background:var(--surface-low);padding:28px 16px 20px;z-index:20}.brand{display:flex;align-items:center;gap:12px;padding:0 12px 28px}.brand img{width:42px;height:42px;border-radius:10px;object-fit:contain;background:var(--surface);border:1px solid var(--border);padding:4px}.brand-title{font-size:24px;line-height:1.08;font-weight:800;color:var(--primary)}.brand-sub{font:700 12px/1.3 "Inter",Arial,sans-serif;color:var(--outline);text-transform:uppercase;letter-spacing:.04em}.nav{flex:1;display:grid;align-content:start;gap:4px;overflow:auto}.nav a,.side-footer a{display:flex;align-items:center;gap:12px;border-radius:8px;padding:10px 12px;color:var(--muted);font:700 14px "Inter",Arial,sans-serif}.nav a:hover,.side-footer a:hover{background:var(--surface-mid)}.nav a.active{background:var(--yellow);color:#261900}.side-action{margin:20px 12px}.primary-btn{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;border:0;border-radius:8px;background:var(--primary);color:#fff;padding:13px 14px;font:800 14px "Inter",Arial,sans-serif;cursor:pointer}.side-footer{border-top:1px solid var(--border);padding-top:16px}.logout{color:var(--danger)!important}.main{margin-left:264px;min-width:0;flex:1;display:flex;flex-direction:column;min-height:100vh}.topbar{height:68px;position:sticky;top:0;z-index:10;display:flex;align-items:center;justify-content:space-between;gap:20px;background:var(--surface);border-bottom:1px solid rgba(190,201,200,.55);padding:0 40px}.search{position:relative;width:min(100%,460px)}.search span{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--outline)}.search input{width:100%;border:0;border-radius:999px;background:var(--surface-low);padding:12px 16px 12px 44px;font:500 15px "Public Sans",Arial,sans-serif;outline:none}.admin{display:flex;align-items:center;gap:12px;text-align:right}.admin-name{font:800 14px "Inter",Arial,sans-serif}.admin-role{font:700 12px "Inter",Arial,sans-serif;color:var(--outline)}.avatar{width:40px;height:40px;border-radius:50%;display:grid;place-items:center;background:var(--primary);color:#fff;font:800 14px "Inter",Arial,sans-serif}.content{flex:1;padding:36px 40px 48px}.page-head{display:flex;align-items:flex-end;justify-content:space-between;gap:20px;margin-bottom:28px}.page-head h1{margin:0 0 6px;color:var(--primary);font-size:32px;line-height:1.25}.page-head p{margin:0;color:var(--outline);font-size:17px;line-height:1.5}.grid{display:grid;gap:24px}.stats{grid-template-columns:repeat(4,minmax(0,1fr))}.card{background:var(--surface);border:1px solid rgba(190,201,200,.6);border-radius:12px;box-shadow:var(--shadow)}.stat{padding:22px}.stat:hover,.action:hover{box-shadow:var(--shadow-hover)}.stat-top{display:flex;align-items:flex-start;justify-content:space-between}.icon{width:48px;height:48px;border-radius:10px;display:grid;place-items:center;background:#e8f6f5;color:var(--primary)}.tag{border-radius:999px;background:var(--surface-low);color:var(--muted);padding:4px 8px;font:800 11px "Inter",Arial,sans-serif;text-transform:uppercase}.stat-label{margin:18px 0 6px;color:var(--outline);font:800 12px "Inter",Arial,sans-serif;text-transform:uppercase;letter-spacing:.06em}.stat-value{font-size:42px;line-height:1;font-weight:800}.panel-grid{grid-template-columns:2fr 1fr;margin-top:24px}.panel-head{display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--border);padding:18px 24px}.panel-head h2{margin:0;color:var(--primary);font-size:22px}.panel-link{border:0;background:transparent;color:var(--primary);font:800 14px "Inter",Arial,sans-serif}.table-wrap{overflow-x:auto}table{width:100%;border-collapse:collapse;text-align:left}th{background:var(--surface-low);color:var(--outline);font:800 12px "Inter",Arial,sans-serif;text-transform:uppercase;letter-spacing:.06em}th,td{padding:15px 24px;border-bottom:1px solid rgba(190,201,200,.35)}td{font-size:15px}.status{display:inline-flex;border-radius:999px;padding:5px 10px;font:800 12px "Inter",Arial,sans-serif;text-transform:capitalize}.status.new,.status.scheduled,.status.contacted{background:#e8f6f5;color:var(--success)}.status.closed{background:var(--surface-low);color:var(--muted)}.status.spam{background:var(--danger-bg);color:var(--danger)}.empty{padding:24px;color:var(--outline);line-height:1.5}.inquiries{padding:20px;display:grid;gap:18px}.inquiry{display:flex;gap:12px}.initials{width:40px;height:40px;border-radius:50%;display:grid;place-items:center;background:#a4f0ef;color:#002020;font:800 13px "Inter",Arial,sans-serif;flex:0 0 auto}.inquiry h3{margin:0 0 4px;font-size:14px}.inquiry p{margin:0;color:var(--outline);font-size:13px;line-height:1.4}.actions-title{display:flex;align-items:center;gap:16px;margin:32px 0 18px}.actions-title h2{margin:0;color:var(--primary);font-size:22px}.rule{height:1px;background:rgba(190,201,200,.6);flex:1}.actions{grid-template-columns:repeat(4,minmax(0,1fr))}.action{display:grid;justify-items:center;text-align:center;gap:12px;padding:24px}.action h3{margin:0;font-size:15px}.action p{margin:4px 0 0;color:var(--outline);font-size:12px}.settings-grid{grid-template-columns:7fr 5fr;align-items:start}.settings-wide{grid-column:1/-1}.form-card{padding:24px}.form-card h2{display:flex;align-items:center;gap:10px;margin:0 0 22px;color:var(--primary);font-size:22px}.form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}.field{display:grid;gap:7px}.field.full{grid-column:1/-1}.field label{color:var(--muted);font:800 13px "Inter",Arial,sans-serif}.input,.textarea,.select{width:100%;border:1px solid var(--border);border-radius:8px;background:var(--surface);color:var(--text);padding:11px 12px;font:500 15px "Public Sans",Arial,sans-serif;outline:none}.textarea{resize:vertical}.input:focus,.textarea:focus,.select:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(0,80,81,.1)}.logo-preview{display:flex;gap:18px;align-items:center;border:1px dashed var(--border);border-radius:12px;background:var(--bg);padding:18px}.logo-preview img{width:96px;height:96px;border-radius:12px;object-fit:contain;background:#fff;border:1px solid var(--border);padding:8px}.hint{margin:6px 0 0;color:var(--outline);font-size:13px;line-height:1.45}.hours-table input{max-width:130px}.check-cell{text-align:center}.checkbox{width:17px;height:17px;accent-color:var(--primary)}.sticky-actions{position:sticky;bottom:0;margin:30px -40px -48px;padding:18px 40px;background:rgba(255,255,255,.9);border-top:1px solid var(--border);backdrop-filter:blur(10px);display:flex;align-items:center;justify-content:space-between;gap:16px}.secondary-btn{border:1px solid var(--primary);border-radius:8px;background:#fff;color:var(--primary);padding:12px 18px;font:800 14px "Inter",Arial,sans-serif;cursor:pointer}.toast{position:fixed;right:24px;bottom:24px;z-index:60;max-width:360px;border-radius:10px;padding:14px 16px;box-shadow:0 12px 32px rgba(0,0,0,.14);font:700 14px "Inter",Arial,sans-serif}.toast.success{background:#e8f6f5;color:var(--success);border:1px solid #b6d9d5}.toast.error{background:var(--danger-bg);color:var(--danger);border:1px solid #ffb4ab}.footer{height:42px;border-top:1px solid var(--border);background:var(--surface);display:flex;align-items:center;justify-content:space-between;padding:0 40px;color:var(--outline);font:700 12px "Inter",Arial,sans-serif}
@media(max-width:1100px){.stats,.actions,.form-grid,.settings-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.panel-grid{grid-template-columns:1fr}.topbar{padding:0 24px}.content{padding:28px 24px}}@media(max-width:760px){.cms-shell{display:block}.sidebar{position:static;width:auto}.main{margin-left:0}.topbar{height:auto;padding:16px;align-items:flex-start;flex-direction:column}.search{width:100%}.page-head{align-items:flex-start;flex-direction:column}.stats,.actions,.form-grid,.settings-grid{grid-template-columns:1fr}.footer{height:auto;align-items:flex-start;flex-direction:column;gap:6px;padding:14px 16px}.content{padding:22px 16px}.sticky-actions{position:static;margin:24px 0 0;padding:16px;align-items:stretch;flex-direction:column}.sticky-actions .primary-btn,.sticky-actions .secondary-btn{width:100%}}
.status.published,.status.accepting{background:#e8f6f5;color:var(--success)}.status.draft,.status.review{background:#fff3cf;color:#7a5900}.status.archived,.status.hidden{background:var(--surface-low);color:var(--muted)}.status.unavailable{background:var(--danger-bg);color:var(--danger)}
</style>
</head>
<body>
<div class="cms-shell">
  <aside class="sidebar">
    <a class="brand" href="dashboard.php" aria-label="NMC Clinic CMS dashboard">
      <img src="../uploads/logo.jpeg" alt="NMC Clinic logo">
      <span><span class="brand-title">NMC Clinic</span><span class="brand-sub">CMS</span></span>
    </a>
    <nav class="nav" aria-label="CMS navigation">
      <?php foreach ($nav as $key => $item): ?>
        <a class="<?= $active === $key ? 'active' : '' ?>" href="<?= e($item[1]) ?>">
          <span class="material-symbols-outlined"><?= e($item[2]) ?></span>
          <span><?= e($item[0]) ?></span>
        </a>
      <?php endforeach; ?>
    </nav>
    <div class="side-action">
      <a class="primary-btn" href="doctors.php"><span class="material-symbols-outlined">add</span>Add Doctor</a>
    </div>
    <div class="side-footer">
      <a href="settings.php"><span class="material-symbols-outlined">contact_support</span>Support</a>
      <a class="logout" href="logout.php"><span class="material-symbols-outlined">logout</span>Log Out</a>
    </div>
  </aside>
  <main class="main">
    <header class="topbar">
      <div class="search">
        <span class="material-symbols-outlined">search</span>
        <input type="search" placeholder="Search doctors, specialties, posts..." aria-label="Search CMS">
      </div>
      <div class="admin">
        <div>
          <div class="admin-name"><?= e((string) $admin['full_name']) ?></div>
          <div class="admin-role">CMS Administrator</div>
        </div>
        <div class="avatar" aria-hidden="true"><?= e(cms_initials((string) $admin['full_name'])) ?></div>
      </div>
    </header>
    <?php
}

function cms_footer(): void
{
    ?>
    <footer class="footer">
      <span>NMC Clinic CMS</span>
      <span>© <?= date('Y') ?> NMC Clinic. All rights reserved.</span>
    </footer>
  </main>
</div>
</body>
</html>
    <?php
}

function cms_initials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name)) ?: [];
    $initials = '';

    foreach ($parts as $part) {
        if ($part !== '') {
            $initials .= strtoupper(substr($part, 0, 1));
        }
        if (strlen($initials) >= 2) {
            break;
        }
    }

    return $initials !== '' ? $initials : 'A';
}

function cms_status_class(string $status): string
{
    return preg_match('/^[a-z_]+$/', $status) ? $status : 'new';
}

function cms_excerpt(string $value, int $limit = 82): string
{
    $value = trim(preg_replace('/\s+/', ' ', $value) ?? '');

    if (strlen($value) <= $limit) {
        return $value;
    }

    return rtrim(substr($value, 0, $limit - 3)) . '...';
}
