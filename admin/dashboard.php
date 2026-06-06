<?php
// Ensure auth runs before any output
require_once __DIR__ . '/../app/config/config.php';
require_admin();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
$db = Database::getInstance();
$bookM = new Book($db);
$orderM = new Order($db);
$bookCount = $bookM->count();
$ordersTotal = $orderM->countAll();
$ordersPaid = $orderM->countPaid();
$revenue = $orderM->revenueTotal();
?>
<div class="max-w-6xl mx-auto px-4 py-8">
  <?php flash_render(); ?>
  <div class="flex items-center gap-4 mb-6">
    <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="KingOfPeace Books" class="h-10 w-auto">
    <h2 class="text-2xl font-bold text-brandBlue">Admin Dashboard</h2>
  </div>
  <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 mt-6">
    <div class="rounded-xl border border-gray-200 p-4 bg-white">
      <p class="text-gray-500">Books</p>
      <p class="text-3xl font-bold text-brandBlue"><?php echo $bookCount; ?></p>
    </div>
    <div class="rounded-xl border border-gray-200 p-4 bg-white">
      <p class="text-gray-500">Orders</p>
      <p class="text-3xl font-bold text-brandBlue"><?php echo $ordersTotal; ?></p>
    </div>
    <div class="rounded-xl border border-gray-200 p-4 bg-white">
      <p class="text-gray-500">Paid Orders</p>
      <p class="text-3xl font-bold text-brandBlue"><?php echo $ordersPaid; ?></p>
    </div>
    <div class="rounded-xl border border-gray-200 p-4 bg-white">
      <p class="text-gray-500">Revenue (GHS)</p>
      <p class="text-3xl font-bold text-brandGold"><?php echo number_format($revenue,2); ?></p>
    </div>
  </div>

  <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 mt-8">
    <a href="<?php echo BASE_URL; ?>/admin/upload-book.php" class="rounded-xl border border-gray-200 p-5 bg-white hover:shadow">
      <h3 class="font-semibold text-brandBlue mb-2">Upload Book</h3>
      <p class="text-gray-600">Add a new book with cover and PDF.</p>
    </a>
    <a href="<?php echo BASE_URL; ?>/admin/manage-books.php" class="rounded-xl border border-gray-200 p-5 bg-white hover:shadow">
      <h3 class="font-semibold text-brandBlue mb-2">Manage Books</h3>
      <p class="text-gray-600">Edit or delete existing books.</p>
    </a>
    <a href="<?php echo BASE_URL; ?>/admin/orders.php" class="rounded-xl border border-gray-200 p-5 bg-white hover:shadow">
      <h3 class="font-semibold text-brandBlue mb-2">Orders</h3>
      <p class="text-gray-600">View and track orders.</p>
    </a>
    <a href="<?php echo BASE_URL; ?>/admin/approvals.php" class="rounded-xl border border-gray-200 p-5 bg-white hover:shadow">
      <h3 class="font-semibold text-brandBlue mb-2">Book Approvals</h3>
      <p class="text-gray-600">Review pending book submissions.</p>
    </a>
    <a href="<?php echo BASE_URL; ?>/admin/author-approvals.php" class="rounded-xl border border-gray-200 p-5 bg-white hover:shadow">
      <h3 class="font-semibold text-brandBlue mb-2">Author Approvals</h3>
      <p class="text-gray-600">Review author applications.</p>
    </a>
    <a href="<?php echo BASE_URL; ?>/admin/manage-users.php" class="rounded-xl border border-gray-200 p-5 bg-white hover:shadow">
      <h3 class="font-semibold text-brandBlue mb-2">Manage Users</h3>
      <p class="text-gray-600">Manage user accounts.</p>
    </a>
  </div>

  <?php $recent = $bookM->recent(6); if ($recent): ?>
  <div class="mt-10">
    <h3 class="text-xl font-semibold text-brandBlue mb-3">Recent Books</h3>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <?php foreach ($recent as $rb): ?>
      <a href="<?php echo $bookM->publicUrl($rb); ?>" class="flex items-center gap-3 border border-gray-200 rounded-xl p-3 bg-white hover:shadow">
        <img src="<?php echo cover_src($rb['cover_image']); ?>" class="w-12 h-12 rounded object-cover" alt="thumb">
        <div class="min-w-0">
          <p class="font-semibold text-brandBlue truncate"><?php echo sanitize($rb['title']); ?></p>
          <p class="text-sm text-brandGold font-bold">GHS <?php echo number_format($rb['price'],2); ?></p>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
