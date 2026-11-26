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
<?php public_render_page_head('about'); ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:ital,wght@0,400..800;1,500..700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css?v=20261011">
</head>
<body>
<a class="skip-link" href="#top">Skip to content</a>
<div class="page">

  <?php site_header('about'); ?>

  <!-- ============ PAGE BANNER ============ -->
  <section class="banner" id="top">
    <div class="banner-veil"></div>
    <div class="banner-in">
      <div class="crumb">
        <a href="home">Home</a>
        <span class="sep">/</span>
        <span class="here">About</span>
      </div>
      <span class="pill"><span class="dot"></span>About NMC</span>
      <h1>A community clinic built around <span class="accent">people</span></h1>
      <p class="lead">We bring specialist care, diagnostics, and rehabilitation together in one welcoming clinic in Butwal, so getting the care you need feels simpler and closer to home.</p>
    </div>
  </section>

  <!-- ============ INTRO ============ -->
  <section class="wrap split">
    <div class="split-media">
      <div class="photo-ph ratio-4x3"><span>clinic / reception photo</span></div>
      <div class="stat-badge">
        <div class="stat-badge-num">6</div>
        <div class="stat-badge-txt">specialties in<br>one clinic</div>
      </div>
    </div>
    <div class="split-body">
      <h2 class="section-title">Healthcare that feels personal</h2>
      <p class="section-lead">NMC brings specialist consultations, diagnostic testing, physiotherapy, women's healthcare, and kidney care together in one convenient location. Our team focuses on listening carefully, explaining clearly, and helping every patient understand the next step in their care.</p>
      <ul class="tick-list">
        <li><span class="tick"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></span>Experienced medical professionals</li>
        <li><span class="tick"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></span>Modern diagnostic facilities</li>
        <li><span class="tick"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></span>Clear and respectful communication</li>
        <li><span class="tick"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></span>Coordinated care across departments</li>
      </ul>
      <a class="btn btn-primary" href="home#appointment">Book a visit</a>
    </div>
  </section>

  <!-- ============ MISSION & VISION ============ -->
  <section class="band-light">
    <div class="wrap section-80 grid grid-320">
      <div class="mv-card">
        <span class="mv-icon"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="12" r="6"></circle><circle cx="12" cy="12" r="2"></circle></svg></span>
        <h3>Our mission</h3>
        <p>To make trusted, specialist healthcare easier to reach for families in Butwal, with honest advice, timely tests, and care that treats every patient as a person, not a case number.</p>
      </div>
      <div class="mv-card is-dark">
        <span class="mv-icon"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"></path><circle cx="12" cy="12" r="3"></circle></svg></span>
        <h3>Our vision</h3>
        <p>To be the clinic our community turns to first: a calm, well-equipped place where consultation, diagnosis, and recovery work together, close to home.</p>
      </div>
    </div>
  </section>

  <!-- ============ CORE VALUES ============ -->
  <section class="wrap section">
    <div class="center head-block">
      <h2 class="section-title">The values behind our care</h2>
      <p class="section-lead">Simple principles that shape how we treat every patient who walks through our door.</p>
    </div>
    <div class="grid grid-250 gap-22">
      <div class="card value-card">
        <span class="icon-badge lg"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78L12 21.23l8.84-8.84a5.5 5.5 0 0 0 0-7.78z"></path></svg></span>
        <h3>Compassion</h3>
        <p>We listen first and treat every patient with patience, warmth, and respect.</p>
      </div>
      <div class="card value-card">
        <span class="icon-badge lg"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><path d="m9 12 2 2 4-4"></path></svg></span>
        <h3>Trust</h3>
        <p>Honest advice and clear information. No exaggerated claims, ever.</p>
      </div>
      <div class="card value-card">
        <span class="icon-badge lg"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v4"></path><path d="M12 18v4"></path><path d="m4.93 4.93 2.83 2.83"></path><path d="m16.24 16.24 2.83 2.83"></path><path d="M2 12h4"></path><path d="M18 12h4"></path><path d="m4.93 19.07 2.83-2.83"></path><path d="m16.24 7.76 2.83-2.83"></path></svg></span>
        <h3>Excellence</h3>
        <p>Careful diagnosis and modern facilities to support the right decisions.</p>
      </div>
      <div class="card value-card">
        <span class="icon-badge lg"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg></span>
        <h3>Community</h3>
        <p>Care that stays local, familiar, and accessible to the families we serve.</p>
      </div>
    </div>
  </section>

  <!-- ============ STORY / TIMELINE ============ -->
  <section class="band-cream">
    <div class="wrap-narrow section">
      <div class="center head-block">
        <span class="eyebrow">Our story</span>
        <h2 class="section-title">How NMC came together</h2>
      </div>
      <ol class="timeline">
        <li>
          <div class="tl-rail"><span class="tl-dot">01</span><span class="tl-line"></span></div>
          <div class="tl-body">
            <h3>A clear need</h3>
            <p>Families in Butwal often travelled between several places for a consultation, a test, and follow-up care. We set out to bring those steps together in one trusted clinic.</p>
          </div>
        </li>
        <li>
          <div class="tl-rail"><span class="tl-dot">02</span><span class="tl-line"></span></div>
          <div class="tl-body">
            <h3>Care under one roof</h3>
            <p>We brought specialist consultation, an in-house laboratory, diagnostic imaging, physiotherapy, women's health, and kidney care into a single, welcoming space.</p>
          </div>
        </li>
        <li>
          <div class="tl-rail"><span class="tl-dot is-last">03</span></div>
          <div class="tl-body is-last">
            <h3>Growing with our community</h3>
            <p>Today we keep listening to our patients and improving, adding services and refining care around what the community around us actually needs.</p>
          </div>
        </li>
      </ol>
    </div>
  </section>

  <!-- ============ CTA BAND ============ -->
  <section class="band-light">
    <div class="wrap-narrow section-sm center">
      <h2 class="section-title sm">Ready when you are</h2>
      <p class="section-lead">Book a visit online, or call our reception team. We'll help you find the right service and confirm your time.</p>
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
