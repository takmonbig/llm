<section class="section"><div class="wrap" style="max-width:760px">
  <div class="card">
    <span class="badge badge-green">Thank you</span>
    <h1 style="margin-top:10px" data-testid="confirmation-title">Order <?= e($order['order_number']) ?></h1>
    <p>Status: <b data-testid="confirmation-status"><?= e(order_status_label($order['status'])) ?></b> · Payment: <?= e(strtoupper($order['payment_method'])) ?> (<?= e($order['payment_status']) ?>)</p>
    <?php foreach ($items as $it): ?><div class="summary-row"><span><?= e($it['product_title']) ?> × <?= (int) $it['quantity'] ?></span><span><?= money($it['line_total']) ?></span></div><?php endforeach; ?>
    <div class="summary-row"><span>Shipping</span><span><?= (float) $order['shipping_fee'] > 0 ? money($order['shipping_fee']) : 'Free' ?></span></div>
    <div class="summary-row summary-total"><span>Total</span><span data-testid="confirmation-total"><?= money($order['total']) ?></span></div>
    <p class="small muted">Delivering to <?= e($order['customer_name']) ?>, <?= e($order['ship_line1']) ?>, <?= e($order['ship_city']) ?>, <?= e($order['ship_state']) ?> <?= e($order['ship_pincode']) ?></p>
    <?php if ($order['status'] === 'pending_payment'): ?><a class="btn" href="<?= url('payment/retry/' . $order['order_number']) ?>" data-testid="retry-payment-btn">Complete payment</a><?php endif; ?>
    <a class="btn btn-ghost" href="<?= url('shop') ?>" data-testid="continue-shopping-btn">Continue shopping</a>
  </div>
</div></section>
