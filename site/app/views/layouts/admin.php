<?php $success = flash('success'); $error = flash('error'); App\Core\Session::forget('_old'); ?><!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex">
<title><?= e($meta['title'] ?? 'Admin') ?> · Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600&family=Outfit:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('assets/css/app.css') ?>"></head>
<body class="admin-body">
<div class="admin-shell">
  <aside class="admin-side">
    <span class="brand"><?= e(setting('store_name')) ?> Admin</span>
    <a href="<?= url('admin') ?>" data-testid="admin-nav-dashboard">Dashboard</a>
    <a href="<?= url('admin/orders') ?>" data-testid="admin-nav-orders">Orders</a>
    <a href="<?= url('admin/products') ?>" data-testid="admin-nav-products">Products</a>
    <a href="<?= url('admin/reviews') ?>" data-testid="admin-nav-reviews">Reviews</a>
    <a href="<?= url('admin/messages') ?>" data-testid="admin-nav-messages">Messages</a>
    <a href="<?= url('admin/settings') ?>" data-testid="admin-nav-settings">Settings</a>
    <a href="<?= url('/') ?>" target="_blank" data-testid="admin-nav-store">View store ↗</a>
    <form method="post" action="<?= url('admin/logout') ?>" style="margin-top:20px;padding:0 12px"><?= csrf_field() ?><button class="btn btn-sm btn-ghost" style="color:#fff;border-color:#fff" data-testid="admin-logout-btn">Log out</button></form>
  </aside>
  <div class="admin-main">
    <?php if ($success): ?><div class="flash flash-success" data-testid="flash-success"><?= e($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="flash flash-error" data-testid="flash-error"><?= e($error) ?></div><?php endif; ?>
    <?= $content ?>
  </div>
</div>
</body></html>
