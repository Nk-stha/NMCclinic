<?php
declare(strict_types=1);

function contact_page_options(): array
{
    $stmt = db()->query(
        "SELECT id, slug, status
         FROM pages
         WHERE deleted_at IS NULL AND page_type = 'contact'
         ORDER BY status = 'published' DESC, slug ASC"
    );

    return $stmt->fetchAll();
}

function contact_page_settings(): array
{
    $stmt = db()->query(
        'SELECT cp.*
         FROM contact_page cp
         INNER JOIN pages p ON p.id = cp.page_id
         WHERE p.deleted_at IS NULL
         ORDER BY p.status = "published" DESC, cp.id ASC
         LIMIT 1'
    );
    $row = $stmt->fetch();

    return $row ?: contact_page_default();
}

function contact_page_default(): array
{
    return [
        'page_id' => '',
        'response_time_note' => '',
        'emergency_title' => '',
        'emergency_description' => '',
        'emergency_phone_label' => '',
        'emergency_phone_number' => '',
        'is_visible' => 1,
    ];
}

function contact_subjects(): array
{
    $stmt = db()->query('SELECT * FROM contact_subjects ORDER BY sort_order ASC, label ASC');

    return $stmt->fetchAll();
}

function contact_settings_from_post(): array
{
    return [
        'page_id' => trim((string) ($_POST['page_id'] ?? '')),
        'response_time_note' => trim((string) ($_POST['response_time_note'] ?? '')),
        'emergency_title' => trim((string) ($_POST['emergency_title'] ?? '')),
        'emergency_description' => trim((string) ($_POST['emergency_description'] ?? '')),
        'emergency_phone_label' => trim((string) ($_POST['emergency_phone_label'] ?? '')),
        'emergency_phone_number' => trim((string) ($_POST['emergency_phone_number'] ?? '')),
        'is_visible' => isset($_POST['is_visible']) ? 1 : 0,
    ];
}

function contact_subjects_from_post(): array
{
    $ids = $_POST['subject_id'] ?? [];
    $labels = $_POST['subject_label'] ?? [];
    $values = $_POST['subject_value'] ?? [];
    $emails = $_POST['subject_email'] ?? [];
    $active = $_POST['subject_active'] ?? [];
    $subjects = [];

    if (!is_array($labels)) {
        return $subjects;
    }

    foreach ($labels as $index => $label) {
        $label = trim((string) $label);
        $value = trim((string) ($values[$index] ?? ''));
        $email = trim((string) ($emails[$index] ?? ''));

        if ($label === '' && $value === '' && $email === '') {
            continue;
        }

        $subjects[] = [
            'id' => isset($ids[$index]) && ctype_digit((string) $ids[$index]) ? (int) $ids[$index] : null,
            'label' => $label,
            'value' => contact_subject_value($value !== '' ? $value : $label),
            'recipient_email' => $email,
            'is_active' => isset($active[$index]) ? 1 : 0,
        ];
    }

    return $subjects;
}

function contact_subject_value(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9_]+/', '_', $value) ?? '';
    $value = trim($value, '_');

    return $value !== '' ? $value : 'subject';
}

