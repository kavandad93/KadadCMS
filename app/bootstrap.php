<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params(['httponly' => true, 'secure' => $https, 'samesite' => 'Lax', 'path' => '/']);
    session_start();
}
function e($value): string { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function go(string $url): void { header('Location: '.$url, true, 303); exit; }
function csrf(): string { if (empty($_SESSION['_csrf'])) $_SESSION['_csrf'] = bin2hex(random_bytes(32)); return $_SESSION['_csrf']; }
function check_csrf(): void {
    $sent = (string)($_POST['_csrf'] ?? '');
    if (empty($_SESSION['_csrf']) || !hash_equals((string)$_SESSION['_csrf'], $sent)) { http_response_code(403); exit('درخواست معتبر نیست؛ صفحه را تازه‌سازی کنید.'); }
}
function db(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;
    $file = __DIR__.'/config.php';
    if (!is_file($file)) go('/public/install.php');
    $c = require $file;
    $pdo = new PDO('mysql:host='.$c['host'].';dbname='.$c['name'].';charset=utf8mb4', $c['user'], $c['pass'], [
        PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES=>false
    ]);
    return $pdo;
}
function require_login(): void { if (empty($_SESSION['user_id'])) go('index.php?page=login'); }
function notice(string $message): void { $_SESSION['_notice'] = $message; }
