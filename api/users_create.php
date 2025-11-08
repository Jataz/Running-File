<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../middleware.php';

header('Content-Type: application/json');
require_auth();
$user = current_user();
if (!$user || ($user['class'] ?? '') !== 'E') {
    http_response_code(403);
    echo json_encode(['error' => 'forbidden', 'message' => 'admin_only']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'method_not_allowed']);
    exit;
}

$username = trim((string)($_POST['username'] ?? ''));
$password = (string)($_POST['password'] ?? '');
$class = trim((string)($_POST['class'] ?? ''));
$deptId = (int)($_POST['department_id'] ?? 0);

if ($username === '' || $password === '' || $class === '') {
    http_response_code(400);
    echo json_encode(['error' => 'bad_request', 'message' => 'username_password_class_required']);
    exit;
}
if (!in_array($class, ['A','B','C','D','E'], true)) {
    http_response_code(400);
    echo json_encode(['error' => 'bad_request', 'message' => 'invalid_class']);
    exit;
}

try {
    $pdo = get_pdo();
    // Ensure department exists when provided (required for A/B users)
    if ($deptId > 0) {
        $d = $pdo->prepare('SELECT id FROM departments WHERE id = ?');
        $d->execute([$deptId]);
        if (!$d->fetch()) { $deptId = 0; }
    }

    // For restricted classes A/B, department_id must be set
    if (in_array($class, ['A','B'], true) && $deptId <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'bad_request', 'message' => 'department_required_for_restricted_classes']);
        exit;
    }

    // Insert user if not exists
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['error' => 'conflict', 'message' => 'username_exists']);
        exit;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $ins = $pdo->prepare('INSERT INTO users (username, password_hash, class, department_id) VALUES (?,?,?,?)');
    $ins->execute([$username, $hash, $class, $deptId > 0 ? $deptId : null]);
    echo json_encode(['ok' => true]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'server', 'message' => $e->getMessage()]);
}