<h2>Admin login</h2>
<form method="post" action="<?= url('admin/login') ?>">
  <?= csrf_field() ?>
  <div class="field"><label>Email</label><input name="email" type="email" data-testid="admin-login-email"></div>
  <div class="field"><label>Password</label><input name="password" type="password" data-testid="admin-login-password"></div>
  <button class="btn btn-block" data-testid="admin-login-submit-btn">Log in</button>
</form>
