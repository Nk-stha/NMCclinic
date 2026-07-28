<?php
declare(strict_types=1);

require_once __DIR__ . '/cms/config/database.php';

header('Content-Type: application/json; charset=utf-8');

$response = [
    'ok' => true,
    'posts' => [],
    'categories' => [],
];

try {
    $stmt = db()->query(
        'SELECT p.slug, p.title, p.excerpt, p.author_name, p.reviewer_name, p.reviewed_at,
                p.published_at, p.is_featured, p.reading_time_minutes,
                c.name AS category_name,
                m.file_path AS image_path,
                m.alt_text AS image_alt
         FROM blog_posts p
         LEFT JOIN blog_categories c ON c.id = p.category_id AND c.deleted_at IS NULL
         LEFT JOIN media_assets m ON m.id = p.featured_image_id AND m.deleted_at IS NULL
         WHERE p.deleted_at IS NULL AND p.status = "published"
         ORDER BY p.is_featured DESC, p.sort_order ASC, p.published_at DESC, p.title ASC'
    );

    $categorySet = [];
    foreach ($stmt->fetchAll() as $post) {
        $category = (string) ($post['category_name'] ?? 'Health Guides');
        $categorySet[$category] = true;

        $response['posts'][] = [
            'slug' => (string) $post['slug'],
            'url' => 'blog/' . rawurlencode((string) $post['slug']),
            'title' => (string) $post['title'],
            'excerpt' => (string) $post['excerpt'],
            'category' => $category,
            'author_name' => (string) ($post['author_name'] ?? ''),
            'reviewer_name' => (string) ($post['reviewer_name'] ?? ''),
            'reviewed_at' => format_blog_month($post['reviewed_at'] ? (string) $post['reviewed_at'] : null),
            'published_at' => format_blog_month($post['published_at'] ? (string) $post['published_at'] : null),
            'is_featured' => (int) $post['is_featured'] === 1,
            'reading_time_minutes' => $post['reading_time_minutes'] !== null ? (int) $post['reading_time_minutes'] : null,
            'image_path' => $post['image_path'] ? (string) $post['image_path'] : null,
            'image_alt' => $post['image_alt'] ? (string) $post['image_alt'] : (string) $post['title'],
        ];
    }

    $response['categories'] = array_keys($categorySet);
    sort($response['categories']);
} catch (Throwable $exception) {
    $response['ok'] = false;
}

echo json_encode($response, JSON_THROW_ON_ERROR);

function format_blog_month(?string $value): string
{
    if ($value === null || $value === '') {
        return '';
    }

    $timestamp = strtotime($value);
    if ($timestamp === false) {
        return '';
    }

    return date('F Y', $timestamp);
}
