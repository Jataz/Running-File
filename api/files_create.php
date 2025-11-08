<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../middleware.php';

header('Content-Type: application/json');
require_auth();
$user = current_user();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'method_not_allowed']);
    exit;
}

$subject = trim((string)($_POST['subject'] ?? ''));
$owner = trim((string)($_POST['owner'] ?? ''));
$due = trim((string)($_POST['due'] ?? ''));
$tags = trim((string)($_POST['tags'] ?? ''));
$desc = trim((string)($_POST['description'] ?? ''));

// Departments can be provided as codes or ids; accept array
$deptCodes = $_POST['dept_codes'] ?? [];
$deptIds = $_POST['dept_ids'] ?? [];
if (!is_array($deptCodes)) $deptCodes = [$deptCodes];
if (!is_array($deptIds)) $deptIds = [$deptIds];

if ($subject === '') {
    http_response_code(400);
    echo json_encode(['error' => 'subject_required']);
    exit;
}

try {
    $pdo = get_pdo();
    $pdo->beginTransaction();

    // Resolve departments
    $deptIdSet = [];
    if (!empty($deptIds)) {
        foreach ($deptIds as $id) {
            $id = (int)$id; if ($id > 0) $deptIdSet[$id] = true;
        }
    }
    if (!empty($deptCodes)) {
        $stmt = $pdo->prepare('SELECT id FROM departments WHERE code = ?');
        foreach ($deptCodes as $code) {
            $code = trim((string)$code); if ($code === '') continue;
            $stmt->execute([$code]);
            $row = $stmt->fetch();
            if ($row) { $deptIdSet[(int)$row['id']] = true; }
        }
    }
    $deptIdList = array_keys($deptIdSet);
    if (empty($deptIdList)) {
        // Default to user's own department
        if (!empty($user['department_id'])) {
            $deptIdList = [(int)$user['department_id']];
        }
    }

    // Enforce A/B restriction: must only use their own department
    if (!class_has_all_access($user['class'])) {
        $deptIdList = [(int)$user['department_id']];
    }

    $stmt = $pdo->prepare('INSERT INTO files (ref, subject, owner, due_date, tags, description, status, created_by) VALUES (NULL,?,?,?,?,?,"new",?)');
    $stmt->execute([$subject, $owner, ($due ?: null), $tags, $desc, (int)$user['id']]);
    $fileId = (int)$pdo->lastInsertId();

    // Generate a simple ref based on ID
    $ref = 'F-' . str_pad((string)$fileId, 6, '0', STR_PAD_LEFT);
    $upd = $pdo->prepare('UPDATE files SET ref = ? WHERE id = ?');
    $upd->execute([$ref, $fileId]);

    // Map to departments
    $link = $pdo->prepare('INSERT IGNORE INTO file_departments (file_id, department_id) VALUES (?, ?)');
    foreach ($deptIdList as $did) {
        $link->execute([$fileId, (int)$did]);
    }

    $pdo->commit();
    echo json_encode(['ok' => true, 'id' => $fileId, 'ref' => $ref]);
} catch (Throwable $e) {
    if ($pdo && $pdo->inTransaction()) { $pdo->rollBack(); }
    http_response_code(500);
    echo json_encode(['error' => 'server', 'message' => $e->getMessage()]);
}

?>