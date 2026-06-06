<?php
require_once __DIR__ . '/../app/config/config.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    flash_set('error', 'Invalid request method');
    header('Location: ' . BASE_URL . '/admin/manage-users.php');
    exit;
}

try {
    verify_csrf();
} catch (Exception $e) {
    flash_set('error', 'Invalid CSRF token. Please try again.');
    header('Location: ' . BASE_URL . '/admin/manage-users.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$reason = sanitize($_POST['reason'] ?? '');

if (!$id) {
    flash_set('error', 'Invalid user ID provided');
    header('Location: ' . BASE_URL . '/admin/manage-users.php');
    exit;
}

if (!$reason) {
    flash_set('error', 'Suspension reason is required');
    header('Location: ' . BASE_URL . '/admin/manage-users.php');
    exit;
}

$db = Database::getInstance();
$userModel = new User($db);
$user = $userModel->find($id);

if (!$user) {
    flash_set('error', 'User not found');
    header('Location: ' . BASE_URL . '/admin/manage-users.php');
    exit;
}

// Prevent suspension of admin users
if ($user['role'] === 'admin') {
    flash_set('error', 'Cannot suspend admin users');
    header('Location: ' . BASE_URL . '/admin/manage-users.php');
    exit;
}

try {
    $suspended = $userModel->suspend($id, $reason, current_user_id());
    
    if ($suspended) {
        flash_set('success', 'User suspended successfully');
    } else {
        flash_set('error', 'Failed to suspend user. Please try again.');
    }
} catch (Exception $e) {
    flash_set('error', 'Error suspending user: ' . $e->getMessage());
}

header('Location: ' . BASE_URL . '/admin/manage-users.php');
exit;
