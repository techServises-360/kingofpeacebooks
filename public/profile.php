<?php
require_once __DIR__ . '/../app/config/config.php';
require_login();
$db = Database::getInstance();
$userM = new User($db);
$meId = (int)current_user_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  verify_csrf();
  $action = $_POST['action'] ?? '';
  if ($action === 'request_author') {
    $ok = $userM->requestAuthor($meId);
    if ($ok) {
      login_user($meId, 'author');
      flash_set('success', 'Author request submitted. Please verify your email (if not verified) and wait for admin approval.');
    } else {
      flash_set('error', 'Failed to submit author request. Please try again later.');
    }
  }
  header('Location: ' . BASE_URL . '/public/profile.php');
  exit;
}

$user = $userM->find($meId);
if (!$user) { header('Location: ' . BASE_URL . '/public/login.php'); exit; }
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>
<div class="max-w-3xl mx-auto px-4 py-8">
  <?php if (function_exists('flash_render')) { flash_render(); } ?>
  <h1 class="text-2xl font-bold text-brandBlue mb-1">My Profile</h1>
  <p class="text-gray-700 mb-6">Welcome, <span class="font-semibold"><?php echo htmlspecialchars($user['name']); ?></span></p>

  <div class="bg-white border border-gray-200 rounded-xl p-6 space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <p class="text-gray-500 text-sm">Name</p>
        <p class="font-semibold"><?php echo htmlspecialchars($user['name']); ?></p>
      </div>
      <div>
        <p class="text-gray-500 text-sm">Email</p>
        <p class="font-semibold"><?php echo htmlspecialchars($user['email']); ?></p>
      </div>
      <div>
        <p class="text-gray-500 text-sm">Role</p>
        <span class="inline-block px-2 py-1 text-xs rounded-full <?php echo ($user['role']==='admin')?'bg-blue-100 text-blue-700':'bg-gray-100 text-gray-700'; ?>"><?php echo htmlspecialchars($user['role']); ?></span>
      </div>
      <div>
        <p class="text-gray-500 text-sm">Member since</p>
        <p class="font-semibold"><?php echo isset($user['created_at']) ? htmlspecialchars(date('M j, Y', strtotime($user['created_at']))) : '-'; ?></p>
      </div>
    </div>

    <?php $requestedAuthor = isset($user['requested_author']) && (int)$user['requested_author'] === 1; ?>
    <?php $authorStatus = $user['author_status'] ?? null; ?>
    <?php if (($user['role'] ?? 'user') !== 'admin'): ?>
      <div class="pt-4 border-t border-gray-200">
        <h2 class="text-lg font-semibold text-brandBlue mb-2">Author Access</h2>

        <?php if (($user['role'] ?? 'user') === 'author'): ?>
          <?php if ($authorStatus === 'approved'): ?>
            <div class="rounded border border-green-200 bg-green-50 p-4 text-sm text-green-800">Your author account is approved.</div>
          <?php elseif ($authorStatus === 'rejected'): ?>
            <div class="rounded border border-red-200 bg-red-50 p-4 text-sm text-red-800">Your author request was rejected. You can contact support for details.</div>
          <?php else: ?>
            <div class="rounded border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-800">Your author request is pending admin approval.</div>
            <?php if (!$requestedAuthor && (!$authorStatus || $authorStatus === 'none')): ?>
              <div class="mt-3">
                <form method="post" class="inline">
                  <?php csrf_input(); ?>
                  <input type="hidden" name="action" value="request_author">
                  <button type="submit" class="bg-brandGold text-black px-4 py-2 rounded-md font-semibold">Resubmit Author Request</button>
                </form>
              </div>
            <?php endif; ?>
          <?php endif; ?>
        <?php elseif ($requestedAuthor): ?>
          <div class="rounded border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-800">Your author request is pending admin approval.</div>
        <?php else: ?>
          <p class="text-sm text-gray-700 mb-3">Want to publish books? Apply for an author account. Author access requires admin approval.</p>
          <form method="post" class="inline">
            <?php csrf_input(); ?>
            <input type="hidden" name="action" value="request_author">
            <button type="submit" class="bg-brandGold text-black px-4 py-2 rounded-md font-semibold">Apply as Author</button>
          </form>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <div class="pt-4 border-t border-gray-200 flex items-center gap-3">
      <a href="<?php echo BASE_URL; ?>/public/my-books.php" class="bg-brandBlue text-white px-4 py-2 rounded-md">View My Books</a>
      <a href="<?php echo BASE_URL; ?>/public/login.php?action=logout" class="px-4 py-2 rounded-md border border-gray-300 hover:bg-gray-50">Logout</a>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
