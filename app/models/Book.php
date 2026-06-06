<?php
class Book {
  private PDO $db;
  public function __construct(PDO $db) { $this->db = $db; }

  private function generatePublicId(): string {
    return 'bk_' . bin2hex(random_bytes(8));
  }

  private function createUniquePublicId(): string {
    try {
      do {
        $publicId = $this->generatePublicId();
        $st = $this->db->prepare('SELECT 1 FROM books WHERE public_id = ? LIMIT 1');
        $st->execute([$publicId]);
      } while ($st->fetch());

      return $publicId;
    } catch (Throwable $e) {
      return $this->generatePublicId();
    }
  }

  public function all() {
    return $this->db->query('SELECT * FROM books ORDER BY created_at DESC')->fetchAll();
  }

  // Return only approved books if status column exists; otherwise fallback to all
  public function allApproved() {
    try {
      $st = $this->db->query("SELECT * FROM books WHERE status = 'approved' ORDER BY created_at DESC");
      $rows = $st->fetchAll();
      // if query succeeded, use it
      return $rows;
    } catch (Throwable $e) {
      return $this->all();
    }
  }

  public function find(int $id) {
    $st = $this->db->prepare('SELECT * FROM books WHERE id = ?');
    $st->execute([$id]);
    return $st->fetch();
  }

  public function findByPublicId(string $publicId) {
    $st = $this->db->prepare('SELECT * FROM books WHERE public_id = ?');
    $st->execute([$publicId]);
    return $st->fetch();
  }

  public function publicUrl(array $book): string {
    if (!empty($book['public_id'])) {
      return BASE_URL . '/public/book.php?book=' . urlencode((string)$book['public_id']);
    }

    return BASE_URL . '/public/book.php?id=' . urlencode((string)$book['id']);
  }

  public function create(array $data) {
    $publicId = $this->createUniquePublicId();
    // Try to insert with moderation fields if present; fallback to legacy schema
    try {
      $sql = 'INSERT INTO books (public_id,title,author,price,base_price,discount_percentage,description,cover_image,file_path,status,created_at) VALUES (?,?,?,?,?,?,?,?,?,?,CURRENT_TIMESTAMP)';
      $st = $this->db->prepare($sql);
      $st->execute([
        $publicId, $data['title'], $data['author'], $data['price'], $data['base_price'] ?? null, $data['discount_percentage'] ?? 0, $data['description'], $data['cover_image'], $data['file_path'], 'pending'
      ]);
    } catch (Throwable $e) {
      // Fallback: schema without status but still has base_price/discount_percentage
      try {
        $sql = 'INSERT INTO books (public_id,title,author,price,base_price,discount_percentage,description,cover_image,file_path,created_at) VALUES (?,?,?,?,?,?,?,?,?,CURRENT_TIMESTAMP)';
        $st = $this->db->prepare($sql);
        $st->execute([
          $publicId, $data['title'], $data['author'], $data['price'], $data['base_price'] ?? null, $data['discount_percentage'] ?? 0, $data['description'], $data['cover_image'], $data['file_path']
        ]);
      } catch (Throwable $fallbackE) {
        // Fallback without new fields
        $sql = 'INSERT INTO books (title,author,price,description,cover_image,file_path,created_at) VALUES (?,?,?,?,?,?,CURRENT_TIMESTAMP)';
        $st = $this->db->prepare($sql);
        $st->execute([
          $data['title'], $data['author'], $data['price'], $data['description'], $data['cover_image'], $data['file_path']
        ]);
      }
    }
    return (int)$this->db->lastInsertId();
  }

  public function update(int $id, array $data) {
    // Try to update with new fields if present; fallback to legacy schema
    try {
      $sql = 'UPDATE books SET title=?, author=?, price=?, base_price=?, discount_percentage=?, description=?, cover_image=?, file_path=? WHERE id=?';
      $st = $this->db->prepare($sql);
      $result = $st->execute([
        $data['title'], $data['author'], $data['price'], $data['base_price'] ?? null, $data['discount_percentage'] ?? 0, $data['description'], $data['cover_image'], $data['file_path'], $id
      ]);
      
      if ($result) {
        return true;
      } else {
        // Log error for debugging
        error_log('Book update failed: ' . json_encode([
          'sql' => $sql,
          'data' => $data,
          'id' => $id,
          'error' => $st->errorInfo()
        ]));
        return false;
      }
    } catch (Throwable $e) {
      // Fallback without new fields
      try {
        $sql = 'UPDATE books SET title=?, author=?, price=?, description=?, cover_image=?, file_path=? WHERE id=?';
        $st = $this->db->prepare($sql);
        $result = $st->execute([
          $data['title'], $data['author'], $data['price'], $data['description'], $data['cover_image'], $data['file_path'], $id
        ]);
        
        if ($result) {
          return true;
        } else {
          error_log('Book update fallback failed: ' . json_encode([
            'sql' => $sql,
            'data' => $data,
            'id' => $id,
            'error' => $st->errorInfo()
          ]));
          return false;
        }
      } catch (Throwable $fallbackE) {
        error_log('Book update error: ' . $fallbackE->getMessage());
        return false;
      }
    }
  }

