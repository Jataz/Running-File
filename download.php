<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/middleware.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo 'Invalid id';
    exit;
}

require_auth();
$user = current_user();
try {
    $pdo = get_pdo();
    $stmt = $pdo->prepare('SELECT * FROM documents WHERE id = ?');
    $stmt->execute([$id]);
    $doc = $stmt->fetch();
    if (!$doc) {
        http_response_code(404);
        echo 'File not found';
        exit;
    }
    // Restrict download for A/B to their department
    if (!class_has_all_access($user['class'])) {
        if ((int)$doc['department_id'] !== (int)$user['department_id']) {
            http_response_code(403);
            echo 'Forbidden';
            exit;
        }
    }

    $path = UPLOAD_DIR . DIRECTORY_SEPARATOR . $doc['stored_name'];
    if (!is_file($path)) {
        http_response_code(410);
        echo 'File missing on disk';
        exit;
    }

    $mime = $doc['mime_type'] ?: 'application/octet-stream';
    $downloadName = basename($doc['original_name']);
    header('Content-Type: ' . $mime);
    header('Content-Length: ' . (string)filesize($path));
    header('Content-Disposition: attachment; filename="' . rawurlencode($downloadName) . '"');
    readfile($path);
    exit;
} catch (Throwable $e) {
    http_response_code(500);
    echo 'Server error: ' . htmlspecialchars($e->getMessage());
    exit;
}

?>