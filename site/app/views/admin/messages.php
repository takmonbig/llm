<h1>Messages</h1>
<?php if (!$messages): ?><p class="muted" data-testid="no-messages">No messages yet.</p><?php endif; ?>
<?php foreach ($messages as $m): ?>
<div class="card" style="margin-bottom:12px" data-testid="admin-message-<?= (int) $m['id'] ?>">
  <b><?= e($m['subject'] ?: '(no subject)') ?></b> <?php if (!$m['is_read']): ?><span class="badge badge-orange">New</span><?php endif; ?>
  <p class="pre"><?= e($m['message']) ?></p>
  <p class="small muted"><?= e($m['name']) ?> · <?= e($m['email']) ?> <?= e($m['phone']) ?> · <?= format_date($m['created_at']) ?></p>
  <?php if (!$m['is_read']): ?><form method="post" action="<?= url('admin/messages/' . (int) $m['id'] . '/read') ?>"><?= csrf_field() ?><button class="btn btn-sm btn-ghost" data-testid="message-read-<?= (int) $m['id'] ?>">Mark read</button></form><?php endif; ?>
</div>
<?php endforeach; ?>
