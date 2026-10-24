<section class="section"><div class="wrap">
  <h1 data-testid="account-welcome">Hello, <?= e($user['name']) ?></h1>
  <?php require __DIR__ . '/../partials/account-nav.php'; ?>
  <div class="grid-2">
    <div class="card">
      <h3>Profile</h3>
      <form method="post" action="<?= url('account/profile') ?>">
        <?= csrf_field() ?>
        <div class="field"><label>Name</label><input name="name" value="<?= e($user['name']) ?>" data-testid="profile-name"></div>
        <div class="field"><label>Email</label><input value="<?= e($user['email']) ?>" disabled></div>
        <div class="field"><label>Mobile</label><input name="phone" value="<?= e($user['phone']) ?>" data-testid="profile-phone"></div>
        <button class="btn btn-sm" data-testid="profile-save-btn">Save profile</button>
      </form>
      <h3 style="margin-top:24px">Change password</h3>
      <form method="post" action="<?= url('account/password') ?>">
        <?= csrf_field() ?>
        <div class="field"><label>Current password</label><input name="current_password" type="password" data-testid="current-password"></div>
        <div class="field"><label>New password</label><input name="new_password" type="password" data-testid="new-password"></div>
        <button class="btn btn-sm btn-ghost" data-testid="password-save-btn">Change password</button>
      </form>
    </div>
    <div class="card">
      <h3>Recent orders</h3>
      <?php if (!$orders): ?><p class="muted" data-testid="no-orders">No orders yet.</p><?php endif; ?>
      <?php foreach ($orders as $o): ?><div class="summary-row"><a href="<?= url('account/orders/' . $o['order_number']) ?>"><?= e($o['order_number']) ?></a><span><?= e(order_status_label($o['status'])) ?> · <?= money($o['total']) ?></span></div><?php endforeach; ?>
      <p class="small muted" style="margin-top:16px"><?= (int) $addressCount ?> saved address(es)</p>
    </div>
  </div>
</div></section>
