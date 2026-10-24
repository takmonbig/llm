<section class="section"><div class="wrap form-narrow">
  <h1>Log in</h1>
  <form method="post" action="<?= url('login') ?>" class="card">
    <?= csrf_field() ?>
    <div class="field"><label>Email</label><input name="email" type="email" value="<?= old('email') ?>" data-testid="login-email"></div>
    <div class="field"><label>Password</label><input name="password" type="password" data-testid="login-password"></div>
    <button class="btn btn-block" data-testid="login-submit-btn">Log in</button>
    <p class="small" style="margin-top:14px"><a href="<?= url('forgot-password') ?>" data-testid="forgot-password-link">Forgot password?</a> · <a href="<?= url('register') ?>" data-testid="register-link">Create an account</a></p>
  </form>
</div></section>
