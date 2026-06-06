<?php
class BookController {
  private Book $books;
  public function __construct(PDO $db) { $this->books = new Book($db); }

  private function storage(): ?SupabaseStorage {
    return SupabaseStorage::fromEnv();
  }

  private function isRemotePath(?string $path): bool {
    if (!$path) { return false; }
    return (bool)preg_match('#^https?://#i', $path);
  }

  public function list() { return $this->books->all(); }
  public function show(int $id) { return $this->books->find($id); }

  public function handleUpload() {
    verify_csrf();
    require_admin();
    $title = trim((string)($_POST['title'] ?? ''));
    $author = trim((string)($_POST['author'] ?? ''));
    $basePrice = (float)($_POST['base_price'] ?? 0);
    $discountPercentage = (float)($_POST['discount_percentage'] ?? 0);
    $finalPrice = $basePrice * (1 - $discountPercentage / 100);
    $description = trim((string)($_POST['description'] ?? ''));

    $cover = $_FILES['cover_image'] ?? null;
    $file = $_FILES['book_file'] ?? null;

    $coverPath = '';
    $filePath = '';

    $storage = $this->storage();

    if ($cover && $cover['tmp_name']) {
      $ext = pathinfo($cover['name'], PATHINFO_EXTENSION);
      $name = 'cover_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
      $tmp = $cover['tmp_name'];
      $contentType = $cover['type'] ?? '';
      if ($contentType === '' && is_file($tmp)) { $contentType = mime_content_type($tmp) ?: ''; }
      if ($storage && $storage->upload(SUPABASE_BUCKET_COVERS, $name, $tmp, $contentType ?: 'application/octet-stream', true)) {
        $coverPath = $storage->publicUrl(SUPABASE_BUCKET_COVERS, $name);
      } else {
        $dest = __DIR__ . '/../../assets/images/' . $name;
        if (!is_dir(dirname($dest))) { mkdir(dirname($dest), 0775, true); }
        move_uploaded_file($tmp, $dest);
        $coverPath = 'assets/images/' . $name;
      }
    }

    if ($file && $file['tmp_name']) {
      $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
      $name = 'book_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
      $tmp = $file['tmp_name'];
      $contentType = $file['type'] ?? '';
      if ($contentType === '' && is_file($tmp)) { $contentType = mime_content_type($tmp) ?: ''; }
      if ($storage && $storage->upload(SUPABASE_BUCKET_BOOKS, $name, $tmp, $contentType ?: 'application/octet-stream', true)) {
        $filePath = $name;
      } else {
        $dest = __DIR__ . '/../../app/storage/books/' . $name;
        if (!is_dir(dirname($dest))) { mkdir(dirname($dest), 0775, true); }
        move_uploaded_file($tmp, $dest);
        $filePath = 'app/storage/books/' . $name;
      }
    }

    $this->books->create([
      'title' => $title,
      'author' => $author,
      'price' => $finalPrice,
      'base_price' => $basePrice,
      'discount_percentage' => $discountPercentage,
      'description' => $description,
      'cover_image' => $coverPath,
      'file_path' => $filePath,
    ]);
    flash_set('success', 'Book uploaded successfully.');
    header('Location: ' . BASE_URL . '/admin/manage-books.php');
    exit;
  }

  public function handleAuthorUpload() {
    verify_csrf();
    require_approved_author();
    $title = trim((string)($_POST['title'] ?? ''));
    $author = trim((string)($_POST['author'] ?? ''));
    if ($author === '' && function_exists('current_user_id')) {
      // Load user to use their name as author display
      $db = Database::getInstance();
      $u = (new User($db))->find((int)current_user_id());
      if ($u && !empty($u['name'])) { $author = $u['name']; }
    }
    $basePrice = (float)($_POST['base_price'] ?? 0);
    $discountPercentage = (float)($_POST['discount_percentage'] ?? 0);
    $finalPrice = $basePrice * (1 - $discountPercentage / 100);
    $description = trim((string)($_POST['description'] ?? ''));

    $cover = $_FILES['cover_image'] ?? null;
    $file = $_FILES['book_file'] ?? null;

    $coverPath = '';
    $filePath = '';

    $storage = $this->storage();

    if ($cover && $cover['tmp_name']) {
      $ext = pathinfo($cover['name'], PATHINFO_EXTENSION);
      $name = 'cover_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
      $tmp = $cover['tmp_name'];
      $contentType = $cover['type'] ?? '';
      if ($contentType === '' && is_file($tmp)) { $contentType = mime_content_type($tmp) ?: ''; }
      if ($storage && $storage->upload(SUPABASE_BUCKET_COVERS, $name, $tmp, $contentType ?: 'application/octet-stream', true)) {
        $coverPath = $storage->publicUrl(SUPABASE_BUCKET_COVERS, $name);
      } else {
        $dest = __DIR__ . '/../../assets/images/' . $name;
        if (!is_dir(dirname($dest))) { mkdir(dirname($dest), 0775, true); }
        move_uploaded_file($tmp, $dest);
        $coverPath = 'assets/images/' . $name;
      }
    }

    if ($file && $file['tmp_name']) {
      $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
      $name = 'book_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
      $tmp = $file['tmp_name'];
      $contentType = $file['type'] ?? '';
      if ($contentType === '' && is_file($tmp)) { $contentType = mime_content_type($tmp) ?: ''; }
      if ($storage && $storage->upload(SUPABASE_BUCKET_BOOKS, $name, $tmp, $contentType ?: 'application/octet-stream', true)) {
        $filePath = $name;
      } else {
        $dest = __DIR__ . '/../../app/storage/books/' . $name;
        if (!is_dir(dirname($dest))) { mkdir(dirname($dest), 0775, true); }
        move_uploaded_file($tmp, $dest);
        $filePath = 'app/storage/books/' . $name;
      }
    }

    $this->books->create([
      'title' => $title,
      'author' => $author,
      'price' => $finalPrice,
      'base_price' => $basePrice,
      'discount_percentage' => $discountPercentage,
      'description' => $description,
      'cover_image' => $coverPath,
      'file_path' => $filePath,
    ]);
    flash_set('success', 'Book submitted for review successfully.');
    header('Location: ' . BASE_URL . '/author/dashboard.php');
    exit;
  }

