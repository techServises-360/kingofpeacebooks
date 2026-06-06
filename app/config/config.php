<?php
// Basic configuration
define('ENV', 'live'); // 'test' or 'live' - SET TO 'live' FOR PRODUCTION
define('BASE_URL', '');

// Database - Neon PostgreSQL on Render
define('DB_HOST', $_ENV['NEON_DB_HOST'] ?? 'localhost');
define('DB_PORT', $_ENV['NEON_DB_PORT'] ?? '5432');
define('DB_NAME', $_ENV['NEON_DB_NAME'] ?? 'bookstore');
define('DB_USER', $_ENV['NEON_DB_USER'] ?? 'postgres');
define('DB_PASS', $_ENV['NEON_DB_PASSWORD'] ?? '');
define('DB_TYPE', 'pgsql'); // PostgreSQL

// Paystack - Use environment variables for security
define('PAYSTACK_PUBLIC_KEY', $_ENV['PAYSTACK_PUBLIC_KEY'] ?? '');
define('PAYSTACK_SECRET_KEY', $_ENV['PAYSTACK_SECRET_KEY'] ?? '');
define('PAYSTACK_CURRENCY', 'GHS');

// Supabase Storage
define('SUPABASE_URL', $_ENV['SUPABASE_URL'] ?? '');
define('SUPABASE_SERVICE_ROLE_KEY', $_ENV['SUPABASE_SERVICE_ROLE_KEY'] ?? '');
define('SUPABASE_ANON_KEY', $_ENV['SUPABASE_ANON_KEY'] ?? '');
define('SUPABASE_BUCKET_COVERS', $_ENV['SUPABASE_BUCKET_COVERS'] ?? 'covers');
define('SUPABASE_BUCKET_BOOKS', $_ENV['SUPABASE_BUCKET_BOOKS'] ?? 'books');

// Google Apps Script for Email Verification
define(
  'EMAIL_VERIFICATION_SCRIPT_URL',
  $_ENV['EMAIL_VERIFICATION_SCRIPT_URL'] ?? ''
);

function paystack_keys() {
  return [
    'public' => PAYSTACK_PUBLIC_KEY,
    'secret' => PAYSTACK_SECRET_KEY,
  ];
}

function send_verification_email($email, $code, $userType = 'user') {
  // Use Google Apps Script for better deliverability and to avoid spam
  if (!EMAIL_VERIFICATION_SCRIPT_URL) {
    error_log('EMAIL_VERIFICATION_SCRIPT_URL is not set; cannot send verification email.');
    return false;
  }
  $url = EMAIL_VERIFICATION_SCRIPT_URL . '?action=send&email=' . urlencode($email) . '&code=' . urlencode($code) . '&type=' . urlencode($userType);
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  curl_setopt($ch, CURLOPT_TIMEOUT, 30);
  $response = curl_exec($ch);
  $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  
  return $http_code === 200 && trim($response) === 'ok';
}

// SessionsZ
if (session_status() === PHP_SESSION_NONE) {
  $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
  if (!$secure && (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')) {
    $secure = true;
  }
  session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => $secure,
    'httponly' => true,
    'samesite' => 'Lax',
  ]);
  session_start();
}

// Autoload basic app classes
spl_autoload_register(function ($class) {
  $paths = [
    __DIR__ . '/../models/' . $class . '.php',
    __DIR__ . '/../controllers/' . $class . '.php',
    __DIR__ . '/../services/' . $class . '.php',
  ];
  foreach ($paths as $p) {
    if (file_exists($p)) { require_once $p; return; }
  }
});

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/sanitize.php';
require_once __DIR__ . '/../helpers/csrf.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/flash.php';
