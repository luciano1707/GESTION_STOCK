<?php
// include/auth_check.php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

function isAdmin(): bool {
    return ($_SESSION['role'] ?? '') === 'admin';
}

function requireAdmin(): void {
    if (!isAdmin()) {
        header('Location: index.php');
        exit;
    }
}
