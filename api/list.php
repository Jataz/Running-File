<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../middleware.php';

header('Content-Type: application/json');
require_auth();
$user = current_user();

try {
    $pdo = get_pdo();
    if (class_has_all_access($user['class'])) {
        $stmt = $pdo->query('SELECT d.*, dept.name AS department_name FROM documents d LEFT JOIN departments dept ON dept.id = d.department_id ORDER BY d.uploaded_at DESC');
        $docs = $stmt->fetchAll();
    } else {
        $stmt = $pdo->prepare('SELECT d.*, dept.name AS department_name FROM documents d LEFT JOIN departments dept ON dept.id = d.department_id WHERE d.department_id = ? ORDER BY d.uploaded_at DESC');
        $stmt->execute([(int)$user['department_id']]);
        $docs = $stmt->fetchAll();
    }
    echo json_encode(['items' => $docs]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'server', 'message' => $e->getMessage()]);
}

?>