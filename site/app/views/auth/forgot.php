<section class="section"><div class="wrap form-narrow">
  <h1>Forgot password</h1>
  <form method="post" action="<?= url('forgot-password') ?>" class="card">
    <?= csrf_field() ?>
    <div class="field"><label>Email</label><input name="email" type="email" data-testid="forgot-email"></div>
    <button class="btn btn-block" data-testid="forgot-submit-btn">Send reset link</button>
    <p class="small muted" style="margin-top:12px">In this preview, emails are only written to a log file, not sent.</p>
  </form>
</div></section>
