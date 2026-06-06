<?php
require_once __DIR__ . '/../app/config/config.php';
$already = is_logged_in();
if ($already) { header('Location: ' . BASE_URL . '/public/profile.php'); exit; }
$db = Database::getInstance();
$auth = new AuthController($db);
if ($_SERVER['REQUEST_METHOD'] === 'POST') { $auth->register(); }

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>
<div class="max-w-md mx-auto px-4 py-8">
  <div class="flex justify-center mb-6">
    <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="KingOfPeace Books" class="h-12 w-auto">
  </div>
  <h2 class="text-2xl font-bold text-brandBlue mb-4 text-center">Create Account</h2>
    <?php 
    $error = $_GET['err'] ?? '';
    if ($error === '1'): ?>
        <p class="text-red-600 mb-3">Please fill in all required fields</p>
    <?php elseif ($error === '2'): ?>
        <p class="text-red-600 mb-3">Email already exists. Please use a different email.</p>
    <?php elseif ($error === '3'): ?>
        <p class="text-red-600 mb-3">Registration failed. Please try again.</p>
    <?php endif; 
    ?>
  <form method="post" class="bg-white border border-gray-200 rounded-xl p-5 space-y-4">
    <?php csrf_input(); ?>
    <div>
      <label class="font-semibold">Name</label>
      <input class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2" type="text" name="name" required>
    </div>
    <div>
      <label class="font-semibold">Email</label>
      <input class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2" type="email" name="email" required>
    </div>
    <div>
      <label class="font-semibold">Password</label>
      <div class="relative">
        <input class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 pr-10" type="password" name="password" id="regPassword" required>
        <button type="button" onclick="togglePassword('regPassword', this)" class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
          <svg id="regPassword-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
          </svg>
        </button>
      </div>
    </div>
    <div>
      <label class="font-semibold">Confirm Password</label>
      <div class="relative">
        <input class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 pr-10" type="password" name="confirm_password" id="regConfirmPassword" required oninput="checkPasswordMatch()">
        <button type="button" onclick="togglePassword('regConfirmPassword', this)" class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
          <svg id="regConfirmPassword-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
          </svg>
        </button>
      </div>
      <div id="passwordMatchError" class="text-red-500 text-sm mt-1 hidden">Passwords do not match</div>
    </div>
    <div>
      <label class="font-semibold">Sign up as</label>
      <select name="role" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2">
        <option value="user">Reader/User</option>
        <option value="author">Author (requires admin approval)</option>
      </select>
      <p class="text-xs text-gray-600 mt-1">If you choose Author, your author privileges will be activated after admin approval. You can still use your account meanwhile.</p>
    </div>
    <button class="w-full bg-brandGold text-black font-semibold px-4 py-2 rounded-md" type="submit" onclick="return validatePassword()">Register</button>
  </form>
  <p class="mt-3 text-sm text-gray-600">Already have an account? <a class="text-brandBlue underline" href="<?php echo BASE_URL; ?>/public/login.php">Login</a></p>
</div>

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

function checkPasswordMatch() {
  const password = document.getElementById('regPassword').value;
  const confirmPassword = document.getElementById('regConfirmPassword').value;
  const errorDiv = document.getElementById('passwordMatchError');
  
  if (confirmPassword && password !== confirmPassword) {
    errorDiv.classList.remove('hidden');
    errorDiv.textContent = 'Passwords do not match';
  } else {
    errorDiv.classList.add('hidden');
  }
}

function validatePassword() {
  const password = document.getElementById('regPassword').value;
  const confirmPassword = document.getElementById('regConfirmPassword').value;
  const errorDiv = document.getElementById('passwordMatchError');
  
  if (!password || !confirmPassword) {
    errorDiv.classList.remove('hidden');
    errorDiv.textContent = 'Please fill in both password fields';
    return false;
  }
  
  if (password !== confirmPassword) {
    errorDiv.classList.remove('hidden');
    errorDiv.textContent = 'Passwords do not match';
    return false;
  }
  
  errorDiv.classList.add('hidden');
  return true;
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
