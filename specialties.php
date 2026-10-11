<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/public_seo.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php public_render_page_head('specialties'); ?>
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
        <a href="about">About</a>
        <a href="specialties" aria-current="page">Specialties</a>
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
      <a href="about">About</a>
      <a href="specialties" aria-current="page">Specialties</a>
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
        <span class="here">Specialties</span>
      </div>
      <span class="pill"><span class="dot"></span>Our specialties</span>
      <h1>Care for the health needs <span class="accent">that matter most</span></h1>
      <p class="lead">From your first consultation to diagnosis and recovery, our services are designed to make healthcare easier to access, all in one trusted clinic in Butwal.</p>
    </div>
  </section>

  <!-- ============ SPECIALTY QUICK NAV ============ -->
  <section class="wrap quick-nav-wrap" aria-label="Specialty quick links">
    <div class="quick-nav" data-cms-specialty-nav>
        <a href="#consultation">
          <span class="icon-badge sm"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2v6a6 6 0 0 0 12 0V2"></path><path d="M8 15v1a6 6 0 0 0 6 6 6 6 0 0 0 6-6v-4"></path><circle cx="20" cy="10" r="2"></circle></svg></span>
          <span class="quick-label">Specialist consultation</span>
        </a>
        <a href="#diagnostics">
          <span class="icon-badge sm"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2v6l4.5 8a2 2 0 0 1-1.7 3H6.7A2 2 0 0 1 5 16l4.5-8V2"></path><path d="M8.5 2h7"></path><path d="M7 16h10"></path></svg></span>
          <span class="quick-label">Advanced diagnostics</span>
        </a>
        <a href="#laboratory">
          <span class="icon-badge sm"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M9 2v6.5L4.6 17A2 2 0 0 0 6.4 20h11.2a2 2 0 0 0 1.8-3L15 8.5V2"></path><line x1="8" y1="2" x2="16" y2="2"></line><circle cx="12" cy="15" r="1.3"></circle></svg></span>
          <span class="quick-label">Laboratory services</span>
        </a>
        <a href="#physiotherapy">
          <span class="icon-badge sm"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="5" r="2"></circle><path d="M12 7v6"></path><path d="m9 21 3-4 3 4"></path><path d="M7 11h10"></path></svg></span>
          <span class="quick-label">Physiotherapy &amp; rehabilitation</span>
        </a>
        <a href="#women">
          <span class="icon-badge sm"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21.35 10.55 20C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54z"></path></svg></span>
          <span class="quick-label">Obstetrics &amp; gynecology</span>
        </a>
        <a href="#kidney">
          <span class="icon-badge sm"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3C8 3 5 6 5 10c0 3.5 2.5 6 4.5 7.5C11 18.7 11 21 12 21s1-2.3 2.5-3.5C16.5 16 19 13.5 19 10c0-4-3-7-7-7z"></path><path d="M12 9v4"></path></svg></span>
          <span class="quick-label">Nephrology &amp; kidney care</span>
        </a>
    </div>
  </section>

  <!-- ============ SPECIALTY DETAIL SECTIONS ============ -->
  <section class="wrap spec-list" data-cms-specialty-list>
      <div class="spec-row" id="consultation">
        <div class="spec-media">
          <div class="photo-ph ratio-4x3"><span>consultation room photo</span></div>
        </div>
        <div class="spec-body">
          <span class="eyebrow with-icon" aria-hidden="true"><span class="icon-badge sm"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2v6a6 6 0 0 0 12 0V2"></path><path d="M8 15v1a6 6 0 0 0 6 6 6 6 0 0 0 6-6v-4"></path><circle cx="20" cy="10" r="2"></circle></svg></span></span>
          <h2 class="section-title">Specialist consultation</h2>
          <p class="section-lead">Meet the right doctor for clear advice, an accurate diagnosis, and a practical care plan you can understand. We take time to listen and explain your options.</p>
          <ul class="tick-list compact">
          <li><span class="tick sm"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></span>Experienced doctors across key specialties</li>
          <li><span class="tick sm"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></span>Clear explanation of your condition</li>
          <li><span class="tick sm"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></span>A practical, step-by-step care plan</li>
          </ul>
          <a class="link-cta inline" href="home#appointment">Book this service <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></a>
        </div>
      </div>

      <div class="spec-row" id="diagnostics">
        <div class="spec-media">
          <div class="photo-ph ratio-4x3"><span>imaging / diagnostics photo</span></div>
        </div>
        <div class="spec-body">
          <span class="eyebrow with-icon" aria-hidden="true"><span class="icon-badge sm"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2v6l4.5 8a2 2 0 0 1-1.7 3H6.7A2 2 0 0 1 5 16l4.5-8V2"></path><path d="M8.5 2h7"></path><path d="M7 16h10"></path></svg></span></span>
          <h2 class="section-title">Advanced diagnostics</h2>
          <p class="section-lead">Access reliable diagnostic tests and imaging with timely reports, so your doctor can review results and guide your next step without unnecessary delays.</p>
          <ul class="tick-list compact">
          <li><span class="tick sm"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></span>On-site imaging services</li>
          <li><span class="tick sm"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></span>Clear turnaround on reports</li>
          <li><span class="tick sm"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></span>Results reviewed with your doctor</li>
          </ul>
          <a class="link-cta inline" href="home#appointment">Book this service <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></a>
        </div>
      </div>

      <div class="spec-row" id="laboratory">
        <div class="spec-media">
          <div class="photo-ph ratio-4x3"><span>laboratory photo</span></div>
        </div>
        <div class="spec-body">
          <span class="eyebrow with-icon" aria-hidden="true"><span class="icon-badge sm"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M9 2v6.5L4.6 17A2 2 0 0 0 6.4 20h11.2a2 2 0 0 0 1.8-3L15 8.5V2"></path><line x1="8" y1="2" x2="16" y2="2"></line><circle cx="12" cy="15" r="1.3"></circle></svg></span></span>
          <h2 class="section-title">Laboratory services</h2>
          <p class="section-lead">Routine and specialist testing for screening, diagnosis, and follow-up care, handled in-house so you can complete important tests in one place.</p>
          <ul class="tick-list compact">
          <li><span class="tick sm"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></span>Routine and specialist testing</li>
          <li><span class="tick sm"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></span>Screening and health monitoring</li>
          <li><span class="tick sm"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></span>Convenient in-house sample collection</li>
          </ul>
          <a class="link-cta inline" href="home#appointment">Book this service <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></a>
        </div>
      </div>

      <div class="spec-row" id="physiotherapy">
        <div class="spec-media">
          <div class="photo-ph ratio-4x3"><span>physiotherapy photo</span></div>
        </div>
        <div class="spec-body">
          <span class="eyebrow with-icon" aria-hidden="true"><span class="icon-badge sm"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="5" r="2"></circle><path d="M12 7v6"></path><path d="m9 21 3-4 3 4"></path><path d="M7 11h10"></path></svg></span></span>
          <h2 class="section-title">Physiotherapy &amp; rehabilitation</h2>
          <p class="section-lead">Personalized support to reduce pain, rebuild strength, and improve movement, with modern physiotherapy guided by your recovery goals.</p>
          <ul class="tick-list compact">
          <li><span class="tick sm"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></span>Pain relief and mobility support</li>
          <li><span class="tick sm"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></span>Personalized recovery programs</li>
          <li><span class="tick sm"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></span>Guidance for lasting improvement</li>
          </ul>
          <a class="link-cta inline" href="home#appointment">Book this service <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></a>
        </div>
      </div>

      <div class="spec-row" id="women">
        <div class="spec-media">
          <div class="photo-ph ratio-4x3"><span>women's health photo</span></div>
        </div>
        <div class="spec-body">
          <span class="eyebrow with-icon" aria-hidden="true"><span class="icon-badge sm"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21.35 10.55 20C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54z"></path></svg></span></span>
          <h2 class="section-title">Obstetrics &amp; gynecology</h2>
          <p class="section-lead">Respectful care for gynecology, pregnancy, and reproductive health, with a calm, supportive environment at every stage of life.</p>
          <ul class="tick-list compact">
          <li><span class="tick sm"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></span>Pregnancy and antenatal care</li>
          <li><span class="tick sm"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></span>Gynecology and reproductive health</li>
          <li><span class="tick sm"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></span>Respectful, private consultations</li>
          </ul>
          <a class="link-cta inline" href="home#appointment">Book this service <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></a>
        </div>
      </div>

      <div class="spec-row" id="kidney">
        <div class="spec-media">
          <div class="photo-ph ratio-4x3"><span>kidney care photo</span></div>
        </div>
        <div class="spec-body">
          <span class="eyebrow with-icon" aria-hidden="true"><span class="icon-badge sm"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3C8 3 5 6 5 10c0 3.5 2.5 6 4.5 7.5C11 18.7 11 21 12 21s1-2.3 2.5-3.5C16.5 16 19 13.5 19 10c0-4-3-7-7-7z"></path><path d="M12 9v4"></path></svg></span></span>
          <h2 class="section-title">Nephrology &amp; kidney care</h2>
          <p class="section-lead">Specialist support for kidney conditions and related health concerns, from early diagnosis through ongoing management and follow-up.</p>
          <ul class="tick-list compact">
          <li><span class="tick sm"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></span>Diagnosis of kidney-related conditions</li>
          <li><span class="tick sm"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></span>Ongoing management and monitoring</li>
          <li><span class="tick sm"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></span>Coordinated follow-up care</li>
          </ul>
          <a class="link-cta inline" href="home#appointment">Book this service <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></a>
        </div>
      </div>
  </section>

  <!-- ============ CTA BAND ============ -->
  <section class="band-light">
    <div class="wrap-narrow section-sm center">
      <h2 class="section-title sm">Not sure which service you need?</h2>
      <p class="section-lead">Call our reception team and we'll help you choose the right department and book a convenient time.</p>
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
