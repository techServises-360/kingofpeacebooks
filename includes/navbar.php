<?php $isHome = basename($_SERVER['SCRIPT_NAME']) === 'index.php'; ?>
<nav class="<?php echo $isHome
  ? 'z-30 text-white md:absolute md:inset-x-0 md:top-0 md:bg-transparent sticky top-0 bg-[#0a1224]'
  : 'sticky top-0 z-30 bg-white text-brandBlue shadow-sm'; ?>">
  <div class="max-w-6xl mx-auto px-4">
    <div class="flex items-center justify-between h-16">
      <a href="<?php echo BASE_URL; ?>/public/index.php" class="font-bold text-lg tracking-wide <?php echo $isHome ? '' : 'text-brandBlue'; ?>">
        <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="KingOfPeace Books" class="h-8 w-auto inline-block">
      </a>
      <?php $cuName = null; $avatar = BASE_URL . '/assets/images/avatar.png'; if (is_logged_in()) { try { $dbx = Database::getInstance(); $um = new User($dbx); $cu = $um->find((int)current_user_id()); $cuName = $cu['name'] ?? null; if (!empty($cu['avatar'])) { $avatar = BASE_URL . '/' . ltrim($cu['avatar'],'/'); } } catch (Throwable $e) { $cuName = null; } } ?>
      <button type="button" class="md:hidden inline-flex items-center justify-center p-2 rounded-full <?php echo $isHome ? 'bg-white/10 hover:bg-white/20 text-white' : 'bg-gray-100 hover:bg-gray-200 text-brandBlue'; ?> focus:outline-none" aria-label="Open menu" onclick="var m=document.getElementById('mobileMenu'); if(m){ m.classList.toggle('hidden'); }">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
      </button>
      <div class="hidden md:flex items-center gap-6">
        <ul class="flex items-center gap-6 text-sm whitespace-nowrap no-scrollbar <?php echo $isHome ? 'text-gray-300' : 'text-gray-700'; ?>">
          <li><a class="<?php echo $isHome ? 'hover:text-white' : 'hover:text-brandBlue'; ?>" href="<?php echo BASE_URL; ?>/public/index.php">Home</a></li>
          <li><a class="<?php echo $isHome ? 'hover:text-white' : 'hover:text-brandBlue'; ?>" href="<?php echo BASE_URL; ?>/public/books.php">Popular Lists</a></li>
          <li><a class="<?php echo $isHome ? 'hover:text-white' : 'hover:text-brandBlue'; ?>" href="<?php echo is_logged_in() ? BASE_URL.'/public/my-books.php' : BASE_URL.'/public/books.php'; ?>">Library</a></li>
        </ul>
        <?php if (function_exists('is_approved_author') && is_approved_author()): ?>
          <a href="<?php echo BASE_URL; ?>/author/submit-book.php" class="inline-flex items-center bg-brandGold text-black px-4 py-2 rounded-full text-sm font-semibold">Publish Your Book</a>
        <?php else: ?>
          <a href="<?php echo is_logged_in() ? BASE_URL.'/public/profile.php' : BASE_URL.'/public/register.php'; ?>" class="inline-flex items-center bg-brandGold text-black px-4 py-2 rounded-full text-sm font-semibold">Publish Your Book</a>
        <?php endif; ?>
        <div class="flex items-center gap-2">
          <a class="inline-flex items-center justify-center w-8 h-8 rounded-full <?php echo $isHome ? 'bg-white/10 hover:bg-white/20 text-white' : 'bg-gray-100 hover:bg-gray-200 text-brandBlue'; ?>" href="<?php echo BASE_URL; ?>/public/books.php" title="Search">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
          </a>
          <a class="inline-flex items-center justify-center w-8 h-8 rounded-full <?php echo $isHome ? 'bg-white/10 hover:bg-white/20 text-white' : 'bg-gray-100 hover:bg-gray-200 text-brandBlue'; ?>" href="#" title="Notifications">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 1 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          </a>
          <?php if (is_logged_in()): ?>
            <?php 
            // Determine correct profile/dashboard link based on user role
            $profileLink = BASE_URL . '/public/profile.php';
            if (function_exists('is_admin') && is_admin()) {
              $profileLink = BASE_URL . '/admin/dashboard.php';
            } elseif (function_exists('is_approved_author') && is_approved_author()) {
              $profileLink = BASE_URL . '/author/dashboard.php';
            }
            ?>
            <a href="<?php echo $profileLink; ?>" class="inline-flex items-center gap-2 rounded-full px-2 py-1 <?php echo $isHome ? 'bg-white/10 text-white' : 'bg-gray-100 text-brandBlue'; ?>">
              <span class="text-sm"><?php echo htmlspecialchars($cuName ?: 'Profile'); ?></span>
              <span class="inline-block w-7 h-7 rounded-full overflow-hidden <?php echo $isHome ? 'bg-white/20' : 'bg-gray-200'; ?>">
                <img src="<?php echo $avatar; ?>" alt="avatar" class="w-full h-full object-cover">
              </span>
            </a>
            <a href="<?php echo BASE_URL; ?>/public/login.php?action=logout" class="text-sm <?php echo $isHome ? 'hover:text-white' : 'hover:text-brandBlue'; ?>">Logout</a>
          <?php else: ?>
            <a href="<?php echo BASE_URL; ?>/public/login.php" class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm <?php echo $isHome ? 'bg-white/10 text-white' : 'bg-gray-100 text-brandBlue'; ?>">Login</a>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div id="mobileMenu" class="md:hidden hidden pb-3">
      <ul class="flex items-center gap-4 overflow-x-auto whitespace-nowrap py-2 no-scrollbar text-sm <?php echo $isHome ? 'text-gray-200' : 'text-brandBlue'; ?>">
        <li><a class="block px-2 py-1 rounded <?php echo $isHome ? 'hover:bg-white/10' : 'hover:bg-gray-100'; ?>" href="<?php echo BASE_URL; ?>/public/index.php">Home</a></li>
        <li><a class="block px-2 py-1 rounded <?php echo $isHome ? 'hover:bg-white/10' : 'hover:bg-gray-100'; ?>" href="<?php echo BASE_URL; ?>/public/books.php">Popular Lists</a></li>
        <li><a class="block px-2 py-1 rounded <?php echo $isHome ? 'hover:bg-white/10' : 'hover:bg-gray-100'; ?>" href="<?php echo is_logged_in() ? BASE_URL.'/public/my-books.php' : BASE_URL.'/public/books.php'; ?>">Library</a></li>
        <li><a class="block px-2 py-1 rounded bg-brandGold text-black" href="<?php echo function_exists('is_approved_author') && is_approved_author() ? BASE_URL.'/author/submit-book.php' : (is_logged_in()?BASE_URL.'/public/profile.php':BASE_URL.'/public/register.php'); ?>">Publish Your Book</a></li>
        <?php if (function_exists('is_admin') && is_admin()): ?>
          <li><a class="block px-2 py-1 rounded <?php echo $isHome ? 'hover:bg-white/10' : 'hover:bg-gray-100'; ?>" href="<?php echo BASE_URL; ?>/admin/dashboard.php">Admin Dashboard</a></li>
        <?php endif; ?>
        <?php if (is_logged_in()): ?>
          <?php 
          // Determine correct profile/dashboard link based on user role
          $profileLink = BASE_URL . '/public/profile.php';
          if (function_exists('is_admin') && is_admin()) {
            $profileLink = BASE_URL . '/admin/dashboard.php';
          } elseif (function_exists('is_approved_author') && is_approved_author()) {
            $profileLink = BASE_URL . '/author/dashboard.php';
          }
          ?>
          <li><a class="block px-2 py-1 rounded <?php echo $isHome ? 'hover:bg-white/10' : 'hover:bg-gray-100'; ?>" href="<?php echo $profileLink; ?>"><?php echo htmlspecialchars($cuName ?: 'Profile'); ?></a></li>
          <li><a class="block px-2 py-1 rounded border <?php echo $isHome ? 'border-white/20' : 'border-gray-200'; ?>" href="<?php echo BASE_URL; ?>/public/login.php?action=logout">Logout</a></li>
        <?php else: ?>
          <li><a class="block px-2 py-1 rounded <?php echo $isHome ? 'hover:bg-white/10' : 'hover:bg-gray-100'; ?>" href="<?php echo BASE_URL; ?>/public/login.php">Login</a></li>
          <li><a class="block px-2 py-1 rounded <?php echo $isHome ? 'hover:bg-white/10' : 'hover:bg-gray-100'; ?>" href="<?php echo BASE_URL; ?>/public/register.php">Register</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
