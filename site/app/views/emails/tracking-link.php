<div style="font-family:Arial,sans-serif;max-width:560px">
<p>Hi <?= e($order['customer_name']) ?>,</p>
<p>Track your order <?= e($order['order_number']) ?> using this secure link (valid for 48 hours):</p>
<p><a href="<?= e($link) ?>"><?= e($link) ?></a></p>
</div>
