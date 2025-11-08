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

try {
    $pdo = get_pdo();
    $stmt = $pdo->prepare('SELECT stored_name, file_id, uploaded_by FROM documents WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if ($row) {
        // Permission: only document owner may delete; admins (C/D/E) may also delete
        $allowed = can_delete_by_class($user['class']) || ((int)($row['uploaded_by'] ?? 0) === (int)$user['id']);
        if (!$allowed) {
            // Hide unauthorized by silent redirect
            $redirFileId = isset($_GET['file_id']) ? (int)$_GET['file_id'] : (int)($row['file_id'] ?? 0);
            header('Location: ' . ($redirFileId > 0 ? '/files/' . $redirFileId : '/dashboard'));
            exit;
        }

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
// Optional redirect to file detail
$redirFileId = isset($_GET['file_id']) ? (int)$_GET['file_id'] : 0;
$go = isset($_GET['go']) ? trim((string)$_GET['go']) : '';
if ($redirFileId > 0) {
    header('Location: /files/' . $redirFileId);
} elseif ($go !== '') {
    header('Location: ' . $go);
} else {
    header('Location: /index.php?deleted=1');
}
exit;

?>