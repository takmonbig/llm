<section class="section"><div class="wrap" style="max-width:820px">
  <h1 data-testid="page-title"><?= e($title) ?></h1>
  <?php if ($intro): ?><p class="flash flash-success"><?= e($intro) ?></p><?php endif; ?>
  <div class="prose" data-testid="page-body"><?= $body ? nl2p($body) : '<p class="muted">Content coming soon.</p>' ?></div>
</div></section>
