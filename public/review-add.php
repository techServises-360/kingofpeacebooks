<?php
require_once __DIR__ . '/../app/config/config.php';
require_login();
verify_csrf();
$db = Database::getInstance();
$book_id = (int)($_POST['book_id'] ?? 0);
$rating = (int)($_POST['rating'] ?? 0);
$comment = trim((string)($_POST['comment'] ?? ''));
$bookM = new Book($db);
$book = $book_id > 0 ? $bookM->find($book_id) : null;
$returnUrl = $book ? $bookM->publicUrl($book) : BASE_URL . '/public/books.php';

if ($book_id <= 0 || $rating <= 0 || $comment === '') {
  flash_set('error', 'Please provide rating and comment.');
  header('Location: ' . $returnUrl);
  exit;
}

if (!$book) {
  http_response_code(404);
  exit('Book not found');
}

$reviewM = new Review($db);
$reviewM->create((int)current_user_id(), $book_id, $rating, $comment);
flash_set('success', 'Thanks for your review!');
header('Location: ' . $returnUrl);
