<?php $banners = json_decode((string) setting('hero_banners', '[]'), true) ?: []; ?>
<?php if ($banners): ?>
<section class="hero-slider" data-testid="hero-slider">
  <?php foreach ($banners as $i => $b): ?>
  <a class="hero-slide <?= $i === 0 ? 'active' : '' ?>" href="<?= url($b['link'] ?? '') ?>"><img src="<?= url($b['image']) ?>" alt="<?= e($b['alt'] ?? '') ?>"></a>
  <?php endforeach; ?>
  <div class="hero-dots"><?php foreach ($banners as $i => $b): ?><button class="<?= $i === 0 ? 'active' : '' ?>" aria-label="Slide <?= $i + 1 ?>" data-testid="hero-dot-<?= $i ?>"></button><?php endforeach; ?></div>
</section>
<?php endif; ?>

<?php if ($product): ?>
<section class="section">
  <div class="wrap grid-2">
    <img src="<?= url('assets/images/gallery/combo-both-bottles.jpg') ?>" alt="<?= e($product['title']) ?>" style="border-radius:16px">
    <div>
      <div class="eyebrow">The combo</div>
      <h1 data-testid="home-product-title"><?= e($product['title']) ?></h1>
      <p class="muted"><?= e($product['subtitle']) ?></p>
      <div class="price" data-testid="home-product-price"><?= money($product['price']) ?><?php if ($product['compare_at_price']): ?><s><?= money($product['compare_at_price']) ?></s><?php endif; ?></div>
      <p><?= e($product['short_description']) ?></p>
      <?php if ($rating['count']): ?><p><span class="stars">★</span> <?= e($rating['avg']) ?> (<?= (int) $rating['count'] ?> reviews)</p><?php endif; ?>
      <form method="post" action="<?= url('cart/buy-now') ?>" style="display:inline">
        <?= csrf_field() ?><input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
        <button class="btn" data-testid="home-buy-now-btn">Buy Now</button>
      </form>
      <a class="btn btn-ghost" href="<?= url('product/' . $product['slug']) ?>" data-testid="home-view-product-btn">View details</a>
    </div>
  </div>
</section>

<?php if ($variants): ?>
<section class="section section-alt">
  <div class="wrap">
    <div class="eyebrow">Inside the box</div>
    <h2>Two bottles, one combo</h2>
    <div class="grid-2" style="margin-top:24px">
      <?php foreach ($variants as $v): ?>
      <div class="card variant" data-testid="variant-card-<?= (int) $v['id'] ?>">
        <?php if ($v['image_path']): ?><img src="<?= url($v['image_path']) ?>" alt="<?= e($v['name']) ?>"><?php endif; ?>
        <div><h3><?= e($v['name']) ?></h3><span class="badge"><?= (int) $v['tablet_count'] ?> tablets</span><p class="small muted"><?= e($v['description']) ?></p></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>
<?php endif; ?>

<section class="section">
  <div class="wrap grid-3 feature-tiles">
    <div><img src="<?= url('assets/images/gallery/made-for-your-everyday.jpg') ?>" alt="Made for your everyday"><h3 style="margin-top:12px">Made for your everyday</h3></div>
    <div><img src="<?= url('assets/images/gallery/discover-whats-inside.jpg') ?>" alt="Discover what's inside"><h3 style="margin-top:12px">Discover what's inside</h3></div>
    <div><img src="<?= url('assets/images/gallery/delivered-free.jpg') ?>" alt="Delivered free"><h3 style="margin-top:12px">Delivered free across India</h3></div>
  </div>
</section>

<?php if ($reviews): ?>
<section class="section section-alt"><div class="wrap"><h2>What customers say</h2>
  <?php foreach (array_slice($reviews, 0, 4) as $r): ?><div class="review"><span class="stars"><?= str_repeat('★', (int) $r['rating']) ?></span> <b><?= e($r['title']) ?></b><p><?= e($r['body']) ?></p><span class="small muted">— <?= e($r['reviewer_name']) ?></span></div><?php endforeach; ?>
</div></section>
<?php endif; ?>

<?php if ($faqs): ?>
<section class="section"><div class="wrap faq" style="max-width:820px">
  <div class="eyebrow">Questions</div><h2>Frequently asked</h2>
  <?php foreach (array_slice($faqs, 0, 5) as $i => $f): ?><details data-testid="home-faq-<?= $i ?>"><summary><?= e($f['q']) ?></summary><p><?= e($f['a']) ?></p></details><?php endforeach; ?>
  <a href="<?= url('faq') ?>" data-testid="home-all-faqs-link">See all FAQs →</a>
</div></section>
<?php endif; ?>
