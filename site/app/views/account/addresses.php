<section class="section"><div class="wrap">
  <h1>Saved addresses</h1>
  <?php require __DIR__ . '/../partials/account-nav.php'; ?>
  <div class="grid-2">
    <div>
      <?php if (!$addresses): ?><p class="muted" data-testid="no-addresses">No saved addresses.</p><?php endif; ?>
      <?php foreach ($addresses as $a): ?>
      <div class="card" style="margin-bottom:12px" data-testid="address-<?= (int) $a['id'] ?>">
        <b><?= e($a['label'] ?: $a['full_name']) ?></b><?php if ($a['is_default']): ?> <span class="badge badge-green">Default</span><?php endif; ?>
        <p class="small"><?= e($a['full_name']) ?>, <?= e($a['phone']) ?><br><?= e($a['line1']) ?> <?= e($a['line2']) ?><br><?= e($a['city']) ?>, <?= e($a['state']) ?> <?= e($a['pincode']) ?></p>
        <form method="post" action="<?= url('account/addresses/' . (int) $a['id'] . '/delete') ?>"><?= csrf_field() ?><button class="link-btn small" data-testid="address-delete-<?= (int) $a['id'] ?>">Remove</button></form>
      </div>
      <?php endforeach; ?>
    </div>
    <form method="post" action="<?= url('account/addresses') ?>" class="card">
      <?= csrf_field() ?>
      <h3>Add address</h3>
      <div class="field"><label>Label (e.g. Home)</label><input name="label" data-testid="addr-label"></div>
      <?php $address = null; require __DIR__ . '/../partials/address-fields.php'; ?>
      <label class="check"><input type="checkbox" name="is_default" value="1" data-testid="addr-default"> Make default</label>
      <button class="btn" style="margin-top:12px" data-testid="addr-save-btn">Save address</button>
    </form>
  </div>
</div></section>
