<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/middleware.php';

start_session_once();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'login';

header('Content-Type: application/json');

try {
    $pdo = get_pdo();
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'db', 'message' => $e->getMessage()]);
    exit;
}

if ($action === 'login' && $method === 'POST') {
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $class = trim((string)($_POST['class'] ?? ''));
    $deptCode = trim((string)($_POST['department'] ?? ''));

    if ($username === '' || $password === '') {
        http_response_code(400);
        echo json_encode(['error' => 'bad_request']);
        exit;
    }

    $stmt = $pdo->prepare('SELECT u.*, d.name as dept_name, d.code as dept_code FROM users u LEFT JOIN departments d ON d.id = u.department_id WHERE u.username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        http_response_code(401);
        echo json_encode(['error' => 'invalid_credentials']);
        exit;
    }

    // Optional: verify selected class/department matches stored profile
    if ($class && $class !== $user['class']) {
        http_response_code(403);
        echo json_encode(['error' => 'class_mismatch']);
        exit;
    }
    if ($deptCode) {
        $deptStmt = $pdo->prepare('SELECT id, name, code FROM departments WHERE code = ?');
        $deptStmt->execute([$deptCode]);
        $dept = $deptStmt->fetch();
        if ($dept) {
            // For A/B, restrict to their assigned department; for C/D/E, allow switching
            if (!class_has_all_access($user['class']) && (int)$user['department_id'] !== (int)$dept['id']) {
                http_response_code(403);
                echo json_encode(['error' => 'department_restricted']);
                exit;
            }
            $user['department_id'] = $dept['id'];
            $user['dept_name'] = $dept['name'];
            $user['dept_code'] = $dept['code'];
        }
    }

    $_SESSION['user'] = [
        'id' => (int)$user['id'],
        'username' => $user['username'],
        'class' => $user['class'],
        'department_id' => (int)($user['department_id'] ?? 0),
        'dept_name' => $user['dept_name'] ?? null,
        'dept_code' => $user['dept_code'] ?? null,
    ];
    echo json_encode(['ok' => true, 'user' => $_SESSION['user']]);
    exit;
}

if ($action === 'me' && $method === 'GET') {
    $u = current_user();
    echo json_encode(['user' => $u]);
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'not_found']);

?>