<?php
require_once __DIR__ . '/app/Controllers/OutboxController.php';
$controller = new OutboxController();
$controller->index();
?>