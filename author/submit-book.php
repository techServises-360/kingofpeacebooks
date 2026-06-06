<?php
$page_title = 'Submit Book';
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/helpers/auth.php';

// Check if user is verified author before proceeding
if (!is_logged_in()) {
  header('Location: ' . BASE_URL . '/public/login.php');
  exit;
}

$db = Database::getInstance();
$userModel = new User($db);

// Check if user has verified their email for author access
$userId = (int)current_user_id();
if (!$userModel->isEmailVerified($userId)) {
  flash_set('error', 'You must verify your email before submitting books. Please check your email for the verification code.');
  header('Location: ' . BASE_URL . '/public/verify-email.php?user_id=' . $userId);
  exit;
}

require_approved_author();
require_once __DIR__ . '/../app/models/Book.php';
require_once __DIR__ . '/../app/controllers/BookController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  (new BookController($db))->handleAuthorUpload();
  exit;
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>
<main class="flex-1">
  <div class="max-w-3xl mx-auto px-4 py-10">
    <div class="flex items-center gap-4 mb-6">
      <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="KingOfPeace Books" class="h-10 w-auto">
      <h1 class="text-2xl font-semibold">Submit Your Book</h1>
    </div>
    <form class="space-y-6" action="" method="post" enctype="multipart/form-data">
      <?php csrf_input(); ?>
      <div>
        <label class="block text-sm font-medium mb-1">Title</label>
        <input required name="title" type="text" class="w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brandGold" placeholder="Book title">
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Author Name (optional)</label>
        <input name="author" type="text" class="w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brandGold" placeholder="Defaults to your profile name">
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1">Base Price (GHS)</label>
          <input required name="base_price" type="number" step="0.01" min="0" id="base_price" class="w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brandGold" placeholder="0.00">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Discount (%)</label>
          <input required name="discount_percentage" type="number" step="0.01" min="0" max="100" id="discount_percentage" value="0" class="w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brandGold" placeholder="0.00">
        </div>
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Final Price (GHS)</label>
        <input readonly name="price" type="number" step="0.01" min="0" id="final_price" class="w-full rounded border border-gray-200 bg-gray-50 px-3 py-2" placeholder="Auto-calculated">
        <p class="text-xs text-gray-500 mt-1">Final price is automatically calculated from base price and discount</p>
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Description</label>
        <textarea required name="description" rows="5" class="w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brandGold" placeholder="Brief description"></textarea>
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Cover Image</label>
        <input required name="cover_image" type="file" accept="image/*" class="block w-full text-sm" />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Book File</label>
        <input required name="book_file" type="file" class="block w-full text-sm" />
      </div>
      <p class="text-sm text-gray-600">Submissions require approval by our editors. You will be notified once reviewed.</p>
      <div class="pt-2">
        <button class="inline-flex items-center px-4 py-2 rounded bg-brandGold text-black font-semibold hover:opacity-90">Submit for Review</button>
      </div>
    </form>
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
  </div>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
