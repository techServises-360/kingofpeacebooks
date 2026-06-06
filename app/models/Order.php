<?php
class Order {
  private PDO $db;
  public function __construct(PDO $db) { $this->db = $db; }

  public function create(int $user_id, int $book_id, string $reference, float $amount, string $status = 'pending') {
    $maxRetries = 3;
    $retryCount = 0;
    
    while ($retryCount < $maxRetries) {
      try {
        $st = $this->db->prepare('INSERT INTO orders (user_id, book_id, paystack_reference, amount, status, created_at) VALUES (?,?,?,?,?,CURRENT_TIMESTAMP)');
        $st->execute([$user_id, $book_id, $reference, $amount, $status]);
        return (int)$this->db->lastInsertId();
      } catch (PDOException $e) {
        if ($e->getMessage() === 'SQLSTATE[HY000]: General error: 2006 MySQL server has gone away' && $retryCount < $maxRetries - 1) {
          // Reconnect to database
          $this->db = Database::getInstance();
          $retryCount++;
          usleep(100000); // Wait 0.1 seconds before retry
          continue;
        }
        throw $e;
      }
    }
  }

  public function findByReference(string $ref) {
    $maxRetries = 3;
    $retryCount = 0;
    
    while ($retryCount < $maxRetries) {
      try {
        $st = $this->db->prepare('SELECT * FROM orders WHERE paystack_reference = ? LIMIT 1');
        $st->execute([$ref]);
        return $st->fetch();
      } catch (PDOException $e) {
        if ($e->getMessage() === 'SQLSTATE[HY000]: General error: 2006 MySQL server has gone away' && $retryCount < $maxRetries - 1) {
          // Reconnect to database
          $this->db = Database::getInstance();
          $retryCount++;
          usleep(100000); // Wait 0.1 seconds before retry
          continue;
        }
        throw $e;
      }
    }
  }

  public function markPaid(string $ref) {
    $maxRetries = 3;
    $retryCount = 0;
    
    while ($retryCount < $maxRetries) {
      try {
        $st = $this->db->prepare("UPDATE orders SET status = 'paid', payment_date = COALESCE(payment_date, CURRENT_TIMESTAMP), updated_at = CURRENT_TIMESTAMP WHERE paystack_reference = ?");
        return $st->execute([$ref]);
      } catch (PDOException $e) {
        if ($e->getMessage() === 'SQLSTATE[HY000]: General error: 2006 MySQL server has gone away' && $retryCount < $maxRetries - 1) {
          // Reconnect to database
          $this->db = Database::getInstance();
          $retryCount++;
          usleep(100000); // Wait 0.1 seconds before retry
          continue;
        }
        throw $e;
      }
    }
  }

  public function all() {
    return $this->db->query('SELECT o.*, b.title, b.cover_image, u.email FROM orders o JOIN books b ON o.book_id=b.id JOIN users u ON o.user_id=u.id ORDER BY o.created_at DESC')->fetchAll();
  }

  public function byUser(int $user_id) {
    $maxRetries = 3;
    $retryCount = 0;
    
    while ($retryCount < $maxRetries) {
      try {
        $st = $this->db->prepare('SELECT o.*, b.title FROM orders o JOIN books b ON o.book_id=b.id WHERE o.user_id = ? ORDER BY o.created_at DESC');
        $st->execute([$user_id]);
        return $st->fetchAll();
      } catch (PDOException $e) {
        if ($e->getMessage() === 'SQLSTATE[HY000]: General error: 2006 MySQL server has gone away' && $retryCount < $maxRetries - 1) {
          // Reconnect to database
          $this->db = Database::getInstance();
          $retryCount++;
          usleep(100000); // Wait 0.1 seconds before retry
          continue;
        }
        throw $e;
      }
    }
  }

  public function hasPaid(int $user_id, int $book_id): bool {
    $maxRetries = 3;
    $retryCount = 0;
    
    while ($retryCount < $maxRetries) {
      try {
        $st = $this->db->prepare("SELECT 1 FROM orders WHERE user_id = ? AND book_id = ? AND status = 'paid' LIMIT 1");
        $st->execute([$user_id, $book_id]);
        return (bool)$st->fetchColumn();
      } catch (PDOException $e) {
        if ($e->getMessage() === 'SQLSTATE[HY000]: General error: 2006 MySQL server has gone away' && $retryCount < $maxRetries - 1) {
          // Reconnect to database
          $this->db = Database::getInstance();
          $retryCount++;
          usleep(100000); // Wait 0.1 seconds before retry
          continue;
        }
        throw $e;
      }
    }
  }

  public function paidWithBooksByUser(int $user_id) {
    $maxRetries = 3;
    $retryCount = 0;
    
    while ($retryCount < $maxRetries) {
      try {
        $st = $this->db->prepare("SELECT o.*, b.* FROM orders o JOIN books b ON o.book_id=b.id WHERE o.user_id = ? AND o.status = 'paid' ORDER BY o.created_at DESC");
        $st->execute([$user_id]);
        return $st->fetchAll();
      } catch (PDOException $e) {
        if ($e->getMessage() === 'SQLSTATE[HY000]: General error: 2006 MySQL server has gone away' && $retryCount < $maxRetries - 1) {
          // Reconnect to database
          $this->db = Database::getInstance();
          $retryCount++;
          usleep(100000); // Wait 0.1 seconds before retry
          continue;
        }
        throw $e;
      }
    }
  }

