<section class="section"><div class="wrap">
  <h1>Order history</h1>
  <?php require __DIR__ . '/../partials/account-nav.php'; ?>
  <?php if (!$orders): ?><p class="muted" data-testid="no-orders">You have not placed any orders yet.</p><?php else: ?>
  <div class="table-wrap"><table>
    <tr><th>Order</th><th>Date</th><th>Status</th><th>Total</th></tr>
    <?php foreach ($orders as $o): ?><tr><td><a href="<?= url('account/orders/' . $o['order_number']) ?>" data-testid="order-link-<?= e($o['order_number']) ?>"><?= e($o['order_number']) ?></a></td><td><?= format_date($o['created_at'], 'd M Y') ?></td><td><?= e(order_status_label($o['status'])) ?></td><td><?= money($o['total']) ?></td></tr><?php endforeach; ?>
  </table></div>
  <?php endif; ?>
</div></section>
