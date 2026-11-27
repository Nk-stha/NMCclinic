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
        'content' => ['Page Content', 'content.php', 'article'],
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
/* Visible keyboard focus: the fields above drop the outline and their 10% ring is too faint to see. */
.input:focus-visible,.textarea:focus-visible,.select:focus-visible,.search input:focus-visible{outline:2px solid var(--primary);outline-offset:1px}
/* Phone shell: compact topbar + collapsible sidebar. Desktop (>760px) hides the new controls and is unchanged. */
@media(min-width:761px) and (max-width:1100px){.sticky-actions{margin:30px -24px -28px;padding:18px 24px}.admin>div:first-child{display:none}}
.menu-btn,.top-brand,.side-search{display:none}.sr-only{position:absolute;width:1px;height:1px;margin:-1px;padding:0;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}.grid>*{min-width:0}
@media(max-width:760px){.topbar{height:60px;flex-direction:row;align-items:center;gap:8px;padding:0 12px}.topbar>.search,.admin>div:first-child{display:none}.admin{margin-left:auto}
.cms-js .menu-btn{display:grid;place-items:center;width:44px;height:44px;flex:0 0 44px;border:0;border-radius:8px;background:transparent;color:var(--primary);cursor:pointer;touch-action:manipulation;-webkit-tap-highlight-color:transparent}.menu-btn:active{background:var(--surface-mid)}.menu-btn:focus-visible,.top-brand:focus-visible{outline:2px solid var(--primary);outline-offset:2px}
.top-brand{display:flex;align-items:center;gap:8px;min-width:0;color:var(--primary);font:800 18px "Public Sans",Arial,sans-serif;white-space:nowrap}.top-brand img{width:32px;height:32px;border-radius:8px;object-fit:contain;border:1px solid var(--border);background:var(--surface)}
.cms-js .sidebar{display:none;position:fixed;inset:60px 0 0 0;width:auto;padding:16px;overflow-y:auto;overscroll-behavior:contain;border-right:0;border-top:1px solid var(--border);z-index:30}.cms-js .sidebar.open{display:flex}.cms-js .sidebar .brand{display:none}.cms-js .sidebar .nav{flex:none;overflow:visible}.cms-js.menu-open body{overflow:hidden}
.cms-js .side-search{display:block;width:100%;margin:0 0 12px}.side-search input{background:var(--surface);font-size:16px}.input,.textarea,.select{font-size:16px}.nav a,.side-footer a{min-height:44px}
.table-wrap table{min-width:720px}.table-wrap td:first-child{min-width:180px}.table-wrap{background:linear-gradient(to right,var(--surface) 30%,transparent) left/40px 100% no-repeat local,linear-gradient(to left,var(--surface) 30%,transparent) right/40px 100% no-repeat local,radial-gradient(farthest-side at 0 50%,rgba(19,106,106,.18),transparent) left/14px 100% no-repeat scroll,radial-gradient(farthest-side at 100% 50%,rgba(19,106,106,.18),transparent) right/14px 100% no-repeat scroll,var(--surface)}}
/* Page Content editor (cms/content.php), scoped to .content-editor. Colours are the :root tokens; radii (8/10/12px) and type sizes reuse the literals the rest of the CMS already uses, since no radius/spacing tokens exist. */
.content-editor{-webkit-tap-highlight-color:transparent}.content-editor .field{align-content:start}.content-editor [hidden]{display:none!important}
.content-editor .input,.content-editor .textarea,.content-editor .select{font-size:16px;min-height:44px}.content-editor button,.content-editor a,.content-editor summary{touch-action:manipulation}.content-editor button,.content-editor summary{-webkit-user-select:none;user-select:none}.content-editor :is(a,button):focus-visible{outline:2px solid var(--primary);outline-offset:2px}
.content-editor .secondary-btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;min-height:44px}.content-editor .secondary-btn .material-symbols-outlined{font-size:20px}
.content-pages th[scope=row]{background:none;color:var(--text);font:700 15px "Public Sans",Arial,sans-serif;text-transform:none;letter-spacing:0}.content-pages td:last-child{text-align:right}.content-pages time{font-variant-numeric:tabular-nums}.content-pages .status{text-transform:none}.content-pages tbody tr:last-child>*{border-bottom:0}
.content-panel{margin-bottom:16px}html:has(.content-editor){scroll-padding:84px 0 84px}.content-panel-head{display:flex;align-items:center;gap:12px;min-height:64px;padding:16px 20px 16px 24px;border-radius:12px;cursor:pointer;list-style:none}.content-panel-head::-webkit-details-marker{display:none}.content-panel-head:focus-visible{outline:2px solid var(--primary);outline-offset:-2px}.content-panel[open]>.content-panel-head{border-bottom:1px solid var(--border);border-radius:12px 12px 0 0}
.content-panel-title{display:grid;gap:2px;flex:1;min-width:0}.content-panel-name{color:var(--primary);font:700 20px/1.3 "Public Sans",Arial,sans-serif;overflow-wrap:anywhere;text-wrap:balance}.content-panel-meta{color:var(--outline);font:600 13px "Inter",Arial,sans-serif}.content-chevron{flex:0 0 auto;color:var(--outline);transition:transform .2s cubic-bezier(.2,.8,.2,1)}.content-panel[open] .content-chevron{transform:rotate(180deg)}
.content-badge{flex:0 0 auto;border-radius:999px;background:var(--danger-bg);color:var(--danger);padding:4px 10px;font:800 12px "Inter",Arial,sans-serif}
.content-section{padding:24px}.content-alert{display:flex;align-items:center;gap:8px;margin:0 0 20px;padding:12px 14px;border:1px solid var(--danger);border-radius:8px;background:var(--danger-bg);color:var(--danger);font:700 14px/1.4 "Inter",Arial,sans-serif}
.content-field-error{display:flex;align-items:flex-start;gap:6px;margin:2px 0 0;color:var(--danger);font:600 13px/1.45 "Inter",Arial,sans-serif}.content-field-error .material-symbols-outlined{font-size:18px}.content-editor .field.has-error label{color:var(--danger)}.content-editor .field.has-error :is(.input,.textarea,.select){border-color:var(--danger);box-shadow:0 0 0 1px var(--danger)}
.content-editor .field-heading .textarea{color:var(--primary);font:700 18px/1.35 "Public Sans",Arial,sans-serif}
.content-input-icon{position:relative}.content-input-icon .material-symbols-outlined{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--outline);font-size:20px;pointer-events:none}.content-input-icon .input{padding-left:40px}
.content-media{display:grid;gap:10px}.content-thumbs{display:flex;flex-wrap:wrap;gap:12px}.content-thumb{display:grid;gap:4px;margin:0}.content-thumb img{width:72px;height:72px;object-fit:cover;border:1px solid var(--border);border-radius:8px;background:var(--surface-low)}.content-thumb figcaption{color:var(--outline);font:700 12px "Inter",Arial,sans-serif}
.content-icon-pick{display:flex;align-items:center;gap:10px}.content-icon-preview{flex:0 0 44px;height:44px;display:grid;place-items:center;border:1px solid var(--border);border-radius:8px;background:var(--surface-low);color:var(--primary)}.content-icon-pick .select{flex:1;min-width:0}
.content-items-block{margin-top:28px;padding-top:20px;border-top:1px solid var(--border)}.content-items-head{display:flex;flex-wrap:wrap;align-items:baseline;justify-content:space-between;gap:4px 12px;margin-bottom:12px}.content-items-head h3{margin:0;color:var(--text);font:800 16px "Inter",Arial,sans-serif}.content-count{color:var(--outline);font:600 13px "Inter",Arial,sans-serif;font-variant-numeric:tabular-nums}
.content-items{display:grid;gap:12px}.content-item{min-width:0;margin:0;padding:0;border:1px solid var(--border);border-radius:10px;background:var(--bg)}.content-item .form-grid{padding:14px}
.content-item-head{display:flex;align-items:center;justify-content:space-between;gap:8px;padding:2px 2px 2px 14px;border-bottom:1px solid var(--border)}.content-item-num{color:var(--muted);font:800 12px "Inter",Arial,sans-serif;text-transform:uppercase;letter-spacing:.06em}.content-item-tools{display:flex;gap:2px}
.content-icon-btn{width:44px;height:44px;display:grid;place-items:center;border:0;border-radius:8px;background:transparent;color:var(--muted);cursor:pointer;transition:background-color .12s ease-out}.content-icon-btn:disabled{opacity:.35;cursor:default}.content-icon-btn.content-remove{color:var(--danger)}.content-icon-btn:active:not(:disabled){background:var(--surface-mid)}
.content-empty{margin:0;padding:20px;border:1px dashed var(--border);border-radius:10px;color:var(--outline);font-size:14px;text-align:center}.content-add{margin-top:12px}.content-add:disabled{opacity:.5;cursor:not-allowed}
.content-actions{position:sticky;bottom:0;z-index:5;display:flex;flex-wrap:wrap;align-items:center;gap:8px 20px;margin:28px -24px -24px;padding:12px 24px;border-top:1px solid var(--border);border-radius:0 0 12px 12px;background:var(--surface)}.content-editor .content-save{width:auto;min-height:44px}
.content-view{display:inline-flex;align-items:center;gap:4px;min-height:44px;color:var(--primary);font:800 14px "Inter",Arial,sans-serif;text-decoration:underline;text-underline-offset:3px}.content-view .material-symbols-outlined{font-size:18px}
.content-reset{display:inline-flex;align-items:center;gap:6px;min-height:44px;margin-left:auto;padding:0 14px;border:1px solid var(--danger);border-radius:8px;background:var(--surface);color:var(--danger);font:800 14px "Inter",Arial,sans-serif;cursor:pointer}.content-reset .material-symbols-outlined{font-size:20px}
.content-editor .toast{top:84px;bottom:auto;display:flex;align-items:center;gap:10px;max-width:min(420px,calc(100vw - 32px));padding:6px 6px 6px 14px;animation:content-toast-in .24s cubic-bezier(.2,.8,.2,1)}.content-toast-close{flex:0 0 44px;height:44px;display:grid;place-items:center;margin-left:auto;border:0;border-radius:8px;background:transparent;color:inherit;cursor:pointer}
@keyframes content-toast-in{from{opacity:0;transform:translateY(-8px)}}
@media(hover:hover) and (pointer:fine){.content-panel-head:hover,.content-editor .secondary-btn:not(:disabled):hover{background:var(--surface-low)}.content-editor .primary-btn:hover{background:var(--primary2)}.content-icon-btn:not(:disabled):hover{background:var(--surface-mid)}.content-icon-btn.content-remove:not(:disabled):hover,.content-reset:hover{background:var(--danger-bg)}.content-view:hover{color:var(--primary2)}}
@media(prefers-reduced-motion:reduce){.content-chevron,.content-icon-btn{transition:none}.content-editor .toast{animation:none}}
@media(max-width:760px){html:has(.content-editor){scroll-padding:76px 0 84px}.content-panel-head{padding:14px 12px 14px 16px}.content-panel-name{font-size:18px}.content-section{padding:18px 16px}.content-actions{margin:24px -16px -18px;padding:12px 16px calc(12px + env(safe-area-inset-bottom,0px))}.content-actions{flex-wrap:nowrap;gap:8px}.content-editor .content-save{flex:1;min-width:0;width:auto}.content-view,.content-reset{flex:0 0 44px;justify-content:center;width:44px;height:44px;margin:0;padding:0;border:1px solid currentColor;border-radius:8px}.content-actions .content-label{position:absolute;width:1px;height:1px;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap}.content-editor .toast{top:12px;left:16px;right:16px;max-width:none}.content-editor .page-head .secondary-btn{align-self:stretch}}
@media(max-width:600px){.content-pages table,.content-pages tbody{display:block}.content-pages thead{display:none}.content-pages tr{display:grid;grid-template-columns:minmax(0,1fr) auto;align-items:center;gap:4px 12px;padding:14px 16px;border-bottom:1px solid rgba(190,201,200,.35)}.content-pages tbody tr:last-child{border-bottom:0}.content-pages tr>*{padding:0;border:0}.content-pages td:nth-child(2){grid-row:2;color:var(--outline);font-size:14px}.content-pages td:last-child{grid-row:1/3;grid-column:2}}
</style>
</head>
<body>
<div class="cms-shell">
  <aside class="sidebar" id="cms-sidebar">
    <a class="brand" href="dashboard.php" aria-label="NMC Clinic CMS dashboard">
      <img src="../uploads/logo.jpeg" alt="NMC Clinic logo">
      <span><span class="brand-title">NMC Clinic</span><span class="brand-sub">CMS</span></span>
    </a>
    <nav class="nav" aria-label="CMS navigation">
      <?php foreach ($nav as $key => $item): ?>
        <a class="<?= $active === $key ? 'active' : '' ?>" href="<?= e($item[1]) ?>"<?= $active === $key ? ' aria-current="page"' : '' ?>>
          <span class="material-symbols-outlined" aria-hidden="true"><?= e($item[2]) ?></span>
          <span><?= e($item[0]) ?></span>
        </a>
      <?php endforeach; ?>
    </nav>
    <div class="side-action">
      <a class="primary-btn" href="doctors.php"><span class="material-symbols-outlined" aria-hidden="true">add</span>Add Doctor</a>
    </div>
    <div class="search side-search">
      <span class="material-symbols-outlined" aria-hidden="true">search</span>
      <input type="search" placeholder="Search doctors, specialties, posts…" aria-label="Search CMS">
    </div>
    <div class="side-footer">
      <a href="settings.php"><span class="material-symbols-outlined" aria-hidden="true">contact_support</span>Support</a>
      <a class="logout" href="logout.php"><span class="material-symbols-outlined" aria-hidden="true">logout</span>Log Out</a>
    </div>
  </aside>
  <main class="main">
    <header class="topbar">
      <button type="button" class="menu-btn" aria-controls="cms-sidebar" aria-expanded="false"><span class="material-symbols-outlined" aria-hidden="true">menu</span><span class="sr-only">Menu</span></button>
      <a class="top-brand" href="dashboard.php"><img src="../uploads/logo.jpeg" alt="" width="32" height="32"><span>NMC Clinic</span></a>
      <div class="search">
        <span class="material-symbols-outlined" aria-hidden="true">search</span>
        <input type="search" placeholder="Search doctors, specialties, posts…" aria-label="Search CMS">
      </div>
      <div class="admin">
        <div>
          <div class="admin-name"><?= e((string) $admin['full_name']) ?></div>
          <div class="admin-role">CMS Administrator</div>
        </div>
        <div class="avatar" aria-hidden="true"><?= e(cms_initials((string) $admin['full_name'])) ?></div>
      </div>
    </header>
    <script>
    // Phone menu (CSS only collapses the sidebar once this has run, so the nav stays reachable without JS).
    (function () {
      var root = document.documentElement, btn = document.querySelector('.menu-btn'), nav = document.getElementById('cms-sidebar');
      root.classList.add('cms-js');
      function set(open, focusBtn) {
        nav.classList.toggle('open', open);
        root.classList.toggle('menu-open', open);
        btn.setAttribute('aria-expanded', String(open));
        btn.querySelector('.material-symbols-outlined').textContent = open ? 'close' : 'menu';
        // The sidebar sits before the topbar in the DOM, so move focus into it on open.
        if (open) nav.querySelector('.nav a').focus();
        else if (focusBtn) btn.focus();
      }
      btn.addEventListener('click', function () { set(!nav.classList.contains('open'), false); });
      document.addEventListener('keydown', function (ev) {
        if (ev.key === 'Escape' && nav.classList.contains('open')) set(false, true);
      });
    })();
    </script>
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

