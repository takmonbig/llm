<div style="display:flex;justify-content:space-between;align-items:center"><h1>Products</h1><a class="btn btn-sm" href="<?= url('admin/products/new') ?>" data-testid="admin-new-product-btn">New product</a></div>
<div class="table-wrap"><table>
  <tr><th></th><th>Title</th><th>SKU</th><th>Price</th><th>Stock</th><th>Status</th></tr>
  <?php foreach ($products as $p): ?>
  <tr data-testid="admin-product-row-<?= (int) $p['id'] ?>">
    <td><img src="<?= product_image_url($p['image']) ?>" alt="" style="width:48px;height:48px;object-fit:cover;border-radius:6px"></td>
    <td><a href="<?= url('admin/products/' . (int) $p['id']) ?>"><?= e($p['title']) ?></a></td>
    <td><?= e($p['sku']) ?></td><td><?= money($p['price']) ?></td><td><?= (int) $p['stock'] ?></td>
    <td><span class="badge <?= $p['is_active'] ? 'badge-green' : 'badge-red' ?>"><?= $p['is_active'] ? 'Active' : 'Hidden' ?></span></td>
  </tr>
  <?php endforeach; ?>
</table></div>
