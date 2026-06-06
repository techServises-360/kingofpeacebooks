<?php
require_once __DIR__ . '/../app/config/config.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Method Not Allowed'); }
$db = Database::getInstance();
$bc = new BookController($db);
$bc->handleDelete();
