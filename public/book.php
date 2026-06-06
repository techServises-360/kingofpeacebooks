<?php
require_once __DIR__ . '/../app/config/config.php';
$db = Database::getInstance();
$bookM = new Book($db);
$publicId = trim((string)($_GET['book'] ?? ''));
$id = (int)($_GET['id'] ?? 0);
$book = $publicId !== '' ? $bookM->findByPublicId($publicId) : $bookM->find($id);
if (!$book) { http_response_code(404); exit('Not found'); }
$bookPublicId = (string)($book['public_id'] ?? '');
$sharePath = BASE_URL . '/public/book.php' . ($bookPublicId !== '' ? '?book=' . urlencode($bookPublicId) : '?id=' . urlencode((string)$book['id']));
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? '';
$shareUrl = $host !== '' && strpos($sharePath, 'http') !== 0 ? $scheme . '://' . $host . $sharePath : $sharePath;
// Hide unapproved books unless admin or the submitter
try {
  if (isset($book['status']) && $book['status'] !== 'approved') {
    $allow = false;
    if (function_exists('is_admin') && is_admin()) { $allow = true; }
    elseif (function_exists('is_logged_in') && is_logged_in() && isset($book['submitted_by']) && (int)$book['submitted_by'] === (int)current_user_id()) { $allow = true; }
    if (!$allow) { http_response_code(404); exit('Not found'); }
  }
} catch (Throwable $e) { /* ignore if legacy schema */ }
if ($publicId === '' && $bookPublicId !== '') {
  header('Location: ' . BASE_URL . '/public/book.php?book=' . urlencode($bookPublicId), true, 301);
  exit;
}
// Determine if current user has already purchased
$paid = false;
if (is_logged_in()) {
  $orders = new Order($db);
  $paid = $orders->hasPaid(current_user_id(), $book['id']);
}
$reviewM = new Review($db);
$reviewsCount = $reviewM->countForBook($book['id']);
$sort = $_GET['sort'] ?? 'newest';
$allowedSort = ['newest','oldest','rating_high','rating_low'];
if (!in_array($sort, $allowedSort, true)) { $sort = 'newest'; }
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 6;
$offset = ($page - 1) * $perPage;
$reviews = $reviewM->listForBookPaginated($book['id'], $offset, $perPage, $sort);
$totalPages = max(1, (int)ceil($reviewsCount / $perPage));
$userReview = null;
if (is_logged_in()) {
  $userReview = $reviewM->findUserReview($book['id'], (int)current_user_id());
}
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>
<div class="max-w-6xl mx-auto px-4 py-8">
  <?php if (function_exists('flash_render')) { flash_render(); } ?>
  <div class="flex items-center justify-between gap-4 mb-6">
    <div class="flex items-center gap-4 min-w-0">
      <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="KingOfPeace Books" class="h-10 w-auto">
      <h2 class="text-2xl font-bold text-brandBlue truncate"><?php echo sanitize($book['title']); ?></h2>
    </div>
    <button type="button" id="share-book-button" data-share-url="<?php echo htmlspecialchars($shareUrl, ENT_QUOTES); ?>" class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-brandBlue shadow-sm hover:bg-gray-50" aria-label="Copy book link">
      <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <circle cx="18" cy="5" r="3"></circle>
        <circle cx="6" cy="12" r="3"></circle>
        <circle cx="18" cy="19" r="3"></circle>
        <path d="M8.59 13.51l6.83 3.98"></path>
        <path d="M15.41 6.51L8.59 10.49"></path>
      </svg>
      <span id="share-book-label">Share</span>
    </button>
  </div>
  <div class="grid gap-6 md:grid-cols-[280px_1fr]">
    <div class="relative w-full rounded-xl overflow-hidden bg-gray-50" style="padding-top:160%;">
      <img src="<?php echo cover_src($book['cover_image']); ?>" class="absolute inset-0 w-full h-full object-contain" alt="cover">
    </div>
    <div>
      <p class="text-gray-600">by <?php echo sanitize($book['author']); ?></p>
      <div class="mt-4">
      <?php if (!empty($book['base_price']) && $book['base_price'] > $book['price']): ?>
        <div class="flex items-center gap-2">
          <span class="text-lg text-gray-500 line-through">GHS <?php echo number_format($book['base_price'],2); ?></span>
          <span class="bg-red-100 text-red-800 text-xs font-medium px-2 py-1 rounded"><?php echo number_format($book['discount_percentage'],1); ?>% OFF</span>
        </div>
      <?php endif; ?>
      <p class="text-2xl text-brandGold font-bold">GHS <?php echo number_format($book['price'],2); ?></p>
      <p class="text-sm text-gray-600 mt-1"><?php echo (int)$reviewsCount; ?> review<?php echo $reviewsCount===1?'':'s'; ?></p>
    </div>
    <p class="mt-4 text-gray-700"><?php echo nl2br(sanitize($book['description'])); ?></p>
      <div class="mt-6">
        <?php if ($paid): ?>
          <a class="inline-block bg-brandGold text-black px-4 py-2 rounded-md font-semibold" href="<?php echo BASE_URL; ?>/public/download.php?book_id=<?php echo $book['id']; ?>">Download</a>
        <?php else: ?>
          <a class="inline-block bg-brandBlue text-white px-4 py-2 rounded-md" href="<?php echo BASE_URL; ?>/public/checkout.php?book_id=<?php echo $book['id']; ?>">Buy Now</a>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="mt-10 grid md:grid-cols-2 gap-6">
    <div>
      <div class="flex items-center justify-between mb-3">
        <h3 class="text-xl font-semibold text-brandBlue">Reviews (<?php echo (int)$reviewsCount; ?>)</h3>
        <form method="get" class="flex items-center gap-2">
          <?php if ($bookPublicId !== ''): ?>
            <input type="hidden" name="book" value="<?php echo htmlspecialchars($bookPublicId); ?>">
          <?php else: ?>
            <input type="hidden" name="id" value="<?php echo (int)$book['id']; ?>">
          <?php endif; ?>
          <label class="text-sm text-gray-600">Sort</label>
          <select name="sort" class="border border-gray-300 rounded-md px-2 py-1 text-sm" onchange="this.form.submit()">
            <option value="newest" <?php echo $sort==='newest'?'selected':''; ?>>Newest</option>
            <option value="oldest" <?php echo $sort==='oldest'?'selected':''; ?>>Oldest</option>
            <option value="rating_high" <?php echo $sort==='rating_high'?'selected':''; ?>>Rating: High to Low</option>
            <option value="rating_low" <?php echo $sort==='rating_low'?'selected':''; ?>>Rating: Low to High</option>
          </select>
        </form>
      </div>

      <?php if ($userReview): ?>
        <div class="border-2 border-brandGold rounded-lg p-4 bg-yellow-50 mb-6">
          <div class="flex items-center justify-between">
            <p class="font-semibold text-brandBlue">Your review</p>
            <button type="button" class="text-sm text-brandBlue underline" onclick="document.getElementById('edit-review').classList.toggle('hidden');">Edit</button>
          </div>
          <p class="text-yellow-600 mt-1"><?php echo str_repeat('★', (int)$userReview['rating']); ?><span class="text-gray-300"><?php echo str_repeat('★', 5 - (int)$userReview['rating']); ?></span></p>
          <p class="text-gray-700 mt-1"><?php echo nl2br(htmlspecialchars($userReview['comment'])); ?></p>
          <form id="edit-review" class="hidden mt-3" method="post" action="<?php echo BASE_URL; ?>/public/review-update.php">
            <?php csrf_input(); ?>
            <input type="hidden" name="review_id" value="<?php echo (int)$userReview['id']; ?>">
            <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
            <div class="grid grid-cols-1 gap-2">
              <select name="rating" class="border border-gray-300 rounded-md px-3 py-2" required>
                <?php for ($i=5; $i>=1; $i--): ?>
                  <option value="<?php echo $i; ?>" <?php echo ((int)$userReview['rating']===$i)?'selected':''; ?>><?php echo str_repeat('★',$i) . str_repeat('☆',5-$i); ?></option>
                <?php endfor; ?>
              </select>
              <textarea name="comment" rows="3" class="border border-gray-300 rounded-md px-3 py-2" required><?php echo htmlspecialchars($userReview['comment']); ?></textarea>
              <div>
                <button class="bg-brandBlue text-white px-3 py-1 rounded-md text-sm" type="submit">Save</button>
              </div>
            </div>
          </form>
        </div>
      <?php endif; ?>

      <?php if (!$reviews): ?>
        <p class="text-gray-600">No reviews yet. Be the first to review.</p>
      <?php else: ?>
        <div class="space-y-4">
          <?php foreach ($reviews as $r): ?>
            <?php if ($userReview && (int)$r['id'] === (int)$userReview['id']) { continue; } ?>
            <div class="border border-gray-200 rounded-lg p-3 bg-white">
              <div class="flex items-center justify-between">
                <p class="font-semibold text-brandBlue truncate mr-2"><?php echo htmlspecialchars($r['name'] ?? 'User'); ?></p>
                <p class="text-sm text-gray-500"><?php echo htmlspecialchars(date('M j, Y', strtotime($r['created_at']))); ?></p>
              </div>
              <p class="text-yellow-600 mt-1"><?php echo str_repeat('★', (int)$r['rating']); ?><span class="text-gray-300"><?php echo str_repeat('★', 5 - (int)$r['rating']); ?></span></p>
              <p class="text-gray-700 mt-1"><?php echo nl2br(htmlspecialchars($r['comment'])); ?></p>
            </div>
          <?php endforeach; ?>
        </div>
        <?php if ($totalPages > 1): ?>
          <div class="flex items-center justify-center gap-2 mt-4">
            <?php $base = BASE_URL . '/public/book.php' . ($bookPublicId !== '' ? '?book=' . urlencode($bookPublicId) : '?id=' . urlencode((string)$book['id'])) . '&sort=' . urlencode($sort) . '&page='; ?>
            <a class="px-3 py-1 rounded border <?php echo $page<=1?'pointer-events-none opacity-50':'hover:bg-gray-50'; ?>" href="<?php echo $page<=1?'#':$base.($page-1); ?>">Prev</a>
            <span class="text-sm text-gray-600">Page <?php echo $page; ?> of <?php echo $totalPages; ?></span>
            <a class="px-3 py-1 rounded border <?php echo $page>=$totalPages?'pointer-events-none opacity-50':'hover:bg-gray-50'; ?>" href="<?php echo $page>=$totalPages?'#':$base.($page+1); ?>">Next</a>
          </div>
        <?php endif; ?>
      <?php endif; ?>
    </div>
    <div>
      <h3 class="text-xl font-semibold text-brandBlue mb-3">Add a review</h3>
      <?php if (!is_logged_in()): ?>
        <p class="text-gray-600">Please <a class="text-brandBlue underline" href="<?php echo BASE_URL; ?>/public/login.php">log in</a> to write a review.</p>
      <?php else: ?>
        <form method="post" action="<?php echo BASE_URL; ?>/public/review-add.php" class="bg-white border border-gray-200 rounded-xl p-4 space-y-3">
          <?php csrf_input(); ?>
          <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
          <div>
            <label class="font-semibold">Rating</label>
            <select name="rating" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2" required>
              <option value="">Select rating</option>
              <option value="5">★★★★★</option>
              <option value="4">★★★★☆</option>
              <option value="3">★★★☆☆</option>
              <option value="2">★★☆☆☆</option>
              <option value="1">★☆☆☆☆</option>
            </select>
          </div>
          <div>
            <label class="font-semibold">Comment</label>
            <textarea name="comment" rows="4" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2" required></textarea>
          </div>
          <button class="bg-brandBlue text-white px-4 py-2 rounded-md" type="submit">Submit Review</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
</div>
<script>
  (function () {
    const button = document.getElementById('share-book-button');
    const label = document.getElementById('share-book-label');
    if (!button || !label) return;

    button.addEventListener('click', async function () {
      const url = button.getAttribute('data-share-url');
      try {
        if (navigator.share) {
          await navigator.share({ title: document.title, url });
          return;
        }

        await navigator.clipboard.writeText(url);
        label.textContent = 'Copied';
        setTimeout(function () { label.textContent = 'Share'; }, 1800);
      } catch (error) {
        label.textContent = 'Copy failed';
        setTimeout(function () { label.textContent = 'Share'; }, 1800);
      }
    });
  })();
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
