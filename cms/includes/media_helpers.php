<?php
declare(strict_types=1);

function media_upload_dir(): string
{
    return dirname(__DIR__, 2) . '/uploads/media';
}

function media_public_path(string $filename): string
{
    return 'uploads/media/' . $filename;
}

function media_allowed_mimes(): array
{
    return [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
}

function media_list(): array
{
    $stmt = db()->query(
        'SELECT *
         FROM media_assets
         WHERE deleted_at IS NULL
         ORDER BY created_at DESC, id DESC'
    );

    return $stmt->fetchAll();
}

function media_find(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM media_assets WHERE id = :id AND deleted_at IS NULL LIMIT 1');
    $stmt->execute([':id' => $id]);
    $media = $stmt->fetch();

    return $media ?: null;
}

function media_validate_metadata(array $data): array
{
    $errors = [];

    foreach (['alt_text' => 255, 'title_text' => 255, 'caption' => 255] as $field => $limit) {
        if ($data[$field] !== '' && strlen($data[$field]) > $limit) {
            $errors[] = str_replace('_', ' ', ucfirst($field)) . ' must be ' . $limit . ' characters or less.';
        }
    }

    foreach (['focal_point_x', 'focal_point_y'] as $field) {
        if ($data[$field] !== '' && (!is_numeric($data[$field]) || (float) $data[$field] < 0 || (float) $data[$field] > 100)) {
            $errors[] = str_replace('_', ' ', ucfirst($field)) . ' must be between 0 and 100.';
        }
    }

    return $errors;
}

function media_metadata_from_post(): array
{
    return [
        'alt_text' => trim((string) ($_POST['alt_text'] ?? '')),
        'title_text' => trim((string) ($_POST['title_text'] ?? '')),
        'caption' => trim((string) ($_POST['caption'] ?? '')),
        'focal_point_x' => trim((string) ($_POST['focal_point_x'] ?? '')),
        'focal_point_y' => trim((string) ($_POST['focal_point_y'] ?? '')),
        'is_public' => isset($_POST['is_public']) ? 1 : 0,
    ];
}

function media_save_metadata(int $id, array $data): bool
{
    $stmt = db()->prepare(
        'UPDATE media_assets
         SET alt_text = :alt_text,
             title_text = :title_text,
             caption = :caption,
             focal_point_x = :focal_point_x,
             focal_point_y = :focal_point_y,
             is_public = :is_public
         WHERE id = :id AND deleted_at IS NULL'
    );
    $stmt->execute([
        ':alt_text' => $data['alt_text'] !== '' ? $data['alt_text'] : null,
        ':title_text' => $data['title_text'] !== '' ? $data['title_text'] : null,
        ':caption' => $data['caption'] !== '' ? $data['caption'] : null,
        ':focal_point_x' => $data['focal_point_x'] !== '' ? number_format((float) $data['focal_point_x'], 2, '.', '') : null,
        ':focal_point_y' => $data['focal_point_y'] !== '' ? number_format((float) $data['focal_point_y'], 2, '.', '') : null,
        ':is_public' => (int) $data['is_public'],
        ':id' => $id,
    ]);

    return $stmt->rowCount() > 0;
}

function media_soft_delete(int $id): bool
{
    $stmt = db()->prepare('UPDATE media_assets SET deleted_at = CURRENT_TIMESTAMP WHERE id = :id AND deleted_at IS NULL');
    $stmt->execute([':id' => $id]);

    return $stmt->rowCount() > 0;
}

function media_upload(array $file, array $metadata): int
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Image upload failed. Please choose a valid image file.');
    }

    $maxBytes = 2 * 1024 * 1024;
    if ((int) $file['size'] <= 0 || (int) $file['size'] > $maxBytes) {
        throw new RuntimeException('Image must be 2MB or smaller.');
    }

    $tmpName = (string) $file['tmp_name'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = (string) $finfo->file($tmpName);
    $allowed = media_allowed_mimes();
    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Only JPG, PNG, and WebP images are allowed.');
    }

    $size = getimagesize($tmpName);
    if ($size === false) {
        throw new RuntimeException('Uploaded file is not a valid image.');
    }

    $dir = media_upload_dir();
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        throw new RuntimeException('Media upload directory is not writable.');
    }

    $filename = date('Ymd-His') . '-' . bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
    $destination = $dir . '/' . $filename;
    if (!move_uploaded_file($tmpName, $destination)) {
        throw new RuntimeException('Image could not be saved.');
    }

    $path = media_public_path($filename);
    $stmt = db()->prepare(
        'INSERT INTO media_assets
         (file_path, mime_type, width, height, file_size, alt_text, title_text, caption, focal_point_x, focal_point_y, is_public)
         VALUES
         (:file_path, :mime_type, :width, :height, :file_size, :alt_text, :title_text, :caption, :focal_point_x, :focal_point_y, :is_public)'
    );
    $stmt->execute([
        ':file_path' => $path,
        ':mime_type' => $mime,
        ':width' => (int) $size[0],
        ':height' => (int) $size[1],
        ':file_size' => (int) $file['size'],
        ':alt_text' => $metadata['alt_text'] !== '' ? $metadata['alt_text'] : null,
        ':title_text' => $metadata['title_text'] !== '' ? $metadata['title_text'] : null,
        ':caption' => $metadata['caption'] !== '' ? $metadata['caption'] : null,
        ':focal_point_x' => $metadata['focal_point_x'] !== '' ? number_format((float) $metadata['focal_point_x'], 2, '.', '') : null,
        ':focal_point_y' => $metadata['focal_point_y'] !== '' ? number_format((float) $metadata['focal_point_y'], 2, '.', '') : null,
        ':is_public' => (int) $metadata['is_public'],
    ]);

    return (int) db()->lastInsertId();
}

function media_size_label(?int $bytes): string
{
    if (!$bytes) {
        return '';
    }

    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    }

    return number_format($bytes / 1024, 1) . ' KB';
}
