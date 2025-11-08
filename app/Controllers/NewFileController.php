<?php
require_once __DIR__ . '/../../middleware.php';

class NewFileController {
    public function index(): void {
        require_auth();
        $u = current_user();
        $title = 'Create New File';
        $content = $this->renderView(__DIR__ . '/../Views/newfile/index.php', [ 'user' => $u ]);
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