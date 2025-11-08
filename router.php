<?php
// Simple router for PHP built-in server to serve index.html at root
// and pass through to PHP endpoints.

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Serve existing files directly
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false; // let PHP's built-in server handle it
}

// Serve the SPA via index.php (which loads index.html and injects integration)
if ($uri === '/' || $uri === '/index.php' || $uri === '/index.html') {
    require __DIR__ . '/index.php';
    return true;
}

// Fallback: 404
http_response_code(404);
echo 'Not Found';
?>