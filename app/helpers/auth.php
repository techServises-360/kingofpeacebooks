<?php
function login_user($id, $role) {
  $_SESSION['user_id'] = (int)$id;
  $_SESSION['role'] = $role;
}

function logout_user() {
  $_SESSION = [];
  if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
  }
  session_destroy();
}

function is_logged_in() { return !empty($_SESSION['user_id']); }
function current_user_id() { return $_SESSION['user_id'] ?? null; }
function current_role() { return $_SESSION['role'] ?? 'user'; }
function is_admin() { return current_role() === 'admin'; }
function is_author() { $r = current_role(); return $r === 'author' || $r === 'admin'; }

// Approved author check: admins are always allowed; authors must have author_status='approved'
function is_approved_author(): bool {
  if (is_admin()) { return true; }
  if (current_role() !== 'author') { return false; }
  try {
    $db = Database::getInstance();
    $u = (new User($db))->find((int)current_user_id());
    return isset($u['author_status']) && $u['author_status'] === 'approved';
  } catch (Throwable $e) { return false; }
}

function require_login() {
  if (!is_logged_in()) { header('Location: ' . BASE_URL . '/public/login.php'); exit; }
}

function require_admin() {
  require_login();
  if (!is_admin()) { http_response_code(403); exit('Forbidden'); }
}

function require_author() {
  require_login();
  if (!is_author()) { http_response_code(403); exit('Forbidden'); }
}

function require_approved_author() {
  require_login();
  if (!is_approved_author()) { http_response_code(403); exit('Forbidden'); }
}
