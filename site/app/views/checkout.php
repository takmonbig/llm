<?php $err = fn($k) => !empty($errors[$k]) ? '<div class="field-error">' . e($errors[$k]) . '</div>' : ''; ?>
<section class="section"><div class="wrap">
  <h1>Secure checkout</h1>
  <?php if (!empty($errors['_form'])): ?><div class="flash flash-error" data-testid="checkout-form-error"><?= e($errors['_form']) ?></div><?php endif; ?>
  <form method="post" action="<?= url('checkout') ?>" class="grid-2" style="grid-template-columns:3fr 2fr">
    <?= csrf_field() ?>
    <div class="card">
      <h3>Contact</h3>
      <div class="field"><label>Email</label><input name="email" type="email" value="<?= old('email', $user['email'] ?? '') ?>" data-testid="checkout-email"><?= $err('email') ?></div>
      <h3>Delivery address</h3>
      <?php require __DIR__ . '/partials/address-fields.php'; ?>
      <div class="field"><label>Order note (optional)</label><textarea name="note" rows="2" data-testid="checkout-note"><?= old('note') ?></textarea></div>
      <h3>Payment</h3>
      <?php if (!$methods): ?><p class="flash flash-error">No payment method is enabled.</p><?php endif; ?>
      <?php foreach ($methods as $k => $label): ?>
        <label class="check"><input type="radio" name="payment_method" value="<?= e($k) ?>" <?= count($methods) === 1 || old('payment_method') === $k ? 'checked' : '' ?> data-testid="payment-method-<?= e($k) ?>"> <?= e($label) ?></label>
      <?php endforeach; ?>
      <?= $err('payment_method') ?>
      <?php if (!$razorpayKey): ?><p class="small muted">Online payment is not set up in this preview (no payment keys), so only Cash on Delivery is shown.</p><?php endif; ?>
    </div>
    <div class="card">
      <h3>Order summary</h3>
      <?php foreach ($cart['items'] as $it): ?><div class="summary-row"><span><?= e($it['title']) ?> × <?= (int) $it['quantity'] ?></span><span><?= money($it['line_total']) ?></span></div><?php endforeach; ?>
      <div class="summary-row"><span>Shipping</span><span><?= e($cart['shipping_fmt']) ?></span></div>
      <div class="summary-row summary-total"><span>Total</span><span data-testid="checkout-total"><?= e($cart['total_fmt']) ?></span></div>
      <button class="btn btn-block" style="margin-top:16px" data-testid="place-order-btn">Place order</button>
    </div>
  </form>
</div></section>
