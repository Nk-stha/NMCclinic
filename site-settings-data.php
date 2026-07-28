<?php
declare(strict_types=1);

require_once __DIR__ . '/cms/config/database.php';

header('Content-Type: application/json; charset=utf-8');

$response = [
    'ok' => true,
    'settings' => null,
    'hours' => [],
    'hours_summary' => '',
    'socials' => [],
];

try {
    $stmt = db()->query('SELECT * FROM business_contact_settings ORDER BY id ASC LIMIT 1');
    $settings = $stmt->fetch();
    if (!$settings) {
        echo json_encode($response, JSON_THROW_ON_ERROR);
        exit;
    }

    $addressParts = array_filter([
        (string) $settings['address_line'],
        (string) $settings['city'],
        (string) ($settings['district'] ?? ''),
        (string) $settings['country'],
    ]);

    $response['settings'] = [
        'clinic_name' => (string) ($settings['clinic_name'] ?? 'NMC Clinic'),
        'address' => implode(', ', $addressParts),
        'primary_phone' => (string) $settings['primary_phone'],
        'primary_phone_href' => phone_href((string) $settings['primary_phone']),
        'secondary_phone' => (string) ($settings['secondary_phone'] ?? ''),
        'secondary_phone_href' => phone_href((string) ($settings['secondary_phone'] ?? '')),
        'whatsapp_number' => (string) ($settings['whatsapp_number'] ?? ''),
        'whatsapp_url' => whatsapp_url((string) ($settings['whatsapp_number'] ?? '')),
        'email' => (string) $settings['email'],
        'email_href' => 'mailto:' . (string) $settings['email'],
        'latitude' => $settings['latitude'] !== null ? (string) $settings['latitude'] : '',
        'longitude' => $settings['longitude'] !== null ? (string) $settings['longitude'] : '',
        'map_zoom' => isset($settings['map_zoom']) ? (int) $settings['map_zoom'] : 14,
        'map_embed_url' => map_embed_url($settings),
        'privacy_url' => (string) ($settings['privacy_url'] ?? ''),
        'terms_url' => (string) ($settings['terms_url'] ?? ''),
        'accessibility_url' => (string) ($settings['accessibility_url'] ?? ''),
    ];

    $stmt = db()->query(
        'SELECT day_of_week, opens_at, closes_at, label, is_closed
         FROM opening_hours
         ORDER BY sort_order ASC, day_of_week ASC'
    );
    foreach ($stmt->fetchAll() as $hour) {
        $response['hours'][] = [
            'day' => day_label((int) $hour['day_of_week']),
            'opens_at' => $hour['opens_at'] ? substr((string) $hour['opens_at'], 0, 5) : '',
            'closes_at' => $hour['closes_at'] ? substr((string) $hour['closes_at'], 0, 5) : '',
            'label' => (string) ($hour['label'] ?? ''),
            'is_closed' => (int) $hour['is_closed'] === 1,
        ];
    }
    $response['hours_summary'] = hours_summary($response['hours']);

    $stmt = db()->query(
        'SELECT platform, label, url
         FROM social_links
         WHERE is_visible = 1 AND url <> ""
         ORDER BY sort_order ASC, platform ASC'
    );
    foreach ($stmt->fetchAll() as $social) {
        $response['socials'][] = [
            'platform' => (string) $social['platform'],
            'label' => (string) $social['label'],
            'url' => (string) $social['url'],
        ];
    }
} catch (Throwable $exception) {
    $response['ok'] = false;
}

echo json_encode($response, JSON_THROW_ON_ERROR);

function phone_href(string $value): string
{
    $number = preg_replace('/[^\d+]/', '', $value) ?? '';

    return $number !== '' ? 'tel:' . $number : '';
}

function whatsapp_url(string $value): string
{
    $number = preg_replace('/\D+/', '', $value) ?? '';

    return $number !== '' ? 'https://wa.me/' . $number : '';
}

function map_embed_url(array $settings): string
{
    if (($settings['latitude'] ?? null) !== null && ($settings['longitude'] ?? null) !== null) {
        return 'https://www.google.com/maps?q=' . rawurlencode((string) $settings['latitude'] . ',' . (string) $settings['longitude']) . '&z=' . (int) ($settings['map_zoom'] ?? 14) . '&output=embed';
    }

    $parts = array_filter([
        (string) $settings['address_line'],
        (string) $settings['city'],
        (string) ($settings['district'] ?? ''),
        (string) $settings['country'],
    ]);

    return 'https://www.google.com/maps?q=' . rawurlencode(implode(', ', $parts)) . '&output=embed';
}

function day_label(int $day): string
{
    $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    return $days[$day] ?? '';
}

function hours_summary(array $hours): string
{
    $visible = array_values(array_filter($hours, static function (array $hour): bool {
        return !$hour['is_closed'] && ($hour['label'] !== '' || ($hour['opens_at'] !== '' && $hour['closes_at'] !== ''));
    }));

    if ($visible === []) {
        return '';
    }

    $first = $visible[0];
    $last = $visible[count($visible) - 1];
    $time = $first['label'] !== '' ? $first['label'] : time_label($first['opens_at']) . '-' . time_label($first['closes_at']);

    if (count($visible) === 1) {
        return short_day($first['day']) . ' ' . $time;
    }

    return short_day($first['day']) . '-' . short_day($last['day']) . ' ' . $time;
}

function short_day(string $day): string
{
    return substr($day, 0, 3);
}

function time_label(string $time): string
{
    if ($time === '') {
        return '';
    }

    $timestamp = strtotime($time);
    if ($timestamp === false) {
        return $time;
    }

    return date('g A', $timestamp);
}
