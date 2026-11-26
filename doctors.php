<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/public_seo.php';
require_once __DIR__ . '/includes/site_footer.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php public_render_page_head('doctors'); ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:ital,wght@0,400..800;1,500..700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css?v=20261011">
</head>
<body>
<a class="skip-link" href="#top">Skip to content</a>
<div class="page">

  <?php site_header('doctors'); ?>

  <!-- ============ PAGE BANNER ============ -->
  <section class="banner" id="top">
    <div class="banner-veil"></div>
    <div class="banner-in">
      <div class="crumb">
        <a href="home">Home</a>
        <span class="sep">/</span>
        <span class="here">Doctors</span>
      </div>
      <span class="pill"><span class="dot"></span>Our medical team</span>
      <h1>Meet the doctors <span class="accent">behind your care</span></h1>
      <p class="lead">View specialties, consultation schedules, and choose the doctor who best matches your needs.</p>
    </div>
  </section>

  <!-- ============ FILTER + DOCTOR GRID ============ -->
  <section class="wrap listing">
    <div class="tabs" data-filter-group="doctors" data-cms-doctor-tabs aria-label="Filter doctors by specialty">
        <button type="button" class="tab lg is-active" data-filter="All" aria-pressed="true">All</button>
        <button type="button" class="tab lg" data-filter="General Medicine" aria-pressed="false">General Medicine</button>
        <button type="button" class="tab lg" data-filter="Obstetrics &amp; Gynecology" aria-pressed="false">Obstetrics &amp; Gynecology</button>
        <button type="button" class="tab lg" data-filter="Nephrology" aria-pressed="false">Nephrology</button>
        <button type="button" class="tab lg" data-filter="Physiotherapy" aria-pressed="false">Physiotherapy</button>
    </div>

    <div class="grid grid-280 fill-280" data-filter-target="doctors" data-cms-doctor-list>
      <article class="doc-card" data-category="General Medicine">
        <div class="doc-photo">
          <span class="ph-label">doctor photo</span>
          <span class="doc-tag">General Medicine</span>
        </div>
        <div class="doc-body">
          <h3>Dr. [Name]</h3>
          <div class="doc-hours"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#286963" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg> Sun-Fri</div>
          <div class="doc-actions">
            <a class="doc-view-solid" href="home#appointment">Book a visit</a>
          </div>
        </div>
      </article>
      <article class="doc-card" data-category="Obstetrics &amp; Gynecology">
        <div class="doc-photo">
          <span class="ph-label">doctor photo</span>
          <span class="doc-tag">Obstetrics &amp; Gynecology</span>
        </div>
        <div class="doc-body">
          <h3>Dr. [Name]</h3>
          <div class="doc-hours"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#286963" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg> Mon-Sat</div>
          <div class="doc-actions">
            <a class="doc-view-solid" href="home#appointment">Book a visit</a>
          </div>
        </div>
      </article>
      <article class="doc-card" data-category="Nephrology">
        <div class="doc-photo">
          <span class="ph-label">doctor photo</span>
          <span class="doc-tag">Nephrology</span>
        </div>
        <div class="doc-body">
          <h3>Dr. [Name]</h3>
          <div class="doc-hours"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#286963" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg> Sun, Tue, Thu</div>
          <div class="doc-actions">
            <a class="doc-view-solid" href="home#appointment">Book a visit</a>
          </div>
        </div>
      </article>
      <article class="doc-card" data-category="Physiotherapy">
        <div class="doc-photo">
          <span class="ph-label">doctor photo</span>
          <span class="doc-tag">Physiotherapy</span>
        </div>
        <div class="doc-body">
          <h3>Dr. [Name]</h3>
          <div class="doc-hours"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#286963" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg> Sun-Fri</div>
          <div class="doc-actions">
            <a class="doc-view-solid" href="home#appointment">Book a visit</a>
          </div>
        </div>
      </article>
      <article class="doc-card" data-category="General Medicine">
        <div class="doc-photo">
          <span class="ph-label">doctor photo</span>
          <span class="doc-tag">General Medicine</span>
        </div>
        <div class="doc-body">
          <h3>Dr. [Name]</h3>
          <div class="doc-hours"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#286963" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg> Sun-Fri</div>
          <div class="doc-actions">
            <a class="doc-view-solid" href="home#appointment">Book a visit</a>
          </div>
        </div>
      </article>
      <article class="doc-card" data-category="Obstetrics &amp; Gynecology">
        <div class="doc-photo">
          <span class="ph-label">doctor photo</span>
          <span class="doc-tag">Obstetrics &amp; Gynecology</span>
        </div>
        <div class="doc-body">
          <h3>Dr. [Name]</h3>
          <div class="doc-hours"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#286963" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg> Tue-Sun</div>
          <div class="doc-actions">
            <a class="doc-view-solid" href="home#appointment">Book a visit</a>
          </div>
        </div>
      </article>
    </div>
    <p class="listing-note" data-filter-empty="doctors" hidden>No doctors match this specialty yet.</p>
    <p class="listing-note">Specialist names, photos, and registration details will be added as they are confirmed.</p>
  </section>

  <!-- ============ CTA BAND ============ -->
  <section class="band-light">
    <div class="wrap-narrow section-sm center">
      <h2 class="section-title sm">Ready to see a specialist?</h2>
      <p class="section-lead">Book online or call reception, and our team will confirm your appointment with the right doctor.</p>
      <div class="cta-row">
        <a class="btn btn-primary btn-lg" href="home#appointment">Book a visit</a>
        <a class="btn btn-light btn-lg" href="tel:+97771000000">Call +977 71-000000</a>
      </div>
    </div>
  </section>

  <?php site_footer(true); ?>

</div>
<script src="assets/script.js?v=20261011"></script>
</body>
</html>