  public function handleUpdate() {
    verify_csrf();
    require_admin();
    $id = (int)($_POST['id'] ?? 0);
    $existing = $this->books->find($id);
    if (!$existing) { http_response_code(404); exit('Book not found'); }

    $title = trim((string)($_POST['title'] ?? $existing['title']));
    $author = trim((string)($_POST['author'] ?? $existing['author']));
    $basePrice = (float)($_POST['base_price'] ?? $existing['base_price'] ?? $existing['price']);
    $discountPercentage = (float)($_POST['discount_percentage'] ?? $existing['discount_percentage'] ?? 0);
    $finalPrice = $basePrice * (1 - $discountPercentage / 100);
    $description = trim((string)($_POST['description'] ?? $existing['description']));

    $coverPath = $existing['cover_image'];
    $filePath = $existing['file_path'];

    $storage = $this->storage();

    if (!empty($_FILES['cover_image']['tmp_name'])) {
      $ext = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
      $name = 'cover_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
      $tmp = $_FILES['cover_image']['tmp_name'];
      $contentType = $_FILES['cover_image']['type'] ?? '';
      if ($contentType === '' && is_file($tmp)) { $contentType = mime_content_type($tmp) ?: ''; }
      if ($storage && $storage->upload(SUPABASE_BUCKET_COVERS, $name, $tmp, $contentType ?: 'application/octet-stream', true)) {
        if (!empty($coverPath) && !$this->isRemotePath($coverPath)) {
          $old = __DIR__ . '/../../' . $coverPath;
          if (is_file($old)) { @unlink($old); }
        }
        $coverPath = $storage->publicUrl(SUPABASE_BUCKET_COVERS, $name);
      } else {
        $dest = __DIR__ . '/../../assets/images/' . $name;
        if (!is_dir(dirname($dest))) { mkdir(dirname($dest), 0775, true); }
        if (move_uploaded_file($tmp, $dest)) {
          if (!empty($coverPath) && !$this->isRemotePath($coverPath)) {
            $old = __DIR__ . '/../../' . $coverPath;
            if (is_file($old)) { @unlink($old); }
          }
          $coverPath = 'assets/images/' . $name;
        }
      }
    }

    if (!empty($_FILES['book_file']['tmp_name'])) {
      $ext = pathinfo($_FILES['book_file']['name'], PATHINFO_EXTENSION);
      $name = 'book_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
      $tmp = $_FILES['book_file']['tmp_name'];
      $contentType = $_FILES['book_file']['type'] ?? '';
      if ($contentType === '' && is_file($tmp)) { $contentType = mime_content_type($tmp) ?: ''; }
      if ($storage && $storage->upload(SUPABASE_BUCKET_BOOKS, $name, $tmp, $contentType ?: 'application/octet-stream', true)) {
        if (!empty($filePath) && !$this->isRemotePath($filePath) && strpos($filePath, 'app/storage/') === 0) {
          $old = __DIR__ . '/../../' . $filePath;
          if (is_file($old)) { @unlink($old); }
        }
        $filePath = $name;
      } else {
        $dest = __DIR__ . '/../../app/storage/books/' . $name;
        if (!is_dir(dirname($dest))) { mkdir(dirname($dest), 0775, true); }
        if (move_uploaded_file($tmp, $dest)) {
          if (!empty($filePath) && !$this->isRemotePath($filePath) && strpos($filePath, 'app/storage/') === 0) {
            $old = __DIR__ . '/../../' . $filePath;
            if (is_file($old)) { @unlink($old); }
          }
          $filePath = 'app/storage/books/' . $name;
        }
      }
    }

    $this->books->update($id, [
      'title' => $title,
      'author' => $author,
      'price' => $finalPrice,
      'base_price' => $basePrice,
      'discount_percentage' => $discountPercentage,
      'description' => $description,
      'cover_image' => $coverPath,
      'file_path' => $filePath,
    ]);
    
    flash_set('success', 'Book updated successfully.');
    header('Location: ' . BASE_URL . '/admin/manage-books.php');
    exit;
  }

  public function handleDelete() {
    verify_csrf();
    require_admin();
    $id = (int)($_POST['id'] ?? 0);
    $existing = $this->books->find($id);
    if ($existing) {
      // delete files
      if (!empty($existing['cover_image'])) {
        if (!$this->isRemotePath($existing['cover_image'])) {
          $p = __DIR__ . '/../../' . $existing['cover_image'];
          if (is_file($p)) { @unlink($p); }
        }
      }
      if (!empty($existing['file_path'])) {
        if (!$this->isRemotePath($existing['file_path']) && strpos($existing['file_path'], 'app/storage/') === 0) {
          $p = __DIR__ . '/../../' . $existing['file_path'];
          if (is_file($p)) { @unlink($p); }
        }
      }
      
      if ($this->books->delete($id)) {
        flash_set('success', 'Book deleted successfully.');
      } else {
        flash_set('error', 'Failed to delete book. Please try again.');
      }
    } else {
      flash_set('error', 'Book not found.');
    }
    header('Location: ' . BASE_URL . '/admin/manage-books.php');
    exit;
  }
}
