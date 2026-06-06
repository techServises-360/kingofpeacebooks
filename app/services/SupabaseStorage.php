<?php

class SupabaseStorage {
  private string $url;
  private string $serviceKey;
  private string $anonKey;

  public function __construct(string $url, string $serviceKey, string $anonKey = '') {
    $this->url = rtrim($url, '/');
    $this->serviceKey = $serviceKey;
    $this->anonKey = $anonKey;
  }

  public static function fromEnv(): ?self {
    if (!defined('SUPABASE_URL') || !defined('SUPABASE_SERVICE_ROLE_KEY')) { return null; }
    $anon = defined('SUPABASE_ANON_KEY') ? SUPABASE_ANON_KEY : '';
    $s = new self(SUPABASE_URL, SUPABASE_SERVICE_ROLE_KEY, $anon);
    return $s->isConfigured() ? $s : null;
  }

  public function isConfigured(): bool {
    return $this->url !== '' && $this->serviceKey !== '';
  }

  public static function isAbsoluteUrl(string $path): bool {
    return (bool)preg_match('#^https?://#i', $path);
  }

  /**
   * Treat as a Supabase storage object key if it's not a URL and not a legacy local storage path.
   */
  public static function looksLikeObjectKey(string $filePath): bool {
    if (self::isAbsoluteUrl($filePath)) { return false; }
    if (strpos($filePath, 'app/storage/') === 0) { return false; }
    return $filePath !== '';
  }

  public function signedBookUrlForFilePath(string $filePath, int $expiresInSeconds = 600): string {
    $filePath = trim($filePath);
    if ($filePath === '') { return ''; }
    if (!self::looksLikeObjectKey($filePath)) { return ''; }
    if (!defined('SUPABASE_BUCKET_BOOKS')) { return ''; }
    return $this->signedUrl(SUPABASE_BUCKET_BOOKS, $filePath, $expiresInSeconds);
  }

  private function headers(array $extra = []): array {
    $headers = [
      'Authorization: Bearer ' . $this->serviceKey,
      'apikey: ' . ($this->anonKey !== '' ? $this->anonKey : $this->serviceKey),
    ];
    foreach ($extra as $h) {
      $headers[] = $h;
    }
    return $headers;
  }

  public function upload(string $bucket, string $objectPath, string $tmpFile, string $contentType, bool $upsert = true): bool {
    if (!$this->isConfigured()) { return false; }
    if (!is_file($tmpFile)) { return false; }

    $body = file_get_contents($tmpFile);
    if ($body === false) { return false; }

    $endpoint = $this->url . '/storage/v1/object/' . rawurlencode($bucket) . '/' . str_replace('%2F', '/', rawurlencode($objectPath));

    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers([
      'Content-Type: ' . $contentType,
      'x-upsert: ' . ($upsert ? 'true' : 'false'),
    ]));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

    $res = curl_exec($ch);
    $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http >= 200 && $http < 300) {
      return true;
    }

    error_log('Supabase upload failed: ' . $http . ' ' . (is_string($res) ? $res : ''));
    return false;
  }

  public function publicUrl(string $bucket, string $objectPath): string {
    return $this->url . '/storage/v1/object/public/' . rawurlencode($bucket) . '/' . str_replace('%2F', '/', rawurlencode($objectPath));
  }

  public function signedUrl(string $bucket, string $objectPath, int $expiresInSeconds = 600): string {
    if (!$this->isConfigured()) { return ''; }

    $endpoint = $this->url . '/storage/v1/object/sign/' . rawurlencode($bucket) . '/' . str_replace('%2F', '/', rawurlencode($objectPath));

    $payload = json_encode(['expiresIn' => $expiresInSeconds]);
    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers(['Content-Type: application/json']));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

    $res = curl_exec($ch);
    $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http < 200 || $http >= 300 || !is_string($res)) {
      error_log('Supabase signedUrl failed: ' . $http . ' ' . (is_string($res) ? $res : ''));
      return '';
    }

    $data = json_decode($res, true);
    $signedPath = $data['signedURL'] ?? '';
    if (!is_string($signedPath) || $signedPath === '') {
      return '';
    }

    return $this->url . '/storage/v1' . $signedPath;
  }
}
