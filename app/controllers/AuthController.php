<?php
class AuthController {
  private User $users;
  public function __construct(PDO $db) { $this->users = new User($db); }

  public function register() {
    // Temporarily disable CSRF for debugging - REMOVE IN PRODUCTION
    // try {
    //     verify_csrf();
    // } catch (Exception $csrfError) {
    //     $_SESSION['debug_error'] = $csrfError->getMessage();
    //     header('Location: ' . BASE_URL . '/public/register.php?err=3');
    //     exit;
    // }
    
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $roleChoice = sanitize($_POST['role'] ?? 'user'); // 'user' or 'author'
    if (!$name || !$email || !$password) { header('Location: ' . BASE_URL . '/public/register.php?err=1'); exit; }
    
    try {
        $requestedAuthor = ($roleChoice === 'author');
        
        // Create user immediately but mark as unverified
        $userId = $this->users->create($name, $email, $password, 'user', $requestedAuthor);
        
        if ($userId) {
            // Generate and send verification code
            $userType = $requestedAuthor ? 'author' : 'user';
            $verificationCode = $this->users->generateVerificationCode($userId);
            if ($verificationCode && function_exists('send_verification_email')) {
                send_verification_email($email, $verificationCode, $userType);
                flash_set('info', 'Account created! Please check your email for verification code. You have 24 hours to verify your account.');
                header('Location: ' . BASE_URL . '/public/verify-email.php?user_id=' . $userId);
                exit;
            } else {
                // Fallback if email sending fails
                flash_set('success', 'Account created successfully! Please check your email for verification.');
                header('Location: ' . BASE_URL . '/public/login.php');
                exit;
            }
        } else {
            flash_set('error', 'Failed to create account. Please try again.');
            header('Location: ' . BASE_URL . '/public/register.php?err=3');
            exit;
        }
        
    } catch (Exception $e) {
        $errorMsg = $e->getMessage();
        if ($errorMsg === 'Email already exists') {
            header('Location: ' . BASE_URL . '/public/register.php?err=2');
        } else {
            // Store debug error in session
            $_SESSION['debug_error'] = $errorMsg;
            header('Location: ' . BASE_URL . '/public/register.php?err=3');
        }
        exit;
    }
  }

  public function login() {
    try {
      verify_csrf();
    } catch (Throwable $e) {
      if (function_exists('flash_set')) {
        flash_set('error', $e->getMessage());
      }
      header('Location: ' . BASE_URL . '/public/login.php');
      exit;
    }
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $u = $this->users->findByEmail($email);
    if (!$u || !password_verify($password, $u['password'])) {
      header('Location: ' . BASE_URL . '/public/login.php?err=1'); exit;
    }
    
    // Check if user is email verified
    if (!$this->users->isEmailVerified($u['id'])) {
      // Ensure they have a usable verification code (generate a fresh one if missing/expired)
      $needsNewCode = empty($u['email_verification_code']) || empty($u['email_verification_expires']);
      if (!$needsNewCode) {
        $expTs = strtotime((string)$u['email_verification_expires']);
        if ($expTs !== false && $expTs <= time()) {
          $needsNewCode = true;
        }
      }

      if ($needsNewCode) {
        $verificationCode = $this->users->generateVerificationCode((int)$u['id']);
        if ($verificationCode && function_exists('send_verification_email')) {
          send_verification_email((string)$u['email'], $verificationCode, (string)($u['role'] ?? 'user'));
        }
      }

      flash_set('info', 'Please verify your email to continue. We\'ve sent you a verification code.');
      header('Location: ' . BASE_URL . '/public/verify-email.php?user_id=' . (int)$u['id']);
      exit;
    }
    
    // Check if user is suspended
    if ($this->users->isSuspended($u['id'])) {
      header('Location: ' . BASE_URL . '/public/login.php?err=3');
      exit;
    }
    
    login_user($u['id'], $u['role']);
    
    // Redirect based on user role and verification status
    if ($u['role'] === 'author') {
      $authorStatus = $u['author_status'] ?? null;
      if ($authorStatus === 'approved') {
        flash_set('success', 'Welcome back, Author!');
        header('Location: ' . BASE_URL . '/author/dashboard.php');
      } elseif ($authorStatus === 'rejected') {
        flash_set('error', 'Your author request was rejected. Contact support for details.');
        header('Location: ' . BASE_URL . '/public/profile.php');
      } else {
        flash_set('info', 'Your author request is pending admin approval.');
        header('Location: ' . BASE_URL . '/public/profile.php');
      }
    } else {
      flash_set('success', 'Welcome back!');
      header('Location: ' . BASE_URL . '/public/index.php');
    }
  }

  public function logout() {
    logout_user();
    header('Location: ' . BASE_URL . '/public/index.php');
  }
}
