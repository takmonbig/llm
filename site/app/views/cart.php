<section class="section"><div class="wrap">
  <h1>Your cart</h1>
  <?php if (!$cart['items']): ?>
    <p class="muted" data-testid="cart-empty">Your cart is empty.</p>
    <a class="btn" href="<?= url('shop') ?>" data-testid="cart-shop-btn">Shop now</a>
  <?php else: ?>
  <div class="grid-2" style="grid-template-columns:2fr 1fr">
    <div>
      <?php foreach ($cart['items'] as $it): ?>
      <div class="cart-item" data-testid="cart-item-<?= (int) $it['id'] ?>">
        <img src="<?= e($it['image_url']) ?>" alt="<?= e($it['title']) ?>">
        <div>
          <a href="<?= e($it['url']) ?>"><b><?= e($it['title']) ?></b></a>
          <div class="small muted"><?= money($it['price']) ?> each</div>
          <form method="post" action="<?= url('cart/update') ?>" class="qty" style="margin-top:8px">
            <?= csrf_field() ?><input type="hidden" name="item_id" value="<?= (int) $it['id'] ?>">
            <input type="number" name="quantity" value="<?= (int) $it['quantity'] ?>" min="0" max="10" data-testid="cart-qty-input-<?= (int) $it['id'] ?>">
            <button class="btn btn-sm btn-ghost" data-testid="cart-update-btn-<?= (int) $it['id'] ?>">Update</button>
          </form>
          <form method="post" action="<?= url('cart/remove') ?>" style="margin-top:6px">
            <?= csrf_field() ?><input type="hidden" name="item_id" value="<?= (int) $it['id'] ?>">
            <button class="link-btn small" data-testid="cart-remove-btn-<?= (int) $it['id'] ?>">Remove</button>
          </form>
        </div>
        <b><?= money($it['line_total']) ?></b>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="card">
      <div class="summary-row"><span>Subtotal</span><span data-testid="cart-subtotal"><?= e($cart['subtotal_fmt']) ?></span></div>
      <div class="summary-row"><span>Shipping</span><span><?= e($cart['shipping_fmt']) ?></span></div>
      <div class="summary-row summary-total"><span>Total</span><span data-testid="cart-total"><?= e($cart['total_fmt']) ?></span></div>
      <a class="btn btn-block" href="<?= url('checkout') ?>" style="margin-top:16px" data-testid="cart-checkout-btn">Checkout</a>
    </div>
  </div>
  <?php endif; ?>
</div></section>
