<?php
require_once __DIR__ . '/../../middleware.php';
class ReportsController { public function index(): void { require_auth(); $title='Reports'; $content=$this->renderView(__DIR__.'/../Views/reports/index.php',[]); $this->renderLayout($title,$content);} private function renderLayout(string $t,string $c): void { $pageTitle=$t; $pageContent=$c; include __DIR__.'/../Views/layout.php'; } private function renderView(string $p,array $v): string { extract($v); ob_start(); include $p; return ob_get_clean(); } }
?>