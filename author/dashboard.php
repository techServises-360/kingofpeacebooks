<?php
require_once __DIR__ . '/../app/config/config.php';
require_approved_author();

// Check if user has verified their email
$db = Database::getInstance();
$userModel = new User($db);
$userId = (int)current_user_id();
if (!$userModel->isEmailVerified($userId)) {
  flash_set('error', 'You must verify your email before accessing author features. Please check your email for the verification code.');
  header('Location: ' . BASE_URL . '/public/verify-email.php?user_id=' . $userId);
  exit;
}
$me = $userModel->find((int)current_user_id());
$bookM = new Book($db);
$orderM = new Order($db);
// Stats for this author (by submitted_by)
$uid = (int)current_user_id();
$counts = ['total'=>0,'pending'=>0,'approved'=>0,'rejected'=>0];
try {
  $row = $db->query("SELECT COUNT(*) c FROM books WHERE submitted_by = $uid")->fetch();
  $counts['total'] = (int)($row['c'] ?? 0);
  foreach (['pending','approved','rejected'] as $st) {
    $r = $db->query("SELECT COUNT(*) c FROM books WHERE submitted_by = $uid AND status = '".$st."'")->fetch();
    $counts[$st] = (int)($r['c'] ?? 0);
  }
} catch (Throwable $e) { /* legacy schema: ignore */ }

$revenue = 0.0; $paidOrders = 0;
try { $revenue = $orderM->revenueByAuthor($uid); $paidOrders = $orderM->countPaidByAuthor($uid); } catch (Throwable $e) {}

