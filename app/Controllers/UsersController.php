<?php
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../middleware.php';

class UsersController {
    public function index(): void {
        require_auth();
        $u = current_user();
        if (!$u || ($u['class'] ?? '') !== 'E') {
            // Hide unauthorized access instead of 403
            http_response_code(404);
            echo 'Not Found';
            return;
        }
        try {
            $pdo = get_pdo();
            $items = $pdo->query("SELECT u.id, u.username, u.class, u.active, d.name AS dept_name, u.department_id FROM users u LEFT JOIN departments d ON d.id = u.department_id ORDER BY u.username ASC")->fetchAll();
            $deps = $pdo->query("SELECT id, name FROM departments ORDER BY name ASC")->fetchAll();
        } catch (Throwable $e) {
            http_response_code(500);
            echo 'Server error: ' . htmlspecialchars($e->getMessage());
            return;
        }
        $title = 'User Management';
        $content = $this->renderView(__DIR__ . '/../Views/users/index.php', [ 'items' => $items, 'deps' => $deps ]);
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
        ob_start();
        include $path;
        return ob_get_clean();
    }
}
?>