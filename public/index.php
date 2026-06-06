<?php
$page_title = 'Home';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
$db = Database::getInstance();
$books = (new Book($db))->allApproved();
$reviewM = new Review($db);
// compute global average rating
$avgRow = $db->query('SELECT AVG(rating) AS a FROM reviews')->fetch();
$avgRating = $avgRow && $avgRow['a'] !== null ? round((float)$avgRow['a'], 1) : 4.9;
// Always use the earliest approved uploaded book as the hero background (fallback to any)
try {
  $oldest = $db->query("SELECT cover_image FROM books WHERE status='approved' ORDER BY created_at ASC LIMIT 1")->fetch();
} catch (Throwable $e) {
  $oldest = $db->query('SELECT cover_image FROM books ORDER BY created_at ASC LIMIT 1')->fetch();
}
$firstCover = $oldest && !empty($oldest['cover_image'])
  ? cover_src($oldest['cover_image'], '')
  : (isset($books[0]) ? cover_src($books[0]['cover_image'] ?? '', '') : '');
?>
<section class="relative bg-gray-900 text-white">
  <div class="absolute inset-0 z-0 bg-gradient-to-b from-gray-900/90 via-gray-900/80 to-black/90"></div>
  <?php if ($firstCover): ?>
    <div class="hidden md:block absolute inset-y-0 right-0 w-1/2 opacity-20" style="background-image:url('<?php echo $firstCover; ?>'); background-size:cover; background-position:center;"></div>
  <?php endif; ?>
  <div class="relative max-w-6xl mx-auto px-4 pt-10 md:pt-16 pb-10">
    <div class="grid md:grid-cols-2 gap-8 items-center">
      <div>
        <div class="mb-6">
          <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="KingOfPeace Books" class="h-16 w-auto">
        </div>
        <h1 class="mt-4 text-3xl md:text-5xl font-extrabold tracking-tight">A Home for African Knowledge, Truth, and Wisdom</h1>
        <p class="mt-4 text-gray-300 max-w-xl">Buy, download, and read instantly. Secure payments via Paystack. Built for African authors and readers.</p>
        <div class="mt-4 flex items-center gap-6 text-sm">
          <div class="flex items-center gap-2">
            <span class="flex items-center -ml-0.5">
              <?php for ($i=1; $i<=5; $i++): ?>
                <svg class="w-4 h-4 <?php echo $i <= ceil($avgRating) ? 'text-brandGold' : 'text-gray-500'; ?>" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.802 2.035a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.802-2.035a1 1 0 00-1.176 0l-2.802 2.035c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
              <?php endfor; ?>
            </span>
            <span class="text-gray-300"><?php echo number_format($avgRating,1); ?></span>
          </div>
          <div class="text-gray-300">Titles: <?php echo (int)count($books); ?>+</div>
          <div class="text-gray-300">Format: PDF</div>
        </div>
        <div class="mt-6 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
          <a href="#books" class="inline-flex items-center justify-center bg-brandGold text-black px-5 py-3 rounded-md font-semibold">Browse Books</a>
          <?php if (!is_logged_in()): ?>
            <a href="<?php echo BASE_URL; ?>/public/register.php" class="inline-flex items-center justify-center border border-white/20 px-5 py-3 rounded-md">More Info</a>
          <?php else: ?>
            <a href="<?php echo BASE_URL; ?>/public/profile.php" class="inline-flex items-center justify-center border border-white/20 px-5 py-3 rounded-md">Profile</a>
          <?php endif; ?>
        </div>
        <div class="mt-3 flex items-center gap-4 text-xs text-gray-300">
          <span class="inline-flex items-center gap-2">
            <svg class="w-4 h-4 text-green-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            Secure payments by Paystack
          </span>
          <span class="inline-flex items-center gap-2">
            <svg class="w-4 h-4 text-brandGold" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M2 12l10-9 10 9-10 9-10-9z"/></svg>
            Made for Africa
          </span>
          <a href="https://wa.me/233554521480" class="underline">WhatsApp Support</a>
        </div>
      </div>

      <div class="hidden md:block">
        <div class="flex items-end justify-end gap-3">
          <?php $thumbs = array_slice($books, 0, 3); ?>
          <?php foreach ($thumbs as $i => $t): ?>
            <?php 
              $isMid = ($i === 1);
              $base = 'relative rounded-lg overflow-hidden ring-1 ring-white/10 shadow-lg transform hover:-translate-y-1 hover:shadow-xl transition';
              $size = $isMid ? 'w-32 h-48 scale-105 md:scale-110 z-10' : 'w-28 h-40 opacity-90';
            ?>
            <div class="<?php echo $size . ' ' . $base; ?>">
              <img class="absolute inset-0 w-full h-full object-cover" src="<?php echo cover_src($t['cover_image']); ?>" alt="cover">
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="relative z-20 max-w-5xl mx-auto px-4 pb-10">
    <div class="min-w-0 text-center">
      <p class="text-sm text-gray-300">Quote of the moment</p>
      <p id="quote-text" class="mt-2 text-2xl md:text-3xl font-semibold tracking-tight text-white drop-shadow-sm"></p>
      <p id="quote-author" class="mt-2 text-base text-brandGold font-medium"></p>
      <p class="mt-1 text-sm text-gray-300">Books that awaken the mind and restore identity.</p>
    </div>
  </div>


  <div id="books" class="relative z-20 max-w-6xl mx-auto px-4 pb-12">
    <h2 class="text-2xl font-bold text-brandBlue mb-4">Latest Books</h2>
    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-2.5">
      <?php foreach ($books as $b): ?>
      <div class="flex flex-col">
        <a href="<?php echo BASE_URL; ?>/public/book.php?id=<?php echo $b['id']; ?>" class="block">
          <div class="relative w-full" style="padding-top:140%;">
            <img class="absolute inset-0 w-full h-full object-cover rounded-lg ring-1 ring-white/10" src="<?php echo cover_src($b['cover_image']); ?>" alt="cover">
          </div>
        </a>
        <div class="pt-2 text-center">
          <h3 class="text-white font-semibold text-xs leading-snug" title="<?php echo htmlspecialchars($b['title']); ?>" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;min-height:2.2em;">
            <?php echo sanitize($b['title']); ?>
          </h3>
          <?php $authorName = trim((string)$b['author']); if (strcasecmp($authorName,'Francis')===0) { $authorName = 'Kofi Zinor Francis Fosu'; } ?>
          <p class="mt-0.5 text-[10px] text-gray-300 truncate">by <?php echo sanitize($authorName); ?></p>
          <?php $rc = $reviewM->countForBook((int)$b['id']); ?>
          <div class="mt-1.5 flex items-center justify-center text-[10px] gap-1">
            <?php if (!empty($b['base_price']) && $b['base_price'] > $b['price']): ?>
              <span class="text-gray-300 line-through">GHS <?php echo number_format($b['base_price'],2); ?></span>
              <span class="text-brandGold font-bold">GHS <?php echo number_format($b['price'],2); ?></span>
            <?php else: ?>
              <span class="text-brandGold font-bold">GHS <?php echo number_format($b['price'],2); ?></span>
            <?php endif; ?>
            <span class="flex items-center gap-0.5 text-gray-300">
              <svg class="w-3 h-3 text-yellow-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.802 2.035a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.802-2.035a1 1 0 00-1.176 0l-2.802 2.035c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
              <?php echo (int)$rc; ?>
            </span>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<script>
  // Curated, brand-aligned quotes (no external API)
  (function(){
    const txt = document.getElementById('quote-text');
    const author = document.getElementById('quote-author');
    const quotes = [
      { content: 'When truth is hidden, people suffer without knowing why.', author: 'The King of Peace' },
      { content: 'A mind without knowledge becomes a slave, even in freedom.', author: 'The King of Peace' },
      { content: 'Not every problem is spiritual, and not every solution is physical—wisdom is knowing the difference.', author: 'The King of Peace' },
      { content: 'Those who control knowledge shape generations.', author: 'The King of Peace' },
      { content: 'Africa does not lack power; it lacks organized wisdom.', author: 'The King of Peace' }
    ];
    let i = 0;
    function rotate(){
      const q = quotes[i % quotes.length];
      txt.textContent = '“' + q.content + '”';
      author.textContent = q.author ? '— ' + q.author : '';
      i++;
    }
    rotate();
    setInterval(rotate, 15000);
  })();
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
