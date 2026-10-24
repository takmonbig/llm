<h1>Reviews</h1>
<div class="account-nav"><?php foreach (['pending', 'approved', 'rejected', 'hidden', 'all'] as $s): ?><a class="btn btn-sm <?= $status === $s ? '' : 'btn-ghost' ?>" href="?status=<?= $s ?>" data-testid="reviews-tab-<?= $s ?>"><?= ucfirst($s) ?></a><?php endforeach; ?></div>
<?php if (!$reviews): ?><p class="muted" data-testid="no-reviews">No reviews in this list.</p><?php endif; ?>
<?php foreach ($reviews as $r): ?>
<div class="card" style="margin-bottom:12px" data-testid="admin-review-<?= (int) $r['id'] ?>">
  <span class="stars"><?= str_repeat('★', (int) $r['rating']) ?></span> <b><?= e($r['title']) ?></b> <span class="badge"><?= e($r['status']) ?></span>
  <p><?= e($r['body']) ?></p>
  <p class="small muted"><?= e($r['reviewer_name']) ?> · <?= e($r['product_title']) ?> · <?= format_date($r['created_at']) ?></p>
  <form method="post" action="<?= url('admin/reviews/' . (int) $r['id'] . '/status') ?>?status=<?= e($status) ?>" style="display:flex;gap:6px">
    <?= csrf_field() ?>
    <?php foreach (['approved' => 'Approve', 'rejected' => 'Reject', 'hidden' => 'Hide'] as $k => $label): ?><button class="btn btn-sm btn-ghost" name="status" value="<?= $k ?>" data-testid="review-<?= $k ?>-<?= (int) $r['id'] ?>"><?= $label ?></button><?php endforeach; ?>
  </form>
</div>
<?php endforeach; ?>
