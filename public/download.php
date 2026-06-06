<?php
require_once __DIR__ . '/../app/config/config.php';
require_login();

$db = Database::getInstance();
$orders = new Order($db);
$books = new Book($db);

$book_id = (int)($_GET['book_id'] ?? 0);
if (!$book_id) { http_response_code(400); exit('Invalid request'); }

$book = $books->find($book_id);
if (!$book) { http_response_code(404); exit('Not found'); }

if (!$orders->hasPaid(current_user_id(), $book_id)) {
  http_response_code(403); exit('Payment required');
}


$relPath = (string)($book['file_path'] ?? '');
if ($relPath === '') { http_response_code(404); exit('File missing'); }

// Supabase: file_path stored as object key (e.g. "book_....pdf")
$storage = SupabaseStorage::fromEnv();
if ($storage) {
  $signed = $storage->signedBookUrlForFilePath($relPath, 600);
  if ($signed !== '') {
    header('Location: ' . $signed, true, 302);
    exit;
  }
  if (SupabaseStorage::looksLikeObjectKey($relPath)) {
    http_response_code(404); exit('File missing');
  }
}

// Legacy local fallback (Render filesystem may not persist)
$fullPath = realpath(__DIR__ . '/../' . $relPath);
if (!$fullPath || !is_file($fullPath)) { http_response_code(404); exit('File missing'); }

$filename = basename($fullPath);
$filesize = filesize($fullPath);

header('Content-Description: File Transfer');
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Transfer-Encoding: binary');
header('Content-Length: ' . $filesize);
header('Cache-Control: private, no-transform, no-store, must-revalidate');

$fp = fopen($fullPath, 'rb');
if ($fp) {
  while (!feof($fp)) {
    echo fread($fp, 8192);
    @ob_flush();
    flush();
  }
  fclose($fp);
}
exit;
