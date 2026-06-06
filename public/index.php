<?php
$page_title = 'Home';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
$db = Database::getInstance();
$bookM = new Book($db);
$books = $bookM->allApproved();
$reviewM = new Review($db);
$totalBooks = count($books);
?>

<!-- Hero Section: Clear Value Proposition -->
<section class="bg-gradient-to-br from-gray-900 via-blue-900 to-gray-900 text-white">
  <div class="max-w-6xl mx-auto px-4 py-12 md:py-20">
    <div class="text-center max-w-3xl mx-auto">
      <!-- Logo -->
      <div class="mb-6">
        <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="KingOfPeace Books" class="h-16 md:h-20 w-auto mx-auto">
      </div>
      
      <!-- Clear Headline -->
      <h1 class="text-3xl md:text-5xl lg:text-6xl font-bold mb-4 leading-tight">
        African Books.<br>
        <span class="text-brandGold">Buy. Download. Read.</span>
      </h1>
      
      <!-- Simple Sub-headline -->
      <p class="text-lg md:text-xl text-gray-300 mb-8 max-w-2xl mx-auto">
        Instant access to <?php echo $totalBooks; ?>+ books by African authors
      </p>
      
      <!-- Single Primary CTA -->
      <a href="#latest-books" class="inline-flex items-center justify-center bg-brandGold hover:bg-yellow-500 text-black text-lg md:text-xl font-bold px-8 md:px-12 py-4 md:py-5 rounded-xl shadow-2xl hover:shadow-yellow-500/50 transition-all transform hover:scale-105">
        Browse Books Now
        <svg class="w-6 h-6 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
        </svg>
      </a>
      
      <!-- Trust Indicators (Minimal) -->
      <div class="mt-8 flex flex-wrap items-center justify-center gap-4 md:gap-6 text-sm text-gray-400">
        <span class="flex items-center gap-2">
          <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
          </svg>
          Secure Payment
        </span>
        <span class="flex items-center gap-2">
          <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
          </svg>
          Instant Download
        </span>
        <span class="flex items-center gap-2">
          <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
          </svg>
          PDF Format
        </span>
      </div>
    </div>
  </div>
</section>

<!-- How It Works: 3 Simple Steps -->
<section class="bg-white py-12 md:py-16">
  <div class="max-w-6xl mx-auto px-4">
    <h2 class="text-2xl md:text-3xl font-bold text-center text-gray-900 mb-12">How It Works</h2>
    
    <div class="grid md:grid-cols-3 gap-8">
      <!-- Step 1 -->
      <div class="text-center">
        <div class="w-16 h-16 bg-brandBlue text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">
          1
        </div>
        <h3 class="text-xl font-bold text-gray-900 mb-2">Browse & Choose</h3>
        <p class="text-gray-600">Find the book you want from our collection</p>
      </div>
      
      <!-- Step 2 -->
      <div class="text-center">
        <div class="w-16 h-16 bg-brandBlue text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">
          2
        </div>
        <h3 class="text-xl font-bold text-gray-900 mb-2">Pay Securely</h3>
        <p class="text-gray-600">Complete payment with mobile money or card</p>
      </div>
      
      <!-- Step 3 -->
      <div class="text-center">
        <div class="w-16 h-16 bg-brandGold text-black rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">
          3
        </div>
        <h3 class="text-xl font-bold text-gray-900 mb-2">Download & Read</h3>
        <p class="text-gray-600">Get instant access to your PDF book</p>
      </div>
    </div>
  </div>
</section>

