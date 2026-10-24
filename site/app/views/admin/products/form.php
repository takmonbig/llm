<?php
$p = $product ?? [];
$v = fn($k, $d = '') => old($k, $p[$k] ?? $d);
$action = $product ? url('admin/products/' . (int) $p['id']) : url('admin/products/new');
?>
<h1><?= $product ? 'Edit: ' . e($p['title']) : 'New product' ?></h1>
<form method="post" action="<?= $action ?>" class="card">
  <?= csrf_field() ?>
  <div class="form-row">
    <div class="field"><label>Title</label><input name="title" value="<?= $v('title') ?>" data-testid="product-form-title"></div>
    <div class="field"><label>Slug</label><input name="slug" value="<?= $v('slug') ?>" data-testid="product-form-slug"></div>
  </div>
  <div class="field"><label>Subtitle</label><input name="subtitle" value="<?= $v('subtitle') ?>" data-testid="product-form-subtitle"></div>
  <div class="form-row">
    <div class="field"><label>Price (₹)</label><input name="price" value="<?= $v('price') ?>" data-testid="product-form-price"></div>
    <div class="field"><label>Compare-at price (₹)</label><input name="compare_at_price" value="<?= $v('compare_at_price') ?>" data-testid="product-form-compare"></div>
  </div>
  <div class="form-row">
    <div class="field"><label>SKU</label><input name="sku" value="<?= $v('sku') ?>" data-testid="product-form-sku"></div>
    <div class="field"><label>Stock</label><input name="stock" type="number" value="<?= $v('stock', 0) ?>" data-testid="product-form-stock"></div>
  </div>
  <div class="form-row">
    <div class="field"><label>Bottle count</label><input name="bottle_count" type="number" value="<?= $v('bottle_count', 1) ?>"></div>
    <div class="field"><label>Tablet count</label><input name="tablet_count" type="number" value="<?= $v('tablet_count', 0) ?>"></div>
  </div>
  <?php foreach (['short_description' => 'Short description', 'description' => 'Description', 'ingredients' => 'Ingredients', 'usage_directions' => 'Usage directions', 'warnings' => 'Warnings', 'storage_instructions' => 'Storage', 'manufacturer_info' => 'Manufacturer info', 'verification_notes' => 'Internal verification notes', 'seo_title' => 'SEO title', 'seo_description' => 'SEO description'] as $k => $label): ?>
  <div class="field"><label><?= $label ?></label><textarea name="<?= $k ?>" rows="3" data-testid="product-form-<?= $k ?>"><?= $v($k) ?></textarea></div>
  <?php endforeach; ?>
  <?php foreach (['is_active' => 'Active', 'is_featured' => 'Featured', 'is_combo' => 'Combo'] as $k => $label): ?>
  <label class="check"><input type="checkbox" name="<?= $k ?>" value="1" <?= !empty($p[$k]) ? 'checked' : '' ?> data-testid="product-form-<?= $k ?>"> <?= $label ?></label>
  <?php endforeach; ?>
  <?php if ($variants): ?><h3 style="margin-top:20px">Variants</h3>
  <?php foreach ($variants as $va): ?>
  <div class="form-row"><div class="field"><label>Name</label><input name="variants[<?= (int) $va['id'] ?>][name]" value="<?= e($va['name']) ?>"></div><div class="field"><label>Tablets</label><input name="variants[<?= (int) $va['id'] ?>][tablet_count]" value="<?= (int) $va['tablet_count'] ?>"></div></div>
  <div class="field"><textarea name="variants[<?= (int) $va['id'] ?>][description]" rows="2"><?= e($va['description']) ?></textarea></div>
  <?php endforeach; endif; ?>
  <?php if ($images): ?><h3 style="margin-top:20px">Images</h3>
  <?php foreach ($images as $img): ?>
  <div style="display:grid;grid-template-columns:64px 1fr 80px;gap:10px;align-items:center;margin-bottom:8px">
    <?php if ($img['media_type'] === 'video'): ?><span class="badge">Video</span><?php else: ?><img src="<?= url($img['path']) ?>" alt="" style="width:64px;height:64px;object-fit:cover;border-radius:6px"><?php endif; ?>
    <input name="image_alt[<?= (int) $img['id'] ?>]" value="<?= e($img['alt_text']) ?>">
    <input name="image_order[<?= (int) $img['id'] ?>]" value="<?= (int) $img['sort_order'] ?>" type="number">
  </div>
  <?php endforeach; endif; ?>
  <button class="btn" style="margin-top:16px" data-testid="product-form-save-btn">Save product</button>
</form>
<?php if ($product): ?>
<form method="post" action="<?= url('admin/products/' . (int) $p['id'] . '/images') ?>" enctype="multipart/form-data" class="card" style="margin-top:20px">
  <?= csrf_field() ?><h3>Upload image</h3>
  <div class="form-row"><input type="file" name="image" accept="image/*" data-testid="product-image-file"><input name="alt_text" placeholder="Alt text"></div>
  <button class="btn btn-sm" style="margin-top:10px" data-testid="product-image-upload-btn">Upload</button>
</form>
<?php endif; ?>
