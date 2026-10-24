<div style="font-family:Arial,sans-serif;max-width:560px">
<h2>Thank you for your order</h2>
<p>Hi <?= e($order['customer_name']) ?>, your order <b><?= e($order['order_number']) ?></b> has been received.</p>
<table cellpadding="6"><?php foreach ($items as $it): ?><tr><td><?= e($it['product_title']) ?> × <?= (int) $it['quantity'] ?></td><td><?= money($it['line_total']) ?></td></tr><?php endforeach; ?>
<tr><td><b>Total</b></td><td><b><?= money($order['total']) ?></b></td></tr></table>
<p>Payment: <?= e(strtoupper($order['payment_method'])) ?></p>
</div>
