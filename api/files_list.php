<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../middleware.php';

header('Content-Type: application/json');
require_auth();
$user = current_user();

try {
    $pdo = get_pdo();
    if (class_has_all_access($user['class'])) {
        $sql = 'SELECT f.*, GROUP_CONCAT(d.name ORDER BY d.name SEPARATOR ", ") AS departments
                FROM files f
                LEFT JOIN file_departments fd ON fd.file_id = f.id
                LEFT JOIN departments d ON d.id = fd.department_id
                GROUP BY f.id
                ORDER BY f.created_at DESC';
        $stmt = $pdo->query($sql);
    } else {
        $sql = 'SELECT f.*, GROUP_CONCAT(d.name ORDER BY d.name SEPARATOR ", ") AS departments
                FROM files f
                LEFT JOIN file_departments fd ON fd.file_id = f.id
                LEFT JOIN departments d ON d.id = fd.department_id
                WHERE fd.department_id = ?
                GROUP BY f.id
                ORDER BY f.created_at DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([(int)$user['department_id']]);
    }
    $rows = $stmt->fetchAll();
    echo json_encode(['items' => $rows]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'server', 'message' => $e->getMessage()]);
}
?>