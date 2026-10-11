<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/public_seo.php';

header('Content-Type: text/plain; charset=utf-8');

$base = public_base_url();
?>
User-agent: *
Allow: /
Disallow: /cms/

Sitemap: <?= $base ?>/sitemap.xml
