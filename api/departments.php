<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../middleware.php';

header('Content-Type: application/json');
require_auth();

try {
    $pdo = get_pdo();
    $rows = $pdo->query('SELECT id, name, code FROM departments ORDER BY name')->fetchAll();
    echo json_encode(['items' => $rows]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'server', 'message' => $e->getMessage()]);
}
?>