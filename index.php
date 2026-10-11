<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/public_seo.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php public_render_page_head('home'); ?>
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
      <a class="brand" href="home" aria-current="page">
        <img src="uploads/logo.jpeg" alt="NMC clinic logo" width="48" height="48">
        <span class="brand-text">
          <span class="brand-name">NMC</span>
          <span class="brand-sub">Clinic</span>
        </span>
      </a>

      <nav class="nav-main" aria-label="Primary">
        <a href="home" aria-current="page">Home</a>
        <a href="about">About</a>
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
      <a href="home" aria-current="page">Home</a>
      <a href="about">About</a>
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

  <!-- ============ HERO ============ -->
  <section class="hero" id="top">
    <div class="hero-veil"></div>
    <div class="hero-in">
      <div class="hero-copy">
        <span class="pill"><span class="dot"></span>Butwal and Bhairahawa</span>
        <h1>Specialists, lab tests and physio in <em>one visit</em></h1>
        <p class="lead">See an experienced doctor, finish your tests on site, and leave knowing your next step.</p>
        <div class="hero-actions">
          <a class="btn btn-yellow btn-hero" href="#appointment">Book a visit</a>
        </div>
      </div>
    </div>
  </section>

  <!-- ============ QUICK ACTIONS ============ -->
  <section class="wrap info-strip-wrap" aria-label="Clinic status and quick contact">
    <div class="info-strip">
      <div class="info-item" role="status">
        <span class="status-dot" data-open-dot></span>
        <span><span class="info-k">Butwal clinic today</span><span class="info-v" data-open-status>Sun-Fri 8 AM-8 PM, Sat 9 AM-2 PM</span></span>
      </div>
      <a class="info-item is-emergency" href="tel:+97771000000" data-phone-href>
        <span class="icon-badge"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"></path></svg></span>
        <span><span class="info-k">Emergency line</span><span class="info-v" data-phone>+977 71-000000</span></span>
      </a>
    </div>
  </section>

  <!-- ============ SERVICES ============ -->
  <section class="wrap home-pad-svc" id="services">
    <div class="head-stack">
      <h2 class="section-title">Care for the health needs that matter most</h2>
      <p class="section-lead">From your first consultation to diagnosis and recovery, in one clinic at Traffic Chowk.</p>
    </div>
    <div class="bento" data-cms-home-specialty-list data-reveal>
        <article class="card card-hover svc-card">
          <span class="icon-badge"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2v6a6 6 0 0 0 12 0V2"></path><path d="M8 15v1a6 6 0 0 0 6 6 6 6 0 0 0 6-6v-4"></path><circle cx="20" cy="10" r="2"></circle></svg></span>
          <h3>Specialist consultation</h3>
          <p>Personalized medical advice from experienced doctors.</p>
          <a class="link-cta inline" href="specialties#consultation">Learn more <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></a>
        </article>
        <article class="card card-hover svc-card">
          <span class="icon-badge"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2v6l4.5 8a2 2 0 0 1-1.7 3H6.7A2 2 0 0 1 5 16l4.5-8V2"></path><path d="M8.5 2h7"></path><path d="M7 16h10"></path></svg></span>
          <h3>Advanced diagnostics</h3>
          <p>In-house laboratory and imaging services for timely and accurate results.</p>
          <a class="link-cta inline" href="specialties#diagnostics">Learn more <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></a>
        </article>
        <article class="card card-hover svc-card">
          <span class="icon-badge"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M9 2v6.5L4.6 17A2 2 0 0 0 6.4 20h11.2a2 2 0 0 0 1.8-3L15 8.5V2"></path><line x1="8" y1="2" x2="16" y2="2"></line><circle cx="12" cy="15" r="1.3"></circle></svg></span>
          <h3>Laboratory services</h3>
          <p>Reliable diagnostic testing, screening, and health monitoring.</p>
          <a class="link-cta inline" href="specialties#laboratory">Learn more <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></a>
        </article>
        <article class="card card-hover svc-card">
          <span class="icon-badge"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="5" r="2"></circle><path d="M12 7v6"></path><path d="m9 21 3-4 3 4"></path><path d="M7 11h10"></path></svg></span>
          <h3>Physiotherapy &amp; rehabilitation</h3>
          <p>Modern physiotherapy services for pain relief, recovery, and improved movement.</p>
          <a class="link-cta inline" href="specialties#physiotherapy">Learn more <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></a>
        </article>
        <article class="card card-hover svc-card">
          <span class="icon-badge"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21.35 10.55 20C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54z"></path></svg></span>
          <h3>Obstetrics &amp; gynecology</h3>
          <p>Comprehensive pregnancy, reproductive, and women's healthcare.</p>
          <a class="link-cta inline" href="specialties#women">Learn more <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></a>
        </article>
        <article class="card card-hover svc-card">
          <span class="icon-badge"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3C8 3 5 6 5 10c0 3.5 2.5 6 4.5 7.5C11 18.7 11 21 12 21s1-2.3 2.5-3.5C16.5 16 19 13.5 19 10c0-4-3-7-7-7z"></path><path d="M12 9v4"></path></svg></span>
          <h3>Nephrology &amp; kidney care</h3>
          <p>Specialist diagnosis and treatment for kidney-related conditions.</p>
          <a class="link-cta inline" href="specialties#kidney">Learn more <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></a>
        </article>
    </div>
  </section>

  <!-- ============ ABOUT ============ -->
  <section id="about">
    <div class="wrap split pad-about">
      <div class="split-media">
        <div class="about-photo">
          <img src="uploads/team-BJF-74gA.jpg" loading="lazy" width="640" height="480"
               alt="The NMC clinic team, doctors, nurses, and staff at the Butwal clinic">
        </div>
        <div class="stat-badge">
          <div class="stat-badge-num">6</div>
          <div class="stat-badge-txt">specialties in<br>one clinic</div>
        </div>
      </div>
      <div class="split-body">
        <h2 class="section-title">Care that starts with listening</h2>
        <p class="section-lead">Specialist care, testing and recovery in one place, with a team that listens and explains your next step.</p>
        <a class="link-cta inline" href="about">More about NMC <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></a>
      </div>
    </div>
  </section>

  <!-- ============ DIAGNOSTICS ============ -->
  <section class="band-light" id="diagnostics">
    <div class="wrap home-pad-80">
      <div class="head-stack">
        <h2 class="section-title">Faster answers. Clearer next steps.</h2>
        <p class="section-lead">Most tests happen here, so you are not travelling between labs while you wait for answers.</p>
      </div>
      <ol class="journey" data-reveal>
        <li>
          <span class="journey-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"></path></svg></span>
          <h3>Sample taken here</h3>
          <p>Samples are collected at the clinic, with no second trip to another lab.</p>
        </li>
        <li>
          <span class="journey-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg></span>
          <h3>Tested in-house</h3>
          <p>Our laboratory and imaging team run routine and specialist tests on site.</p>
        </li>
        <li>
          <span class="journey-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg></span>
          <h3>Report ready</h3>
          <p>Reception tells you when to expect your report before you leave.</p>
        </li>
        <li>
          <span class="journey-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><polyline points="17 11 19 13 23 9"></polyline></svg></span>
          <h3>Reviewed with your doctor</h3>
          <p>Go through the results with the right doctor and agree on the next step.</p>
        </li>
      </ol>
      <a class="btn btn-outline" href="specialties#diagnostics">See diagnostic services</a>
    </div>
  </section>

  <!-- ============ DOCTORS ============ -->
  <section class="wrap home-pad-88" id="doctors">
    <div class="head-row">
      <div class="head-stack">
        <h2 class="section-title">Meet the doctors behind your care</h2>
        <p class="section-lead">See each doctor's specialty and clinic days, then book the one who fits.</p>
      </div>
      <a class="link-cta inline" href="doctors">See all doctors <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></a>
    </div>
    <div class="grid grid-260 gap-22" data-cms-home-doctor-list>
        <article class="doc-card is-home" data-category="General Medicine">
          <div class="doc-photo"><span class="ph-label">doctor photo</span></div>
          <div class="doc-body">
            <span class="doc-chip">General Medicine</span>
            <h3>Dr. [Name]</h3>
            <div class="doc-hours"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#286963" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg> Sun-Fri</div>
            <div class="doc-actions">
              <a class="doc-view" href="#appointment">Book a visit</a>
            </div>
          </div>
        </article>
        <article class="doc-card is-home" data-category="Obstetrics &amp; Gynecology">
          <div class="doc-photo"><span class="ph-label">doctor photo</span></div>
          <div class="doc-body">
            <span class="doc-chip">Obstetrics &amp; Gynecology</span>
            <h3>Dr. [Name]</h3>
            <div class="doc-hours"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#286963" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg> Mon-Sat</div>
            <div class="doc-actions">
              <a class="doc-view" href="#appointment">Book a visit</a>
            </div>
          </div>
        </article>
        <article class="doc-card is-home" data-category="Nephrology">
          <div class="doc-photo"><span class="ph-label">doctor photo</span></div>
          <div class="doc-body">
            <span class="doc-chip">Nephrology</span>
            <h3>Dr. [Name]</h3>
            <div class="doc-hours"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#286963" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg> Sun, Tue, Thu</div>
            <div class="doc-actions">
              <a class="doc-view" href="#appointment">Book a visit</a>
            </div>
          </div>
        </article>
        <article class="doc-card is-home" data-category="Physiotherapy">
          <div class="doc-photo"><span class="ph-label">doctor photo</span></div>
          <div class="doc-body">
            <span class="doc-chip">Physiotherapy</span>
            <h3>Dr. [Name]</h3>
            <div class="doc-hours"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#286963" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg> Sun-Fri</div>
            <div class="doc-actions">
              <a class="doc-view" href="#appointment">Book a visit</a>
            </div>
          </div>
        </article>
    </div>
    <p class="listing-note" data-cms-home-doctor-fallback-note>Specialist names and profiles will be added as details are confirmed.</p>
  </section>

  <!-- ============ WHY CHOOSE US ============ -->
  <section class="wrap home-pad-top24">
    <div class="why-panel lattice">
      <div>
        <h2 class="section-title on-dark">Why patients choose NMC</h2>
        <p class="section-lead">Specialists, tests and follow-up in one place, with a team that explains things plainly.</p>
      </div>
      <div class="why-list" data-reveal>
        <div class="why-item">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="7"></circle><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline></svg>
          <h3>Experienced specialists</h3>
          <p>Consult qualified doctors across the specialties families need most.</p>
        </div>
        <div class="why-item">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
          <h3>Diagnostics under one roof</h3>
          <p>Consultation and the tests you need, in the same building on the same day.</p>
        </div>
        <div class="why-item">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"></polygon></svg>
          <h3>Clear guidance at every step</h3>
          <p>Understand your condition, your results and your options before you go home.</p>
        </div>
        <div class="why-item">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
          <h3>Respectful, coordinated care</h3>
          <p>Doctors, lab and reception work together, so you do not repeat your story.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ============ APPOINTMENT ============ -->
  <section class="appt-band lattice" id="appointment">
    <div class="wrap home-pad-80 appointment">
      <div class="appointment-copy">
        <span class="eyebrow">Appointments</span>
        <h2 class="section-title">Book your visit</h2>
        <p class="section-lead">Choose a service and a day that suits you. Our reception team will call to confirm the time.</p>
        <div class="dept-help">
          <span class="icon-badge"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#286963" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"></path></svg></span>
          <div>
            <div class="dept-help-title">Not sure which department?</div>
            <div class="dept-help-text">Call reception at <a href="tel:+97771000000">+977 71-000000</a></div>
          </div>
        </div>
      </div>

      <div class="appointment-form-col">
        <form class="form-card is-home" data-success="appointment-ok" action="submit-appointment.php" method="post">
          <div class="grid grid-200 form-grid">
            <div class="field">
              <label for="f-name">Full name</label>
              <input class="input" id="f-name" name="name" type="text" required placeholder="Your name">
            </div>
            <div class="field">
              <label for="f-phone">Phone number</label>
              <input class="input" id="f-phone" name="phone" type="tel" required placeholder="+977 …">
            </div>
            <div class="field">
              <label for="f-service">Service or department</label>
              <select class="select" id="f-service" name="service">
                <option value="">Select a service</option>
                <option>Specialist Consultation</option>
                <option>Advanced Diagnostics</option>
                <option>Laboratory Services</option>
                <option>Physiotherapy &amp; Rehabilitation</option>
                <option>Obstetrics &amp; Gynecology</option>
                <option>Nephrology &amp; Kidney Care</option>
              </select>
            </div>
            <div class="field">
              <label for="f-date">Preferred date</label>
              <input class="input" id="f-date" name="date" type="date">
            </div>
            <div class="field">
              <label for="f-time">Preferred time</label>
              <select class="select" id="f-time" name="time">
                <option value="">Any time</option>
                <option>Morning (8 AM-12 PM)</option>
                <option>Afternoon (12-4 PM)</option>
                <option>Evening (4-8 PM)</option>
              </select>
            </div>
          </div>
          <div class="field">
            <label for="f-msg">Message <span class="optional">(optional)</span></label>
            <textarea class="textarea" id="f-msg" name="message" rows="3" placeholder="Anything you'd like us to know"></textarea>
          </div>
          <label class="consent">
            <input type="checkbox" name="consent_accepted" value="1" required>
            <span>I agree to be contacted by NMC about this appointment request.</span>
          </label>
          <button class="btn btn-primary btn-block" type="submit">Request appointment</button>
          <div class="form-ok" id="appointment-ok" role="status" hidden>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#286963" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            Request received. Our reception team will call you to confirm the time.
          </div>
          <p class="form-note">This request does not confirm your appointment. Our reception team will call you to confirm the date and time.</p>
        </form>
      </div>
    </div>
  </section>

  <!-- ============ PATIENT INFORMATION ============ -->
  <section class="wrap home-pad-88">
    <div class="patient-grid">
      <div>
        <div class="head-stack">
          <h2 class="section-title">Before you come in</h2>
          <p class="section-lead">Four things that make the visit quicker for you and for the doctor.</p>
        </div>
        <a class="link-cta inline" href="contact">Read the full patient guide <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></a>
      </div>
      <div class="checklist" data-reveal>
        <div class="check-item">
          <span class="icon-badge"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect></svg></span>
          <div>
            <h3>What to bring</h3>
            <p>A valid ID, previous reports, prescriptions and a list of the medicines you take.</p>
          </div>
        </div>
        <div class="check-item">
          <span class="icon-badge"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"></circle><polyline points="12 7 12 12 15 14"></polyline></svg></span>
          <div>
            <h3>Opening hours</h3>
            <p>Check the schedule before travelling, especially around public holidays.</p>
          </div>
        </div>
        <div class="check-item">
          <span class="icon-badge"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"></path></svg></span>
          <div>
            <h3>Test preparation</h3>
            <p>Some tests need fasting or other preparation. Call us if you are unsure.</p>
          </div>
        </div>
        <div class="check-item">
          <span class="icon-badge"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg></span>
          <div>
            <h3>Reports and payment</h3>
            <p>Ask reception when your report will be ready and which payment methods we accept.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Patient stories: add a section here once verified, permitted testimonials exist.
       An empty "coming soon" box reads as unfinished, so it is left out for now. -->

  <!-- ============ HEALTH ARTICLES ============ -->
  <section class="wrap home-pad-top24" id="articles">
    <div class="head-stack">
      <h2 class="section-title">Clear information for better health decisions</h2>
      <p class="section-lead">Practical guides, reviewed by our doctors, on common conditions, tests and treatments.</p>
    </div>
    <div class="grid grid-280 gap-22" data-cms-home-blog-list></div>
    <div class="section-foot">
      <a class="btn btn-outline" href="blog">All health guides <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></a>
    </div>
  </section>

  <!-- ============ LOCATION & CONTACT ============ -->
  <section class="wrap home-pad-88" id="contact" data-branch-picker>
    <div class="head-stack">
      <span class="eyebrow">Visit us</span>
      <h2 class="section-title">Find a branch near you</h2>
      <p class="section-lead">Choose a branch to see it on the map. Call ahead, or walk in during opening hours.</p>
    </div>
    <div class="contact-split">
      <div class="contact-form-col">
        <div class="branch-list" data-branch-list aria-label="Clinic branches">
          <div class="branch-item is-active">
            <button type="button" class="branch-select" aria-pressed="true"
                    data-branch-name="Butwal Branch" data-branch-address="Traffic Chowk, Butwal, Rupandehi, Nepal"
                    data-branch-lat="27.7000" data-branch-lng="83.4480" data-branch-zoom="15"
                  data-branch-phone="+977 71-000000" data-branch-phone-href="tel:+97771000000">
              <span class="icon-badge md"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg></span>
              <span class="branch-body">
                <span class="branch-head"><span class="branch-name">Butwal Branch</span><span class="branch-tag">Main clinic</span></span>
                <span class="branch-text">Traffic Chowk, Butwal, Rupandehi, Nepal</span>
                <span class="branch-cta" data-branch-cta>Showing on map</span>
              </span>
            </button>
            <div class="branch-meta"><a href="tel:+97771000000">+977 71-000000</a></div>
          </div>
          <div class="branch-item">
            <button type="button" class="branch-select" aria-pressed="false"
                    data-branch-name="Bhairahawa Branch" data-branch-address="Siddharthanagar, Bhairahawa, Rupandehi, Nepal"
                    data-branch-lat="27.5050" data-branch-lng="83.4520" data-branch-zoom="15"
                  data-branch-phone="+977 98-0000000" data-branch-phone-href="tel:+9779800000000">
              <span class="icon-badge md"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg></span>
              <span class="branch-body">
                <span class="branch-head"><span class="branch-name">Bhairahawa Branch</span><span class="branch-tag is-soft">Branch</span></span>
                <span class="branch-text">Siddharthanagar, Bhairahawa, Rupandehi, Nepal</span>
                <span class="branch-cta" data-branch-cta>Show on map</span>
              </span>
            </button>
            <div class="branch-meta"><a href="tel:+9779800000000">+977 98-0000000</a></div>
          </div>
        </div>
        <div class="branch-list branch-extra">
          <div class="branch">
            <span class="icon-badge md"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#286963" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"></rect><path d="m22 7-10 6L2 7"></path></svg></span>
            <div>
              <div class="branch-name">Email us</div>
              <div class="branch-text"><a href="mailto:info@nmcbutwal.com.np">info@nmcbutwal.com.np</a></div>
            </div>
          </div>
          <div class="branch">
            <span class="icon-badge md is-warm"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#124E4A" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"></circle><polyline points="12 7 12 12 15 14"></polyline></svg></span>
            <div>
              <div class="branch-name">Opening hours</div>
              <div class="branch-text">Sun-Fri: 8:00 AM - 8:00 PM<br>Saturday: 9:00 AM - 2:00 PM</div>
            </div>
          </div>
        </div>
      </div>
      <div class="contact-side">
        <p class="map-caption" data-branch-caption aria-live="polite">Showing Butwal Branch</p>
        <div class="map-frame">
          <iframe data-branch-map title="Map of NMC Butwal Branch" loading="lazy"
                  src="https://www.google.com/maps?q=27.7000%2C83.4480&amp;z=15&amp;output=embed"></iframe>
        </div>
        <a class="btn btn-outline btn-block" data-branch-directions href="https://www.google.com/maps/dir/?api=1&amp;destination=27.7000%2C83.4480" target="_blank" rel="noopener">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="3 11 22 2 13 21 11 13 3 11"></polygon></svg> Get directions
        </a>
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
