<?php if (!empty($next)): ?>
<div class="countdown" data-countdown="<?= e(date('c', strtotime($next['scheduled_at']))) ?>">
  <div class="cd-label"><span class="dot-live"></span> Sıradaki canlı yayın · <?= e(tr_date($next['scheduled_at'])) ?></div>
  <div class="cd-title"><?= e($next['title']) ?></div>
  <div class="cd-grid" aria-label="Geri sayım">
    <div><b data-cd="d">00</b><span>gün</span></div>
    <div><b data-cd="h">00</b><span>saat</span></div>
    <div><b data-cd="m">00</b><span>dakika</span></div>
    <div><b data-cd="s">00</b><span>saniye</span></div>
  </div>
</div>
<?php endif; ?>
