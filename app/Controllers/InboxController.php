<?php
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../middleware.php';

class InboxController {
    public function index(): void {
        require_auth();
        $u = current_user();
        try {
            $pdo = get_pdo();
            $deptId = (int)($u['department_id'] ?? 0);
            $stmt = $pdo->prepare("SELECT f.id, f.ref, f.subject, f.owner, f.status, f.due_date,
                                          GROUP_CONCAT(d.name SEPARATOR ', ') AS departments
                                     FROM files f
                                     JOIN file_departments fd ON fd.file_id = f.id
                                     JOIN departments d ON d.id = fd.department_id
                                    WHERE fd.department_id = ?
                                    GROUP BY f.id
                                    ORDER BY f.created_at DESC");
            $stmt->execute([$deptId]);
            $items = $stmt->fetchAll();
        } catch (Throwable $e) {
            http_response_code(500);
            echo 'Server error: ' . htmlspecialchars($e->getMessage());
            return;
        }
        $title = 'Inbox';
        $content = $this->renderView(__DIR__ . '/../Views/inbox/index.php', [ 'items' => $items ]);
        $this->renderLayout($title, $content);
    }

    private function renderLayout(string $title, string $content): void {
        $layoutPath = __DIR__ . '/../Views/layout.php';
        $pageTitle = $title; $pageContent = $content; include $layoutPath;
    }
    private function renderView(string $path, array $vars): string {
        extract($vars); ob_start(); include $path; return ob_get_clean();
    }
}
?>