  public function delete(int $id) {
    try {
      $st = $this->db->prepare('DELETE FROM books WHERE id=?');
      $result = $st->execute([$id]);
      
      if ($result) {
        return true;
      } else {
        error_log('Book delete failed: ' . json_encode([
          'id' => $id,
          'error' => $st->errorInfo()
        ]));
        return false;
      }
    } catch (Throwable $e) {
      error_log('Book delete error: ' . $e->getMessage());
      return false;
    }
  }

  // Moderation helpers (safe if status column exists)
  public function approve(int $id, ?int $reviewedBy = null): bool {
    try {
      $st = $this->db->prepare("UPDATE books SET status='approved', reviewed_by=?, reviewed_at=NOW(), reject_reason=NULL WHERE id=?");
      return $st->execute([$reviewedBy, $id]);
    } catch (Throwable $e) {
      return false;
    }
  }

  public function reject(int $id, string $reason = '', ?int $reviewedBy = null): bool {
    try {
      $st = $this->db->prepare("UPDATE books SET status='rejected', reviewed_by=?, reviewed_at=NOW(), reject_reason=? WHERE id=?");
      return $st->execute([$reviewedBy, $reason, $id]);
    } catch (Throwable $e) {
      return false;
    }
  }

  public function count(): int {
    $row = $this->db->query('SELECT COUNT(*) AS c FROM books')->fetch();
    return (int)($row['c'] ?? 0);
  }

  public function recent(int $limit = 5) {
    // Prefer approved if status exists
    try {
      $st = $this->db->prepare("SELECT * FROM books WHERE status = 'approved' ORDER BY created_at DESC LIMIT ?");
    } catch (Throwable $e) {
      $st = $this->db->prepare('SELECT * FROM books ORDER BY created_at DESC LIMIT ?');
    }
    $st->bindValue(1, $limit, PDO::PARAM_INT);
    $st->execute();
    return $st->fetchAll();
  }

  public function searchByTitle(string $query) {
    $st = $this->db->prepare('SELECT * FROM books WHERE title LIKE ? ORDER BY created_at DESC');
    $st->execute(['%' . $query . '%']);
    return $st->fetchAll();
  }

  public function authors(): array {
    $rows = $this->db->query("SELECT DISTINCT author FROM books WHERE author IS NOT NULL AND author <> '' ORDER BY author ASC")->fetchAll();
    return array_map(fn($r) => $r['author'], $rows);
  }

  public function filterAndPaginate(array $opts): array {
    $q = trim((string)($opts['q'] ?? ''));
    $author = trim((string)($opts['author'] ?? ''));
    $min = isset($opts['min_price']) && $opts['min_price'] !== '' ? (float)$opts['min_price'] : null;
    $max = isset($opts['max_price']) && $opts['max_price'] !== '' ? (float)$opts['max_price'] : null;
    $sort = (string)($opts['sort'] ?? 'newest');
    $page = max(1, (int)($opts['page'] ?? 1));
    $perPage = min(48, max(8, (int)($opts['per_page'] ?? 12)));

    $where = [];
    $params = [];
    if ($q !== '') { $where[] = '(title LIKE ? OR author LIKE ?)'; $params[] = "%$q%"; $params[] = "%$q%"; }
    if ($author !== '') { $where[] = 'author = ?'; $params[] = $author; }
    if ($min !== null) { $where[] = 'price >= ?'; $params[] = $min; }
    if ($max !== null) { $where[] = 'price <= ?'; $params[] = $max; }
    $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

    // Enforce approved-only if column exists
    try {
      // quick probe to see if status column exists (cached by DB once parsed)
      $this->db->query("SELECT status FROM books LIMIT 1");
      $whereSql = $whereSql ? ($whereSql . " AND status = 'approved'") : "WHERE status = 'approved'";
    } catch (Throwable $e) {
      // ignore if column not present
    }

    switch ($sort) {
      case 'price_asc': $order = 'ORDER BY price ASC, created_at DESC'; break;
      case 'price_desc': $order = 'ORDER BY price DESC, created_at DESC'; break;
      case 'title_asc': $order = 'ORDER BY title ASC'; break;
      case 'title_desc': $order = 'ORDER BY title DESC'; break;
      default: $order = 'ORDER BY created_at DESC'; // newest
    }

    // total count
    $countSql = "SELECT COUNT(*) AS c FROM books $whereSql";
    $stc = $this->db->prepare($countSql);
    $stc->execute($params);
    $total = (int)($stc->fetch()['c'] ?? 0);

    // items
    $offset = ($page - 1) * $perPage;
    $sql = "SELECT * FROM books $whereSql $order LIMIT ? OFFSET ?";
    $sti = $this->db->prepare($sql);
    $bindParams = $params;
    $sti->bindValue(1 + count($bindParams), $perPage, PDO::PARAM_INT);
    $sti->bindValue(2 + count($bindParams), $offset, PDO::PARAM_INT);
    // need to execute with all params in order
    $allParams = array_merge($params, [$perPage, $offset]);
    $sti->execute($allParams);
    $items = $sti->fetchAll();

    return [ 'items' => $items, 'total' => $total, 'page' => $page, 'per_page' => $perPage ];
  }
}
