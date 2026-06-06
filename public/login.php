<?php
require_once __DIR__ . '/../app/config/config.php';
$db = Database::getInstance();
$auth = new AuthController($db);
if (($_GET['action'] ?? '') === 'logout') { $auth->logout(); }
if ($_SERVER['REQUEST_METHOD'] === 'POST') { $auth->login(); }

// Render page only if not redirected above
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>
<main class="flex-1 flex items-center justify-center px-4 py-6">
  <div class="w-full max-w-md">
    <div class="flex justify-center mb-6">
      <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="KingOfPeace Books" class="h-12 w-auto">
    </div>
    <h2 class="text-2xl font-bold text-brandBlue mb-4 text-center">Login</h2>
    <?php if (function_exists('flash_render')) { echo flash_render(); } ?>
    <?php 
    $error = $_GET['err'] ?? '';
    if ($error === '1'): ?>
        <p class="text-red-600 mb-3">Invalid email or password</p>
    <?php elseif ($error === '2'): ?>
        <p class="text-red-600 mb-3">Please verify your email before logging in</p>
    <?php elseif ($error === '3'): ?>
        <p class="text-red-600 mb-3">Your account is suspended. Please contact support.</p>
    <?php endif; 
    ?>
    <form method="post" class="bg-white border border-gray-200 rounded-xl p-5 space-y-4">
      <?php csrf_input(); ?>
      <div>
        <label class="font-semibold">Email</label>
        <input class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2" type="email" name="email" required>
      </div>
      <div>
        <label class="font-semibold">Password</label>
        <div class="relative">
          <input class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 pr-10" type="password" name="password" id="loginPassword" required>
          <button type="button" onclick="togglePassword('loginPassword', this)" class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
            <svg id="loginPassword-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
            </svg>
          </button>
        </div>
      </div>
      <button class="w-full bg-brandGold text-black font-semibold px-4 py-2 rounded-md" type="submit">Login</button>
    </form>
    <p class="mt-3 text-sm text-gray-600">Don't have an account? <a class="text-brandBlue underline" href="<?php echo BASE_URL; ?>/public/register.php">Sign up</a></p>
  </div>
  
</main>

<script>
function togglePassword(inputId, button) {
  const input = document.getElementById(inputId);
  const icon = document.getElementById(inputId + '-icon');
  
  if (input.type === 'password') {
    input.type = 'text';
    icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563 3.669A10.05 10.05 0 0012 19c4.478 0 8.268-2.943 9.543-7a9.97 9.97 0 001.563-3.669A10.05 10.05 0 0113.875 18.825z"></path>';
  } else {
    input.type = 'password';
    icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>';
  }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
