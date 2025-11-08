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

$id = (int)($_POST['id'] ?? 0);
$subject = trim((string)($_POST['subject'] ?? ''));
$owner = trim((string)($_POST['owner'] ?? ''));
$due = trim((string)($_POST['due'] ?? ''));
$tags = trim((string)($_POST['tags'] ?? ''));
$desc = trim((string)($_POST['description'] ?? ''));
$status = trim((string)($_POST['status'] ?? ''));
$deptIds = $_POST['dept_ids'] ?? [];
if (!is_array($deptIds)) { $deptIds = [$deptIds]; }

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'bad_request']);
    exit;
}

try {
    $pdo = get_pdo();
    $pdo->beginTransaction();

    // Load file and check permission
    $fstmt = $pdo->prepare('SELECT id, created_by FROM files WHERE id = ?');
    $fstmt->execute([$id]);
    $row = $fstmt->fetch();
    if (!$row) {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode(['error' => 'not_found']);
        exit;
    }
    $isCreator = ((int)$row['created_by'] === (int)$user['id']);
    $isPrivileged = class_has_all_access($user['class']);
    if (!$isCreator && !$isPrivileged) {
        $pdo->rollBack();
        http_response_code(403);
        echo json_encode(['error' => 'forbidden']);
        exit;
    }

    // Update file fields
    $fields = ['subject' => $subject, 'owner' => $owner, 'due_date' => ($due ?: null), 'tags' => $tags, 'description' => $desc];
    if ($status !== '') { $fields['status'] = $status; }
    $set = [];
    $vals = [];
    foreach ($fields as $k=>$v) { $set[] = "$k = ?"; $vals[] = $v; }
    $vals[] = $id;
    $sql = 'UPDATE files SET ' . implode(', ', $set) . ' WHERE id = ?';
    $upd = $pdo->prepare($sql);
    $upd->execute($vals);

    // Update departments mapping
    // Enforce A/B: restrict departments to their own
    if (!$isPrivileged) {
        $deptIds = [ (int)($user['department_id'] ?? 0) ];
    }
    // Normalize
    $deptIdSet = [];
    foreach ($deptIds as $did) { $did = (int)$did; if ($did > 0) { $deptIdSet[$did] = true; } }
    $deptIdList = array_keys($deptIdSet);

    $pdo->prepare('DELETE FROM file_departments WHERE file_id = ?')->execute([$id]);
    if (!empty($deptIdList)) {
        $link = $pdo->prepare('INSERT IGNORE INTO file_departments (file_id, department_id) VALUES (?, ?)');
        foreach ($deptIdList as $did) { $link->execute([$id, (int)$did]); }
    }

    $pdo->commit();
    echo json_encode(['ok' => true]);
} catch (Throwable $e) {
    if ($pdo && $pdo->inTransaction()) { $pdo->rollBack(); }
    http_response_code(500);
    echo json_encode(['error' => 'server', 'message' => $e->getMessage()]);
}

?>