<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/public_seo.php';
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

  <!-- ============ HEADER ============ -->
  <header class="site-header">
    <div class="header-bar">
      <a class="brand" href="home">
        <img src="uploads/logo.jpeg" alt="NMC clinic logo" width="48" height="48">
        <span class="brand-text">
          <span class="brand-name">NMC</span>
          <span class="brand-sub">Clinic</span>
        </span>
      </a>

      <nav class="nav-main" aria-label="Primary">
        <a href="home">Home</a>
        <a href="about" aria-current="page">About</a>
        <a href="specialties">Specialties</a>
        <a href="doctors">Doctors</a>
        <a href="blog">Blog</a>
        <a href="contact">Contact</a>
      </nav>

      <div class="header-actions">
        <a class="header-phone" href="tel:+97771000000">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#286963" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
          +977 71-000000
        </a>
        <a class="btn btn-primary" href="home#appointment">Book a visit</a>
      </div>

      <button class="nav-toggle" type="button" aria-label="Open menu" aria-expanded="false">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="4" y1="7" x2="20" y2="7"></line><line x1="4" y1="12" x2="20" y2="12"></line><line x1="4" y1="17" x2="20" y2="17"></line></svg>
      </button>
    </div>

    <div class="nav-mobile">
      <a href="home">Home</a>
      <a href="about" aria-current="page">About</a>
      <a href="specialties">Specialties</a>
      <a href="doctors">Doctors</a>
      <a href="blog">Blog</a>
      <a href="contact">Contact</a>
      <div class="nav-mobile-cta">
        <a class="m-book" href="home#appointment">Book a visit</a>
        <a class="m-call" href="tel:+97771000000">Call +977 71-000000</a>
      </div>
    </div>
  </header>

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

  <!-- ============ FOOTER ============ -->
  <footer class="site-footer">
    <div class="footer-in">
      <div class="footer-grid">
        <div>
          <div class="footer-brand">
            <img src="uploads/logo.jpeg" alt="NMC clinic logo" width="44" height="44" loading="lazy">
            <span>NMC</span>
          </div>
          <p class="footer-about">A community clinic in Butwal offering specialist consultation, diagnostics, physiotherapy, women's health and kidney care.</p>
          <a class="footer-phone" href="tel:+97771000000">+977 71-000000</a>
        </div>
        <div>
          <h4>Services</h4>
          <div class="footer-list">
            <a href="specialties#consultation">Specialist consultation</a>
            <a href="specialties#diagnostics">Advanced diagnostics</a>
            <a href="specialties#laboratory">Laboratory services</a>
            <a href="specialties#physiotherapy">Physiotherapy</a>
            <a href="specialties#kidney">Kidney care</a>
          </div>
        </div>
        <div>
          <h4>Contact</h4>
          <div class="footer-list footer-contact">
            <span>Traffic Chowk, Butwal, Rupandehi, Nepal</span>
            <a href="mailto:info@nmcbutwal.com.np">info@nmcbutwal.com.np</a>
            <span>Sun-Fri: 8 AM-8 PM</span>
            <span>Saturday: 9 AM-2 PM</span>
          </div>
        </div>
      </div>

      <div class="footer-emergency">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#FFCC2A" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
        <p>For medical emergencies, contact the nearest emergency service or hospital immediately.</p>
      </div>

      <div class="footer-bottom">
        <span>© <span data-year>2026</span> NMC Clinic, Butwal. All rights reserved.</span>
        <div class="footer-legal">
          <a href="#">Privacy Policy</a>
          <a href="#">Terms &amp; Conditions</a>
          <a href="#">Accessibility</a>
        </div>
      </div>
    </div>
  </footer>

  <!-- ============ WHATSAPP FLOAT ============ -->
  <a class="wa-float" href="https://wa.me/9779800000000" target="_blank" rel="noopener" aria-label="Chat with us on WhatsApp">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="#ffffff" aria-hidden="true"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2 22l5.25-1.38c1.45.79 3.08 1.21 4.79 1.21h.01c5.46 0 9.91-4.45 9.91-9.91C21.96 6.45 17.5 2 12.04 2zm5.8 14.04c-.24.68-1.42 1.32-1.95 1.37-.5.05-1.13.07-1.83-.11-.42-.11-.96-.29-1.66-.59-2.92-1.26-4.83-4.2-4.98-4.4-.15-.2-1.19-1.58-1.19-3.01 0-1.43.75-2.13 1.02-2.42.27-.29.58-.36.78-.36.19 0 .39 0 .56.01.18.01.42-.07.66.5.24.59.82 2.02.89 2.17.07.15.12.32.02.51-.09.2-.14.32-.28.49-.14.17-.29.38-.42.51-.14.14-.28.29-.12.57.16.28.72 1.19 1.55 1.93 1.06.95 1.96 1.24 2.24 1.38.28.14.44.12.6-.07.16-.19.69-.8.87-1.08.18-.28.36-.23.61-.14.25.09 1.61.76 1.89.9.28.14.46.21.53.32.07.12.07.66-.17 1.34z"></path></svg>
    WhatsApp us
  </a>

  <nav class="mobile-bar" aria-label="Quick contact">
    <a href="tel:+97771000000" data-phone-href><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>Call</a>
    <a href="https://wa.me/9779800000000" target="_blank" rel="noopener" data-whatsapp><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2 22l5.25-1.38c1.45.79 3.08 1.21 4.79 1.21h.01c5.46 0 9.91-4.45 9.91-9.91C21.96 6.45 17.5 2 12.04 2z"></path></svg>WhatsApp</a>
    <a class="is-book" href="home#appointment">Book a visit</a>
  </nav>

</div>
<script src="assets/script.js?v=20261011"></script>
</body>
</html>