function contact_validate(array $settings, array $subjects): array
{
    $errors = [];

    if ($settings['page_id'] === '' || !ctype_digit($settings['page_id'])) {
        $errors[] = 'Contact page is required.';
    } else {
        $stmt = db()->prepare("SELECT id FROM pages WHERE id = :id AND page_type = 'contact' AND deleted_at IS NULL LIMIT 1");
        $stmt->execute([':id' => (int) $settings['page_id']]);
        if (!$stmt->fetch()) {
            $errors[] = 'Selected contact page is invalid.';
        }
    }

    foreach ([
        'response_time_note' => 255,
        'emergency_title' => 150,
        'emergency_phone_label' => 100,
        'emergency_phone_number' => 30,
    ] as $field => $limit) {
        if ($settings[$field] !== '' && strlen($settings[$field]) > $limit) {
            $errors[] = str_replace('_', ' ', ucfirst($field)) . ' must be ' . $limit . ' characters or less.';
        }
    }

    $seenValues = [];
    foreach ($subjects as $subject) {
        if ($subject['label'] === '') {
            $errors[] = 'Subject label is required when a subject row is filled.';
            break;
        }
        if (strlen($subject['label']) > 100 || strlen($subject['value']) > 100) {
            $errors[] = 'Subject label and value must be 100 characters or less.';
            break;
        }
        if (isset($seenValues[$subject['value']])) {
            $errors[] = 'Subject values must be unique.';
            break;
        }
        $seenValues[$subject['value']] = true;
        if ($subject['id'] === null) {
            $stmt = db()->prepare('SELECT id FROM contact_subjects WHERE value = :value LIMIT 1');
            $stmt->execute([':value' => $subject['value']]);
        } else {
            $stmt = db()->prepare('SELECT id FROM contact_subjects WHERE value = :value AND id <> :id LIMIT 1');
            $stmt->execute([
                ':value' => $subject['value'],
                ':id' => $subject['id'],
            ]);
        }
        if ($stmt->fetch()) {
            $errors[] = 'Subject value "' . $subject['value'] . '" already exists.';
            break;
        }
        if ($subject['recipient_email'] !== '' && !filter_var($subject['recipient_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Subject recipient email is invalid.';
            break;
        }
    }

    return $errors;
}

function contact_save(array $settings, array $subjects): void
{
    $pdo = db();
    $pdo->beginTransaction();

    try {
        $stmt = $pdo->prepare(
            'INSERT INTO contact_page
             (page_id, response_time_note, emergency_title, emergency_description, emergency_phone_label, emergency_phone_number, is_visible)
             VALUES
             (:page_id, :response_time_note, :emergency_title, :emergency_description, :emergency_phone_label, :emergency_phone_number, :is_visible)
             ON DUPLICATE KEY UPDATE
             response_time_note = VALUES(response_time_note),
             emergency_title = VALUES(emergency_title),
             emergency_description = VALUES(emergency_description),
             emergency_phone_label = VALUES(emergency_phone_label),
             emergency_phone_number = VALUES(emergency_phone_number),
             is_visible = VALUES(is_visible)'
        );
        $stmt->execute([
            ':page_id' => (int) $settings['page_id'],
            ':response_time_note' => $settings['response_time_note'] !== '' ? $settings['response_time_note'] : null,
            ':emergency_title' => $settings['emergency_title'] !== '' ? $settings['emergency_title'] : null,
            ':emergency_description' => $settings['emergency_description'] !== '' ? $settings['emergency_description'] : null,
            ':emergency_phone_label' => $settings['emergency_phone_label'] !== '' ? $settings['emergency_phone_label'] : null,
            ':emergency_phone_number' => $settings['emergency_phone_number'] !== '' ? $settings['emergency_phone_number'] : null,
            ':is_visible' => (int) $settings['is_visible'],
        ]);

        $insert = $pdo->prepare(
            'INSERT INTO contact_subjects (label, value, recipient_email, sort_order, is_active)
             VALUES (:label, :value, :recipient_email, :sort_order, :is_active)'
        );
        $update = $pdo->prepare(
            'UPDATE contact_subjects
             SET label = :label,
                 value = :value,
                 recipient_email = :recipient_email,
                 sort_order = :sort_order,
                 is_active = :is_active
             WHERE id = :id'
        );
        foreach ($subjects as $index => $subject) {
            $params = [
                ':label' => $subject['label'],
                ':value' => $subject['value'],
                ':recipient_email' => $subject['recipient_email'] !== '' ? $subject['recipient_email'] : null,
                ':sort_order' => $index,
                ':is_active' => (int) $subject['is_active'],
            ];

            if ($subject['id'] === null) {
                $insert->execute($params);
            } else {
                $params[':id'] = $subject['id'];
                $update->execute($params);
            }
        }

        $pdo->commit();
    } catch (Throwable $exception) {
        $pdo->rollBack();
        throw $exception;
    }
}

function contact_inquiries_list(): array
{
    $stmt = db()->query(
        'SELECT ci.*, cs.label AS subject_label
         FROM contact_inquiries ci
         LEFT JOIN contact_subjects cs ON cs.id = ci.subject_id
         WHERE ci.deleted_at IS NULL
         ORDER BY ci.created_at DESC'
    );

    return $stmt->fetchAll();
}

function appointment_requests_list(): array
{
    $stmt = db()->query(
        'SELECT ar.*, s.title AS specialty_title
         FROM appointment_requests ar
         LEFT JOIN specialties s ON s.id = ar.specialty_id
         WHERE ar.deleted_at IS NULL
         ORDER BY ar.created_at DESC'
    );

    return $stmt->fetchAll();
}

function update_submission_status(string $type, int $id, string $status): bool
{
    $map = [
        'contact' => ['contact_inquiries', ['new', 'contacted', 'closed', 'spam']],
        'appointment' => ['appointment_requests', ['new', 'contacted', 'scheduled', 'closed', 'spam']],
    ];

    if (!isset($map[$type]) || !in_array($status, $map[$type][1], true)) {
        return false;
    }

    $sql = 'UPDATE ' . $map[$type][0] . ' SET status = :status, responded_at = CASE WHEN :status2 IN ("contacted", "scheduled", "closed") THEN CURRENT_TIMESTAMP ELSE responded_at END WHERE id = :id AND deleted_at IS NULL';
    $stmt = db()->prepare($sql);
    $stmt->execute([
        ':status' => $status,
        ':status2' => $status,
        ':id' => $id,
    ]);

    return $stmt->rowCount() > 0;
}
