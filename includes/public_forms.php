<?php
declare(strict_types=1);

require_once __DIR__ . '/../cms/config/database.php';

function form_json_response(bool $ok, string $message, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => $ok, 'message' => $message], JSON_THROW_ON_ERROR);
    exit;
}

function form_request_method_is_post(): bool
{
    return strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) === 'POST';
}

function form_value(string $key, int $limit = 500): string
{
    $value = trim((string) ($_POST[$key] ?? ''));
    $value = preg_replace('/\s+/', ' ', $value) ?? '';

    return mb_substr($value, 0, $limit);
}

function form_textarea_value(string $key, int $limit = 3000): string
{
    $value = trim((string) ($_POST[$key] ?? ''));

    return mb_substr($value, 0, $limit);
}

function form_rate_limit(string $bucket, int $seconds = 30): bool
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $key = 'public_form_' . $bucket . '_last_submit';
    $now = time();
    $last = (int) ($_SESSION[$key] ?? 0);
    if ($last > 0 && ($now - $last) < $seconds) {
        return false;
    }

    $_SESSION[$key] = $now;
    return true;
}

function form_ip_hash(): ?string
{
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    if ($ip === '') {
        return null;
    }

    return hash('sha256', $ip);
}

function form_user_agent(): ?string
{
    $agent = trim((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''));

    return $agent !== '' ? mb_substr($agent, 0, 500) : null;
}

function form_find_contact_subject_id(string $subject): ?int
{
    if ($subject === '') {
        return null;
    }

    $value = strtolower($subject);
    $value = preg_replace('/[^a-z0-9_]+/', '_', $value) ?? '';
    $value = trim($value, '_');

    $stmt = db()->prepare('SELECT id FROM contact_subjects WHERE is_active = 1 AND (value = :value OR label = :label) LIMIT 1');
    $stmt->execute([
        ':value' => $value,
        ':label' => $subject,
    ]);
    $id = $stmt->fetchColumn();

    return $id ? (int) $id : null;
}

function form_find_specialty_id(string $service): ?int
{
    if ($service === '') {
        return null;
    }

    $stmt = db()->prepare('SELECT id FROM specialties WHERE deleted_at IS NULL AND status = "published" AND is_visible = 1 AND title = :title LIMIT 1');
    $stmt->execute([':title' => $service]);
    $id = $stmt->fetchColumn();

    return $id ? (int) $id : null;
}

function form_message_with_context(string $message, array $context): string
{
    $lines = [];
    foreach ($context as $label => $value) {
        if ($value !== '') {
            $lines[] = $label . ': ' . $value;
        }
    }

    if ($lines === []) {
        return $message;
    }

    $prefix = implode("\n", $lines);

    return trim($prefix . ($message !== '' ? "\n\n" . $message : ''));
}
