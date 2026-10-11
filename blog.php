<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/public_seo.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php public_render_page_head('blog_index'); ?>
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
        <a href="specialties">Specialties</a>
        <a href="doctors">Doctors</a>
        <a href="blog" aria-current="page">Blog</a>
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
      <a href="specialties">Specialties</a>
      <a href="doctors">Doctors</a>
      <a href="blog" aria-current="page">Blog</a>
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
        <span class="here">Blog</span>
      </div>
      <span class="pill"><span class="dot"></span>Health guides</span>
      <h1>Clear information for <span class="accent">better health decisions</span></h1>
      <p class="lead">Practical articles reviewed by healthcare professionals to help you understand common conditions, tests, and treatments.</p>
    </div>
  </section>

  <!-- ============ FEATURED POST ============ -->
  <section class="wrap featured-wrap" data-cms-featured-blog></section>

  <!-- ============ FILTER + POST GRID ============ -->
  <section class="wrap listing tight">
    <div class="tabs" data-filter-group="posts" data-cms-blog-tabs aria-label="Filter articles by category"></div>

    <div class="grid grid-300 fill-300" data-filter-target="posts" data-cms-blog-list></div>
    <p class="listing-note" data-filter-empty="posts" hidden>No articles in this category yet.</p>
  </section>

  <!-- ============ NEWSLETTER ============ -->
  <section class="band-dark lattice">
    <div class="wrap-narrow section-sm center newsletter">
      <span class="eyebrow on-dark">Stay informed</span>
      <h2 class="section-title on-dark nl">Health tips, straight to your inbox</h2>
      <p class="section-lead">Occasional, practical health guides from our team. No spam, and you can unsubscribe anytime.</p>
      <form class="newsletter-form" data-success="newsletter-ok">
        <label class="visually-hidden" for="nl-email">Email address</label>
        <input id="nl-email" name="email" type="email" required placeholder="Your email address">
        <button class="btn btn-yellow" type="submit">Subscribe</button>
      </form>
      <p class="newsletter-ok" id="newsletter-ok" role="status" hidden>You're subscribed. Thank you.</p>
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
