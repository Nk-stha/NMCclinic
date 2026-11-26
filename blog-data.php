<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/site_data.php';

header('Content-Type: application/json; charset=utf-8');

$posts = site_blog_posts();
echo json_encode(['ok' => site_data_ok(), 'posts' => $posts, 'categories' => site_distinct($posts, 'category')], JSON_THROW_ON_ERROR);
