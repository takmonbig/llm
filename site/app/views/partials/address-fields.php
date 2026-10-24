<?php
$a = $address ?? [];
$err = fn($k) => !empty($errors[$k]) ? '<div class="field-error">' . e($errors[$k]) . '</div>' : '';
$oldState = App\Core\Session::get('_old', [])['state'] ?? ($a['state'] ?? '');
?>
<div class="form-row">
  <div class="field"><label>Full name</label><input name="full_name" value="<?= old('full_name', $a['full_name'] ?? ($user['name'] ?? '')) ?>" data-testid="addr-full-name"><?= $err('full_name') ?></div>
  <div class="field"><label>Mobile number</label><input name="phone" value="<?= old('phone', $a['phone'] ?? ($user['phone'] ?? '')) ?>" data-testid="addr-phone"><?= $err('phone') ?></div>
</div>
<div class="field"><label>Address line 1</label><input name="line1" value="<?= old('line1', $a['line1'] ?? '') ?>" data-testid="addr-line1"><?= $err('line1') ?></div>
<div class="form-row">
  <div class="field"><label>Address line 2</label><input name="line2" value="<?= old('line2', $a['line2'] ?? '') ?>" data-testid="addr-line2"></div>
  <div class="field"><label>Landmark</label><input name="landmark" value="<?= old('landmark', $a['landmark'] ?? '') ?>" data-testid="addr-landmark"></div>
</div>
<div class="form-row">
  <div class="field"><label>City</label><input name="city" value="<?= old('city', $a['city'] ?? '') ?>" data-testid="addr-city"><?= $err('city') ?></div>
  <div class="field"><label>State</label><select name="state" data-testid="addr-state"><option value="">Select state</option><?php foreach ($states as $s): ?><option <?= $s === $oldState ? 'selected' : '' ?>><?= e($s) ?></option><?php endforeach; ?></select><?= $err('state') ?></div>
</div>
<div class="field" style="max-width:220px"><label>PIN code</label><input name="pincode" value="<?= old('pincode', $a['pincode'] ?? '') ?>" data-testid="addr-pincode"><?= $err('pincode') ?></div>
