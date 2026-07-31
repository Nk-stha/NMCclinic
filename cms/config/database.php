<?php
declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $envFile = dirname(__DIR__, 2) . '/.env';
    if (is_file($envFile) && is_readable($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines !== false) {
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#')) {
                    continue;
                }
                if (str_contains($line, '=')) {
                    [$key, $value] = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value, " \t\n\r\0\x0B\"'");
                    if ($key !== '') {
                        if (getenv($key) === false) {
                            putenv("{$key}={$value}");
                        }
                        if (!isset($_ENV[$key])) {
                            $_ENV[$key] = $value;
                        }
                    }
                }
            }
        }
    }

    $getVal = static function (string $key, string $default): string {
        $val = getenv($key);
        if ($val !== false && $val !== '') {
            return (string) $val;
        }
        if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
            return (string) $_ENV[$key];
        }
        if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
            return (string) $_SERVER[$key];
        }
        return $default;
    };

    $host = $getVal('NMC_DB_HOST', 'localhost');
    $name = $getVal('NMC_DB_NAME', 'clinic');
    $user = $getVal('NMC_DB_USER', 'root');
    $pass = $getVal('NMC_DB_PASS', '');

    $dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";

    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;
}

