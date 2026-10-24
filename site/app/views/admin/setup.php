<h2>Create first admin</h2>
<form method="post" action="<?= url('admin/setup') ?>">
  <?= csrf_field() ?>
  <div class="field"><label>Setup key</label><input name="setup_key" data-testid="setup-key"></div>
  <div class="field"><label>Name</label><input name="name" data-testid="setup-name"></div>
  <div class="field"><label>Email</label><input name="email" type="email" data-testid="setup-email"></div>
  <div class="field"><label>Password (min 10 characters)</label><input name="password" type="password" data-testid="setup-password"></div>
  <button class="btn btn-block" data-testid="setup-submit-btn">Create admin</button>
</form>
