<?php
$page_title = 'Book Approvals';
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/helpers/auth.php';
require_admin();
$db = Database::getInstance();
require_once __DIR__ . '/../app/models/Book.php';

$bookM = new Book($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  verify_csrf();
  $id = (int)($_POST['id'] ?? 0);
  $action = $_POST['action'] ?? '';
  $reviewer = function_exists('current_user_id') ? (int)current_user_id() : null;
  if ($action === 'approve') {
    if ($bookM->approve($id, $reviewer)) { flash_set('success', 'Book approved.'); } else { flash_set('error', 'Failed to approve.'); }
  } elseif ($action === 'reject') {
    $reason = trim((string)($_POST['reason'] ?? ''));
    if ($bookM->reject($id, $reason, $reviewer)) { flash_set('success', 'Book rejected.'); } else { flash_set('error', 'Failed to reject.'); }
  }
  header('Location: ' . BASE_URL . '/admin/approvals.php');
  exit;
}

try {
  $pending = $db->query("SELECT * FROM books WHERE status='pending' ORDER BY created_at ASC")->fetchAll();
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
      <h1 class="text-2xl font-semibold">Pending Book Reviews</h1>
    </div>
    <?php if (function_exists('flash_render')) { echo flash_render(); } ?>
    <?php if (empty($pending)): ?>
      <div class="rounded border border-gray-200 p-6 text-gray-600">No pending books.</div>
    <?php else: ?>
      <div class="overflow-x-auto rounded border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Book</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Author</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted</th>
              <th class="px-4 py-3"></th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <?php foreach ($pending as $b): ?>
              <tr>
                <td class="px-4 py-3">
                  <div class="flex items-center gap-3">
                    <img src="<?php echo cover_src($b['cover_image']); ?>" class="w-12 h-16 object-cover rounded" alt="cover">
                    <div>
                      <div class="font-medium line-clamp-1" title="<?php echo htmlspecialchars($b['title']); ?>"><?php echo htmlspecialchars($b['title']); ?></div>
                      <div class="text-xs text-gray-500 line-clamp-1" title="<?php echo htmlspecialchars($b['description']); ?>"><?php echo htmlspecialchars($b['description']); ?></div>
                    </div>
                  </div>
                </td>
                <td class="px-4 py-3 text-sm text-gray-700"><?php echo htmlspecialchars($b['author']); ?></td>
                <td class="px-4 py-3 text-sm text-gray-700">
                  <?php if (!empty($b['base_price']) && $b['base_price'] > $b['price']): ?>
                    <div>
                      <span class="line-through text-gray-500">GHS <?php echo number_format((float)$b['base_price'], 2); ?></span>
                      <span class="ml-1">GHS <?php echo number_format((float)$b['price'], 2); ?></span>
                      <span class="ml-1 text-xs bg-red-100 text-red-800 px-1 py-0.5 rounded"><?php echo number_format((float)$b['discount_percentage'], 1); ?>% OFF</span>
                    </div>
                  <?php else: ?>
                    GHS <?php echo number_format((float)$b['price'], 2); ?>
                  <?php endif; ?>
                </td>
                <td class="px-4 py-3 text-sm text-gray-700"><?php echo htmlspecialchars($b['created_at'] ?? ''); ?></td>
                <td class="px-4 py-3">
                  <div class="flex items-center gap-3">
                    <a href="<?php echo BASE_URL; ?>/admin/view-book-file.php?book_id=<?php echo (int)$b['id']; ?>" target="_blank" rel="noopener" class="px-3 py-1.5 rounded border border-gray-300 text-sm hover:bg-gray-50">View File</a>
                    <form method="post" action="" class="inline">
                      <?php csrf_input(); ?>
                      <input type="hidden" name="id" value="<?php echo (int)$b['id']; ?>">
                      <input type="hidden" name="action" value="approve">
                      <button class="px-3 py-1.5 rounded bg-green-600 text-white text-sm hover:bg-green-700">Approve</button>
                    </form>
                    <form method="post" action="" class="inline-flex items-center gap-2">
                      <?php csrf_input(); ?>
                      <input type="hidden" name="id" value="<?php echo (int)$b['id']; ?>">
                      <input type="hidden" name="action" value="reject">
                      <input name="reason" type="text" placeholder="Reason" class="border rounded px-2 py-1 text-sm" />
                      <button class="px-3 py-1.5 rounded bg-red-600 text-white text-sm hover:bg-red-700">Reject</button>
                    </form>
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
