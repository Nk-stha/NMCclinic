<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/site_data.php';

header('Content-Type: application/json; charset=utf-8');

$doctors = site_doctors();
echo json_encode(['ok' => site_data_ok(), 'doctors' => $doctors, 'specialties' => site_distinct($doctors, 'specialty')], JSON_THROW_ON_ERROR);
