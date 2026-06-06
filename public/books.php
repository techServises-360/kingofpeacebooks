<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
$db = Database::getInstance();
$bookModel = new Book($db);
$reviewM = new Review($db);

// Inputs
$q = sanitize($_GET['q'] ?? '');
$author = sanitize($_GET['author'] ?? '');
$min_price = sanitize($_GET['min_price'] ?? '');
$max_price = sanitize($_GET['max_price'] ?? '');
$sort = sanitize($_GET['sort'] ?? 'newest');
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = min(48, max(8, (int)($_GET['per_page'] ?? 12)));

$result = $bookModel->filterAndPaginate([
  'q' => $q,
  'author' => $author,
  'min_price' => $min_price,
  'max_price' => $max_price,
  'sort' => $sort,
  'page' => $page,
  'per_page' => $per_page,
]);
$authors = $bookModel->authors();
$items = $result['items'];
$total = $result['total'];
$pages = max(1, (int)ceil($total / $per_page));
?>
<div class="max-w-6xl mx-auto px-4 py-8">
  <div class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-4">
      <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="KingOfPeace Books" class="h-10 w-auto">
      <h1 class="text-2xl font-bold text-brandBlue">All Books</h1>
    </div>
  </div>
  <form method="get" class="mt-4 grid gap-3 md:grid-cols-4">
    <div>
      <label class="text-sm text-gray-600">Search</label>
      <input class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2" type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" placeholder="Title or author">
    </div>
    <div>
      <label class="text-sm text-gray-600">Author</label>
      <select class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2" name="author">
        <option value="">All</option>
        <?php foreach ($authors as $a): ?>
          <option value="<?php echo htmlspecialchars($a); ?>" <?php echo $author === $a ? 'selected' : ''; ?>><?php echo htmlspecialchars($a); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="grid grid-cols-2 gap-2">
      <div>
        <label class="text-sm text-gray-600">Min Price</label>
        <input class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2" type="number" step="0.01" name="min_price" value="<?php echo htmlspecialchars($min_price); ?>">
      </div>
      <div>
        <label class="text-sm text-gray-600">Max Price</label>
        <input class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2" type="number" step="0.01" name="max_price" value="<?php echo htmlspecialchars($max_price); ?>">
      </div>
    </div>
    <div>
      <label class="text-sm text-gray-600">Sort</label>
      <select class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2" name="sort">
        <option value="newest" <?php echo $sort==='newest'?'selected':''; ?>>Newest</option>
        <option value="price_asc" <?php echo $sort==='price_asc'?'selected':''; ?>>Price: Low to High</option>
        <option value="price_desc" <?php echo $sort==='price_desc'?'selected':''; ?>>Price: High to Low</option>
        <option value="title_asc" <?php echo $sort==='title_asc'?'selected':''; ?>>Title: A → Z</option>
        <option value="title_desc" <?php echo $sort==='title_desc'?'selected':''; ?>>Title: Z → A</option>
      </select>
    </div>
    <div class="md:col-span-4 flex items-end gap-2">
      <button class="bg-brandBlue text-white px-4 py-2 rounded-md" type="submit">Apply</button>
      <a class="px-4 py-2 rounded-md border border-gray-300" href="<?php echo BASE_URL; ?>/public/books.php">Reset</a>
    </div>
  </form>

  <div class="mt-6">
    <p class="text-sm text-gray-600">Showing <?php echo count($items); ?> of <?php echo $total; ?> results</p>
  </div>

  <div class="mt-3 grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-2.5">
    <?php foreach ($items as $b): ?>
    <div class="flex flex-col">
      <a href="<?php echo BASE_URL; ?>/public/book.php?id=<?php echo $b['id']; ?>" class="block">
        <div class="relative w-full" style="padding-top:140%;">
          <img class="absolute inset-0 w-full h-full object-cover rounded-lg ring-1 ring-black/10" src="<?php echo cover_src($b['cover_image']); ?>" alt="cover">
        </div>
      </a>
      <div class="pt-2 text-center">
        <h3 class="text-brandBlue font-semibold text-xs leading-snug" title="<?php echo htmlspecialchars($b['title']); ?>" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;min-height:2.2em;">
          <?php echo sanitize($b['title']); ?>
        </h3>
        <p class="mt-0.5 text-[10px] text-gray-600 truncate">by <?php echo sanitize($b['author']); ?></p>
        <?php $rc = $reviewM->countForBook((int)$b['id']); ?>
        <div class="mt-1.5 flex items-center justify-center text-[10px] gap-1">
          <?php if (!empty($b['base_price']) && $b['base_price'] > $b['price']): ?>
            <span class="text-gray-500 line-through">GHS <?php echo number_format($b['base_price'],2); ?></span>
            <span class="text-brandGold font-bold">GHS <?php echo number_format($b['price'],2); ?></span>
          <?php else: ?>
            <span class="text-brandGold font-bold">GHS <?php echo number_format($b['price'],2); ?></span>
          <?php endif; ?>
          <span class="flex items-center gap-0.5 text-gray-600">
            <svg class="w-3 h-3 text-yellow-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.802 2.035a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.802-2.035a1 1 0 00-1.176 0l-2.802 2.035c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            <?php echo (int)$rc; ?>
          </span>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <?php if ($pages > 1): ?>
  <div class="mt-6 flex items-center justify-center gap-2">
    <?php for ($p = 1; $p <= $pages; $p++): ?>
      <?php
        $params = $_GET; $params['page'] = $p;
        $url = BASE_URL . '/public/books.php?' . http_build_query($params);
      ?>
      <a href="<?php echo $url; ?>" class="px-3 py-2 rounded border <?php echo $p===$page ? 'bg-brandBlue text-white border-brandBlue' : 'border-gray-300'; ?>"><?php echo $p; ?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
