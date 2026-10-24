<section class="section"><div class="wrap faq" style="max-width:820px">
  <h1>Frequently asked questions</h1>
  <?php if (!$faqs): ?><p class="muted">No FAQs yet.</p><?php endif; ?>
  <?php foreach ($faqs as $i => $f): ?><details data-testid="faq-item-<?= $i ?>"><summary><?= e($f['q']) ?></summary><p><?= e($f['a']) ?></p></details><?php endforeach; ?>
</div></section>
