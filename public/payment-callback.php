<?php
require_once __DIR__ . '/../app/config/config.php';
$db = Database::getInstance();
$pc = new PaymentController($db);
$pc->callback();
