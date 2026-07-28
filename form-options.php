<?php
declare(strict_types=1);

require_once __DIR__ . '/cms/config/database.php';

header('Content-Type: application/json; charset=utf-8');

$response = [
    'ok' => true,
    'specialties' => [],
    'subjects' => [],
];

try {
    $stmt = db()->query(
        'SELECT title
         FROM specialties
         WHERE deleted_at IS NULL AND status = "published" AND is_visible = 1
         ORDER BY sort_order ASC, title ASC'
    );
    foreach ($stmt->fetchAll() as $row) {
        $response['specialties'][] = [
            'label' => (string) $row['title'],
            'value' => (string) $row['title'],
        ];
    }

    $stmt = db()->query(
        'SELECT label, value
         FROM contact_subjects
         WHERE is_active = 1
         ORDER BY sort_order ASC, label ASC'
    );
    foreach ($stmt->fetchAll() as $row) {
        $response['subjects'][] = [
            'label' => (string) $row['label'],
            'value' => (string) $row['value'],
        ];
    }
} catch (Throwable $exception) {
    $response['ok'] = false;
}

echo json_encode($response, JSON_THROW_ON_ERROR);
