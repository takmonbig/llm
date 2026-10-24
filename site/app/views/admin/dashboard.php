<h1>Dashboard</h1>
<div class="stat-grid">
  <?php foreach (['total_orders' => 'Total orders', 'total_sales' => 'Total sales', 'pending_orders' => 'Pending orders', 'cod_orders' => 'COD orders', 'paid_online' => 'Paid online', 'awaiting_payment' => 'Awaiting payment', 'pending_reviews' => 'Pending reviews', 'unread_messages' => 'Unread messages'] as $k => $label): ?>
  <div class="stat" data-testid="stat-<?= $k ?>"><span class="small muted"><?= $label ?></span><b><?= $k === 'total_sales' ? money($stats[$k]) : (int) $stats[$k] ?></b></div>
  <?php endforeach; ?>
</div>
<div class="grid-2">
  <div><h3>Recent orders</h3><div class="table-wrap"><table>
    <tr><th>Order</th><th>Customer</th><th>Status</th><th>Total</th></tr>
    <?php foreach ($recent as $o): ?><tr><td><a href="<?= url('admin/orders/' . (int) $o['id']) ?>"><?= e($o['order_number']) ?></a></td><td><?= e($o['customer_name']) ?></td><td><?= e(order_status_label($o['status'])) ?></td><td><?= money($o['total']) ?></td></tr><?php endforeach; ?>
    <?php if (!$recent): ?><tr><td colspan="4" class="muted">No orders yet.</td></tr><?php endif; ?>
  </table></div></div>
  <div><h3>Products</h3><div class="table-wrap"><table>
    <tr><th>Product</th><th>Price</th><th>Stock</th></tr>
    <?php foreach ($products as $p): ?><tr><td><a href="<?= url('admin/products/' . (int) $p['id']) ?>"><?= e($p['title']) ?></a></td><td><?= money($p['price']) ?></td><td><?= (int) $p['stock'] ?></td></tr><?php endforeach; ?>
  </table></div></div>
</div>
