<?php
declare(strict_types=1);

// Shared DB reads for the JSON endpoints and server-rendered pages.
// Each reader logs and returns its empty shape on failure; site_data_ok() tells endpoints whether any read failed.
require_once __DIR__ . '/../cms/config/database.php';

function site_data_fail(Throwable $exception): void
{
    error_log('site_data: ' . $exception->getMessage());
    $GLOBALS['site_data_failed'] = true;
}

function site_data_ok(): bool
{
    return empty($GLOBALS['site_data_failed']);
}

/** Per-request cache (failures included, so a dead DB is tried once). Pass true to clear it, for CLI checks. */
function site_settings(bool $reset = false): array
{
    static $cache = null;
    if ($reset) {
        $cache = null;
    }

    return $cache ??= site_settings_load();
}

function site_settings_reset_cache(): void
{
    site_settings(true);
}

function site_settings_load(): array
{
    $response = ['settings' => null, 'hours' => [], 'hours_summary' => '', 'socials' => []];

    try {
        $stmt = db()->query('SELECT * FROM business_contact_settings ORDER BY id ASC LIMIT 1');
        $settings = $stmt->fetch();
        if (!$settings) {
            return $response;
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
        site_data_fail($exception);
        return ['settings' => null, 'hours' => [], 'hours_summary' => '', 'socials' => []];
    }

    return $response;
}

function site_branches(): array
{
    $branches = [];

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

            $branches[] = [
                'slug' => (string) $branch['slug'],
                'name' => (string) $branch['name'],
                'tag' => (string) ($branch['tag_label'] ?? ''),
                'address' => $address,
                'phone' => $phone,
                'phone_href' => phone_href($phone),
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
        site_data_fail($exception);
        return [];
    }

    return $branches;
}

function site_specialties(): array
{
    $out = [];

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

            $out[] = [
                'slug' => (string) $specialty['slug'],
                'title' => (string) $specialty['title'],
                'short_description' => (string) ($specialty['short_description'] ?? ''),
                'image_path' => $specialty['image_path'] ? (string) $specialty['image_path'] : null,
                'image_alt' => $specialty['image_alt'] ? (string) $specialty['image_alt'] : (string) $specialty['title'],
                'points' => $points,
            ];
        }
    } catch (Throwable $exception) {
        site_data_fail($exception);
        return [];
    }

    return $out;
}


function site_doctors(): array
{
    $out = [];

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

        foreach ($doctors as $doctor) {
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

            $out[] = [
                'full_name' => (string) $doctor['full_name'],
                'short_bio' => (string) ($doctor['short_bio'] ?? ''),
                'registration_number' => (string) ($doctor['nmc_registration_number'] ?? ''),
                'availability_status' => (string) $doctor['availability_status'],
                'specialty' => (string) ($doctor['specialty_title'] ?? 'General Medicine'),
                'photo_path' => $doctor['photo_path'] ? (string) $doctor['photo_path'] : null,
                'photo_alt' => $doctor['photo_alt'] ? (string) $doctor['photo_alt'] : (string) $doctor['full_name'],
                'qualifications' => $qualifications,
                'schedules' => $schedules,
            ];
        }
    } catch (Throwable $exception) {
        site_data_fail($exception);
        return [];
    }

    return $out;
}

function site_blog_posts(): array
{
    $out = [];

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

        foreach ($stmt->fetchAll() as $post) {
            $out[] = [
                'slug' => (string) $post['slug'],
                'url' => 'blog/' . rawurlencode((string) $post['slug']),
                'title' => (string) $post['title'],
                'excerpt' => (string) $post['excerpt'],
                'category' => (string) ($post['category_name'] ?? 'Health Guides'),
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
    } catch (Throwable $exception) {
        site_data_fail($exception);
        return [];
    }

    return $out;
}

function site_form_options(): array
{
    $response = ['specialties' => [], 'subjects' => []];

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
        site_data_fail($exception);
        return ['specialties' => [], 'subjects' => []];
    }

    return $response;
}

// Sorted unique names, as the doctors and blog endpoints list them next to their rows.
function site_distinct(array $rows, string $key): array
{
    $names = array_values(array_unique(array_column($rows, $key)));
    sort($names);

    return $names;
}

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

function day_name(int $day): string
{
    $days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

    return $days[$day] ?? '';
}

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
