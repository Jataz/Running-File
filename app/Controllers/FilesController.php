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