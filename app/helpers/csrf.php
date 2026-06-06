<?php
function ensure_session() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function csrf_token() {
    ensure_session();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_input() {
    $t = csrf_token();
    echo '<input type="hidden" name="_token" value="' . htmlspecialchars($t, ENT_QUOTES, 'UTF-8') . '">';
}

function verify_csrf() {
    ensure_session();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['_token'] ?? '';
        $sessionToken = $_SESSION['csrf_token'] ?? '';
        
        // Debug: Log CSRF values (remove in production)
        error_log("CSRF Debug - POST token: " . $token);
        error_log("CSRF Debug - Session token: " . $sessionToken);
        error_log("CSRF Debug - Session ID: " . session_id());
        error_log("CSRF Debug - Session status: " . session_status());
        
        if (empty($token)) {
            throw new Exception('CSRF token missing from form');
        }
        
        if (empty($sessionToken)) {
            throw new Exception('CSRF token missing from session');
        }
        
        if (!hash_equals($sessionToken, $token)) {
            // Regenerate token on failure for next attempt
            unset($_SESSION['csrf_token']);
            throw new Exception('Invalid CSRF token. Please try again.');
        }
    }
}
