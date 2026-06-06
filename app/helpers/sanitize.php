<?php
function sanitize($value) {
  if (is_array($value)) { return array_map('sanitize', $value); }
  return htmlspecialchars(trim((string)$value), ENT_QUOTES, 'UTF-8');
}

function cover_src($path, string $fallback = 'assets/images/placeholder.jpg'): string {
  $p = trim((string)($path ?? ''));
  if ($p === '') { $p = $fallback; }
  if ($p === '') { return ''; }
  if (preg_match('#^https?://#i', $p)) { return $p; }
  return (defined('BASE_URL') ? BASE_URL : '') . '/' . ltrim($p, '/');
}
