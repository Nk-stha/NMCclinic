<?php
declare(strict_types=1);

require_once __DIR__ . '/cms/config/database.php';

header('Content-Type: application/json; charset=utf-8');

$response = [
    'ok' => true,
    'doctors' => [],
    'specialties' => [],
];

try {
    $stmt = db()->query(
        'SELECT d.id, d.full_name, d.short_bio, d.nmc_registration_number, d.availability_status,
                s.title AS specialty_title,
                m.file_path AS photo_path,
                m.alt_text AS photo_alt
         FROM doctors d
         LEFT JOIN specialties s ON s.id = d.specialty_id AND s.deleted_at IS NULL
         LEFT JOIN media_assets m ON m.id = d.photo_id AND m.deleted_at IS NULL
         WHERE d.deleted_at IS NULL AND d.status = "published" AND d.availability_status <> "hidden"
         ORDER BY d.sort_order ASC, d.full_name ASC'
    );
    $doctors = $stmt->fetchAll();

    $qualificationStmt = db()->prepare(
        'SELECT qualification
         FROM doctor_qualifications
         WHERE doctor_id = :doctor_id
         ORDER BY sort_order ASC, id ASC
         LIMIT 2'
    );
    $scheduleStmt = db()->prepare(
        'SELECT day_of_week, start_time, end_time, location_label
         FROM doctor_schedules
         WHERE doctor_id = :doctor_id AND is_visible = 1
         ORDER BY sort_order ASC, day_of_week ASC
         LIMIT 3'
    );

    $specialtySet = [];
    foreach ($doctors as $doctor) {
        $specialty = (string) ($doctor['specialty_title'] ?? 'General Medicine');
        $specialtySet[$specialty] = true;

        $qualificationStmt->execute([':doctor_id' => (int) $doctor['id']]);
        $qualifications = [];
        foreach ($qualificationStmt->fetchAll() as $row) {
            $qualifications[] = (string) $row['qualification'];
        }

        $scheduleStmt->execute([':doctor_id' => (int) $doctor['id']]);
        $schedules = [];
        foreach ($scheduleStmt->fetchAll() as $row) {
            $schedules[] = [
                'day' => day_name((int) $row['day_of_week']),
                'start_time' => $row['start_time'] ? substr((string) $row['start_time'], 0, 5) : '',
                'end_time' => $row['end_time'] ? substr((string) $row['end_time'], 0, 5) : '',
                'location' => (string) ($row['location_label'] ?? ''),
            ];
        }

        $response['doctors'][] = [
            'full_name' => (string) $doctor['full_name'],
            'short_bio' => (string) ($doctor['short_bio'] ?? ''),
            'registration_number' => (string) ($doctor['nmc_registration_number'] ?? ''),
            'availability_status' => (string) $doctor['availability_status'],
            'specialty' => $specialty,
            'photo_path' => $doctor['photo_path'] ? (string) $doctor['photo_path'] : null,
            'photo_alt' => $doctor['photo_alt'] ? (string) $doctor['photo_alt'] : (string) $doctor['full_name'],
            'qualifications' => $qualifications,
            'schedules' => $schedules,
        ];
    }

    $response['specialties'] = array_keys($specialtySet);
    sort($response['specialties']);
} catch (Throwable $exception) {
    $response['ok'] = false;
}

echo json_encode($response, JSON_THROW_ON_ERROR);

function day_name(int $day): string
{
    $days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

    return $days[$day] ?? '';
}
