<?php
require_once __DIR__ . '/../app/config/config.php';
require_admin();

$db = Database::getInstance();
$userModel = new User($db);

// Handle search
$search = sanitize($_GET['search'] ?? '');
$role = sanitize($_GET['role'] ?? '');
$status = sanitize($_GET['status'] ?? '');

// Build query
$where = ['1=1'];
$params = [];

if ($search) {
    $where[] = '(name LIKE ? OR email LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($role) {
    $where[] = 'role = ?';
    $params[] = $role;
}

if ($status) {
    if ($status === 'verified') {
        $where[] = 'email_verified = 1';
    } elseif ($status === 'unverified') {
        $where[] = 'email_verified = 0';
    }
}

$whereClause = implode(' AND ', $where);
$sql = "SELECT * FROM users WHERE $whereClause ORDER BY created_at DESC LIMIT 50";

$users = $params ? $db->prepare($sql)->execute($params)->fetchAll() : $db->query($sql)->fetchAll();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="max-w-6xl mx-auto px-4 py-8">
  <div class="flex items-center gap-4 mb-6">
    <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="KingOfPeace Books" class="h-10 w-auto">
    <h2 class="text-2xl font-bold text-brandBlue">Manage Users</h2>
  </div>
  
  <?php if (function_exists('flash_render')) { echo flash_render(); } ?>
  
  <!-- Search and Filters -->
  <div class="bg-white border border-gray-200 rounded-xl p-4 mb-6">
    <form method="get" class="grid gap-4 md:grid-cols-4">
      <div>
        <label class="block text-sm font-medium mb-1">Search</label>
        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
               placeholder="Name or email..." class="w-full border border-gray-300 rounded-lg px-3 py-2">
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Role</label>
        <select name="role" class="w-full border border-gray-300 rounded-lg px-3 py-2">
          <option value="">All Roles</option>
          <option value="user" <?php echo $role === 'user' ? 'selected' : ''; ?>>User/Reader</option>
          <option value="author" <?php echo $role === 'author' ? 'selected' : ''; ?>>Author</option>
          <option value="admin" <?php echo $role === 'admin' ? 'selected' : ''; ?>>Admin</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Email Status</label>
        <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2">
          <option value="">All Status</option>
          <option value="verified" <?php echo $status === 'verified' ? 'selected' : ''; ?>>Verified</option>
          <option value="unverified" <?php echo $status === 'unverified' ? 'selected' : ''; ?>>Unverified</option>
        </select>
      </div>
      <div class="flex items-end">
        <button type="submit" class="bg-brandBlue text-white px-4 py-2 rounded-md">Filter</button>
        <a href="<?php echo BASE_URL; ?>/admin/manage-users.php" class="ml-2 px-4 py-2 border border-gray-300 rounded-md">Clear</a>
      </div>
    </form>
  </div>

  <!-- Users Table -->
  <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email Verified</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Author Status</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <?php if (empty($users)): ?>
            <tr>
              <td colspan="9" class="px-4 py-8 text-center text-gray-500">No users found</td>
            </tr>
          <?php else: ?>
            <?php foreach ($users as $user): ?>
              <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm"><?php echo (int)$user['id']; ?></td>
                <td class="px-4 py-3 text-sm font-medium"><?php echo htmlspecialchars($user['name']); ?></td>
                <td class="px-4 py-3 text-sm"><?php echo htmlspecialchars($user['email']); ?></td>
                <td class="px-4 py-3 text-sm">
                  <span class="px-2 py-1 text-xs rounded-full <?php 
                    echo $user['role'] === 'admin' ? 'bg-red-100 text-red-800' : 
                         ($user['role'] === 'author' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'); 
                  ?>">
                    <?php echo ucfirst($user['role']); ?>
                  </span>
                </td>
                <td class="px-4 py-3 text-sm">
                  <?php if (isset($user['email_verified']) && (int)$user['email_verified'] === 1): ?>
                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Verified</span>
                  <?php else: ?>
                    <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Not Verified</span>
                  <?php endif; ?>
                </td>
                <td class="px-4 py-3 text-sm">
                  <?php if (!$userModel->isSuspended((int)$user['id'])): ?>
                    <span class="px-2 py-1 text-sm rounded-full bg-green-100 text-green-800">✅ Active</span>
                  <?php else: ?>
                    <span class="px-2 py-1 text-sm rounded-full bg-red-100 text-red-800">🚫 Suspended</span>
                  <?php endif; ?>
                </td>
                <td class="px-4 py-3 text-sm"><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                <td class="px-4 py-3 text-sm">
                  <div class="flex items-center gap-2">
                    <a href="<?php echo BASE_URL; ?>/admin/view-user.php?id=<?php echo (int)$user['id']; ?>" 
                       class="text-brandBlue hover:underline">View</a>
                    <?php if (!$userModel->isSuspended((int)$user['id'])): ?>
                      <button onclick="Popup.confirm('Are you sure you want to suspend this user?', () => suspendUser(<?php echo (int)$user['id']; ?>))" 
                              class="text-yellow-600 hover:text-yellow-800">Suspend</button>
                    <?php else: ?>
                      <button onclick="Popup.confirm('Are you sure you want to unsuspend this user?', () => unsuspendUser(<?php echo (int)$user['id']; ?>))" 
                              class="text-green-600 hover:text-green-800">Unsuspend</button>
                    <?php endif; ?>
                    <button onclick="Popup.confirm('Are you sure you want to delete this user? This action cannot be undone.', () => deleteUser(<?php echo (int)$user['id']; ?>))" 
                            class="text-red-600 hover:text-red-800">Delete</button>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
// User management functions
function suspendUser(userId) {
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = '<?php echo BASE_URL; ?>/admin/suspend-user.php';
  
  const csrfInput = document.createElement('input');
  csrfInput.type = 'hidden';
  csrfInput.name = '_token';
  csrfInput.value = '<?php echo csrf_token(); ?>';
  
  const idInput = document.createElement('input');
  idInput.type = 'hidden';
  idInput.name = 'id';
  idInput.value = userId;
  
  const reasonInput = document.createElement('input');
  reasonInput.type = 'hidden';
  reasonInput.name = 'reason';
  reasonInput.value = 'Account suspended by administrator';
  
  form.appendChild(csrfInput);
  form.appendChild(idInput);
  form.appendChild(reasonInput);
  document.body.appendChild(form);
  form.submit();
}

function unsuspendUser(userId) {
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = '<?php echo BASE_URL; ?>/admin/unsuspend-user.php';
  
  const csrfInput = document.createElement('input');
  csrfInput.type = 'hidden';
  csrfInput.name = '_token';
  csrfInput.value = '<?php echo csrf_token(); ?>';
  
  const idInput = document.createElement('input');
  idInput.type = 'hidden';
  idInput.name = 'id';
  idInput.value = userId;
  
  form.appendChild(csrfInput);
  form.appendChild(idInput);
  document.body.appendChild(form);
  form.submit();
}

function deleteUser(userId) {
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = '<?php echo BASE_URL; ?>/admin/delete-user.php';
  
  const csrfInput = document.createElement('input');
  csrfInput.type = 'hidden';
  csrfInput.name = '_token';
  csrfInput.value = '<?php echo csrf_token(); ?>';
  
  const idInput = document.createElement('input');
  idInput.type = 'hidden';
  idInput.name = 'id';
  idInput.value = userId;
  
  form.appendChild(csrfInput);
  form.appendChild(idInput);
  document.body.appendChild(form);
  form.submit();
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
