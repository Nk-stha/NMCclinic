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
<?php public_render_page_head('blog_index'); ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:ital,wght@0,400..800;1,500..700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css?v=20261011">
</head>
<body>
<a class="skip-link" href="#top">Skip to content</a>
<div class="page">

  <?php site_header('blog'); ?>

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

  <?php site_footer(true); ?>

</div>
<script src="assets/script.js?v=20261011"></script>
</body>
</html>
