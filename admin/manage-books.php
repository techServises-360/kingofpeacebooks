<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_admin();
$db = Database::getInstance();
$bookModel = new Book($db);
$q = sanitize($_GET['q'] ?? '');
$books = $q ? $bookModel->searchByTitle($q) : $bookModel->all();
flash_render();
?>
<div class="max-w-6xl mx-auto px-4 py-8">
  <div class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-4">
      <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="KingOfPeace Books" class="h-10 w-auto">
      <h2 class="text-2xl font-bold text-brandBlue">Manage Books</h2>
    </div>
    <a class="bg-brandGold text-black px-4 py-2 rounded-md font-semibold" href="<?php echo BASE_URL; ?>/admin/upload-book.php">+ Add Book</a>
  </div>
  <form method="get" class="mt-4 flex items-center gap-2">
    <input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" placeholder="Search by title..." class="w-full max-w-sm border border-gray-300 rounded-lg px-3 py-2">
    <button class="bg-brandBlue text-white px-4 py-2 rounded-md" type="submit">Search</button>
    <?php if ($q): ?>
      <a class="px-4 py-2 rounded-md border border-gray-300" href="<?php echo BASE_URL; ?>/admin/manage-books.php">Clear</a>
    <?php endif; ?>
  </form>
  <div class="mt-6 overflow-x-auto">
    <table class="min-w-full border border-gray-200 rounded-xl overflow-hidden">
      <thead class="bg-gray-50">
        <tr>
          <th class="text-left px-4 py-3 border-b">ID</th>
          <th class="text-left px-4 py-3 border-b">Thumbnail</th>
          <th class="text-left px-4 py-3 border-b">Title</th>
          <th class="text-left px-4 py-3 border-b">Price</th>
          <th class="text-left px-4 py-3 border-b">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($books as $b): ?>
        <tr class="odd:bg-white even:bg-gray-50">
          <td class="px-4 py-3 border-b"><?php echo $b['id']; ?></td>
          <td class="px-4 py-3 border-b">
            <img class="w-12 h-12 object-cover rounded" src="<?php echo cover_src($b['cover_image']); ?>" alt="thumb">
          </td>
          <td class="px-4 py-3 border-b"><?php echo sanitize($b['title']); ?></td>
          <td class="px-4 py-3 border-b">
            <?php if (!empty($b['base_price']) && $b['base_price'] > $b['price']): ?>
              <div>
                <span class="line-through text-gray-500">GHS <?php echo number_format($b['base_price'],2); ?></span>
                <span class="ml-1">GHS <?php echo number_format($b['price'],2); ?></span>
                <span class="ml-1 text-xs bg-red-100 text-red-800 px-1 py-0.5 rounded"><?php echo number_format($b['discount_percentage'],1); ?>% OFF</span>
              </div>
            <?php else: ?>
              GHS <?php echo number_format($b['price'],2); ?>
            <?php endif; ?>
          </td>
          <td class="px-4 py-3 border-b space-x-2">
            <a class="inline-block bg-brandBlue text-white px-3 py-1 rounded" href="<?php echo BASE_URL; ?>/public/book.php?id=<?php echo $b['id']; ?>">View</a>
            <a class="inline-block border border-gray-300 px-3 py-1 rounded" href="<?php echo BASE_URL; ?>/admin/edit-book.php?id=<?php echo $b['id']; ?>">Edit</a>
            <form id="form-delete-<?php echo $b['id']; ?>" class="inline" method="post" action="<?php echo BASE_URL; ?>/admin/delete-book.php">
              <?php csrf_input(); ?>
              <input type="hidden" name="id" value="<?php echo $b['id']; ?>">
              <button type="button" data-form="form-delete-<?php echo $b['id']; ?>" class="inline-block border border-red-300 text-red-700 px-3 py-1 rounded btn-open-delete">Delete</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 hidden items-center justify-center bg-black/50 z-50">
  <div class="bg-white rounded-xl shadow-lg w-full max-w-md mx-4">
    <div class="px-5 py-4 border-b">
      <h3 class="text-lg font-semibold text-brandBlue">Delete Book</h3>
    </div>
    <div class="px-5 py-4 text-gray-700">
      Are you sure you want to delete this book? This action cannot be undone.
    </div>
    <div class="px-5 py-4 border-t flex items-center justify-end gap-2">
      <button id="cancelDelete" class="px-4 py-2 rounded-md border border-gray-300">Cancel</button>
      <button id="confirmDelete" class="px-4 py-2 rounded-md bg-red-600 text-white">Delete</button>
    </div>
  </div>
</div>

<script>
  (function(){
    const modal = document.getElementById('deleteModal');
    const cancelBtn = document.getElementById('cancelDelete');
    const confirmBtn = document.getElementById('confirmDelete');
    let targetFormId = null;

    function openModal(formId){
      targetFormId = formId;
      modal.classList.remove('hidden');
      modal.classList.add('flex');
    }
    function closeModal(){
      modal.classList.add('hidden');
      modal.classList.remove('flex');
      targetFormId = null;
    }
    document.querySelectorAll('.btn-open-delete').forEach(btn => {
      btn.addEventListener('click', () => openModal(btn.getAttribute('data-form')));
    });
    cancelBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });
    confirmBtn.addEventListener('click', () => {
      if (!targetFormId) return closeModal();
      const form = document.getElementById(targetFormId);
      if (form) form.submit();
      closeModal();
    });
  })();
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
