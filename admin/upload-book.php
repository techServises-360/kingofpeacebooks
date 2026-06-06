<?php
require_once __DIR__ . '/../app/config/config.php';
require_admin();
$db = Database::getInstance();
$bc = new BookController($db);
if ($_SERVER['REQUEST_METHOD'] === 'POST') { $bc->handleUpload(); }

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>
<div class="max-w-3xl mx-auto px-4 py-8">
  <div class="flex items-center gap-4 mb-6">
    <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="KingOfPeace Books" class="h-10 w-auto">
    <h2 class="text-2xl font-bold text-brandBlue">Upload Book</h2>
  </div>
  <form class="mt-6 bg-white border border-gray-200 rounded-xl p-6 space-y-4" method="post" enctype="multipart/form-data">
    <?php csrf_input(); ?>
    <div>
      <label class="font-semibold">Title</label>
      <input class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2" type="text" name="title" required>
    </div>
    <div>
      <label class="font-semibold">Author</label>
      <input class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2" type="text" name="author" required>
    </div>
    <div class="grid grid-cols-2 gap-4">
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
      <textarea class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2" name="description" rows="5" required></textarea>
    </div>
    <div>
      <label class="font-semibold">Cover Image</label>
      <div class="flex items-center gap-3 mt-1">
        <img id="coverPreview" class="w-12 h-12 object-cover rounded" src="<?php echo BASE_URL; ?>/assets/images/placeholder.jpg" alt="thumb">
        <input id="coverInput" class="w-full border border-gray-300 rounded-lg px-3 py-2" type="file" name="cover_image" accept="image/*" required>
      </div>
    </div>
    <div>
      <label class="font-semibold">Book File (PDF)</label>
      <input class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2" type="file" name="book_file" accept="application/pdf" required>
    </div>
    <button class="bg-brandGold text-black font-semibold px-4 py-2 rounded-md" type="submit">Save</button>
  </form>
</div>
<script>
  const input = document.getElementById('coverInput');
  const img = document.getElementById('coverPreview');
  if (input) {
    input.addEventListener('change', (e) => {
      const file = e.target.files && e.target.files[0];
      if (!file) return;
      const url = URL.createObjectURL(file);
      img.src = url;
    });
  }
  
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
