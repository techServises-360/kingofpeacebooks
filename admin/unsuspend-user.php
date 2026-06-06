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

if (!$id) {
    flash_set('error', 'Invalid user ID provided');
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

try {
    $unsuspended = $userModel->unsuspend($id);
    
    if ($unsuspended) {
        flash_set('success', 'User unsuspended successfully');
    } else {
        flash_set('error', 'Failed to unsuspend user. Please try again.');
    }
} catch (Exception $e) {
    flash_set('error', 'Error unsuspending user: ' . $e->getMessage());
}

header('Location: ' . BASE_URL . '/admin/manage-users.php');
exit;
