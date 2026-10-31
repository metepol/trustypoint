<?php use App\Access; ?>
<ol class="curriculum">
  <?php foreach ($lessons as $i => $l): $state = Access::state($l); ?>
    <li class="cur-item state-<?= e($state) ?>">
      <span class="cur-no"><?= str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
      <div class="cur-body">
        <h3 class="cur-title"><?= e($l['title']) ?></h3>
        <?php if ($l['summary']): ?><p class="muted"><?= e($l['summary']) ?></p><?php endif; ?>
      </div>
      <div class="cur-meta">
        <time datetime="<?= e(date('c', strtotime($l['scheduled_at']))) ?>"><?= e(tr_date($l['scheduled_at'])) ?></time>
        <span class="pill pill-<?= e($state) ?>"><?= $state === Access::LIVE ? '● ' : '' ?><?= e(Access::label($state)) ?></span>
      </div>
    </li>
  <?php endforeach; ?>
</ol>
