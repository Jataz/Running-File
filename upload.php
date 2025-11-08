<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/middleware.php';

function fail(string $msg, int $code = 400) {
    http_response_code($code);
    echo htmlspecialchars($msg, ENT_QUOTES, 'UTF-8');
    exit;
}

require_auth();
$user = current_user();

// Optional: attach upload to a business file
$fileId = isset($_POST['file_id']) ? (int)$_POST['file_id'] : 0;
if ($fileId > 0) {
    try {
        $pdo = get_pdo();
        // Verify file exists
        $fstmt = $pdo->prepare('SELECT id FROM files WHERE id = ?');
        $fstmt->execute([$fileId]);
        $frow = $fstmt->fetch();
        if (!$frow) {
            $fileId = 0; // ignore invalid id
        } else {
            // If user is restricted (A/B), ensure the file is accessible by their department
            if (!class_has_all_access($user['class'])) {
                $cstmt = $pdo->prepare('SELECT 1 FROM file_departments WHERE file_id = ? AND department_id = ?');
                $cstmt->execute([$fileId, (int)$user['department_id']]);
                $allowed = (bool)$cstmt->fetchColumn();
                if (!$allowed) {
                    http_response_code(403);
                    echo 'Forbidden: cannot attach to this file';
                    exit;
                }
            }
        }
    } catch (Throwable $e) {
        // On DB error, just detach
        $fileId = 0;
    }
}

if (!isset($_FILES['file'])) {
    fail('No file provided');
}

$file = $_FILES['file'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    fail('Upload error: ' . (string)$file['error']);
}

if ($file['size'] > MAX_UPLOAD_BYTES) {
    fail('File too large');
}

$mime = $file['type'] ?? null;
if ($mime && !in_array($mime, ALLOWED_MIME_TYPES, true)) {
    // Optional: you may comment this out to allow any type
    // fail('File type not allowed');
}

$originalName = $file['name'] ?? 'file';
$safeOriginal = preg_replace('/[^A-Za-z0-9._-]/', '_', $originalName);
$randomPrefix = bin2hex(random_bytes(16));
$storedName = $randomPrefix . '-' . $safeOriginal;

if (!is_dir(UPLOAD_DIR)) {
    @mkdir(UPLOAD_DIR, 0775, true);
}

$destination = UPLOAD_DIR . DIRECTORY_SEPARATOR . $storedName;
$tmpPath = $file['tmp_name'];
$sha256 = @hash_file('sha256', $tmpPath) ?: null;

if (!move_uploaded_file($tmpPath, $destination)) {
    fail('Failed to move uploaded file', 500);
}

try {
    $pdo = get_pdo();
    // If no file_id provided, create a new business file and link to user's dept
    if ($fileId <= 0) {
        $pdo->beginTransaction();
        $subject = $originalName;
        $owner = $user['username'] ?? '';
        $insFile = $pdo->prepare('INSERT INTO files (ref, subject, owner, due_date, tags, description, status, created_by) VALUES (NULL,?,?,?,?,?,"new",?)');
        $insFile->execute([$subject, $owner, null, '', 'Uploaded via New File', (int)($user['id'] ?? 0)]);
        $fileId = (int)$pdo->lastInsertId();
        $ref = 'F-' . str_pad((string)$fileId, 6, '0', STR_PAD_LEFT);
        $updRef = $pdo->prepare('UPDATE files SET ref = ? WHERE id = ?');
        $updRef->execute([$ref, $fileId]);
        // Map to department: restricted classes must map to their own department
        $deptId = (int)($user['department_id'] ?? 0);
        if ($deptId > 0) {
            $link = $pdo->prepare('INSERT IGNORE INTO file_departments (file_id, department_id) VALUES (?, ?)');
            $link->execute([$fileId, $deptId]);
        }
        $pdo->commit();
    }
    $stmt = $pdo->prepare('INSERT INTO documents (stored_name, original_name, mime_type, size, description, sha256, department_id, uploaded_by, file_id) VALUES (?,?,?,?,?,?,?,?,?)');
    $stmt->execute([
        $storedName,
        $originalName,
        $mime,
        (int)($file['size'] ?? 0),
        (string)($_POST['description'] ?? ''),
        $sha256,
        (int)($user['department_id'] ?? 0),
        (int)($user['id'] ?? 0),
        ($fileId > 0 ? $fileId : null),
    ]);
} catch (Throwable $e) {
    // On DB error, try to remove the file
    @unlink($destination);
    fail('Database error: ' . $e->getMessage(), 500);
}

// After upload, go to the new file's details or outbox
header('Location: /outbox');
exit;

?>