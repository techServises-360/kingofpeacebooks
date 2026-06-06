<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_admin();
$db = Database::getInstance();
$orders = (new Order($db))->all();
?>
<div class="max-w-6xl mx-auto px-4 py-8">
  <h2 class="text-2xl font-bold text-brandBlue">Orders</h2>
  <div class="mt-6 overflow-x-auto">
    <table class="min-w-full border border-gray-200 rounded-xl overflow-hidden">
      <thead class="bg-gray-50">
        <tr>
          <th class="text-left px-4 py-3 border-b">Ref</th>
          <th class="text-left px-4 py-3 border-b">User</th>
          <th class="text-left px-4 py-3 border-b">Book</th>
          <th class="text-left px-4 py-3 border-b">Thumbnail</th>
          <th class="text-left px-4 py-3 border-b">Amount</th>
          <th class="text-left px-4 py-3 border-b">Status</th>
          <th class="text-left px-4 py-3 border-b">Date</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($orders as $o): ?>
        <tr class="odd:bg-white even:bg-gray-50">
          <td class="px-4 py-3 border-b font-mono text-sm"><?php echo htmlspecialchars($o['paystack_reference'] ?? ($o['reference'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
          <td class="px-4 py-3 border-b"><?php echo $o['email']; ?></td>
          <td class="px-4 py-3 border-b"><?php echo $o['title']; ?></td>
          <td class="px-4 py-3 border-b">
            <img class="w-12 h-12 object-cover rounded" src="<?php echo cover_src($o['cover_image']); ?>" alt="thumb">
          </td>
          <td class="px-4 py-3 border-b">GHS <?php echo number_format($o['amount'],2); ?></td>
          <td class="px-4 py-3 border-b">
            <?php if ($o['status'] === 'paid'): ?>
              <span class="inline-block text-green-700 bg-green-100 px-2 py-1 rounded">paid</span>
            <?php else: ?>
              <span class="inline-block text-yellow-800 bg-yellow-100 px-2 py-1 rounded">pending</span>
            <?php endif; ?>
          </td>
          <td class="px-4 py-3 border-b"><?php echo $o['created_at']; ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
