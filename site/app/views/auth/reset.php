<section class="section"><div class="wrap form-narrow">
  <h1>Set a new password</h1>
  <form method="post" action="<?= url('reset-password') ?>" class="card">
    <?= csrf_field() ?><input type="hidden" name="token" value="<?= e($token) ?>">
    <div class="field"><label>New password</label><input name="password" type="password" data-testid="reset-password"></div>
    <div class="field"><label>Confirm password</label><input name="password_confirm" type="password" data-testid="reset-password-confirm"></div>
    <button class="btn btn-block" data-testid="reset-submit-btn">Update password</button>
  </form>
</div></section>
