<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/site_data.php';

header('Content-Type: application/json; charset=utf-8');

$branches = site_branches();
echo json_encode(['ok' => site_data_ok(), 'branches' => $branches], JSON_THROW_ON_ERROR);
