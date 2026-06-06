<?php
require_once __DIR__ . '/../app/config/config.php';
require_login();
$db = Database::getInstance();
$pc = new PaymentController($db);
$book_id = (int)($_GET['book_id'] ?? 0);
if ($book_id) { $pc->initiate($book_id); exit; }
http_response_code(400); echo 'Invalid request';
