<?php
// Render/router for PHP built-in server.
// - Allows /public/*.php URLs to work
// - Redirects / to /public/index.php
// - Blocks access to sensitive directories

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = urldecode($uri);

// Block sensitive paths
$blockedPrefixes = [
  '/app/',
  '/migrations/',
  '/.git/',
  '/vendor/',
];
foreach ($blockedPrefixes as $prefix) {
  if (strpos($path, $prefix) === 0) {
    http_response_code(403);
    echo 'Forbidden';
    return true;
  }
}

// Redirect root to public front page
if ($path === '/' || $path === '') {
  header('Location: /public/index.php', true, 302);
  return true;
}

// Serve existing static files directly
$file = __DIR__ . $path;
if ($path !== '/' && is_file($file)) {
  return false;
}

// If someone hits /public (folder), redirect into index
if ($path === '/public') {
  header('Location: /public/index.php', true, 302);
  return true;
}

// Otherwise let PHP built-in server resolve normally
return false;
