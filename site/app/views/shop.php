<section class="section"><div class="wrap">
  <h1><?= $q ? 'Search: ' . e($q) : 'Shop' ?></h1>
  <form method="get" action="<?= url('search') ?>" style="max-width:420px;display:flex;gap:8px;margin-bottom:28px">
    <input name="q" value="<?= e($q) ?>" placeholder="Search products" data-testid="shop-search-input">
    <button class="btn btn-sm" data-testid="shop-search-btn">Search</button>
  </form>
  <?php if (!$products): ?><p class="muted" data-testid="shop-empty">No products found.</p><?php endif; ?>
  <div class="grid-3">
    <?php foreach ($products as $p): ?>
    <a class="card" href="<?= url('product/' . $p['slug']) ?>" data-testid="shop-product-<?= e($p['slug']) ?>">
      <img src="<?= product_image_url($p['image']) ?>" alt="<?= e($p['title']) ?>" style="border-radius:10px;aspect-ratio:1/1;object-fit:cover;width:100%">
      <h3 style="margin-top:14px"><?= e($p['title']) ?></h3>
      <p class="small muted"><?= e($p['subtitle']) ?></p>
      <b><?= money($p['price']) ?></b>
    </a>
    <?php endforeach; ?>
  </div>
</div></section>
