<?php
$errors = App\Core\Session::get('_errors', []);
App\Core\Session::forget('_errors');
$err = fn($k) => !empty($errors[$k]) ? '<div class="field-error">' . e($errors[$k]) . '</div>' : '';
?>
<section class="section"><div class="wrap form-narrow">
  <h1>Create account</h1>
  <form method="post" action="<?= url('register') ?>" class="card">
    <?= csrf_field() ?>
    <div class="field"><label>Name</label><input name="name" value="<?= old('name') ?>" data-testid="register-name"><?= $err('name') ?></div>
    <div class="field"><label>Email</label><input name="email" type="email" value="<?= old('email') ?>" data-testid="register-email"><?= $err('email') ?></div>
    <div class="field"><label>Mobile (optional)</label><input name="phone" value="<?= old('phone') ?>" data-testid="register-phone"><?= $err('phone') ?></div>
    <div class="field"><label>Password (min 8 characters)</label><input name="password" type="password" data-testid="register-password"><?= $err('password') ?></div>
    <button class="btn btn-block" data-testid="register-submit-btn">Create account</button>
    <p class="small" style="margin-top:14px">Already registered? <a href="<?= url('login') ?>" data-testid="login-link">Log in</a></p>
  </form>
</div></section>
