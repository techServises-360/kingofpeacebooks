<?php
require_once __DIR__ . '/../app/config/config.php';
require_login();
verify_csrf();
$db = Database::getInstance();
$review_id = (int)($_POST['review_id'] ?? 0);
$book_id = (int)($_POST['book_id'] ?? 0);
$rating = (int)($_POST['rating'] ?? 0);
$comment = trim((string)($_POST['comment'] ?? ''));
$bookM = new Book($db);
$book = $book_id > 0 ? $bookM->find($book_id) : null;
$returnUrl = $book ? $bookM->publicUrl($book) : BASE_URL . '/public/books.php';

if ($review_id <= 0 || $book_id <= 0 || $rating <= 0 || $comment === '') {
  flash_set('error', 'Invalid review submission.');
  header('Location: ' . $returnUrl);
  exit;
}

$reviewM = new Review($db);
$review = $reviewM->getById($review_id);
if (!$review || (int)$review['user_id'] !== (int)current_user_id() || (int)$review['book_id'] !== $book_id) {
  http_response_code(403);
  exit('Forbidden');
}

$ok = $reviewM->update($review_id, (int)current_user_id(), $rating, $comment);
if ($ok) {
  flash_set('success', 'Your review was updated.');
} else {
  flash_set('error', 'Could not update review.');
}
header('Location: ' . $returnUrl);