// paginate author's books using submitted_by
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
try {
  $offset = ($page - 1) * $perPage;
  $st = $db->prepare("SELECT * FROM books WHERE submitted_by = ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
  $st->bindValue(1, $uid, PDO::PARAM_INT);
  $st->bindValue(2, $perPage, PDO::PARAM_INT);
  $st->bindValue(3, $offset, PDO::PARAM_INT);
  $st->execute();
  $items = $st->fetchAll();
  $tc = $db->prepare("SELECT COUNT(*) AS c FROM books WHERE submitted_by = ?");
  $tc->execute([$uid]);
  $total = (int)($tc->fetch()['c'] ?? 0);
  $pages = max(1, (int)ceil($total / $perPage));
} catch (Throwable $e) {
  // fallback to previous behavior by author name
  $result = $bookM->filterAndPaginate([
    'author' => $me['name'] ?? '',
    'page' => $page,
    'per_page' => 10,
    'sort' => 'newest',
  ]);
  $items = $result['items'];
  $pages = max(1, (int)ceil($result['total'] / $result['per_page']));
}
$items = $result['items'];
$pages = max(1, (int)ceil($result['total'] / $result['per_page']));
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>
<div class="max-w-6xl mx-auto px-4 py-8">
  <?php if (function_exists('flash_render')) { flash_render(); } ?>
  <div class="flex items-center gap-4 mb-6">
    <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="KingOfPeace Books" class="h-10 w-auto">
    <h1 class="text-2xl font-bold text-brandBlue">Author Dashboard</h1>
  </div>
  <p class="text-gray-600 mb-6">Welcome, <span class="font-semibold"><?php echo htmlspecialchars($me['name'] ?? ''); ?></span></p>

  <div class="mt-6 grid md:grid-cols-2 gap-6">
    <div class="bg-white border border-gray-200 rounded-xl p-5">
      <h2 class="text-lg font-semibold text-brandBlue mb-3">Overview</h2>
      <div class="grid grid-cols-2 gap-3 text-sm">
        <div class="rounded-lg border border-gray-200 p-3">
          <p class="text-gray-600">Total Books</p>
          <p class="text-xl font-semibold"><?php echo (int)$counts['total']; ?></p>
        </div>
        <div class="rounded-lg border border-gray-200 p-3">
          <p class="text-gray-600">Pending</p>
          <p class="text-xl font-semibold text-yellow-600"><?php echo (int)$counts['pending']; ?></p>
        </div>
        <div class="rounded-lg border border-gray-200 p-3">
          <p class="text-gray-600">Approved</p>
          <p class="text-xl font-semibold text-green-600"><?php echo (int)$counts['approved']; ?></p>
        </div>
        <div class="rounded-lg border border-gray-200 p-3">
          <p class="text-gray-600">Rejected</p>
          <p class="text-xl font-semibold text-red-600"><?php echo (int)$counts['rejected']; ?></p>
        </div>
        <div class="rounded-lg border border-gray-200 p-3 col-span-2">
          <p class="text-gray-600">Sales (paid orders)</p>
          <p class="text-xl font-semibold">GHS <?php echo number_format($revenue,2); ?> <span class="text-sm text-gray-500">(<?php echo (int)$paidOrders; ?> orders)</span></p>
        </div>
      </div>
    </div>
    <div class="bg-white border border-gray-200 rounded-xl p-5">
      <h2 class="text-lg font-semibold text-brandBlue mb-3">Upload a new book (PDF-first)</h2>
      <form action="<?php echo BASE_URL; ?>/author/upload.php" method="post" enctype="multipart/form-data" class="space-y-3">
        <?php csrf_input(); ?>
        <div>
          <label class="font-semibold">Title</label>
          <input class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2" type="text" name="title" required>
        </div>
        <div>
          <label class="font-semibold">Display Author Name (optional)</label>
          <input class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2" type="text" name="author" placeholder="Defaults to your profile name">
        </div>
        <div class="grid grid-cols-2 gap-2">
          <div>
            <label class="font-semibold">Base Price (GHS)</label>
            <input class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2" type="number" step="0.01" name="base_price" id="base_price" required>
          </div>
          <div>
            <label class="font-semibold">Discount (%)</label>
            <input class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2" type="number" step="0.01" min="0" max="100" name="discount_percentage" id="discount_percentage" value="0">
          </div>
        </div>
        <div>
          <label class="font-semibold">Final Price (GHS)</label>
          <input class="mt-1 w-full border border-gray-200 bg-gray-50 rounded-lg px-3 py-2" type="number" step="0.01" name="price" id="final_price" readonly>
          <p class="text-xs text-gray-500 mt-1">Final price is automatically calculated from base price and discount</p>
        </div>
        <div>
          <label class="font-semibold">Description</label>
          <textarea class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2" name="description" rows="4"></textarea>
        </div>
        <div>
          <label class="font-semibold">Cover Image (optional)</label>
          <input class="mt-1 w-full" type="file" name="cover_image" accept="image/*">
        </div>
        <div>
          <label class="font-semibold">PDF File</label>
          <input class="mt-1 w-full" type="file" name="book_file" accept="application/pdf" required>
        </div>
        <button class="bg-brandBlue text-white px-4 py-2 rounded-md" type="submit">Upload</button>
      </form>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl p-5 md:col-span-2">
      <h2 class="text-lg font-semibold text-brandBlue mb-3">Your recent books</h2>
      <?php if (!$items): ?>
        <p class="text-gray-600">You haven't uploaded any books yet.</p>
      <?php else: ?>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
          <?php foreach ($items as $b): ?>
            <div class="flex gap-3 border border-gray-200 rounded-lg p-2">
              <div class="relative w-16 bg-gray-50 rounded overflow-hidden" style="padding-top:100%;">
                <img class="absolute inset-0 w-full h-full object-cover" src="<?php echo cover_src($b['cover_image']); ?>" alt="cover">
              </div>
              <div class="min-w-0">
                <p class="font-semibold text-brandBlue truncate" title="<?php echo htmlspecialchars($b['title']); ?>"><?php echo htmlspecialchars($b['title']); ?></p>
                <div class="mt-1">
                  <?php if (!empty($b['base_price']) && $b['base_price'] > $b['price']): ?>
                    <div class="flex items-center gap-1">
                      <span class="text-xs text-gray-500 line-through">GHS <?php echo number_format($b['base_price'],2); ?></span>
                      <span class="text-xs text-brandGold font-bold">GHS <?php echo number_format($b['price'],2); ?></span>
                    </div>
                  <?php else: ?>
                    <span class="text-xs text-brandGold font-bold">GHS <?php echo number_format($b['price'],2); ?></span>
                  <?php endif; ?>
                </div>
                <?php $status = $b['status'] ?? null; ?>
                <?php if ($status): ?>
                  <?php $badgeClass = $status==='approved'?'bg-green-100 text-green-700':($status==='rejected'?'bg-red-100 text-red-700':'bg-yellow-100 text-yellow-700'); ?>
                  <span class="inline-block text-xs px-2 py-0.5 rounded <?php echo $badgeClass; ?> capitalize"><?php echo htmlspecialchars($status); ?></span>
                <?php endif; ?>
                <?php $pc = 0; try { $pc = $orderM->countPaidForBook((int)$b['id']); } catch (Throwable $e) {} ?>
                <p class="text-xs text-gray-600 mt-1">Paid purchases: <?php echo (int)$pc; ?></p>
                <div class="mt-1 flex gap-2 text-sm">
                  <a class="text-brandBlue underline" href="<?php echo $bookM->publicUrl($b); ?>">Details</a>
                  <a class="text-brandBlue underline" target="_blank" rel="noopener" href="<?php echo BASE_URL; ?>/admin/view-book-file.php?book_id=<?php echo (int)$b['id']; ?>">View File</a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <?php if ($pages > 1): ?>
          <div class="mt-3 flex items-center justify-center gap-2">
            <?php for ($p=1; $p<=$pages; $p++): $url = BASE_URL . '/author/dashboard.php?page=' . $p; ?>
              <a class="px-2 py-1 rounded border <?php echo $p===$page?'bg-brandBlue text-white border-brandBlue':'border-gray-300'; ?>" href="<?php echo $url; ?>"><?php echo $p; ?></a>
            <?php endfor; ?>
          </div>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</div>
<script>
  document.getElementById('base_price').addEventListener('input', calculateFinalPrice);
  document.getElementById('discount_percentage').addEventListener('input', calculateFinalPrice);
  
  function calculateFinalPrice() {
    const basePrice = parseFloat(document.getElementById('base_price').value) || 0;
    const discount = parseFloat(document.getElementById('discount_percentage').value) || 0;
    const finalPrice = basePrice * (1 - discount / 100);
    document.getElementById('final_price').value = finalPrice.toFixed(2);
  }
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
