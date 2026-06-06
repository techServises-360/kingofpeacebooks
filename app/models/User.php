<?php
class User {
  private PDO $db;
  public function __construct(PDO $db) { $this->db = $db; }

  public function findByEmail(string $email) {
    $st = $this->db->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $st->execute([$email]);
    return $st->fetch();
  }

  public function requestAuthor(int $userId): bool {
    try {
      $st = $this->db->prepare("UPDATE users SET role='author', requested_author=TRUE, author_status='pending', author_requested_at=NOW() WHERE id=?");
      return $st->execute([$userId]);
    } catch (Throwable $e) {
      error_log('requestAuthor failed: ' . $e->getMessage());

      // Older schemas may not have author request columns; fallback to role update
      try {
        $st = $this->db->prepare("UPDATE users SET role='author' WHERE id=?");
        return $st->execute([$userId]);
      } catch (Throwable $e2) {
        error_log('requestAuthor fallback failed: ' . $e2->getMessage());
        return false;
      }
    }
  }

  public function create(string $name, string $email, string $password, string $role = 'user', bool $requestedAuthor = false) {
    // First check if email already exists
    if ($this->findByEmail($email)) {
        throw new Exception('Email already exists');
    }
    
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Create user immediately in database but mark as unverified
    try {
        if ($requestedAuthor) {
            $sql = "INSERT INTO users (name,email,password,role,requested_author,author_status,author_requested_at,email_verified,created_at)
                    VALUES (?,?,?,?,TRUE,'pending',CURRENT_TIMESTAMP,FALSE,CURRENT_TIMESTAMP)";
            $st = $this->db->prepare($sql);
            $st->execute([$name, $email, $hash, 'author']);
        } else {
            $sql = 'INSERT INTO users (name,email,password,role,email_verified,created_at) VALUES (?,?,?,?,FALSE,CURRENT_TIMESTAMP)';
            $st = $this->db->prepare($sql);
            $st->execute([$name, $email, $hash, $role]);
        }
        return (int)$this->db->lastInsertId();
    } catch (Throwable $e) {
        // Try legacy schema if new schema fails
        try {
            if ($requestedAuthor) {
                $sql = "INSERT INTO users (name,email,password,role,requested_author,author_status,author_requested_at,email_verified,created_at)
                        VALUES (?,?,?,?,TRUE,'pending',CURRENT_TIMESTAMP,FALSE,CURRENT_TIMESTAMP)";
                $st = $this->db->prepare($sql);
                $st->execute([$name, $email, $hash, 'author']);
            } else {
                $sql = 'INSERT INTO users (name,email,password,role,email_verified,created_at) VALUES (?,?,?,?,FALSE,CURRENT_TIMESTAMP)';
                $st = $this->db->prepare($sql);
                $st->execute([$name, $email, $hash, $role]);
            }
            return (int)$this->db->lastInsertId();
        } catch (Throwable $fallbackE) {
            // Try basic schema without email verification columns
            try {
                $sql = 'INSERT INTO users (name,email,password,role,created_at) VALUES (?,?,?,?,?)';
                $st = $this->db->prepare($sql);
                $st->execute([$name, $email, $hash, $role, date('Y-m-d H:i:s')]);
                return (int)$this->db->lastInsertId();
            } catch (Throwable $basicE) {
                // Display actual database error for debugging
                throw new Exception('Database Error: ' . $basicE->getMessage() . ' | SQL: ' . $sql . ' | Values: ' . json_encode([$name, $email, $hash, $role, date('Y-m-d H:i:s')]));
            }
        }
    }
  }

  public function createVerified(string $name, string $email, string $hash, string $role, bool $requestedAuthor) {
    // Attempt extended insert if author-request columns exist; fallback to legacy schema
    if ($requestedAuthor) {
      try {
        $sql = "INSERT INTO users (name,email,password,role,requested_author,author_status,author_requested_at,email_verified,created_at)
                VALUES (?,?,?,?,TRUE,'pending',CURRENT_TIMESTAMP,TRUE,CURRENT_TIMESTAMP)";
        $st = $this->db->prepare($sql);
        $st->execute([$name, $email, $hash, 'author']);
        return (int)$this->db->lastInsertId();
      } catch (Throwable $e) {
        // fall through to baseline insert
      }
    }
    
    // Create verified user
    try {
        $sql = 'INSERT INTO users (name,email,password,role,email_verified,created_at) VALUES (?,?,?,?,TRUE,CURRENT_TIMESTAMP)';
        $st = $this->db->prepare($sql);
        $st->execute([$name, $email, $hash, $role]);
        return (int)$this->db->lastInsertId();
    } catch (Throwable $e) {
      // Fallback to legacy insert
      $st = $this->db->prepare('INSERT INTO users (name,email,password,role,created_at) VALUES (?,?,?,?,CURRENT_TIMESTAMP)');
      $st->execute([$name, $email, $hash, $role]);
      return (int)$this->db->lastInsertId();
    }
  }

