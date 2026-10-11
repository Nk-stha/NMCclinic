<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/public_seo.php';

http_response_code(404);
$base = public_base_url();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Page not found | NMC Clinic</title>
<meta name="robots" content="noindex, follow">
<?php public_render_head_assets(false); ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:ital,wght@0,400..800;1,500..700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= public_e($base) ?>/assets/style.css?v=20261011">
</head>
<body>
<main class="page">
  <section class="banner" id="top">
    <div class="banner-veil"></div>
    <div class="banner-in">
      <a class="brand" href="<?= public_e($base) ?>/" style="--hdr-brand:#fff;--hdr-brand-sub:rgba(255,255,255,.75);margin-bottom:28px">
        <img src="<?= public_e($base) ?>/uploads/logo.jpeg" alt="NMC clinic logo" width="48" height="48">
        <span class="brand-text"><span class="brand-name">NMC</span><span class="brand-sub">Clinic</span></span>
      </a>
      <span class="pill"><span class="dot"></span>Error 404</span>
      <h1>We could not find that page</h1>
      <p class="lead">The link may be old or mistyped. These pages will get you where you need to go.</p>
      <div class="hero-actions">
        <a class="btn btn-yellow btn-hero" href="<?= public_e($base) ?>/">Go to the homepage</a>
      </div>
    </div>
  </section>
  <section class="wrap section">
    <div class="grid grid-240 gap-16">
      <a class="card card-hover" href="<?= public_e($base) ?>/specialties"><h3>Specialties</h3><p>Consultation, diagnostics, physiotherapy and more.</p></a>
      <a class="card card-hover" href="<?= public_e($base) ?>/doctors"><h3>Doctors</h3><p>See our specialists and their clinic days.</p></a>
      <a class="card card-hover" href="<?= public_e($base) ?>/blog"><h3>Health guides</h3><p>Practical articles reviewed by our doctors.</p></a>
      <a class="card card-hover" href="<?= public_e($base) ?>/contact"><h3>Contact</h3><p>Branches, opening hours and directions.</p></a>
    </div>
  </section>
</main>
</body>
</html>
