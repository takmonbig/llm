<a href="<?= url('admin/orders') ?>">← Orders</a>
<h1>Order <?= e($order['order_number']) ?></h1>
<p><span class="badge" data-testid="admin-order-status"><?= e(order_status_label($order['status'])) ?></span> · <?= e(strtoupper($order['payment_method'])) ?> · <?= e($order['payment_status']) ?> · <?= format_date($order['created_at']) ?></p>
<div class="grid-2">
  <div class="card">
    <h3>Customer</h3>
    <p><?= e($order['customer_name']) ?><br><?= e($order['email']) ?> · <?= e($order['phone']) ?><br><?= e($order['ship_line1']) ?> <?= e($order['ship_line2']) ?> <?= e($order['ship_landmark']) ?><br><?= e($order['ship_city']) ?>, <?= e($order['ship_state']) ?> <?= e($order['ship_pincode']) ?></p>
    <?php if ($order['customer_note']): ?><p class="small">Note: <?= e($order['customer_note']) ?></p><?php endif; ?>
    <h3>Items</h3>
    <?php foreach ($items as $it): ?><div class="summary-row"><span><?= e($it['product_title']) ?> × <?= (int) $it['quantity'] ?></span><span><?= money($it['line_total']) ?></span></div><?php endforeach; ?>
    <div class="summary-row summary-total"><span>Total</span><span><?= money($order['total']) ?></span></div>
    <h3 style="margin-top:20px">History</h3>
    <ul class="timeline"><?php foreach ($history as $h): ?><li><b><?= e(order_status_label($h['to_status'])) ?></b> <span class="small muted"><?= format_date($h['created_at']) ?> · <?= e($h['changed_by']) ?><?= $h['note'] ? ' — ' . e($h['note']) : '' ?></span></li><?php endforeach; ?></ul>
    <?php if ($payments): ?><h3 style="margin-top:20px">Payments</h3><?php foreach ($payments as $pm): ?><p class="small"><?= e($pm['provider']) ?> · <?= e($pm['status']) ?> · <?= money($pm['amount']) ?> · <?= e($pm['provider_payment_id']) ?></p><?php endforeach; endif; ?>
  </div>
  <div>
    <?php if ($nextStatuses): ?>
    <form method="post" action="<?= url('admin/orders/' . (int) $order['id'] . '/status') ?>" class="card" style="margin-bottom:16px">
      <?= csrf_field() ?><h3>Update status</h3>
      <div class="field"><select name="status" data-testid="admin-status-select"><?php foreach ($nextStatuses as $s): ?><option value="<?= $s ?>"><?= e(order_status_label($s)) ?></option><?php endforeach; ?></select></div>
      <div class="field"><input name="note" placeholder="Note (optional)" data-testid="admin-status-note"></div>
      <button class="btn btn-sm" data-testid="admin-status-save-btn">Update</button>
    </form>
    <?php endif; ?>
    <form method="post" action="<?= url('admin/orders/' . (int) $order['id'] . '/tracking') ?>" class="card">
      <?= csrf_field() ?><h3>Tracking</h3>
      <div class="field"><label>Courier</label><input name="tracking_courier" value="<?= e($order['tracking_courier']) ?>" data-testid="admin-tracking-courier"></div>
      <div class="field"><label>Tracking number</label><input name="tracking_number" value="<?= e($order['tracking_number']) ?>" data-testid="admin-tracking-number"></div>
      <div class="field"><label>Tracking URL</label><input name="tracking_url" value="<?= e($order['tracking_url']) ?>" data-testid="admin-tracking-url"></div>
      <div class="field"><label>Admin note</label><textarea name="admin_note" rows="2"><?= e($order['admin_note']) ?></textarea></div>
      <button class="btn btn-sm" data-testid="admin-tracking-save-btn">Save tracking</button>
    </form>
  </div>
</div>
