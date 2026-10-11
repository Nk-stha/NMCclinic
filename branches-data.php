<?php
declare(strict_types=1);

require_once __DIR__ . '/cms/config/database.php';

header('Content-Type: application/json; charset=utf-8');

$response = [
    'ok' => true,
    'branches' => [],
];

try {
    $stmt = db()->query(
        'SELECT slug, name, tag_label, address_line, city, district, country, phone, email, hours_note,
                latitude, longitude, map_zoom, is_primary
         FROM clinic_branches
         WHERE deleted_at IS NULL AND is_visible = 1
         ORDER BY is_primary DESC, sort_order ASC, name ASC'
    );

    foreach ($stmt->fetchAll() as $branch) {
        $address = implode(', ', array_filter([
            (string) $branch['address_line'],
            (string) $branch['city'],
            (string) ($branch['district'] ?? ''),
            (string) $branch['country'],
        ]));
        $phone = (string) ($branch['phone'] ?? '');
        $digits = preg_replace('/[^\d+]/', '', $phone) ?? '';

        $response['branches'][] = [
            'slug' => (string) $branch['slug'],
            'name' => (string) $branch['name'],
            'tag' => (string) ($branch['tag_label'] ?? ''),
            'address' => $address,
            'phone' => $phone,
            'phone_href' => $digits !== '' ? 'tel:' . $digits : '',
            'email' => (string) ($branch['email'] ?? ''),
            'hours' => (string) ($branch['hours_note'] ?? ''),
            'lat' => $branch['latitude'] !== null ? (string) $branch['latitude'] : '',
            'lng' => $branch['longitude'] !== null ? (string) $branch['longitude'] : '',
            'zoom' => (int) $branch['map_zoom'],
            'is_primary' => (int) $branch['is_primary'] === 1,
        ];
    }
} catch (Throwable $exception) {
    // Table missing or database down: the pages keep their built-in branch list.
    $response['ok'] = false;
}

echo json_encode($response, JSON_THROW_ON_ERROR);
