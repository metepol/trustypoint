<?php
use App\View;
use App\Embed;
$preview = Embed::render((string)setting('course_preview', ''), 'Eğitim tanıtım videosu');
?>
<section class="page-hero course-hero">
  <div class="container">
    <nav class="crumbs" aria-label="Sayfa yolu"><a href="<?= e(url('/')) ?>">Ana Sayfa</a><span>/</span><span aria-current="page">Eğitim</span></nav>
    <span class="kicker"><span class="dot-live"></span> <?= e(setting('course_duration')) ?></span>
    <h1 class="display"><?= e(setting('course_title')) ?></h1>
    <p class="lead"><?= e(setting('course_subtitle')) ?></p>
    <div class="chips">
      <span class="chip">🎚 <?= e(setting('course_level')) ?></span>
      <span class="chip">🔴 Canlı yayın</span>
      <span class="chip">🔁 <?= App\Access::replayDays() ?> gün tekrar</span>
      <span class="chip">🇹🇷 Türkçe</span>
    </div>
  </div>
</section>

<section class="section">
  <div class="container course-split">
    <div class="course-main">
      <?php if ($preview): ?><div class="mb-3 reveal"><?= $preview ?></div><?php endif; ?>
      <?php View::partial('countdown', ['next' => $next]); ?>
      <div class="prose mt-3"><h2>Eğitim hakkında</h2><?= setting('course_description') ?></div>

      <div class="two-lists mt-3">
        <div class="glass-card"><h2 class="h3">Neler öğreneceksin?</h2><ul class="ticks"><?php foreach (lines((string)setting('course_outcomes')) as $o): ?><li><?= e($o) ?></li><?php endforeach; ?></ul></div>
        <div class="glass-card"><h2 class="h3">Kimler için?</h2><ul class="ticks"><?php foreach (lines((string)setting('course_audience')) as $o): ?><li><?= e($o) ?></li><?php endforeach; ?></ul></div>
      </div>

      <div class="mt-3" id="mufredat">
        <h2 class="h2">Müfredat & canlı yayın takvimi</h2>
        <p class="muted">Her ders yayın gününde üye panelinde canlı yayınlanır. Yayın sona erince tekrarı eklenir ve yayın tarihinden itibaren <?= App\Access::replayDays() ?> gün izlenebilir.</p>
        <?php View::partial('curriculum', ['lessons' => $lessons]); ?>
      </div>

      <?php if ($faqs): ?>
      <div class="mt-3"><h2 class="h2">Sık sorulan sorular</h2><?php View::partial('faq', ['faqs' => $faqs]); ?></div>
      <?php endif; ?>
    </div>
    <aside class="sticky"><?php View::partial('price-card'); ?></aside>
  </div>
</section>