<!-- Latest Books Section -->
<section id="latest-books" class="bg-gray-50 py-12 md:py-16">
  <div class="max-w-6xl mx-auto px-4">
    <!-- Clear Section Header -->
    <div class="flex items-center justify-between mb-8">
      <div>
        <h2 class="text-2xl md:text-3xl font-bold text-gray-900">Latest Books</h2>
        <p class="text-gray-600 mt-1">Recently added to our collection</p>
      </div>
      <a href="<?php echo BASE_URL; ?>/public/books.php" class="hidden md:inline-flex items-center text-brandBlue hover:text-blue-700 font-semibold">
        View All
        <svg class="w-5 h-5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
      </a>
    </div>
    
    <!-- Books Grid: Clean & Scannable -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 md:gap-6">
      <?php $displayBooks = array_slice($books, 0, 10); ?>
      <?php foreach ($displayBooks as $b): ?>
      <a href="<?php echo $bookM->publicUrl($b); ?>" class="group">
        <div class="bg-white rounded-xl overflow-hidden shadow-md hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
          <!-- Book Cover -->
          <div class="relative bg-gray-100" style="padding-top:140%;">
            <img src="<?php echo cover_src($b['cover_image']); ?>" 
                 alt="<?php echo htmlspecialchars($b['title']); ?>" 
                 class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
          </div>
          
          <!-- Book Info -->
          <div class="p-3">
            <h3 class="font-bold text-sm text-gray-900 line-clamp-2 mb-1 group-hover:text-brandBlue">
              <?php echo sanitize($b['title']); ?>
            </h3>
            <?php $authorName = trim((string)$b['author']); if (strcasecmp($authorName,'Francis')===0) { $authorName = 'Kofi Zinor Francis Fosu'; } ?>
            <p class="text-xs text-gray-600 truncate mb-2">
              <?php echo sanitize($authorName); ?>
            </p>
            
            <!-- Price (Prominent) -->
            <div class="flex items-center justify-between">
              <span class="text-lg font-bold text-brandGold">
                GHS <?php echo number_format($b['price'],2); ?>
              </span>
              <?php $rc = $reviewM->countForBook((int)$b['id']); if ($rc > 0): ?>
              <span class="flex items-center text-xs text-gray-600">
                <svg class="w-4 h-4 text-yellow-400 mr-1" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                <?php echo (int)$rc; ?>
              </span>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    
    <!-- Mobile View All Button -->
    <div class="mt-8 text-center md:hidden">
      <a href="<?php echo BASE_URL; ?>/public/books.php" class="inline-flex items-center justify-center bg-brandBlue hover:bg-blue-700 text-white font-semibold px-8 py-3 rounded-lg">
        View All Books
        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
        </svg>
      </a>
    </div>
  </div>
</section>

<!-- Why Choose Us Section -->
<section class="bg-white py-12 md:py-16 border-t border-gray-200">
  <div class="max-w-6xl mx-auto px-4">
    <h2 class="text-2xl md:text-3xl font-bold text-center text-gray-900 mb-12">Why KingOfPeace Books?</h2>
    
    <div class="grid md:grid-cols-3 gap-8">
      <!-- Feature 1 -->
      <div class="text-center">
        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8 text-brandBlue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
          </svg>
        </div>
        <h3 class="text-xl font-bold text-gray-900 mb-2">African Authors</h3>
        <p class="text-gray-600">Books written by Africans, for Africans</p>
      </div>
      
      <!-- Feature 2 -->
      <div class="text-center">
        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
          </svg>
        </div>
        <h3 class="text-xl font-bold text-gray-900 mb-2">Secure Payments</h3>
        <p class="text-gray-600">Safe transactions with Paystack</p>
      </div>
      
      <!-- Feature 3 -->
      <div class="text-center">
        <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8 text-brandGold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
          </svg>
        </div>
        <h3 class="text-xl font-bold text-gray-900 mb-2">Instant Access</h3>
        <p class="text-gray-600">Download and read immediately</p>
      </div>
    </div>
  </div>
</section>

<!-- Final CTA Section -->
<section class="bg-gradient-to-r from-brandBlue to-blue-900 text-white py-16 md:py-20">
  <div class="max-w-4xl mx-auto px-4 text-center">
    <h2 class="text-3xl md:text-4xl font-bold mb-4">Ready to Start Reading?</h2>
    <p class="text-xl text-blue-100 mb-8">Join thousands of readers discovering African knowledge</p>
    
    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
      <a href="<?php echo BASE_URL; ?>/public/books.php" class="w-full sm:w-auto inline-flex items-center justify-center bg-brandGold hover:bg-yellow-500 text-black text-lg font-bold px-8 py-4 rounded-xl shadow-2xl transition-all">
        Browse All Books
      </a>
      <?php if (!is_logged_in()): ?>
      <a href="<?php echo BASE_URL; ?>/public/register.php" class="w-full sm:w-auto inline-flex items-center justify-center border-2 border-white hover:bg-white hover:text-brandBlue text-lg font-semibold px-8 py-4 rounded-xl transition-all">
        Create Account
      </a>
      <?php endif; ?>
    </div>
    
    <!-- Help Link -->
    <div class="mt-8">
      <a href="https://wa.me/233554521480" class="inline-flex items-center text-blue-100 hover:text-white">
        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
          <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
        </svg>
        Need help? WhatsApp us
      </a>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
