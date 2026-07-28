<?php
declare(strict_types=1);

require_once __DIR__ . '/cms/config/database.php';

header('Content-Type: application/json; charset=utf-8');

$response = [
    'ok' => true,
    'specialties' => [],
];

try {
    $stmt = db()->query(
        'SELECT s.id, s.slug, s.title, s.short_description, m.file_path AS image_path, m.alt_text AS image_alt
         FROM specialties s
         LEFT JOIN media_assets m ON m.id = s.image_id AND m.deleted_at IS NULL
         WHERE s.deleted_at IS NULL AND s.status = "published" AND s.is_visible = 1
         ORDER BY s.sort_order ASC, s.title ASC'
    );
    $specialties = $stmt->fetchAll();

    $pointsStmt = db()->prepare(
        'SELECT body
         FROM specialty_points
         WHERE specialty_id = :specialty_id AND is_visible = 1
         ORDER BY sort_order ASC, id ASC'
    );

    foreach ($specialties as $specialty) {
        $pointsStmt->execute([':specialty_id' => (int) $specialty['id']]);
        $points = [];
        foreach ($pointsStmt->fetchAll() as $point) {
            $points[] = (string) $point['body'];
        }

        $response['specialties'][] = [
            'slug' => (string) $specialty['slug'],
            'title' => (string) $specialty['title'],
            'short_description' => (string) ($specialty['short_description'] ?? ''),
            'image_path' => $specialty['image_path'] ? (string) $specialty['image_path'] : null,
            'image_alt' => $specialty['image_alt'] ? (string) $specialty['image_alt'] : (string) $specialty['title'],
            'points' => $points,
        ];
    }
} catch (Throwable $exception) {
    $response['ok'] = false;
}

echo json_encode($response, JSON_THROW_ON_ERROR);
