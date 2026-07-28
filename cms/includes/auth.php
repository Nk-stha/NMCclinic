<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

function start_cms_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_name('nmc_cms');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/NMCclinic/cms',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_start();
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string
{
    start_cms_session();

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_is_valid(?string $token): bool
{
    start_cms_session();

    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function current_admin(): ?array
{
    start_cms_session();

    if (empty($_SESSION['admin_id'])) {
        return null;
    }

    $stmt = db()->prepare("SELECT id, full_name, email, status FROM cms_admin_users WHERE id = :id AND status = 'active' LIMIT 1");
    $stmt->execute([':id' => (int) $_SESSION['admin_id']]);
    $admin = $stmt->fetch();

    return $admin ?: null;
}

function require_admin(): array
{
    $admin = current_admin();

    if ($admin === null) {
        header('Location: login.php');
        exit;
    }

    return $admin;
}

function login_admin(string $email, string $password): bool
{
    $stmt = db()->prepare('SELECT id, password_hash, status FROM cms_admin_users WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    $admin = $stmt->fetch();

    if (!$admin || $admin['status'] !== 'active' || !password_verify($password, $admin['password_hash'])) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['admin_id'] = (int) $admin['id'];
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    $update = db()->prepare('UPDATE cms_admin_users SET last_login_at = NOW() WHERE id = :id');
    $update->execute([':id' => (int) $admin['id']]);

    return true;
}

function logout_admin(): void
{
    start_cms_session();
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', (bool) $params['secure'], (bool) $params['httponly']);
    }

    session_destroy();
}
