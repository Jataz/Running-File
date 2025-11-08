<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/config.php';

function start_session_once(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function current_user(): ?array {
    start_session_once();
    return $_SESSION['user'] ?? null;
}

function require_auth(): void {
    $u = current_user();
    if (!$u) {
        http_response_code(401);
        echo 'Unauthorized';
        exit;
    }
}

function class_has_all_access(string $class): bool {
    // C (Director), D (PS), E (Admin) can access all departments
    return in_array($class, ['C','D','E'], true);
}

function can_delete_by_class(string $class): bool {
    // Allow delete for C/D/E, restrict A/B
    return in_array($class, ['C','D','E'], true);
}

?>