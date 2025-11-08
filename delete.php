<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/middleware.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: /index.php');
    exit;
}

require_auth();
$user = current_user();
if (!can_delete_by_class($user['class'])) {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

try {
    $pdo = get_pdo();
    $stmt = $pdo->prepare('SELECT stored_name FROM documents WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if ($row) {
        $path = UPLOAD_DIR . DIRECTORY_SEPARATOR . $row['stored_name'];
        if (is_file($path)) {
            @unlink($path);
        }
        $del = $pdo->prepare('DELETE FROM documents WHERE id = ?');
        $del->execute([$id]);
    }
} catch (Throwable $e) {
    // Ignore errors for now; production should log these
}

header('Location: /index.php?deleted=1');
exit;

?>