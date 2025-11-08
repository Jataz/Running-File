<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../middleware.php';

header('Content-Type: application/json');
require_auth();
$u = current_user();
if (!$u || ($u['class'] ?? '') !== 'E') {
    http_response_code(403);
    echo json_encode(['error' => 'forbidden', 'message' => 'admin_only']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'method_not_allowed']);
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$active = $_POST['active'] ?? null; // '0' or '1'
if ($id <= 0 || ($active !== '0' && $active !== '1')) {
    http_response_code(400);
    echo json_encode(['error' => 'bad_request']);
    exit;
}

try {
    $pdo = get_pdo();
    $stmt = $pdo->prepare('UPDATE users SET active = ? WHERE id = ?');
    $stmt->execute([(int)$active, $id]);
    echo json_encode(['ok' => true]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'server', 'message' => $e->getMessage()]);
}
?>