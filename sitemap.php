<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/public_seo.php';

header('Content-Type: application/xml; charset=utf-8');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach (public_sitemap_urls() as $url): ?>
  <url>
    <loc><?= public_e($url['loc']) ?></loc>
    <lastmod><?= public_e($url['lastmod']) ?></lastmod>
    <priority><?= public_e($url['priority']) ?></priority>
  </url>
<?php endforeach; ?>
</urlset>
