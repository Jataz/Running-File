<?php
// Simple router for PHP built-in server to serve MVC routes
// and pass through to PHP endpoints.

require_once __DIR__ . '/middleware.php';
start_session_once();
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Serve existing files directly
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false; // let PHP's built-in server handle it
}

// MVC routes first
if ($uri === '/') {
    if (current_user()) {
        require __DIR__ . '/dashboard.php';
    } else {
        require __DIR__ . '/login.php';
    }
    return true;
}
if ($uri === '/login') { require __DIR__ . '/login.php'; return true; }
if ($uri === '/dashboard') { require __DIR__ . '/dashboard.php'; return true; }
if ($uri === '/files') { require __DIR__ . '/files.php'; return true; }
if ($uri === '/inbox') { require __DIR__ . '/inbox.php'; return true; }
if ($uri === '/outbox') { require __DIR__ . '/outbox.php'; return true; }
if ($uri === '/board') { require __DIR__ . '/board.php'; return true; }
if ($uri === '/reports') { require __DIR__ . '/reports.php'; return true; }
if ($uri === '/audit') { require __DIR__ . '/audit.php'; return true; }
if ($uri === '/settings') { require __DIR__ . '/settings.php'; return true; }
if ($uri === '/admin/users') { require __DIR__ . '/admin/users.php'; return true; }

// Serve SPA for legacy/main UI pages
if ($uri === '/index.php' || $uri === '/index.html' || preg_match('#^/(board|reports|audit|settings)(/.*)?$#', $uri)) {
    require __DIR__ . '/index.php';
    return true;
}

// Fallback: 404
http_response_code(404);
echo 'Not Found';
?>