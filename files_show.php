<?php
require_once __DIR__ . '/app/Controllers/FilesController.php';
$id = 0;
if (isset($_GET['id'])) { $id = (int)$_GET['id']; }
// If the router sets id via path segment, it will be in $_GET['id']
$controller = new FilesController();
$controller->show($id);
?>