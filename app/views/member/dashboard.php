<?php
use App\Access;
use App\View;
?>
<header class="member-head">
  <h1 class="h2">Merhaba <?= e(explode(' ', $user['name'])[0]) ?> <span aria-hidden="true">👋</span></h1>
  <p class="muted"><?= $hasCourse ? 'Canlı yayınlar yayın gününde burada açılır; tekrarlar ' . Access::replayDays() . ' gün boyunca erişimde.' : 'Eğitime kaydolduğunda canlı yayınlar ve tekrarlar burada açılacak.' ?></p>
</header>

<?php if (!$hasCourse): ?>
  <?php if ($pending): ?>
    <div class="alert alert-info">⏳ <b>#<?= (int)$pending['id'] ?></b> numaralı havale bildiriminiz inceleniyor. Onaylandığında e-posta ile bilgilendirileceksiniz.</div>
  <?php else: ?>
    <div class="upsell glass-card">
      <div><span class="kicker">Tek adım kaldı</span><h2 class="h3"><?= e(setting('course_title')) ?></h2><p class="muted"><?= e(setting('course_subtitle')) ?></p></div>
      <a href="<?= e(url('odeme')) ?>" class="btn btn-primary btn-lg"><?= e(setting('course_cta', 'Hemen Kaydol')) ?> · <?= e(money(App\Payments::price())) ?></a>
    </div>
  <?php endif; ?>
<?php endif; ?>

<?php if ($next): $nstate = Access::state($next); ?>
  <div class="next-live <?= $nstate === Access::LIVE ? 'is-live' : '' ?>">
    <?php View::partial('countdown', ['next' => $next]); ?>
    <?php if ($hasCourse): ?>
      <a href="<?= e(url('panel/ders/' . $next['id'])) ?>" class="btn <?= $nstate === Access::LIVE ? 'btn-live' : 'btn-outline' ?> btn-lg"><?= $nstate === Access::LIVE ? '● Canlı yayına katıl' : 'Ders sayfası →' ?></a>
    <?php endif; ?>
  </div>
<?php endif; ?>

<h2 class="h3 mt-3">Dersler</h2>
<div class="lesson-list">
  <?php foreach ($lessons as $i => $l): $state = Access::state($l); $clickable = $hasCourse && in_array($state, [Access::LIVE, Access::REPLAY, Access::PROCESSING, Access::UPCOMING], true); ?>
    <<?= $clickable ? 'a href="' . e(url('panel/ders/' . $l['id'])) . '"' : 'div' ?> class="lesson-row state-<?= e($state) ?><?= $hasCourse ? '' : ' locked' ?>">
      <span class="cur-no"><?= str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
      <div class="lr-body">
        <b><?= e($l['title']) ?></b>
        <small class="muted"><?= e(tr_date($l['scheduled_at'])) ?> · <?= (int)$l['duration_min'] ?> dk
          <?php if ($state === Access::REPLAY): ?> · Tekrar <?= e(tr_date(date('Y-m-d H:i:s', Access::replayUntilTs($l)), false)) ?> tarihine kadar<?php endif; ?>
        </small>
      </div>
      <span class="pill pill-<?= e($state) ?>"><?= $hasCourse ? e(Access::label($state)) : '🔒 Kilitli' ?></span>
    </<?= $clickable ? 'a' : 'div' ?>>
  <?php endforeach; ?>
  <?php if (!$lessons): ?><p class="muted">Ders takvimi yakında yayınlanacak.</p><?php endif; ?>
</div>
