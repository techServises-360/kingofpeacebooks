<?php
require_once __DIR__ . '/../app/config/config.php';
require_admin();

$db = Database::getInstance();
$userModel = new User($db);

$id = (int)($_GET['id'] ?? 0);
$user = $userModel->find($id);

if (!$user) {
    http_response_code(404);
    exit('User not found');
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="max-w-4xl mx-auto px-4 py-8">
  <div class="flex items-center gap-4 mb-6">
    <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="KingOfPeace Books" class="h-10 w-auto">
    <h2 class="text-2xl font-bold text-brandBlue">User Details</h2>
  </div>
  
  <?php if (function_exists('flash_render')) { echo flash_render(); } ?>
  
  <div class="bg-white border border-gray-200 rounded-xl p-6">
    <div class="grid md:grid-cols-2 gap-6">
      <!-- Basic Information -->
      <div class="space-y-4">
        <h3 class="text-lg font-semibold text-brandBlue mb-4">Basic Information</h3>
        
        <div class="space-y-3">
          <div>
            <label class="text-sm font-medium text-gray-600">User ID</label>
            <p class="font-mono"><?php echo (int)$user['id']; ?></p>
          </div>
          
          <div>
            <label class="text-sm font-medium text-gray-600">Name</label>
            <p><?php echo htmlspecialchars($user['name']); ?></p>
          </div>
          
          <div>
            <label class="text-sm font-medium text-gray-600">Email</label>
            <p><?php echo htmlspecialchars($user['email']); ?></p>
          </div>
          
          <div>
            <label class="text-sm font-medium text-gray-600">Role</label>
            <p>
              <span class="px-2 py-1 text-sm rounded-full <?php 
                echo $user['role'] === 'admin' ? 'bg-red-100 text-red-800' : 
                     ($user['role'] === 'author' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'); 
              ?>">
                <?php echo ucfirst($user['role']); ?>
              </span>
            </p>
          </div>
          
          <div>
            <label class="text-sm font-medium text-gray-600">Account Status</label>
            <p>
              <?php if ($userModel->isSuspended((int)$user['id'])): ?>
                <span class="px-2 py-1 text-sm rounded-full bg-red-100 text-red-800">🚫 Suspended</span>
              <?php else: ?>
                <span class="px-2 py-1 text-sm rounded-full bg-green-100 text-green-800">✅ Active</span>
              <?php endif; ?>
            </p>
          </div>
          
          <div>
            <label class="text-sm font-medium text-gray-600">Joined</label>
            <p><?php echo date('F j, Y \a\t g:i A', strtotime($user['created_at'])); ?></p>
          </div>
        </div>
      </div>
      
      <!-- Status Information -->
      <div class="space-y-4">
        <h3 class="text-lg font-semibold text-brandBlue mb-4">Status Information</h3>
        
        <div class="space-y-3">
          <div>
            <label class="text-sm font-medium text-gray-600">Email Verification</label>
            <p>
              <?php if (isset($user['email_verified']) && (int)$user['email_verified'] === 1): ?>
                <span class="px-2 py-1 text-sm rounded-full bg-green-100 text-green-800">✓ Verified</span>
              <?php else: ?>
                <span class="px-2 py-1 text-sm rounded-full bg-yellow-100 text-yellow-800">✗ Not Verified</span>
              <?php endif; ?>
            </p>
          </div>
          
          <?php if (isset($user['author_status'])): ?>
            <div>
              <label class="text-sm font-medium text-gray-600">Author Status</label>
              <p>
                <span class="px-2 py-1 text-sm rounded-full <?php 
                  echo $user['author_status'] === 'approved' ? 'bg-green-100 text-green-800' : 
                       ($user['author_status'] === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'); 
                ?>">
                  <?php echo ucfirst($user['author_status']); ?>
                </span>
              </p>
            </div>
          <?php endif; ?>
          
          <?php if (isset($user['author_requested_at'])): ?>
            <div>
              <label class="text-sm font-medium text-gray-600">Author Requested</label>
              <p><?php echo date('F j, Y \a\t g:i A', strtotime($user['author_requested_at'])); ?></p>
            </div>
          <?php endif; ?>
          
          <?php if (isset($user['author_reviewed_at'])): ?>
            <div>
              <label class="text-sm font-medium text-gray-600">Author Reviewed</label>
              <p><?php echo date('F j, Y \a\t g:i A', strtotime($user['author_reviewed_at'])); ?></p>
            </div>
          <?php endif; ?>
          
          <?php if (!empty($user['author_reject_reason'])): ?>
            <div>
              <label class="text-sm font-medium text-gray-600">Rejection Reason</label>
              <p class="text-red-600 bg-red-50 p-2 rounded"><?php echo htmlspecialchars($user['author_reject_reason']); ?></p>
            </div>
          <?php endif; ?>
          
          <?php if (!empty($user['suspension_reason'])): ?>
          <div>
            <label class="text-sm font-medium text-gray-600">Suspension Reason</label>
            <p class="text-red-600 bg-red-50 p-2 rounded"><?php echo htmlspecialchars($user['suspension_reason']); ?></p>
          </div>
          <?php if (!empty($user['suspended_at'])): ?>
            <div>
              <label class="text-sm font-medium text-gray-600">Suspended At</label>
              <p><?php echo date('F j, Y \a\t g:i A', strtotime($user['suspended_at'])); ?></p>
            </div>
          <?php endif; ?>
          <?php if (!empty($user['suspended_by'])): ?>
            <div>
              <label class="text-sm font-medium text-gray-600">Suspended By</label>
              <p>Admin ID: <?php echo (int)$user['suspended_by']; ?></p>
            </div>
          <?php endif; ?>
        <?php endif; ?>
        </div>
      </div>
    </div>
    
    <!-- Action Buttons -->
    <div class="mt-6 pt-6 border-t border-gray-200">
      <div class="flex items-center gap-3">
        <a href="<?php echo BASE_URL; ?>/admin/manage-users.php" 
           class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">← Back to Users</a>
        
        <?php if ($user['role'] !== 'admin'): ?>
          <form method="post" action="<?php echo BASE_URL; ?>/admin/delete-user.php" 
                onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');" class="inline">
            <?php csrf_input(); ?>
            <input type="hidden" name="id" value="<?php echo (int)$user['id']; ?>">
            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">Delete User</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
