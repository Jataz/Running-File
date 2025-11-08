<?php
require_once __DIR__ . '/../app/Controllers/UsersController.php';

$controller = new UsersController();
$controller->index();
?>