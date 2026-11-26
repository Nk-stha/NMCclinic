<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/site_data.php';

header('Content-Type: application/json; charset=utf-8');

$data = site_form_options();
echo json_encode(['ok' => site_data_ok()] + $data, JSON_THROW_ON_ERROR);