  public function countAll(): int {
    $maxRetries = 3;
    $retryCount = 0;
    
    while ($retryCount < $maxRetries) {
      try {
        $row = $this->db->query('SELECT COUNT(*) AS c FROM orders')->fetch();
        return (int)($row['c'] ?? 0);
      } catch (PDOException $e) {
        if ($e->getMessage() === 'SQLSTATE[HY000]: General error: 2006 MySQL server has gone away' && $retryCount < $maxRetries - 1) {
          // Reconnect to database
          $this->db = Database::getInstance();
          $retryCount++;
          usleep(100000); // Wait 0.1 seconds before retry
          continue;
        }
        throw $e;
      }
    }
  }

  public function countPaid(): int {
    $maxRetries = 3;
    $retryCount = 0;
    
    while ($retryCount < $maxRetries) {
      try {
        $row = $this->db->query("SELECT COUNT(*) AS c FROM orders WHERE status='paid'")->fetch();
        return (int)($row['c'] ?? 0);
      } catch (PDOException $e) {
        if ($e->getMessage() === 'SQLSTATE[HY000]: General error: 2006 MySQL server has gone away' && $retryCount < $maxRetries - 1) {
          // Reconnect to database
          $this->db = Database::getInstance();
          $retryCount++;
          usleep(100000); // Wait 0.1 seconds before retry
          continue;
        }
        throw $e;
      }
    }
  }

  public function revenueTotal(): float {
    $maxRetries = 3;
    $retryCount = 0;
    
    while ($retryCount < $maxRetries) {
      try {
        $row = $this->db->query("SELECT COALESCE(SUM(amount),0) AS s FROM orders WHERE status='paid'")->fetch();
        return (float)($row['s'] ?? 0);
      } catch (PDOException $e) {
        if ($e->getMessage() === 'SQLSTATE[HY000]: General error: 2006 MySQL server has gone away' && $retryCount < $maxRetries - 1) {
          // Reconnect to database
          $this->db = Database::getInstance();
          $retryCount++;
          usleep(100000); // Wait 0.1 seconds before retry
          continue;
        }
        throw $e;
      }
    }
  }

  // Author-centric helpers
  public function revenueByAuthor(int $authorUserId): float {
    $maxRetries = 3;
    $retryCount = 0;
    
    while ($retryCount < $maxRetries) {
      try {
        $st = $this->db->prepare("SELECT COALESCE(SUM(o.amount),0) AS s FROM orders o JOIN books b ON o.book_id=b.id WHERE o.status='paid' AND b.submitted_by = ?");
        $st->execute([$authorUserId]);
        $row = $st->fetch();
        return (float)($row['s'] ?? 0);
      } catch (PDOException $e) {
        if ($e->getMessage() === 'SQLSTATE[HY000]: General error: 2006 MySQL server has gone away' && $retryCount < $maxRetries - 1) {
          // Reconnect to database
          $this->db = Database::getInstance();
          $retryCount++;
          usleep(100000); // Wait 0.1 seconds before retry
          continue;
        }
        throw $e;
      }
    }
  }

  public function countPaidByAuthor(int $authorUserId): int {
    $maxRetries = 3;
    $retryCount = 0;
    
    while ($retryCount < $maxRetries) {
      try {
        $st = $this->db->prepare("SELECT COUNT(*) AS c FROM orders o JOIN books b ON o.book_id=b.id WHERE o.status='paid' AND b.submitted_by = ?");
        $st->execute([$authorUserId]);
        $row = $st->fetch();
        return (int)($row['c'] ?? 0);
      } catch (PDOException $e) {
        if ($e->getMessage() === 'SQLSTATE[HY000]: General error: 2006 MySQL server has gone away' && $retryCount < $maxRetries - 1) {
          // Reconnect to database
          $this->db = Database::getInstance();
          $retryCount++;
          usleep(100000); // Wait 0.1 seconds before retry
          continue;
        }
        throw $e;
      }
    }
  }

  public function countPaidForBook(int $bookId): int {
    $maxRetries = 3;
    $retryCount = 0;
    
    while ($retryCount < $maxRetries) {
      try {
        $st = $this->db->prepare("SELECT COUNT(*) AS c FROM orders WHERE status='paid' AND book_id = ?");
        $st->execute([$bookId]);
        $row = $st->fetch();
        return (int)($row['c'] ?? 0);
      } catch (PDOException $e) {
        if ($e->getMessage() === 'SQLSTATE[HY000]: General error: 2006 MySQL server has gone away' && $retryCount < $maxRetries - 1) {
          // Reconnect to database
          $this->db = Database::getInstance();
          $retryCount++;
          usleep(100000); // Wait 0.1 seconds before retry
          continue;
        }
        throw $e;
      }
    }
  }
}
