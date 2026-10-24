<?php $first = $images[0] ?? null; ?>
<section class="section"><div class="wrap grid-2">
  <div>
    <div class="gallery-main" data-gallery-main data-testid="product-gallery-main">
      <?php if ($first && $first['media_type'] === 'video'): ?><video src="<?= url($first['path']) ?>" controls muted playsinline></video>
      <?php else: ?><img src="<?= product_image_url($first['path'] ?? null) ?>" alt="<?= e($first['alt_text'] ?? $product['title']) ?>"><?php endif; ?>
    </div>
    <div class="thumbs">
      <?php foreach ($images as $i => $img): ?>
      <button type="button" class="<?= $i === 0 ? 'active' : '' ?>" data-thumb data-type="<?= e($img['media_type']) ?>" data-src="<?= url($img['path']) ?>" data-alt="<?= e($img['alt_text']) ?>" data-testid="product-thumb-<?= $i ?>">
        <?php if ($img['media_type'] === 'video'): ?><?php if ($img['poster_path']): ?><img src="<?= url($img['poster_path']) ?>" alt="Video"><?php else: ?><span class="thumb-video">▶ Video</span><?php endif; ?>
        <?php else: ?><img src="<?= url($img['path']) ?>" alt="<?= e($img['alt_text']) ?>"><?php endif; ?>
      </button>
      <?php endforeach; ?>
    </div>
  </div>
  <div>
    <h1 data-testid="product-title"><?= e($product['title']) ?></h1>
    <p class="muted"><?= e($product['subtitle']) ?></p>
    <?php if ($rating['count']): ?><p><span class="stars">★</span> <?= e($rating['avg']) ?> · <?= (int) $rating['count'] ?> reviews</p><?php endif; ?>
    <div class="price" data-testid="product-price"><?= money($product['price']) ?><?php if ($product['compare_at_price']): ?><s><?= money($product['compare_at_price']) ?></s><?php endif; ?></div>
    <p class="small muted">Free shipping · <?= (int) $product['bottle_count'] ?> bottles · <?= (int) $product['tablet_count'] ?> tablets</p>
    <p><?= e($product['short_description']) ?></p>
    <?php if ((int) $product['stock'] > 0): ?>
    <form method="post" action="<?= url('cart/add') ?>" class="qty" style="margin:20px 0">
      <?= csrf_field() ?><input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
      <input type="number" name="quantity" value="1" min="1" max="10" data-testid="product-qty-input">
      <button class="btn" data-testid="add-to-cart-btn">Add to cart</button>
      <button class="btn btn-dark" formaction="<?= url('cart/buy-now') ?>" data-testid="buy-now-btn">Buy now</button>
    </form>
    <?php else: ?><p class="badge badge-red" data-testid="out-of-stock">Out of stock</p><?php endif; ?>
    <?php foreach ($variants as $v): ?>
    <div class="card variant" style="margin-bottom:12px">
      <?php if ($v['image_path']): ?><img src="<?= url($v['image_path']) ?>" alt="<?= e($v['name']) ?>"><?php endif; ?>
      <div><b><?= e($v['name']) ?></b> <span class="badge"><?= (int) $v['tablet_count'] ?> tablets</span><p class="small muted" style="margin:4px 0 0"><?= e($v['description']) ?></p></div>
    </div>
    <?php endforeach; ?>
  </div>
</div></section>

<section class="section section-alt" id="details"><div class="wrap faq" style="max-width:900px">
  <h2>Product details</h2>
  <div class="prose"><?= nl2p($product['description']) ?></div>
  <?php foreach (['ingredients' => 'Ingredients', 'usage_directions' => 'How to use', 'warnings' => 'Warnings', 'storage_instructions' => 'Storage', 'manufacturer_info' => 'Manufacturer'] as $k => $label): if (!$product[$k]) continue; ?>
  <details data-testid="product-detail-<?= $k ?>"><summary><?= $label ?></summary><p class="pre"><?= e($product[$k]) ?></p></details>
  <?php endforeach; ?>
</div></section>

<section class="section" id="reviews"><div class="wrap" style="max-width:900px">
  <h2>Reviews</h2>
  <?php if (!$reviews): ?><p class="muted" data-testid="no-reviews">No reviews yet.</p><?php endif; ?>
  <?php foreach ($reviews as $r): ?><div class="review"><span class="stars"><?= str_repeat('★', (int) $r['rating']) ?></span> <b><?= e($r['title']) ?></b><?php if ($r['is_verified_purchase']): ?> <span class="badge badge-green">Verified</span><?php endif; ?><p><?= e($r['body']) ?></p><span class="small muted">— <?= e($r['reviewer_name']) ?>, <?= format_date($r['created_at'], 'd M Y') ?></span></div><?php endforeach; ?>
  <?php if (current_user()): ?>
  <form method="post" action="<?= url('reviews') ?>" class="card" style="margin-top:24px">
    <?= csrf_field() ?><input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
    <h3>Write a review</h3>
    <div class="field"><label>Rating</label><select name="rating" data-testid="review-rating-select"><?php for ($i = 5; $i >= 1; $i--): ?><option value="<?= $i ?>"><?= $i ?> stars</option><?php endfor; ?></select></div>
    <div class="field"><label>Title</label><input name="title" data-testid="review-title-input"></div>
    <div class="field"><label>Review</label><textarea name="body" rows="4" data-testid="review-body-input"></textarea></div>
    <button class="btn" data-testid="review-submit-btn">Submit review</button>
  </form>
  <?php else: ?><p class="muted"><a href="<?= url('login') ?>" data-testid="review-login-link">Log in</a> to write a review.</p><?php endif; ?>
</div></section>
