<?php
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../middleware.php';

class DashboardController {
    public function index(): void {
        require_auth();
        $u = current_user();
        try {
            $pdo = get_pdo();
            $total = (int)$pdo->query("SELECT COUNT(*) AS c FROM files")->fetch()['c'];
            $byStatus = $pdo->query("SELECT status, COUNT(*) AS c FROM files GROUP BY status")->fetchAll();
            $recent = $pdo->query("SELECT id, ref, subject, owner, status, due_date, created_at FROM files ORDER BY created_at DESC LIMIT 10")->fetchAll();
        } catch (Throwable $e) {
            http_response_code(500);
            echo 'Server error: ' . htmlspecialchars($e->getMessage());
            return;
        }
        $title = 'Dashboard';
        $content = $this->renderView(__DIR__ . '/../Views/dashboard/index.php', [ 'total' => $total, 'byStatus' => $byStatus, 'recent' => $recent ]);
        $this->renderLayout($title, $content, 'light');
    }

    private function renderLayout(string $title, string $content, string $theme = ''): void {
        $layoutPath = __DIR__ . '/../Views/layout.php';
        $pageTitle = $title;
        $pageContent = $content;
        $theme = $theme; // pass theme to layout for optional light styling
        include $layoutPath;
    }
    private function renderView(string $path, array $vars): string {
        extract($vars);
        ob_start(); include $path; return ob_get_clean();
    }
}
?>