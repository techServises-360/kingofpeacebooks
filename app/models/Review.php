<?php
class Review {
  private PDO $db;
  public function __construct(PDO $db) { $this->db = $db; }

  public function countForBook(int $book_id): int {
    $st = $this->db->prepare('SELECT COUNT(*) AS c FROM reviews WHERE book_id = ?');
    $st->execute([$book_id]);
    $row = $st->fetch();
    return (int)($row['c'] ?? 0);
  }

  public function listForBook(int $book_id, int $limit = 20): array {
    $st = $this->db->prepare('SELECT r.*, u.name FROM reviews r JOIN users u ON r.user_id=u.id WHERE r.book_id = ? ORDER BY r.created_at DESC LIMIT ?');
    $st->bindValue(1, $book_id, PDO::PARAM_INT);
    $st->bindValue(2, $limit, PDO::PARAM_INT);
    $st->execute();
    return $st->fetchAll();
  }

  public function listForBookPaginated(int $book_id, int $offset, int $limit, string $sort = 'newest'): array {
    $order = 'r.created_at DESC';
    if ($sort === 'oldest') $order = 'r.created_at ASC';
    if ($sort === 'rating_high') $order = 'r.rating DESC, r.created_at DESC';
    if ($sort === 'rating_low') $order = 'r.rating ASC, r.created_at DESC';
    $sql = "SELECT r.*, u.name FROM reviews r JOIN users u ON r.user_id=u.id WHERE r.book_id = :bid ORDER BY $order LIMIT :limit OFFSET :offset";
    $st = $this->db->prepare($sql);
    $st->bindValue(':bid', $book_id, PDO::PARAM_INT);
    $st->bindValue(':limit', $limit, PDO::PARAM_INT);
    $st->bindValue(':offset', $offset, PDO::PARAM_INT);
    $st->execute();
    return $st->fetchAll();
  }

  public function findUserReview(int $book_id, int $user_id): ?array {
    $st = $this->db->prepare('SELECT r.*, u.name FROM reviews r JOIN users u ON r.user_id=u.id WHERE r.book_id = ? AND r.user_id = ? LIMIT 1');
    $st->execute([$book_id, $user_id]);
    $row = $st->fetch();
    return $row ?: null;
  }

  public function create(int $user_id, int $book_id, int $rating, string $comment): int {
    $rating = max(1, min(5, $rating));
    $st = $this->db->prepare('INSERT INTO reviews (user_id, book_id, rating, comment, created_at) VALUES (?,?,?,?,NOW())');
    $st->execute([$user_id, $book_id, $rating, $comment]);
    return (int)$this->db->lastInsertId();
  }

  public function getById(int $id): ?array {
    $st = $this->db->prepare('SELECT * FROM reviews WHERE id = ?');
    $st->execute([$id]);
    $row = $st->fetch();
    return $row ?: null;
  }

  public function update(int $id, int $user_id, int $rating, string $comment): bool {
    $rating = max(1, min(5, $rating));
    $st = $this->db->prepare('UPDATE reviews SET rating = ?, comment = ? WHERE id = ? AND user_id = ?');
    return $st->execute([$rating, $comment, $id, $user_id]);
  }
}
