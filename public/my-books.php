<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_login();
$db = Database::getInstance();
$orderM = new Order($db);
$paid = $orderM->paidWithBooksByUser((int)current_user_id());
?>
<div class="max-w-6xl mx-auto px-4 py-8">
  <h1 class="text-2xl font-bold text-brandBlue">My Books</h1>
  <?php if (empty($paid)): ?>
    <p class="mt-4 text-gray-600">You haven't purchased any books yet.</p>
    <p class="mt-2"><a class="bg-brandBlue text-white px-4 py-2 rounded-md" href="<?php echo BASE_URL; ?>/public/books.php">Browse Books</a></p>
  <?php else: ?>
    <div class="mt-6 grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
      <?php foreach ($paid as $b): ?>
      <div class="border border-gray-200 rounded-xl overflow-hidden bg-white flex flex-col">
        <div class="relative w-full bg-gray-50" style="padding-top:160%;">
          <img class="absolute inset-0 w-full h-full object-contain" src="<?php echo cover_src($b['cover_image']); ?>" alt="cover">
        </div>
        <div class="p-3 flex-1 flex flex-col">
          <h3 class="text-brandBlue font-semibold text-lg line-clamp-2"><?php echo sanitize($b['title']); ?></h3>
          <p class="text-sm text-gray-600 truncate">by <?php echo sanitize($b['author']); ?></p>
          <div class="mt-3 flex gap-2">
            <a class="inline-block bg-brandBlue text-white px-3 py-2 rounded-md" href="<?php echo BASE_URL; ?>/public/book.php?id=<?php echo $b['id']; ?>">View</a>
            <a class="inline-block bg-brandGold text-black px-3 py-2 rounded-md" href="<?php echo BASE_URL; ?>/public/download.php?book_id=<?php echo $b['id']; ?>">Download</a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
