<?php
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/helpers/auth.php';
require_login();

$db = Database::getInstance();
require_once __DIR__ . '/../app/models/Book.php';

$bookId = (int)($_GET['book_id'] ?? 0);
if ($bookId <= 0) { http_response_code(400); exit('Invalid request'); }

$book = (new Book($db))->find($bookId);
if (!$book) { http_response_code(404); exit('File not found'); }

// Permissions: admin OR submitter may view; otherwise 403
$allowed = false;
if (function_exists('is_admin') && is_admin()) { $allowed = true; }
elseif (function_exists('is_logged_in') && is_logged_in() && isset($book['submitted_by']) && (int)$book['submitted_by'] === (int)current_user_id()) { $allowed = true; }
if (!$allowed) { http_response_code(403); exit('Forbidden'); }

$relPath = $book['file_path'] ?? '';
if (!$relPath) { http_response_code(404); exit('No file attached'); }
$relPath = (string)$relPath;

// Supabase: file_path stored as object key (e.g. "book_....pdf")
$storage = SupabaseStorage::fromEnv();
if ($storage) {
  $signed = $storage->signedBookUrlForFilePath($relPath, 600);
  if ($signed !== '') {
    header('Location: ' . $signed, true, 302);
    exit;
  }
  if (SupabaseStorage::looksLikeObjectKey($relPath)) {
    http_response_code(404); exit('File not found');
  }
}

$absPath = realpath(__DIR__ . '/../' . $relPath);
if (!$absPath || !is_file($absPath)) { http_response_code(404); exit('File not found'); }

$ext = strtolower(pathinfo($absPath, PATHINFO_EXTENSION));
$mime = 'application/octet-stream';
$inline = false;
switch ($ext) {
  case 'pdf': $mime = 'application/pdf'; $inline = true; break;
  case 'epub': $mime = 'application/epub+zip'; break;
  case 'txt': $mime = 'text/plain'; $inline = true; break;
}

header('Content-Type: ' . $mime);
$disposition = $inline ? 'inline' : 'attachment';
header('Content-Disposition: ' . $disposition . '; filename="' . basename($absPath) . '"');
header('Content-Length: ' . filesize($absPath));
header('X-Content-Type-Options: nosniff');

$fp = fopen($absPath, 'rb');
while (!feof($fp)) { echo fread($fp, 8192); }
fclose($fp);
