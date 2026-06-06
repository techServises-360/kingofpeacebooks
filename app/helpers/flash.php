<?php
function flash_set(string $type, string $message): void {
  $_SESSION['flash'][$type][] = $message;
}

function flash_render(): void {
  if (empty($_SESSION['flash'])) { return; }
  foreach ($_SESSION['flash'] as $type => $messages) {
    foreach ((array)$messages as $msg) {
      $cls = $type === 'success' ? 'bg-green-100 text-green-800 border-green-300' : ($type === 'error' ? 'bg-red-100 text-red-800 border-red-300' : 'bg-yellow-100 text-yellow-800 border-yellow-300');
      echo '<div class="max-w-6xl mx-auto px-4 mt-3">';
      echo '<div class="border ' . $cls . ' rounded-md px-4 py-2">' . htmlspecialchars($msg) . '</div>';
      echo '</div>';
    }
  }
  unset($_SESSION['flash']);
}
