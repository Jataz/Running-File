<?php
require_once __DIR__ . '/../../middleware.php';

class LoginController {
    public function index(): void {
        // If already logged in, send to dashboard
        $u = current_user();
        if ($u) {
            header('Location: /dashboard');
            return;
        }
        $title = 'Login';
        $content = $this->renderView(__DIR__ . '/../Views/login/index.php', []);
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