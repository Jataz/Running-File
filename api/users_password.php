<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../middleware.php';

header('Content-Type: application/json');
require_auth();
$admin = current_user();
if (!$admin || ($admin['class'] ?? '') !== 'E') {
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
$password = (string)($_POST['password'] ?? '');
if ($id <= 0 || $password === '') {
    http_response_code(400);
    echo json_encode(['error' => 'bad_request']);
    exit;
}

try {
    $pdo = get_pdo();
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
    $stmt->execute([$hash, $id]);
    echo json_encode(['ok' => true]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'server', 'message' => $e->getMessage()]);
}
?>