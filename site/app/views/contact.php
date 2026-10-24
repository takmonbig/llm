<?php $err = fn($k) => !empty($errors[$k]) ? '<div class="field-error">' . e($errors[$k]) . '</div>' : ''; ?>
<section class="section"><div class="wrap grid-2">
  <div>
    <h1>Contact us</h1>
    <p class="muted">Questions about your order or the combo? Send us a message.</p>
    <?php if (setting('support_email')): ?><p>Email: <?= e(setting('support_email')) ?></p><?php endif; ?>
    <?php if (setting('support_phone')): ?><p>Phone: <?= e(setting('support_phone')) ?></p><?php endif; ?>
    <?php if (setting('business_address')): ?><p class="pre"><?= e(setting('business_address')) ?></p><?php endif; ?>
  </div>
  <form method="post" action="<?= url('contact') ?>" class="card">
    <?= csrf_field() ?>
    <input type="text" name="website" style="display:none" tabindex="-1" autocomplete="off">
    <div class="form-row">
      <div class="field"><label>Name</label><input name="name" value="<?= old('name') ?>" data-testid="contact-name"><?= $err('name') ?></div>
      <div class="field"><label>Email</label><input name="email" type="email" value="<?= old('email') ?>" data-testid="contact-email"><?= $err('email') ?></div>
    </div>
    <div class="form-row">
      <div class="field"><label>Phone (optional)</label><input name="phone" value="<?= old('phone') ?>" data-testid="contact-phone"></div>
      <div class="field"><label>Subject</label><input name="subject" value="<?= old('subject') ?>" data-testid="contact-subject"></div>
    </div>
    <div class="field"><label>Message</label><textarea name="message" rows="5" data-testid="contact-message"><?= old('message') ?></textarea><?= $err('message') ?></div>
    <button class="btn" data-testid="contact-submit-btn">Send message</button>
  </form>
</div></section>
