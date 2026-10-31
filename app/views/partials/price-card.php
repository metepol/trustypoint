<?php
use App\Access;
use App\Auth;
use App\Payments;
$old = trim((string)setting('course_old_price', ''));
$enrolled = Access::hasCourse(Auth::user());
?>
<div class="price-card" id="kayit">
  <div class="price-badge">Tek eğitim · Tek ödeme</div>
  <h3 class="price-title"><?= e(setting('course_title')) ?></h3>
  <div class="price">
    <?php if ($old !== '' && (float)$old > Payments::price()): ?><s><?= e(money($old)) ?></s><?php endif; ?>
    <strong><?= e(money(Payments::price())) ?></strong>
  </div>
  <ul class="ticks">
    <?php foreach (lines((string)setting('course_includes')) as $item): ?><li><?= e($item) ?></li><?php endforeach; ?>
    <li>Her yayının tekrarı <?= (int)Access::replayDays() ?> gün erişimde</li>
  </ul>
  <?php if ($enrolled): ?>
    <a href="<?= e(url('panel')) ?>" class="btn btn-primary btn-lg btn-block">Derslerime git →</a>
  <?php else: ?>
    <a href="<?= e(url('odeme')) ?>" class="btn btn-primary btn-lg btn-block"><?= e(setting('course_cta', 'Hemen Kaydol')) ?> →</a>
    <p class="tiny muted center mt-1">Kart (Stripe) veya Havale/EFT · Güvenli ödeme</p>
  <?php endif; ?>
</div>
