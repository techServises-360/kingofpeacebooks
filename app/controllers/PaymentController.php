<?php
class PaymentController {
  private Order $orders;
  private Book $books;
  private PDO $db;
  public function __construct(PDO $db) { $this->db = $db; $this->orders = new Order($db); $this->books = new Book($db); }

  public function initiate(int $book_id) {
    require_login();
    $book = $this->books->find($book_id);
    if (!$book) { http_response_code(404); exit('Book not found'); }
    $bookUrl = $this->books->publicUrl($book);

    $ref = 'BK' . time() . bin2hex(random_bytes(3));
    $amount = (float)$book['price'];
    $order_id = $this->orders->create(current_user_id(), $book_id, $ref, $amount, 'pending');

    $keys = paystack_keys();
    if (empty($keys['secret']) || empty($keys['public'])) {
      flash_set('error', 'Payment is not configured. Please set Paystack keys.');
      header('Location: ' . $bookUrl);
      return;
    }
    $callback = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . BASE_URL . '/public/payment-callback.php?reference=' . $ref;

    $userEmail = 'customer+' . current_user_id() . '@example.com';
    $userModel = new User($this->db);
    $u = $userModel->find((int)current_user_id());
    if ($u && !empty($u['email'])) { $userEmail = $u['email']; }

    $payload = [
      'email' => $userEmail,
      'amount' => (int)round($amount * 100),
      'reference' => $ref,
      'callback_url' => $callback,
      'currency' => (defined('PAYSTACK_CURRENCY') ? PAYSTACK_CURRENCY : 'GHS'),
      'metadata' => [
        'order_id' => $order_id,
        'book_id' => $book_id,
        'user_id' => (int)current_user_id(),
      ],
    ];

    $ch = curl_init('https://api.paystack.co/transaction/initialize');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $keys['secret'], 'Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    $res = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) { flash_set('error', 'Payment initialization error: ' . $err); header('Location: ' . $bookUrl); return; }
    $data = json_decode($res, true);
    if (!($data['status'] ?? false)) {
      $msg = isset($data['message']) ? $data['message'] : 'Payment initialization failed.';
      flash_set('error', $msg);
      header('Location: ' . $bookUrl);
      return;
    }
    header('Location: ' . $data['data']['authorization_url']);
  }

  public function callback() {
    $ref = sanitize($_GET['reference'] ?? '');
    if (!$ref) { http_response_code(400); exit('No reference'); }
    $keys = paystack_keys();

    $ch = curl_init('https://api.paystack.co/transaction/verify/' . urlencode($ref));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $keys['secret']]);
    $res = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($res, true);

    $order = $this->orders->findByReference($ref);
    $book_id = $order['book_id'] ?? 0;
    $book = $book_id ? $this->books->find((int)$book_id) : null;
    $bookUrl = $book ? $this->books->publicUrl($book) : BASE_URL . '/public/books.php';
    if (($data['status'] ?? false) && ($data['data']['status'] ?? '') === 'success') {
      $paidAmount = (int)($data['data']['amount'] ?? 0);
      $currency = $data['data']['currency'] ?? '';
      $expected = isset($order['amount']) ? (int)round(((float)$order['amount']) * 100) : -1;
      if ($paidAmount === $expected && $currency === 'GHS') {
        $this->orders->markPaid($ref);
        flash_set('success', 'Payment verified. You can now download your book.');
        header('Location: ' . $bookUrl);
        return;
      }
    }
    flash_set('error', 'Payment verification failed or mismatched amount.');
    header('Location: ' . $bookUrl);
  }
}
