<section class="section"><div class="wrap" style="text-align:center">
  <h1 data-testid="error-404">Page not found</h1>
  <p class="muted"><?= e(($message ?? '') ?: 'The page you are looking for does not exist.') ?></p>
  <a class="btn" href="<?= url('/') ?>" data-testid="error-home-btn">Back to home</a>
</div></section>
