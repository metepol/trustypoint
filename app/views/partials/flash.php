<?php $items = flashes(); if ($items): ?>
<div class="toasts" role="status" aria-live="polite">
  <?php foreach ($items as $f): ?>
    <div class="toast toast-<?= e($f['type']) ?>"><span><?= e($f['message']) ?></span><button type="button" class="toast-x" aria-label="Kapat">×</button></div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
