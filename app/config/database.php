<?php
class Database {
  private static $instance = null;
  public static function getInstance(): PDO {
    if (self::$instance === null) {
      $host = defined('DB_HOST') ? DB_HOST : '127.0.0.1';
      $name = defined('DB_NAME') ? DB_NAME : 'bookstore';
      $user = defined('DB_USER') ? DB_USER : 'root';
      $pass = defined('DB_PASS') ? DB_PASS : '';
      $port = defined('DB_PORT') ? DB_PORT : '5432';
      $dbType = defined('DB_TYPE') ? DB_TYPE : 'mysql';
      
      // Create DSN based on database type
      if ($dbType === 'pgsql') {
        // PostgreSQL for Neon
        $dsn = 'pgsql:host=' . $host . ';port=' . $port . ';dbname=' . $name . ';sslmode=require';
      } else {
        // MySQL fallback
        $dsn = 'mysql:host=' . $host . ';port=' . $port . ';dbname=' . $name . ';charset=utf8mb4';
      }
      
      $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
      ];
      try {
        self::$instance = new PDO($dsn, $user, $pass, $options);
      } catch (PDOException $e) {
        throw new PDOException('Database connection failed: ' . $e->getMessage());
      }
    }
    return self::$instance;
  }
}
