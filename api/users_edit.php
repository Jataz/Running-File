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
$username = trim((string)($_POST['username'] ?? ''));
$class = trim((string)($_POST['class'] ?? ''));
$deptId = isset($_POST['department_id']) ? (int)$_POST['department_id'] : null;

if ($id <= 0 || $username === '' || !in_array($class, ['A','B','C','D','E'], true)) {
    http_response_code(400);
    echo json_encode(['error' => 'bad_request']);
    exit;
}

try {
    $pdo = get_pdo();
    // Check username conflict (excluding current user)
    $chk = $pdo->prepare('SELECT id FROM users WHERE username = ? AND id <> ?');
    $chk->execute([$username, $id]);
    if ($chk->fetch()) {
        http_response_code(409);
        echo json_encode(['error' => 'conflict', 'message' => 'username_exists']);
        exit;
    }

    // Validate department if provided
    if ($deptId !== null && $deptId > 0) {
        $d = $pdo->prepare('SELECT id FROM departments WHERE id = ?');
        $d->execute([$deptId]);
        if (!$d->fetch()) { $deptId = null; }
    }

    // For A/B classes, department is required
    if (in_array($class, ['A','B'], true) && !($deptId && $deptId > 0)) {
        http_response_code(400);
        echo json_encode(['error' => 'bad_request', 'message' => 'department_required_for_restricted_classes']);
        exit;
    }

    $stmt = $pdo->prepare('UPDATE users SET username = ?, class = ?, department_id = ? WHERE id = ?');
    $stmt->execute([$username, $class, ($deptId && $deptId > 0) ? $deptId : null, $id]);
    echo json_encode(['ok' => true]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'server', 'message' => $e->getMessage()]);
}
?>