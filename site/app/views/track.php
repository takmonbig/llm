<section class="section"><div class="wrap form-narrow">
  <h1>Track your order</h1>
  <p class="muted">Enter your order number and the email used at checkout. We'll email you a secure tracking link.</p>
  <form method="post" action="<?= url('track') ?>" class="card">
    <?= csrf_field() ?>
    <div class="field"><label>Order number</label><input name="order_number" data-testid="track-order-number"></div>
    <div class="field"><label>Email</label><input name="email" type="email" data-testid="track-email"></div>
    <button class="btn btn-block" data-testid="track-submit-btn">Send tracking link</button>
  </form>
</div></section>
