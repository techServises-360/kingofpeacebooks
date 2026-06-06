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
<!-- Mobile-First Book Details Page -->
<div class="min-h-screen bg-gray-50 pb-20 md:pb-8">
  <div class="max-w-6xl mx-auto">
    <?php if (function_exists('flash_render')) { flash_render(); } ?>
    
    <!-- Mobile Header with Share Button -->
    <div class="bg-white border-b border-gray-200 px-4 py-3 sticky top-0 z-10 md:static">
      <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-2 min-w-0 flex-1">
          <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="KingOfPeace Books" class="h-8 w-auto md:h-10">
          <h1 class="text-base md:text-2xl font-bold text-brandBlue truncate"><?php echo sanitize($book['title']); ?></h1>
        </div>
        <button type="button" id="share-book-button" data-share-url="<?php echo htmlspecialchars($shareUrl, ENT_QUOTES); ?>" class="flex items-center justify-center gap-2 rounded-full border border-gray-300 bg-white px-3 py-2 md:px-4 md:py-2.5 text-xs md:text-sm font-semibold text-brandBlue shadow-sm hover:bg-gray-50 active:bg-gray-100 transition-colors min-w-[80px] md:min-w-[100px]" aria-label="Share book">
          <svg class="h-4 w-4 md:h-5 md:w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <circle cx="18" cy="5" r="3"></circle>
            <circle cx="6" cy="12" r="3"></circle>
            <circle cx="18" cy="19" r="3"></circle>
            <path d="M8.59 13.51l6.83 3.98"></path>
            <path d="M15.41 6.51L8.59 10.49"></path>
          </svg>
          <span id="share-book-label" class="hidden sm:inline">Share</span>
        </button>
      </div>
    </div>

    <!-- Book Content Container -->
    <div class="bg-white md:mt-4 md:rounded-xl md:shadow-sm">
      <!-- Book Cover and Details Section -->
      <div class="grid grid-cols-1 md:grid-cols-[300px_1fr] gap-0 md:gap-8">
        <!-- Book Cover (Mobile: Full Width, Desktop: Fixed Width) -->
        <div class="relative bg-gradient-to-br from-gray-50 to-gray-100 md:rounded-l-xl">
          <div class="relative w-full max-w-[300px] mx-auto md:max-w-none" style="padding-top:140%;">
            <img src="<?php echo cover_src($book['cover_image']); ?>" class="absolute inset-0 w-full h-full object-contain p-4 md:p-6" alt="<?php echo htmlspecialchars($book['title']); ?> cover">
          </div>
        </div>

        <!-- Book Details -->
        <div class="px-4 py-6 md:py-8 md:pr-8">
          <!-- Author -->
          <p class="text-sm md:text-base text-gray-600 mb-2">by <span class="font-medium text-gray-900"><?php echo sanitize($book['author']); ?></span></p>
          
          <!-- Rating and Reviews -->
          <div class="flex items-center gap-2 mb-4">
            <div class="flex items-center">
              <svg class="w-5 h-5 text-yellow-400 fill-current" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
              </svg>
              <span class="ml-1 text-sm font-medium text-gray-700"><?php echo (int)$reviewsCount; ?></span>
            </div>
            <span class="text-sm text-gray-500"><?php echo (int)$reviewsCount; ?> review<?php echo $reviewsCount===1?'':'s'; ?></span>
          </div>

          <!-- Pricing Section -->
          <div class="bg-gradient-to-r from-blue-50 to-yellow-50 border border-gray-200 rounded-xl p-4 mb-6">
            <?php if (!empty($book['base_price']) && $book['base_price'] > $book['price']): ?>
              <div class="flex items-center gap-2 mb-1">
                <span class="text-base md:text-lg text-gray-500 line-through">GHS <?php echo number_format($book['base_price'],2); ?></span>
                <span class="bg-red-500 text-white text-xs font-bold px-2.5 py-1 rounded-full"><?php echo number_format($book['discount_percentage'],1); ?>% OFF</span>
              </div>
            <?php endif; ?>
            <p class="text-3xl md:text-4xl font-bold text-brandGold mb-1">GHS <?php echo number_format($book['price'],2); ?></p>
            <p class="text-xs md:text-sm text-gray-600">Instant digital access after purchase</p>
          </div>

          <!-- Description -->
          <div class="mb-6">
            <h2 class="text-lg md:text-xl font-bold text-brandBlue mb-3">About This Book</h2>
            <div class="text-sm md:text-base text-gray-700 leading-relaxed space-y-2">
              <?php echo nl2br(sanitize($book['description'])); ?>
            </div>
          </div>

          <!-- Desktop CTA Button -->
          <div class="hidden md:block">
            <?php if ($paid): ?>
              <a href="<?php echo BASE_URL; ?>/public/download.php?book_id=<?php echo $book['id']; ?>" class="inline-flex items-center justify-center w-full bg-brandGold hover:bg-yellow-600 text-black font-bold px-8 py-4 rounded-xl transition-colors shadow-lg hover:shadow-xl">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Download Your Book
              </a>
            <?php else: ?>
              <a href="<?php echo BASE_URL; ?>/public/checkout.php?book_id=<?php echo $book['id']; ?>" class="inline-flex items-center justify-center w-full bg-brandBlue hover:bg-blue-700 text-white font-bold px-8 py-4 rounded-xl transition-colors shadow-lg hover:shadow-xl">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Buy Now
              </a>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Reviews Section -->
      <div class="border-t border-gray-200 mt-8">
        <div class="px-4 py-6 md:px-8 md:py-8">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8">
            <!-- Reviews List -->
            <div>
              <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
                <h3 class="text-xl md:text-2xl font-bold text-brandBlue">Reviews (<?php echo (int)$reviewsCount; ?>)</h3>
                <form method="get" class="flex items-center gap-2">
                  <?php if ($bookPublicId !== ''): ?>
                    <input type="hidden" name="book" value="<?php echo htmlspecialchars($bookPublicId); ?>">
                  <?php else: ?>
                    <input type="hidden" name="id" value="<?php echo (int)$book['id']; ?>">
                  <?php endif; ?>
                  <label class="text-xs md:text-sm text-gray-600 whitespace-nowrap">Sort by:</label>
                  <select name="sort" class="border border-gray-300 rounded-lg px-3 py-2 text-xs md:text-sm bg-white focus:ring-2 focus:ring-brandBlue focus:border-transparent" onchange="this.form.submit()">
                    <option value="newest" <?php echo $sort==='newest'?'selected':''; ?>>Newest</option>
                    <option value="oldest" <?php echo $sort==='oldest'?'selected':''; ?>>Oldest</option>
                    <option value="rating_high" <?php echo $sort==='rating_high'?'selected':''; ?>>Highest Rated</option>
                    <option value="rating_low" <?php echo $sort==='rating_low'?'selected':''; ?>>Lowest Rated</option>
                  </select>
                </form>
              </div>

              <!-- User's Review (If exists) -->
              <?php if ($userReview): ?>
                <div class="border-2 border-brandGold rounded-xl p-4 md:p-5 bg-gradient-to-br from-yellow-50 to-orange-50 mb-6 shadow-sm">
                  <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                      <div class="w-8 h-8 bg-brandGold rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                          <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/>
                        </svg>
                      </div>
                      <p class="font-bold text-brandBlue">Your Review</p>
                    </div>
                    <button type="button" class="text-xs md:text-sm text-brandBlue underline hover:no-underline font-medium" onclick="document.getElementById('edit-review').classList.toggle('hidden');">Edit</button>
                  </div>
                  <div class="flex items-center gap-1 mb-2">
                    <?php for ($i=1; $i<=5; $i++): ?>
                      <svg class="w-5 h-5 <?php echo $i<=(int)$userReview['rating']?'text-yellow-400':'text-gray-300'; ?> fill-current" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                      </svg>
                    <?php endfor; ?>
                  </div>
                  <p class="text-sm md:text-base text-gray-700 leading-relaxed"><?php echo nl2br(htmlspecialchars($userReview['comment'])); ?></p>
                  
                  <form id="edit-review" class="hidden mt-4 pt-4 border-t border-yellow-200" method="post" action="<?php echo BASE_URL; ?>/public/review-update.php">
                    <?php csrf_input(); ?>
                    <input type="hidden" name="review_id" value="<?php echo (int)$userReview['id']; ?>">
                    <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                    <div class="space-y-3">
                      <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Update Rating</label>
                        <select name="rating" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 bg-white focus:ring-2 focus:ring-brandBlue focus:border-transparent" required>
                          <?php for ($i=5; $i>=1; $i--): ?>
                            <option value="<?php echo $i; ?>" <?php echo ((int)$userReview['rating']===$i)?'selected':''; ?>><?php echo str_repeat('★',$i) . str_repeat('☆',5-$i); ?></option>
                          <?php endfor; ?>
                        </select>
                      </div>
                      <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Update Comment</label>
                        <textarea name="comment" rows="4" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-brandBlue focus:border-transparent" required><?php echo htmlspecialchars($userReview['comment']); ?></textarea>
                      </div>
                      <div class="flex gap-2">
                        <button type="submit" class="flex-1 bg-brandBlue hover:bg-blue-700 text-white font-semibold px-4 py-2.5 rounded-lg transition-colors">Save Changes</button>
                        <button type="button" class="px-4 py-2.5 border border-gray-300 rounded-lg hover:bg-gray-50" onclick="document.getElementById('edit-review').classList.add('hidden');">Cancel</button>
                      </div>
                    </div>
                  </form>
                </div>
              <?php endif; ?>

              <!-- Reviews List -->
              <?php if (!$reviews): ?>
                <div class="text-center py-12 bg-gray-50 rounded-xl border-2 border-dashed border-gray-300">
                  <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                  </svg>
                  <p class="text-gray-600 font-medium">No reviews yet</p>
                  <p class="text-sm text-gray-500 mt-1">Be the first to share your thoughts!</p>
                </div>
              <?php else: ?>
                <div class="space-y-4">
                  <?php foreach ($reviews as $r): ?>
                    <?php if ($userReview && (int)$r['id'] === (int)$userReview['id']) { continue; } ?>
                    <div class="border border-gray-200 rounded-xl p-4 md:p-5 bg-white hover:shadow-md transition-shadow">
                      <div class="flex items-start justify-between gap-3 mb-3">
                        <div class="flex items-center gap-2 min-w-0 flex-1">
                          <div class="w-10 h-10 bg-gradient-to-br from-brandBlue to-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
                            <span class="text-white font-bold text-sm"><?php echo strtoupper(substr($r['name'] ?? 'U', 0, 1)); ?></span>
                          </div>
                          <div class="min-w-0">
                            <p class="font-semibold text-brandBlue truncate"><?php echo htmlspecialchars($r['name'] ?? 'User'); ?></p>
                            <p class="text-xs text-gray-500"><?php echo htmlspecialchars(date('M j, Y', strtotime($r['created_at']))); ?></p>
                          </div>
                        </div>
                      </div>
                      <div class="flex items-center gap-1 mb-2">
                        <?php for ($i=1; $i<=5; $i++): ?>
                          <svg class="w-4 h-4 <?php echo $i<=(int)$r['rating']?'text-yellow-400':'text-gray-300'; ?> fill-current" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                          </svg>
                        <?php endfor; ?>
                      </div>
                      <p class="text-sm md:text-base text-gray-700 leading-relaxed"><?php echo nl2br(htmlspecialchars($r['comment'])); ?></p>
                    </div>
                  <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                  <div class="flex items-center justify-center gap-2 mt-6 pt-6 border-t border-gray-200">
                    <?php $base = BASE_URL . '/public/book.php' . ($bookPublicId !== '' ? '?book=' . urlencode($bookPublicId) : '?id=' . urlencode((string)$book['id'])) . '&sort=' . urlencode($sort) . '&page='; ?>
                    <a href="<?php echo $page<=1?'#':$base.($page-1); ?>" class="px-4 py-2 rounded-lg border border-gray-300 text-sm font-medium <?php echo $page<=1?'pointer-events-none opacity-50 bg-gray-100':'hover:bg-gray-50 active:bg-gray-100'; ?> transition-colors">
                      <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                      Prev
                    </a>
                    <span class="px-4 py-2 text-sm font-medium text-gray-700">Page <?php echo $page; ?> of <?php echo $totalPages; ?></span>
                    <a href="<?php echo $page>=$totalPages?'#':$base.($page+1); ?>" class="px-4 py-2 rounded-lg border border-gray-300 text-sm font-medium <?php echo $page>=$totalPages?'pointer-events-none opacity-50 bg-gray-100':'hover:bg-gray-50 active:bg-gray-100'; ?> transition-colors">
                      Next
                      <svg class="w-4 h-4 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                  </div>
                <?php endif; ?>
              <?php endif; ?>
            </div>

            <!-- Add Review Form -->
            <div>
              <h3 class="text-xl md:text-2xl font-bold text-brandBlue mb-4">Write a Review</h3>
              <?php if (!is_logged_in()): ?>
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 text-center">
                  <svg class="w-12 h-12 text-blue-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                  </svg>
                  <p class="text-gray-700 mb-3">Please log in to write a review</p>
                  <a href="<?php echo BASE_URL; ?>/public/login.php" class="inline-flex items-center justify-center bg-brandBlue hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition-colors">
                    Log In to Review
                  </a>
                </div>
              <?php else: ?>
                <form method="post" action="<?php echo BASE_URL; ?>/public/review-add.php" class="bg-white border border-gray-200 rounded-xl p-4 md:p-6 shadow-sm space-y-4">
                  <?php csrf_input(); ?>
                  <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                  
                  <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Your Rating *</label>
                    <select name="rating" class="w-full border border-gray-300 rounded-lg px-4 py-3 bg-white focus:ring-2 focus:ring-brandBlue focus:border-transparent text-base" required>
                      <option value="">Select your rating</option>
                      <option value="5">★★★★★ Excellent</option>
                      <option value="4">★★★★☆ Very Good</option>
                      <option value="3">★★★☆☆ Good</option>
                      <option value="2">★★☆☆☆ Fair</option>
                      <option value="1">★☆☆☆☆ Poor</option>
                    </select>
                  </div>
                  
                  <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Your Review *</label>
                    <textarea name="comment" rows="5" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-brandBlue focus:border-transparent text-base resize-none" placeholder="Share your thoughts about this book..." required></textarea>
                    <p class="text-xs text-gray-500 mt-2">Share your honest opinion to help other readers</p>
                  </div>
                  
                  <button type="submit" class="w-full bg-brandBlue hover:bg-blue-700 text-white font-bold px-6 py-3.5 rounded-lg transition-colors shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Submit Review
                  </button>
                </form>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Mobile Sticky Bottom CTA -->
  <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 px-4 py-3 shadow-2xl md:hidden z-20">
    <div class="flex items-center gap-3">
      <div class="flex-1 min-w-0">
        <p class="text-xs text-gray-600 truncate">Price</p>
        <p class="text-xl font-bold text-brandGold truncate">GHS <?php echo number_format($book['price'],2); ?></p>
      </div>
      <?php if ($paid): ?>
        <a href="<?php echo BASE_URL; ?>/public/download.php?book_id=<?php echo $book['id']; ?>" class="flex-shrink-0 bg-brandGold hover:bg-yellow-600 active:bg-yellow-700 text-black font-bold px-6 py-3.5 rounded-xl transition-colors shadow-lg min-w-[140px] text-center">
          Download
        </a>
      <?php else: ?>
        <a href="<?php echo BASE_URL; ?>/public/checkout.php?book_id=<?php echo $book['id']; ?>" class="flex-shrink-0 bg-brandBlue hover:bg-blue-700 active:bg-blue-800 text-white font-bold px-6 py-3.5 rounded-xl transition-colors shadow-lg min-w-[140px] text-center">
          Buy Now
        </a>
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
