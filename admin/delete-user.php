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

// Prevent deletion of admin users
if ($user['role'] === 'admin') {
    flash_set('error', 'Cannot delete admin users');
    header('Location: ' . BASE_URL . '/admin/manage-users.php');
    exit;
}

try {
    // Check if user has orders
    $orderQuery = $db->prepare('SELECT COUNT(*) FROM orders WHERE user_id = ?');
    $orderQuery->execute([$id]);
    $orderCount = $orderQuery->fetchColumn();
    
    if ($orderCount > 0) {
        // Suspend user instead of deleting to preserve order history
        $suspended = $userModel->suspend($id, 'Account suspended due to policy violation - orders preserved', current_user_id());
        
        if ($suspended) {
            flash_set('success', 'User suspended successfully. Orders preserved for record-keeping.');
        } else {
            flash_set('error', 'Failed to suspend user. Please try again.');
        }
    } else {
        // Safe deletion for users without orders
        $deleted = $userModel->delete($id);
        
        if ($deleted) {
            flash_set('success', 'User deleted successfully');
        } else {
            flash_set('error', 'Failed to delete user. Please try again.');
        }
    }
} catch (Exception $e) {
    flash_set('error', 'Error processing user: ' . $e->getMessage());
}

header('Location: ' . BASE_URL . '/admin/manage-users.php');
exit;
