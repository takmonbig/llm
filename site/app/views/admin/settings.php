<?php $s = fn($k) => e($settings[$k] ?? ''); ?>
<h1>Settings</h1>
<div class="card" style="margin-bottom:16px">
  <h3>Integrations</h3>
  <p class="small">Razorpay: <b data-testid="settings-razorpay-status"><?= $razorpay['configured'] ? 'Configured (' . e($razorpay['mode']) . ', ' . e($razorpay['key']) . ')' : 'Not configured' ?></b> · Webhook secret: <?= $razorpay['webhook'] ? 'set' : 'not set' ?></p>
  <p class="small">Mail driver: <b data-testid="settings-mail-driver"><?= e($mail['driver']) ?></b> <?= e($mail['host']) ?></p>
</div>
<form method="post" action="<?= url('admin/settings') ?>" class="card">
  <?= csrf_field() ?>
  <h3>Store</h3>
  <div class="form-row">
    <?php foreach (App\Controllers\Admin\AdminSettingsController::TEXT_KEYS as $k): ?>
    <div class="field"><label><?= e(ucfirst(str_replace('_', ' ', $k))) ?></label><input name="<?= $k ?>" value="<?= $s($k) ?>" data-testid="setting-<?= $k ?>"></div>
    <?php endforeach; ?>
  </div>
  <?php foreach (App\Controllers\Admin\AdminSettingsController::BOOL_KEYS as $k): ?>
  <label class="check"><input type="checkbox" name="<?= $k ?>" value="1" <?= ($settings[$k] ?? '') === '1' ? 'checked' : '' ?> data-testid="setting-<?= $k ?>"> <?= e(ucfirst(str_replace('_', ' ', $k))) ?></label>
  <?php endforeach; ?>
  <h3 style="margin-top:20px">Pages &amp; policies</h3>
  <?php foreach (App\Controllers\Admin\AdminSettingsController::LONG_KEYS as $k): ?>
  <div class="field"><label><?= e(ucfirst(str_replace('_', ' ', $k))) ?></label><textarea name="<?= $k ?>" rows="4" data-testid="setting-<?= $k ?>"><?= $s($k) ?></textarea></div>
  <?php endforeach; ?>
  <h3 style="margin-top:20px">FAQs</h3>
  <?php foreach (array_merge($faqs, [['q' => '', 'a' => '']]) as $i => $f): ?>
  <div class="field"><input name="faq_q[]" value="<?= e($f['q']) ?>" placeholder="Question" data-testid="faq-q-<?= $i ?>"><textarea name="faq_a[]" rows="2" placeholder="Answer" style="margin-top:6px" data-testid="faq-a-<?= $i ?>"><?= e($f['a']) ?></textarea></div>
  <?php endforeach; ?>
  <button class="btn" data-testid="settings-save-btn">Save settings</button>
</form>
<form method="post" action="<?= url('admin/settings/logo') ?>" enctype="multipart/form-data" class="card" style="margin-top:16px">
  <?= csrf_field() ?><h3>Logo</h3>
  <input type="file" name="logo" accept="image/*" data-testid="logo-file"> <button class="btn btn-sm" style="margin-top:10px" data-testid="logo-upload-btn">Upload logo</button>
</form>