  public function generateTempVerificationCode(string $tempId, string $email): string {
    $code = strtoupper(substr(md5($tempId . $email . time() . rand(1000, 9999)), 0, 6));
    $expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));
    
    // Store in session for verification
    $_SESSION['temp_verification'] = [
        'temp_id' => $tempId,
        'email' => $email,
        'code' => $code,
        'expires' => $expires
    ];
    
    return $code;
  }

  public function verifyTempUser(string $tempId, string $code, string $email): bool {
    if (!isset($_SESSION['temp_verification'])) {
        return false;
    }
    
    $temp = $_SESSION['temp_verification'];
    
    // Verify temp ID, code, email, and expiration
    if ($temp['temp_id'] !== $tempId || 
        $temp['code'] !== $code || 
        $temp['email'] !== $email || 
        strtotime($temp['expires']) < time()) {
        return false;
    }
    
    return true;
  }

  public function createFromTemp(string $tempId): int {
    if (!isset($_SESSION['temp_verification']) || 
        $_SESSION['temp_verification']['temp_id'] !== $tempId) {
        throw new Exception('Invalid temporary user session');
        }
        
        $temp = $_SESSION['temp_user'];
        
        // Create the actual user record
        $userId = $this->createVerified(
            $temp['name'],
            $temp['email'], 
            $temp['password'],
            $temp['role'],
            isset($temp['requested_author']) && $temp['requested_author']
        );
        
        // Clear temporary session data
        unset($_SESSION['temp_verification']);
        unset($_SESSION['temp_user']);
        
        return $userId;
    }

  public function find(int $id) {
    $st = $this->db->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
    $st->execute([$id]);
    return $st->fetch();
  }

  // Admin moderation for author requests
  public function approveAuthor(int $id, ?int $reviewedBy = null): bool {
    try {
      // Set role to author and mark approved
      $st = $this->db->prepare("UPDATE users SET role='author', author_status='approved', author_reviewed_by=?, author_reviewed_at=NOW(), author_reject_reason=NULL WHERE id=?");
      return $st->execute([$reviewedBy, $id]);
    } catch (Throwable $e) { return false; }
  }

  public function rejectAuthor(int $id, string $reason = '', ?int $reviewedBy = null): bool {
    try {
      // Keep role as user and mark rejected
      $st = $this->db->prepare("UPDATE users SET role='user', author_status='rejected', author_reviewed_by=?, author_reviewed_at=NOW(), author_reject_reason=? WHERE id=?");
      return $st->execute([$reviewedBy, $reason, $id]);
    } catch (Throwable $e) { return false; }
  }

  // Email verification for authors
  public function generateVerificationCode(int $userId): string {
    $code = (string)random_int(100000, 999999);
    $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
    
    try {
      $st = $this->db->prepare('UPDATE users SET email_verification_code = ?, email_verification_expires = ? WHERE id = ?');
      $st->execute([$code, $expires, $userId]);
      return $code;
    } catch (Throwable $e) {
      return '';
    }
  }

  public function verifyEmail(int $userId, string $code): bool {
    try {
      $st = $this->db->prepare('SELECT id FROM users WHERE id = ? AND email_verification_code = ? AND email_verification_expires > CURRENT_TIMESTAMP');
      $st->execute([$userId, $code]);
      $result = $st->fetch();
      
      if ($result) {
        // Clear verification code and mark email as verified
        $updateSt = $this->db->prepare('UPDATE users SET email_verification_code = NULL, email_verification_expires = NULL, email_verified = TRUE WHERE id = ?');
        $updateSt->execute([$userId]);
        return true;
      }
      return false;
    } catch (Throwable $e) {
      return false;
    }
  }

  public function isEmailVerified(int $userId): bool {
    try {
      $st = $this->db->prepare('SELECT email_verified FROM users WHERE id = ?');
      $st->execute([$userId]);
      $result = $st->fetch();
      return (bool)($result['email_verified'] ?? false);
    } catch (Throwable $e) {
      return false;
    }
  }

  // User suspension and management
  public function suspend(int $userId, string $reason, ?int $suspendedBy = null): bool {
    try {
      $st = $this->db->prepare('UPDATE users SET suspended = TRUE, suspension_reason = ?, suspended_at = NOW(), suspended_by = ? WHERE id = ?');
      return $st->execute([$reason, $suspendedBy, $userId]);
    } catch (Throwable $e) { 
      return false; 
    }
  }

  public function unsuspend(int $userId): bool {
    try {
      $st = $this->db->prepare('UPDATE users SET suspended = FALSE, suspension_reason = NULL, suspended_at = NULL, suspended_by = NULL, unsuspended_at = NOW() WHERE id = ?');
      return $st->execute([$userId]);
    } catch (Throwable $e) { 
      return false; 
    }
  }

  public function isSuspended(int $userId): bool {
    try {
      $st = $this->db->prepare('SELECT suspended FROM users WHERE id = ?');
      $st->execute([$userId]);
      $result = $st->fetch();
      return (bool)($result['suspended'] ?? false);
    } catch (Throwable $e) { 
      return false; 
    }
  }

  public function delete(int $id): bool {
    try {
      // Check if user is suspended
      $user = $this->find($id);
      if ($user && $this->isSuspended($id)) {
        // Don't delete suspended users, just mark as deleted
        $st = $this->db->prepare("UPDATE users SET suspended = TRUE, suspension_reason = 'Account deleted', suspended_at = NOW() WHERE id = ?");
        return $st->execute([$id]);
      }
      
      // For non-suspended users, proceed with normal deletion
      $st = $this->db->prepare('DELETE FROM users WHERE id = ?');
      return $st->execute([$id]);
    } catch (Throwable $e) { 
      return false; 
    }
  }
}
