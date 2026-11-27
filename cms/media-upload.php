<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/media_helpers.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $admin = current_admin();
    if (!$admin) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Authentication required.'], JSON_THROW_ON_ERROR);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed.'], JSON_THROW_ON_ERROR);
        exit;
    }

    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
    if (!csrf_is_valid(is_string($token) ? $token : null)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Security token invalid or expired. Please refresh the page.'], JSON_THROW_ON_ERROR);
        exit;
    }

    if (!isset($_FILES['file']) || !is_array($_FILES['file'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'No image file uploaded.'], JSON_THROW_ON_ERROR);
        exit;
    }

    $file = $_FILES['file'];
    $metadata = [
        'alt_text' => trim((string) ($_POST['alt_text'] ?? '')),
        'title_text' => trim((string) ($_POST['title_text'] ?? '')),
        'caption' => trim((string) ($_POST['caption'] ?? '')),
        'focal_point_x' => '',
        'focal_point_y' => '',
        'is_public' => 1,
    ];

    if ($metadata['alt_text'] === '') {
        $origName = pathinfo((string) ($file['name'] ?? ''), PATHINFO_FILENAME);
        $cleanName = preg_replace('/[-_]+/', ' ', $origName) ?? $origName;
        $metadata['alt_text'] = trim($cleanName);
    }

    $id = media_upload($file, $metadata);
    $media = media_find($id);

    if (!$media) {
        throw new RuntimeException('Media upload succeeded but failed to retrieve record.');
    }

    echo json_encode([
        'success' => true,
        'media' => [
            'id' => (int) $media['id'],
            'file_path' => (string) $media['file_path'],
            'alt_text' => (string) ($media['alt_text'] ?? ''),
            'width' => $media['width'] !== null ? (int) $media['width'] : null,
            'height' => $media['height'] !== null ? (int) $media['height'] : null,
            'file_size' => $media['file_size'] !== null ? (int) $media['file_size'] : null,
        ],
    ], JSON_THROW_ON_ERROR);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage() ?: 'An unexpected error occurred during image upload.',
    ], JSON_THROW_ON_ERROR);
}
