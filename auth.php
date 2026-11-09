<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireAdminLogin(): void
{
    if (empty($_SESSION['admin_logged_in'])) {
        header('Location: admin_login.html');
        exit;
    }
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function redirectWithMessage(string $url, string $message): void
{
    $_SESSION['flash_message'] = $message;
    header('Location: ' . $url);
    exit;
}
?>
