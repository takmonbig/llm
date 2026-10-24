<h1>Orders <span class="muted small">(<?= (int) $total ?>)</span></h1>
<form method="get" class="filters">
  <input name="q" value="<?= e($filters['q']) ?>" placeholder="Search order, name, email, phone" data-testid="orders-filter-q">
  <select name="status" data-testid="orders-filter-status"><option value="">All statuses</option><?php foreach (App\Models\Order::STATUSES as $s): ?><option value="<?= $s ?>" <?= $filters['status'] === $s ? 'selected' : '' ?>><?= e(order_status_label($s)) ?></option><?php endforeach; ?></select>
  <select name="payment" data-testid="orders-filter-payment"><option value="">All payments</option><option value="cod" <?= $filters['pm'] === 'cod' ? 'selected' : '' ?>>COD</option><option value="razorpay" <?= $filters['pm'] === 'razorpay' ? 'selected' : '' ?>>Online</option></select>
  <input type="date" name="from" value="<?= e($filters['from']) ?>"><input type="date" name="to" value="<?= e($filters['to']) ?>">
  <button class="btn btn-sm" data-testid="orders-filter-btn">Filter</button>
  <a class="btn btn-sm btn-ghost" href="<?= url('admin/orders/export') . '?' . e($_SERVER['QUERY_STRING'] ?? '') ?>" data-testid="orders-export-btn">Export CSV</a>
</form>
<div class="table-wrap"><table>
  <tr><th>Order</th><th>Date</th><th>Customer</th><th>Items</th><th>Payment</th><th>Status</th><th>Total</th></tr>
  <?php foreach ($orders as $o): ?>
  <tr><td><a href="<?= url('admin/orders/' . (int) $o['id']) ?>" data-testid="admin-order-link-<?= (int) $o['id'] ?>"><?= e($o['order_number']) ?></a></td><td><?= format_date($o['created_at']) ?></td><td><?= e($o['customer_name']) ?><br><span class="small muted"><?= e($o['phone']) ?></span></td><td><?= (int) $o['item_count'] ?></td><td><?= e(strtoupper($o['payment_method'])) ?> · <?= e($o['payment_status']) ?></td><td><?= e(order_status_label($o['status'])) ?></td><td><?= money($o['total']) ?></td></tr>
  <?php endforeach; ?>
  <?php if (!$orders): ?><tr><td colspan="7" class="muted">No orders found.</td></tr><?php endif; ?>
</table></div>
<?php if ($pages > 1): ?><p><?php for ($i = 1; $i <= $pages; $i++): ?><a class="btn btn-sm <?= $i === $page ? '' : 'btn-ghost' ?>" href="?<?= e(http_build_query(array_merge($_GET, ['page' => $i]))) ?>"><?= $i ?></a> <?php endfor; ?></p><?php endif; ?>
