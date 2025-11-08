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

try {
    $pdo = get_pdo();
    $stmt = $pdo->query("SELECT u.id, u.username, u.class, u.active, u.department_id, d.name AS dept_name FROM users u LEFT JOIN departments d ON d.id = u.department_id ORDER BY u.username ASC");
    $items = $stmt->fetchAll();
    echo json_encode(['ok' => true, 'items' => $items]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'server', 'message' => $e->getMessage()]);
}
?>