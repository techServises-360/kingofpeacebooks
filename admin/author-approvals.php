<?php
$page_title = 'Author Approvals';
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/helpers/auth.php';
require_admin();
$db = Database::getInstance();
require_once __DIR__ . '/../app/models/User.php';

$uM = new User($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  verify_csrf();
  $id = (int)($_POST['id'] ?? 0);
  $action = $_POST['action'] ?? '';
  $reviewer = function_exists('current_user_id') ? (int)current_user_id() : null;
  if ($action === 'approve') {
    $ok = $uM->approveAuthor($id, $reviewer);
    if (function_exists('flash_set')) { $ok ? flash_set('success', 'Author approved.') : flash_set('error', 'Failed to approve.'); }
  } elseif ($action === 'reject') {
    $reason = trim((string)($_POST['reason'] ?? ''));
    $ok = $uM->rejectAuthor($id, $reason, $reviewer);
    if (function_exists('flash_set')) { $ok ? flash_set('success', 'Author rejected.') : flash_set('error', 'Failed to reject.'); }
  }
  header('Location: ' . BASE_URL . '/admin/author-approvals.php');
  exit;
}

try {
  // Order nulls last in MySQL by sorting on IS NULL flag
  $sql = "SELECT * FROM users WHERE (requested_author=TRUE OR role='author') AND (author_status IS NULL OR author_status IN ('pending','none')) ORDER BY (author_requested_at IS NULL) ASC, author_requested_at ASC, created_at ASC";
  $pending = $db->query($sql)->fetchAll();
} catch (Throwable $e) {
  $pending = [];
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>
<main class="flex-1">
  <div class="max-w-6xl mx-auto px-4 py-8">
    <div class="flex items-center gap-4 mb-6">
      <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="KingOfPeace Books" class="h-10 w-auto">
      <h1 class="text-2xl font-semibold">Author Requests</h1>
    </div>
    <?php if (function_exists('flash_render')) { echo flash_render(); } ?>
    <?php if (empty($pending)): ?>
      <div class="rounded border border-gray-200 p-6 text-gray-600">No pending author requests.</div>
    <?php else: ?>
      <div class="overflow-x-auto rounded border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested At</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-4 py-3"></th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <?php foreach ($pending as $u): ?>
            <tr>
              <td class="px-4 py-3 font-medium"><?php echo htmlspecialchars($u['name']); ?></td>
              <td class="px-4 py-3 text-sm text-gray-700"><?php echo htmlspecialchars($u['email']); ?></td>
              <td class="px-4 py-3 text-sm text-gray-700"><?php echo htmlspecialchars($u['author_requested_at'] ?? $u['created_at']); ?></td>
              <td class="px-4 py-3 text-sm text-gray-700">
                <span class="inline-flex items-center px-2 py-0.5 rounded bg-yellow-100 text-yellow-800 text-xs">Pending</span>
              </td>
              <td class="px-4 py-3">
                <div class="flex items-center gap-3">
                  <button type="button" class="px-3 py-1.5 rounded border border-gray-300 text-sm hover:bg-gray-50" onclick="var d=document.getElementById('details-<?php echo (int)$u['id']; ?>'); if(d){ d.classList.toggle('hidden'); }">View Details</button>
                  <form method="post" action="" class="inline">
                    <?php csrf_input(); ?>
                    <input type="hidden" name="id" value="<?php echo (int)$u['id']; ?>">
                    <input type="hidden" name="action" value="approve">
                    <button class="px-3 py-1.5 rounded bg-green-600 text-white text-sm hover:bg-green-700">Approve</button>
                  </form>
                  <form method="post" action="" class="inline-flex items-center gap-2">
                    <?php csrf_input(); ?>
                    <input type="hidden" name="id" value="<?php echo (int)$u['id']; ?>">
                    <input type="hidden" name="action" value="reject">
                    <input name="reason" type="text" placeholder="Reason" class="border rounded px-2 py-1 text-sm" />
                    <button class="px-3 py-1.5 rounded bg-red-600 text-white text-sm hover:bg-red-700">Reject</button>
                  </form>
                </div>
              </td>
            </tr>
            <?php
              // gather more details for this user
              $uid = (int)$u['id'];
              $role = $u['role'] ?? 'user';
              $created = $u['created_at'] ?? '';
              $reqAt = $u['author_requested_at'] ?? null;
              $booksCount = 0; $recent = [];
              try {
                $row = $db->query("SELECT COUNT(*) c FROM books WHERE submitted_by = " . $uid)->fetch();
                $booksCount = (int)($row['c'] ?? 0);
                $recent = $db->query("SELECT id,title,cover_image FROM books WHERE submitted_by = " . $uid . " ORDER BY created_at DESC LIMIT 3")->fetchAll();
              } catch (Throwable $e) { /* ignore */ }
            ?>
            <tr id="details-<?php echo (int)$u['id']; ?>" class="hidden bg-gray-50">
              <td colspan="5" class="px-4 py-4">
                <div class="grid md:grid-cols-3 gap-4">
                  <div>
                    <h4 class="font-semibold text-brandBlue mb-2">Profile</h4>
                    <ul class="text-sm text-gray-700 space-y-1">
                      <li><strong>Name:</strong> <?php echo htmlspecialchars($u['name']); ?></li>
                      <li><strong>Email:</strong> <a class="underline" href="mailto:<?php echo htmlspecialchars($u['email']); ?>"><?php echo htmlspecialchars($u['email']); ?></a></li>
                      <li><strong>Role:</strong> <?php echo htmlspecialchars($role); ?></li>
                      <li><strong>Requested author at:</strong> <?php echo htmlspecialchars($reqAt ?: '—'); ?></li>
                      <li><strong>Created at:</strong> <?php echo htmlspecialchars($created); ?></li>
                    </ul>
                  </div>
                  <div>
                    <h4 class="font-semibold text-brandBlue mb-2">Submissions</h4>
                    <p class="text-sm text-gray-700">Total submitted: <strong><?php echo (int)$booksCount; ?></strong></p>
                    <?php if ($recent): ?>
                      <ul class="mt-2 space-y-2">
                        <?php foreach ($recent as $rb): ?>
                          <li class="flex items-center gap-2">
                            <img src="<?php echo cover_src($rb['cover_image']); ?>" class="w-8 h-10 object-cover rounded" alt="cover">
                            <span class="text-sm truncate" title="<?php echo htmlspecialchars($rb['title']); ?>"><?php echo htmlspecialchars($rb['title']); ?></span>
                            <a class="ml-auto text-sm underline" target="_blank" rel="noopener" href="<?php echo BASE_URL; ?>/admin/view-book-file.php?book_id=<?php echo (int)$rb['id']; ?>">View File</a>
                          </li>
                        <?php endforeach; ?>
                      </ul>
                    <?php else: ?>
                      <p class="text-sm text-gray-600 mt-1">No submissions yet.</p>
                    <?php endif; ?>
                  </div>
                  <div>
                    <h4 class="font-semibold text-brandBlue mb-2">Notes</h4>
                    <p class="text-sm text-gray-600">You can preview attached files before approving or rejecting. Use the Approve/Reject controls above.</p>
                  </div>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
