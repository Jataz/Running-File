<?php
require_once __DIR__ . '/../../middleware.php';
class AuditController { public function index(): void { require_auth(); $title='Audit Log'; $content=$this->renderView(__DIR__.'/../Views/audit/index.php',[]); $this->renderLayout($title,$content);} private function renderLayout(string $t,string $c): void { $pageTitle=$t; $pageContent=$c; include __DIR__.'/../Views/layout.php'; } private function renderView(string $p,array $v): string { extract($v); ob_start(); include $p; return ob_get_clean(); } }
?>