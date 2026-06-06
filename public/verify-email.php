<?php
$page_title = 'Verify Email';
require_once __DIR__ . '/../app/config/config.php';

$db = Database::getInstance();
$userModel = new User($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = (int)($_POST['user_id'] ?? 0);
    $code = sanitize($_POST['code'] ?? '');
    $resend = isset($_POST['resend']);
    
    if ($userId && $resend) {
        // Resend verification code
        $user = $userModel->find($userId);
        if ($user) {
            $newCode = $userModel->generateVerificationCode($userId);
            if ($newCode && function_exists('send_verification_email')) {
                $t = (isset($user['requested_author']) && (int)$user['requested_author'] === 1) ? 'author' : (string)($user['role'] ?? 'user');
                send_verification_email($user['email'], $newCode, $t);
                flash_set('success', 'New verification code sent to your email.');
            } else {
                flash_set('error', 'Failed to send verification code. Please try again.');
            }
        }
    } elseif ($userId && $code) {
        if ($userModel->verifyEmail($userId, $code)) {
            // Check if user requested author access
            $user = $userModel->find($userId);
            $requestedAuthor = isset($user['requested_author']) && (int)$user['requested_author'] === 1;
            
            if ($requestedAuthor) {
                flash_set('success', 'Email verified successfully! Your author request is pending admin approval.');
                header('Location: ' . BASE_URL . '/public/login.php');
            } else {
                flash_set('success', 'Email verified successfully! Your account is now active.');
                header('Location: ' . BASE_URL . '/public/login.php');
            }
            exit;
        } else {
            flash_set('error', 'Invalid or expired verification code. Please try again.');
        }
    }
}

// Get user from parameter
$userId = (int)($_GET['user_id'] ?? 0);
if (!$userId) {
    flash_set('error', 'Invalid verification request.');
    header('Location: ' . BASE_URL . '/public/register.php');
    exit;
}

$user = $userModel->find($userId);
if (!$user) {
    flash_set('error', 'User not found.');
    header('Location: ' . BASE_URL . '/public/register.php');
    exit;
}

// Check if already verified
if ($userModel->isEmailVerified($userId)) {
    flash_set('success', 'Your email is already verified! You can now log in.');
    header('Location: ' . BASE_URL . '/public/login.php');
    exit;
}

// Now include headers after all redirects are handled
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<main class="flex-1">
  <div class="max-w-md mx-auto px-4 py-10">
    <div class="flex justify-center mb-6">
      <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="KingOfPeace Books" class="h-12 w-auto">
    </div>
    
    <div class="bg-white border border-gray-200 rounded-xl p-6">
      <h1 class="text-2xl font-bold text-brandBlue mb-4 text-center">Verify Your Email</h1>
      
      <?php if (function_exists('flash_render')) { echo flash_render(); } ?>
      
      <p class="text-gray-600 mb-6 text-center">
        We've sent a verification code to <strong><?php echo htmlspecialchars($user['email']); ?></strong>
      </p>
      
      <form method="post" class="space-y-4">
        <?php csrf_input(); ?>
        <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
        
        <div>
          <label class="block text-sm font-medium mb-1">Verification Code</label>
          <input 
            type="text" 
            name="code" 
            required 
            maxlength="6"
            placeholder="Enter 6-digit code"
            class="w-full rounded border border-gray-300 px-3 py-2 text-center text-lg font-mono focus:outline-none focus:ring-2 focus:ring-brandGold"
          >
        </div>
        
        <button type="submit" class="w-full bg-brandGold text-black font-semibold px-4 py-2 rounded-md">
          Verify Email
        </button>
      </form>
      
      <div class="mt-6 text-center">
        <p class="text-sm text-gray-600 mb-2">Didn't receive the code?</p>
        <form method="post" class="inline">
          <?php csrf_input(); ?>
          <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
          <input type="hidden" name="resend" value="1">
          <button type="submit" class="text-brandBlue hover:underline text-sm">
            Resend Code
          </button>
        </form>
      </div>
      
      <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded">
        <p class="text-xs text-yellow-800">
          <strong>Important:</strong> You have 24 hours to verify your account. Unverified accounts will be automatically deleted.
        </p>
      </div>
    </div>
  </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
