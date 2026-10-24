<section class="section"><div class="wrap" style="max-width:860px">
  <?php if (!$guest): ?><a href="<?= url('account/orders') ?>" data-testid="back-to-orders">← All orders</a><?php endif; ?>
  <h1 data-testid="order-detail-title">Order <?= e($order['order_number']) ?></h1>
  <p>Placed <?= format_date($order['created_at']) ?> · <span class="badge" data-testid="order-detail-status"><?= e(order_status_label($order['status'])) ?></span></p>
  <div class="grid-2">
    <div class="card">
      <h3>Items</h3>
      <?php foreach ($items as $it): ?><div class="summary-row"><span><?= e($it['product_title']) ?> × <?= (int) $it['quantity'] ?></span><span><?= money($it['line_total']) ?></span></div><?php endforeach; ?>
      <div class="summary-row summary-total"><span>Total</span><span><?= money($order['total']) ?></span></div>
      <p class="small muted">Payment: <?= e(strtoupper($order['payment_method'])) ?> · <?= e($order['payment_status']) ?></p>
      <?php if ($order['tracking_number']): ?><p>Tracking: <?= e($order['tracking_courier']) ?> <?= e($order['tracking_number']) ?><?php if ($order['tracking_url']): ?> — <a href="<?= e($order['tracking_url']) ?>" target="_blank" rel="noopener">Track</a><?php endif; ?></p><?php endif; ?>
    </div>
    <div class="card">
      <h3>History</h3>
      <ul class="timeline"><?php foreach ($history as $h): ?><li><b><?= e(order_status_label($h['to_status'])) ?></b><br><span class="small muted"><?= format_date($h['created_at']) ?><?= $h['note'] ? ' — ' . e($h['note']) : '' ?></span></li><?php endforeach; ?></ul>
    </div>
  </div>
</div></section>