<script>
// Global Image Picker & Dynamic Upload Handler
(function () {
  document.addEventListener('click', function (e) {
    var browseBtn = e.target.closest('.img-picker-browse-btn');
    if (browseBtn) {
      var picker = browseBtn.closest('.image-picker-field');
      var modal = picker.querySelector('.img-picker-modal');
      if (modal) modal.style.display = 'flex';
      return;
    }

    var closeBtn = e.target.closest('.img-picker-modal-close');
    if (closeBtn) {
      var modal = closeBtn.closest('.img-picker-modal');
      if (modal) modal.style.display = 'none';
      return;
    }

    var clearBtn = e.target.closest('.img-picker-clear-btn');
    if (clearBtn) {
      var picker = clearBtn.closest('.image-picker-field');
      picker.querySelector('.img-picker-value').value = '';
      picker.querySelector('.img-picker-thumb').style.display = 'none';
      picker.querySelector('.img-picker-empty-icon').style.display = 'block';
      picker.querySelector('.img-picker-title').textContent = 'No image selected';
      clearBtn.style.display = 'none';
      return;
    }

    var item = e.target.closest('.img-picker-item');
    if (item) {
      var picker = item.closest('.image-picker-field');
      var id = item.dataset.id;
      var src = item.dataset.src;
      var alt = item.dataset.alt;

      picker.querySelector('.img-picker-value').value = id;
      var thumb = picker.querySelector('.img-picker-thumb');
      thumb.src = src;
      thumb.style.display = 'block';
      picker.querySelector('.img-picker-empty-icon').style.display = 'none';
      picker.querySelector('.img-picker-title').textContent = alt || 'Selected Image';
      var clearBtn = picker.querySelector('.img-picker-clear-btn');
      if (clearBtn) clearBtn.style.display = 'inline-flex';

      item.closest('.img-picker-modal').style.display = 'none';
      return;
    }
  });

  // Filter existing library items
  document.addEventListener('input', function (e) {
    if (e.target.classList.contains('img-picker-filter')) {
      var q = e.target.value.toLowerCase().trim();
      var items = e.target.closest('.img-picker-modal').querySelectorAll('.img-picker-item');
      items.forEach(function (el) {
        var txt = (el.dataset.alt || '').toLowerCase();
        el.style.display = txt.indexOf(q) !== -1 ? '' : 'none';
      });
    }

    // Live SERP Preview & Character Counters
    if (e.target.id === 'seo_meta_title' || e.target.id === 'title') {
      var titleInput = document.getElementById('seo_meta_title');
      var mainTitle = document.getElementById('title');
      var serpTitle = document.getElementById('serp-preview-title');
      var countEl = document.getElementById('seo_title_count');
      var val = (titleInput && titleInput.value.trim()) || (mainTitle && mainTitle.value.trim()) || 'Post Title | NMC Clinic Butwal';
      if (serpTitle) serpTitle.textContent = val;
      if (countEl && titleInput) countEl.textContent = titleInput.value.length + ' / 60';
    }

    if (e.target.id === 'seo_meta_description' || e.target.id === 'excerpt') {
      var descInput = document.getElementById('seo_meta_description');
      var excerptInput = document.getElementById('excerpt');
      var serpDesc = document.getElementById('serp-preview-desc');
      var countEl = document.getElementById('seo_desc_count');
      var val = (descInput && descInput.value.trim()) || (excerptInput && excerptInput.value.trim()) || 'Summary description will appear here.';
      if (serpDesc) serpDesc.textContent = val;
      if (countEl && descInput) countEl.textContent = descInput.value.length + ' / 155';
    }
  });

  // AJAX Direct Upload Handler
  document.addEventListener('change', function (e) {
    if (!e.target.classList.contains('img-picker-file-input')) return;
    var fileInput = e.target;
    var file = fileInput.files[0];
    if (!file) return;

    var picker = fileInput.closest('.image-picker-field');
    var statusEl = picker.querySelector('.img-picker-status');
    statusEl.style.display = 'block';
    statusEl.style.color = 'var(--primary)';
    statusEl.textContent = 'Uploading ' + file.name + '...';

    var form = fileInput.closest('form');
    var csrfInput = (form && form.querySelector('input[name="csrf_token"]')) || document.querySelector('input[name="csrf_token"]');
    var token = csrfInput ? csrfInput.value : '';

    var formData = new FormData();
    formData.append('file', file);
    formData.append('csrf_token', token);

    fetch('media-upload.php', {
      method: 'POST',
      body: formData,
      headers: {
        'X-CSRF-TOKEN': token
      }
    })
    .then(function (res) { return res.json(); })
    .then(function (data) {
      if (!data.success) {
        throw new Error(data.error || 'Upload failed');
      }

      var media = data.media;
      picker.querySelector('.img-picker-value').value = media.id;
      var thumb = picker.querySelector('.img-picker-thumb');
      thumb.src = '../' + media.file_path.replace(/^\/+/, '');
      thumb.style.display = 'block';
      picker.querySelector('.img-picker-empty-icon').style.display = 'none';
      picker.querySelector('.img-picker-title').textContent = media.alt_text || media.file_path;
      var clearBtn = picker.querySelector('.img-picker-clear-btn');
      if (clearBtn) clearBtn.style.display = 'inline-flex';

      statusEl.style.color = 'var(--primary2)';
      statusEl.textContent = 'Uploaded & selected successfully!';
      setTimeout(function () { statusEl.style.display = 'none'; }, 3000);
    })
    .catch(function (err) {
      statusEl.style.color = 'var(--danger)';
      statusEl.textContent = 'Upload error: ' + err.message;
    })
    .finally(function () {
      fileInput.value = '';
    });
  });
})();
</script>
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
