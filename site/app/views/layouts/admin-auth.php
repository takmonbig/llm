<?php $success = flash('success'); $error = flash('error'); ?><!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex">
<title><?= e($meta['title'] ?? 'Admin') ?></title>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600&family=Outfit:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('assets/css/app.css') ?>"></head>
<body class="admin-body">
<div class="auth-box">
  <img src="<?= url(setting('store_logo', 'assets/images/brand/logo-src.png')) ?>" alt="" style="height:70px;margin:0 auto 12px">
  <?php if ($success): ?><div class="flash flash-success" data-testid="flash-success"><?= e($success) ?></div><?php endif; ?>
  <?php if ($error): ?><div class="flash flash-error" data-testid="flash-error"><?= e($error) ?></div><?php endif; ?>
  <?= $content ?>
</div>
</body></html>
