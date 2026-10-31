<?php
use App\Access;
use App\Embed;
$resources = array_map(static fn($l) => array_map('trim', array_pad(explode('|', $l, 2), 2, '')), lines((string)$lesson['resources']));
?>
<a href="<?= e(url('panel')) ?>" class="link-arrow">← Derslerim</a>
<header class="member-head mt-1">
  <span class="pill pill-<?= e($state) ?>"><?= $state === Access::LIVE ? '● ' : '' ?><?= e(Access::label($state)) ?></span>
  <h1 class="h2 mt-1"><?= e($lesson['title']) ?></h1>
  <p class="muted"><?= e(tr_date($lesson['scheduled_at'])) ?> · <?= (int)$lesson['duration_min'] ?> dk</p>
</header>

<div class="stage">
<?php switch ($state):
    case Access::LIVE:
        $player = Embed::render((string)$lesson['live_embed'], $lesson['title']);
        if ($player): echo $player; ?>
          <p class="tiny muted mt-1">Yayın görünmüyorsa sayfayı yenileyin. Yayın bittikten sonra tekrarı bu sayfaya eklenecek.</p>
        <?php else: ?>
          <div class="stage-empty"><span class="dot-live"></span><h2 class="h3">Yayın birazdan başlıyor</h2><p class="muted">Yayın bağlantısı yayın saatinde burada görünecek. Sayfa otomatik yenilenir.</p><meta http-equiv="refresh" content="60"></div>
        <?php endif;
        break;
    case Access::REPLAY:
        echo Embed::render((string)$lesson['replay_embed'], $lesson['title']); ?>
        <p class="tiny muted mt-1">Bu tekrar <b><?= e(tr_date(date('Y-m-d H:i:s', Access::replayUntilTs($lesson)), false)) ?></b> tarihine kadar izlenebilir.</p>
        <?php break;
    case Access::PROCESSING: ?>
        <div class="stage-empty"><h2 class="h3">Tekrar hazırlanıyor 🎞</h2><p class="muted">Yayın sona erdi. Kayıt kısa süre içinde buraya eklenecek ve <?= Access::replayDays() ?> gün boyunca izlenebilecek.</p></div>
        <?php break;
    case Access::EXPIRED: ?>
        <div class="stage-empty"><h2 class="h3">Tekrar süresi doldu</h2><p class="muted">Bu dersin tekrar izleme süresi (<?= Access::replayDays() ?> gün) sona erdi.</p></div>
        <?php break;
    default: ?>
        <div class="stage-empty">
          <?php App\View::partial('countdown', ['next' => $lesson]); ?>
          <p class="muted mt-1">Yayın ekranı başlangıçtan <?= (int)setting('live_open_minutes', '30') ?> dakika önce burada açılacak.</p>
        </div>
<?php endswitch; ?>
</div>

<?php if (trim(strip_tags((string)$lesson['description'])) !== ''): ?>
  <div class="prose mt-3"><?= $lesson['description'] ?></div>
<?php endif; ?>

<?php if ($resources && in_array($state, [Access::LIVE, Access::REPLAY, Access::PROCESSING], true)): ?>
  <h2 class="h3 mt-3">Ders kaynakları</h2>
  <ul class="resources">
    <?php foreach ($resources as [$title, $link]): ?>
      <li><?php if (preg_match('#^https?://#', $link)): ?><a href="<?= e($link) ?>" target="_blank" rel="noopener">📎 <?= e($title) ?> ↗</a><?php else: ?>📎 <?= e($title) ?><?php endif; ?></li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>

<nav class="lesson-pager mt-3" aria-label="Ders gezinme">
  <?php foreach ($lessons as $idx => $l): if ((int)$l['id'] !== (int)$lesson['id']) continue; ?>
    <?php if (isset($lessons[$idx - 1])): ?><a class="btn btn-ghost" href="<?= e(url('panel/ders/' . $lessons[$idx - 1]['id'])) ?>">← Önceki ders</a><?php else: ?><span></span><?php endif; ?>
    <?php if (isset($lessons[$idx + 1])): ?><a class="btn btn-ghost" href="<?= e(url('panel/ders/' . $lessons[$idx + 1]['id'])) ?>">Sonraki ders →</a><?php endif; ?>
  <?php endforeach; ?>
</nav>
