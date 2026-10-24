<?php
$meta = $meta ?? [];
$cartCount = 0;
try { $cartCount = App\Services\CartService::summary()['count']; } catch (Throwable) {}
$user = current_user();
$extra = array_filter(explode('|', (string) setting('announcement_extra', '')));
$success = flash('success');
$error = flash('error');
App\Core\Session::forget('_old');
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($meta['title'] ?? setting('store_name', 'Long Live Men')) ?></title>
<?php if (!empty($meta['description'])): ?><meta name="description" content="<?= e($meta['description']) ?>"><?php endif; ?>
<?php if (!empty($meta['robots'])): ?><meta name="robots" content="<?= e($meta['robots']) ?>"><?php endif; ?>
<link rel="icon" href="<?= url('assets/images/brand/logo-src.png') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('assets/css/app.css') ?>">
</head>
<body class="<?= e($bodyClass ?? '') ?>">
<div class="preview-note" data-testid="preview-notice">Preview build — placeholder pages. Online payment is off, emails are only logged, nothing real is charged or sent.</div>
<div class="announce" data-testid="announcement-bar"><span><?= e(setting('announcement_text', '')) ?></span><?php foreach ($extra as $x): ?><span><?= e($x) ?></span><?php endforeach; ?></div>
<header class="site-header">
  <div class="wrap">
    <a href="<?= url('/') ?>" class="logo" data-testid="header-logo-link"><img src="<?= url(setting('store_logo', 'assets/images/brand/logo-src.png')) ?>" alt="<?= e(setting('store_name')) ?>"></a>
    <nav class="nav" data-nav>
      <a href="<?= url('/') ?>" data-testid="nav-home">Home</a>
      <a href="<?= url('shop') ?>" data-testid="nav-shop">Shop</a>
      <a href="<?= url('about') ?>" data-testid="nav-about">About</a>
      <a href="<?= url('faq') ?>" data-testid="nav-faq">FAQs</a>
      <a href="<?= url('track') ?>" data-testid="nav-track">Track Order</a>
      <a href="<?= url('contact') ?>" data-testid="nav-contact">Contact</a>
    </nav>
    <div class="header-actions">
      <a href="<?= url($user ? 'account' : 'login') ?>" data-testid="nav-account"><?= $user ? 'My Account' : 'Login' ?></a>
      <a href="<?= url('cart') ?>" class="cart-link" data-testid="nav-cart">Cart<span class="cart-count" data-testid="cart-count"><?= (int) $cartCount ?></span></a>
      <button class="menu-toggle" data-menu-toggle data-testid="mobile-menu-toggle" aria-label="Menu">&#9776;</button>
    </div>
  </div>
</header>
<main>
  <?php if ($success || $error): ?>
  <div class="wrap">
    <?php if ($success): ?><div class="flash flash-success" data-testid="flash-success"><?= e($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="flash flash-error" data-testid="flash-error"><?= e($error) ?></div><?php endif; ?>
  </div>
  <?php endif; ?>
  <?= $content ?>
</main>
<footer class="site-footer">
  <div class="wrap">
    <div class="footer-grid">
      <div>
        <h4><?= e(setting('store_name')) ?></h4>
        <p><?= e(setting('store_tagline')) ?></p>
        <?php if (setting('support_email')): ?><p>Email: <?= e(setting('support_email')) ?></p><?php endif; ?>
        <?php if (setting('support_phone')): ?><p>Phone: <?= e(setting('support_phone')) ?></p><?php endif; ?>
      </div>
      <div>
        <h4>Shop</h4>
        <ul><li><a href="<?= url('shop') ?>">All products</a></li><li><a href="<?= url('cart') ?>">Cart</a></li><li><a href="<?= url('track') ?>">Track order</a></li><li><a href="<?= url('account') ?>">My account</a></li></ul>
      </div>
      <div>
        <h4>Policies</h4>
        <ul>
          <li><a href="<?= url('shipping-policy') ?>" data-testid="footer-shipping">Shipping</a></li>
          <li><a href="<?= url('return-policy') ?>" data-testid="footer-returns">Returns &amp; Refunds</a></li>
          <li><a href="<?= url('privacy-policy') ?>" data-testid="footer-privacy">Privacy</a></li>
          <li><a href="<?= url('terms') ?>" data-testid="footer-terms">Terms</a></li>
          <li><a href="<?= url('disclaimer') ?>" data-testid="footer-disclaimer">Disclaimer</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-disclaimer"><?= e(setting('medical_disclaimer')) ?><br><br>&copy; <?= date('Y') ?> <?= e(setting('store_name')) ?></div>
  </div>
</footer>
<script src="<?= asset('assets/js/app.js') ?>"></script>
</body>
</html>
