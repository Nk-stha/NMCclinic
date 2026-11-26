<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/public_seo.php';
require_once __DIR__ . '/includes/site_footer.php';
require_once __DIR__ . '/includes/blog_card.php';

$p = page_content('blog');
[$b, $l, $nl] = [$p['banner'], $p['listing'], $p['newsletter']];
$posts = site_blog_posts();
$categories = site_distinct($posts, 'category');
$featured = null;
foreach ($posts as $post) {
    if ($post['is_featured']) { $featured = $post; break; }
}
$featured ??= $posts[0] ?? null;
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
<link rel="stylesheet" href="assets/style.css?v=20261126b">
</head>
<body>
<a class="skip-link" href="#top"><?= c(page_content('global')['header'], 'skip_label') ?></a>
<div class="page">

  <?php site_header('blog'); ?>

  <!-- ============ PAGE BANNER ============ -->
  <section class="banner" id="top">
    <div class="banner-veil"></div>
    <div class="banner-in">
      <div class="crumb">
        <a href="<?= c_href('home') ?>"><?= c($b, 'crumb_home') ?></a>
        <span class="sep">/</span>
        <span class="here"><?= c($b, 'crumb_here') ?></span>
      </div>
      <span class="pill"><span class="dot"></span><?= c($b, 'eyebrow') ?></span>
      <h1><?= c_heading($b, 'heading', 'accent') ?></h1>
      <p class="lead"><?= c($b, 'subheading') ?></p>
    </div>
  </section>

<?php if ($posts): ?>
  <!-- ============ FEATURED POST ============ -->
  <section class="wrap featured-wrap">
<?php render_blog_card($featured, $l, true); ?>
  </section>

  <!-- ============ FILTER + POST GRID ============ -->
  <section class="wrap listing tight">
    <div class="tabs" data-filter-group="posts" aria-label="<?= c($l, 'filter_label') ?>">
      <button type="button" class="tab lg is-active" data-filter="All" aria-pressed="true"><?= c($l, 'all_label') ?></button>
<?php foreach ($categories as $name): ?>
      <button type="button" class="tab lg" data-filter="<?= public_e($name) ?>" aria-pressed="false"><?= public_e($name) ?></button>
<?php endforeach; ?>
    </div>

    <div class="grid grid-300 fill-300" data-filter-target="posts">
<?php foreach ($posts as $post) { render_blog_card($post, $l); } ?>
    </div>
    <p class="listing-note" data-filter-empty="posts" hidden><?= c($l, 'no_match_message') ?></p>
  </section>
<?php else: ?>
  <section class="wrap listing tight">
    <div class="empty-state"><?= c_text($l, 'empty_message') ?></div>
  </section>
<?php endif; ?>

  <!-- ============ NEWSLETTER ============ -->
  <section class="band-dark lattice">
    <div class="wrap-narrow section-sm center newsletter">
      <span class="eyebrow on-dark"><?= c($nl, 'eyebrow') ?></span>
      <h2 class="section-title on-dark nl"><?= c_heading($nl, 'heading') ?></h2>
      <p class="section-lead"><?= c($nl, 'subheading') ?></p>
      <form class="newsletter-form" data-success="newsletter-ok">
        <label class="visually-hidden" for="nl-email"><?= c($nl, 'email_label') ?></label>
        <input id="nl-email" name="email" type="email" required placeholder="<?= c($nl, 'email_placeholder') ?>">
        <button class="btn btn-yellow" type="submit"><?= c($nl, 'submit_label') ?></button>
      </form>
      <p class="newsletter-ok" id="newsletter-ok" role="status" hidden><?= c($nl, 'success_message') ?></p>
    </div>
  </section>

  <?php site_footer(true); ?>

</div>
<script src="assets/script.js?v=20261126b"></script>
</body>
</html>
