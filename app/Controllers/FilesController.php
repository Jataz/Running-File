<?php
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../middleware.php';

class FilesController {
    public function index(): void {
        require_auth();
        $u = current_user();
        try {
            $pdo = get_pdo();
            $sql = "SELECT f.id, f.ref, f.subject, f.owner, f.status, f.due_date,
                           GROUP_CONCAT(d.name SEPARATOR ', ') AS departments
                    FROM files f
                    LEFT JOIN file_departments fd ON fd.file_id = f.id
                    LEFT JOIN departments d ON d.id = fd.department_id";
            $params = [];
            if (!class_has_all_access($u['class'])) {
                $sql .= " WHERE fd.department_id = ?";
                $params[] = (int)($u['department_id'] ?? 0);
            }
            $sql .= " GROUP BY f.id ORDER BY f.created_at DESC";
            $stmt = $pdo->prepare($sql); $stmt->execute($params);
            $items = $stmt->fetchAll();
        } catch (Throwable $e) {
            http_response_code(500);
            echo 'Server error: ' . htmlspecialchars($e->getMessage());
            return;
        }
        $title = 'All Files';
        $content = $this->renderView(__DIR__ . '/../Views/files/index.php', [ 'items' => $items ]);
        $this->renderLayout($title, $content);
    }

    public function show(int $id): void {
        require_auth();
        $u = current_user();
        if ($id <= 0) {
            http_response_code(400);
            echo 'Invalid file id';
            return;
        }
        try {
            $pdo = get_pdo();
            $fstmt = $pdo->prepare(
                "SELECT f.*, GROUP_CONCAT(d.name ORDER BY d.name SEPARATOR ', ') AS departments
                   FROM files f
                   LEFT JOIN file_departments fd ON fd.file_id = f.id
                   LEFT JOIN departments d ON d.id = fd.department_id
                  WHERE f.id = ?
                  GROUP BY f.id"
            );
            $fstmt->execute([$id]);
            $file = $fstmt->fetch();
            if (!$file) {
                http_response_code(404);
                echo 'File not found';
                return;
            }
            if (!class_has_all_access($u['class'])) {
                $cstmt = $pdo->prepare('SELECT 1 FROM file_departments WHERE file_id = ? AND department_id = ?');
                $cstmt->execute([$id, (int)($u['department_id'] ?? 0)]);
                $allowed = (bool)$cstmt->fetchColumn();
                if (!$allowed) {
                    http_response_code(403);
                    echo 'Forbidden';
                    return;
                }
            }

            $dstmt = $pdo->prepare('SELECT id, original_name, mime_type, size, uploaded_at, description FROM documents WHERE file_id = ? ORDER BY uploaded_at DESC');
            $dstmt->execute([$id]);
            $docs = $dstmt->fetchAll();
        } catch (Throwable $e) {
            http_response_code(500);
            echo 'Server error: ' . htmlspecialchars($e->getMessage());
            return;
        }

        $title = 'File Details';
        $content = $this->renderView(__DIR__ . '/../Views/files/show.php', [ 'file' => $file, 'docs' => $docs ]);
        $this->renderLayout($title, $content);
    }

    private function renderLayout(string $title, string $content): void {
        $layoutPath = __DIR__ . '/../Views/layout.php';
        $pageTitle = $title;
        $pageContent = $content;
        include $layoutPath;
    }
    private function renderView(string $path, array $vars): string {
        extract($vars);
        ob_start(); include $path; return ob_get_clean();
    }
}
?>