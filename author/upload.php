<?php
require_once __DIR__ . '/../app/config/config.php';
require_approved_author();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . BASE_URL . '/author/dashboard.php'); exit; }
$db = Database::getInstance();
$controller = new BookController($db);
$controller->handleAuthorUpload();
