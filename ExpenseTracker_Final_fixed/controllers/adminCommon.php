<?php
if (session_status() === PHP_SESSION_NONE) session_start();

function requireAdminRole(): void {
    if (!isset($_SESSION['user_id'])) {
        $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $loginUrl = strpos($script, '/views/admin/') !== false
            ? dirname(dirname($script)) . '/login.php'
            : dirname(dirname($script)) . '/views/login.php';
        header('Location: ' . $loginUrl);
        exit();
    }
    if (strtolower($_SESSION['user_role'] ?? '') !== 'admin') {
        http_response_code(403);
        exit('Access denied. Admin only.');
    }
}

function adminCsrfToken(): string {
    if (empty($_SESSION['admin_csrf'])) $_SESSION['admin_csrf'] = bin2hex(random_bytes(24));
    return $_SESSION['admin_csrf'];
}

function adminVerifyCsrf(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!$token || !hash_equals($_SESSION['admin_csrf'] ?? '', $token)) {
        http_response_code(419);
        exit('Invalid request token. Please go back and try again.');
    }
}

function adminFlash(string $type, string $message): void {
    $_SESSION['admin_flash'] = ['type' => $type, 'message' => $message];
}

function adminConsumeFlash(): ?array {
    $flash = $_SESSION['admin_flash'] ?? null;
    unset($_SESSION['admin_flash']);
    return $flash;
}

function adminRedirect(string $file): void {

    header('Location: ../views/admin/' . $file);

    exit();

}