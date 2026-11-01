<?php
use App\View;
use App\Access;
$heroTitle = preg_replace('/\*(.+?)\*/u', '<em>$1</em>', e(setting('home_hero_title')));
$stats = array_map(static fn($l) => array_pad(explode('|', $l, 2), 2, ''), lines((string)setting('home_stats')));
$marquee = array_filter(array_map('trim', explode('·', (string)setting('home_marquee'))));
?>
<section class="hero">
  <div class="hero-bg" aria-hidden="true"><span class="blob b1"></span><span class="blob b2"></span><span class="grid-lines"></span></div>
  <div class="container hero-grid">
    <div class="hero-copy">
      <span class="kicker"><span class="dot-live"></span> <?= e(setting('home_hero_kicker')) ?></span>
      <h1 class="hero-title"><?= $heroTitle ?></h1>
      <p class="hero-sub"><?= e(setting('course_title')) ?></p>
      <p class="lead"><?= e(setting('home_hero_text')) ?></p>
      <div class="hero-cta">
        <a href="<?= e(url('egitim')) ?>#kayit" class="btn btn-primary btn-lg"><?= e(setting('course_cta', 'Hemen Kaydol')) ?> →</a>
        <a href="<?= e(url('egitim')) ?>#mufredat" class="btn btn-outline btn-lg">Müfredatı gör</a>
      </div>
      <ul class="hero-points">
        <li>● Canlı yayın</li><li>● <?= Access::replayDays() ?> gün tekrar</li><li>● Kaynak kod + prompt kütüphanesi</li>
      </ul>
    </div>
    <div class="hero-visual">
      <div class="code-window" aria-hidden="true">
        <div class="cw-bar"><i></i><i></i><i></i><span>prompt.md</span></div>
        <pre><code><span class="c">// markan için 60 saniyede oyun</span>
<span class="k">&gt;</span> Bir içecek markası için
  kapakları toplayan, 30 sn'lik
  skor tablolu bir mobil oyun yap.
  Renkler: <span class="s">#13306f</span> <span class="s">#2554c7</span>

<span class="k">✓</span> game.js oluşturuldu
<span class="k">✓</span> leaderboard.php eklendi
<span class="k">✓</span> yayına hazır 🚀</code></pre>
      </div>
      <?php View::partial('countdown', ['next' => $next]); ?>
      <div class="spin-badge" aria-hidden="true">
        <svg viewBox="0 0 120 120"><defs><path id="circ" d="M60,60 m-46,0 a46,46 0 1,1 92,0 a46,46 0 1,1 -92,0"/></defs><text><textPath href="#circ">CANLI YAYIN • 1 YIL TEKRAR • VIBE CODING • </textPath></text></svg>
        <span>▶</span>
      </div>
    </div>
  </div>
</section>

<?php if ($marquee): ?>
<div class="marquee" aria-label="Kullanılan teknolojiler">
  <div class="marquee-track">
    <?php for ($i = 0; $i < 2; $i++): foreach ($marquee as $m): ?><span><?= e($m) ?></span><span class="star" aria-hidden="true">✦</span><?php endforeach; endfor; ?>
  </div>
</div>
<?php endif; ?>

<?php if ($stats): ?>
<section class="section">
  <div class="container stats">
    <?php foreach ($stats as [$val, $label]): ?>
      <div class="stat reveal"><b><?= e($val) ?></b><span><?= e($label) ?></span></div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<section class="section">
  <div class="container">
    <div class="section-head">
      <span class="kicker">Nasıl işliyor?</span>
      <h2 class="h1">Üç adım. <em>Sıfır karmaşa.</em></h2>
    </div>
    <div class="steps">
      <div class="step reveal"><span class="step-no">01</span><h3>Kaydol</h3><p>E-posta ve şifrenle 10 saniyede üye ol, kart ya da havale ile eğitime katıl.</p></div>
      <div class="step reveal"><span class="step-no">02</span><h3>Canlı izle</h3><p>Yayın günü üye panelinde yayın ekranı açılır. Soru sor, birlikte kodlayalım.</p></div>
      <div class="step reveal"><span class="step-no">03</span><h3>1 yıl tekrar</h3><p>Kaçırdığın veya tekrar görmek istediğin her ders <?= Access::replayDays() ?> gün boyunca panelinde.</p></div>
    </div>
  </div>
</section>

<section class="section section-alt">
  <div class="container">
    <div class="section-head">
      <span class="kicker">Neler öğreneceksin?</span>
      <h2 class="h1">Briften <em>yayına</em> kadar her şey.</h2>
    </div>
    <div class="bento">
      <?php foreach (lines((string)setting('course_outcomes')) as $i => $o): ?>
        <div class="bento-item reveal b-<?= $i % 6 ?>"><span class="bento-no"><?= str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) ?></span><p><?= e($o) ?></p></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section" id="mufredat">
  <div class="container course-split">
    <div>
      <div class="section-head left">
        <span class="kicker">Canlı ders takvimi</span>
        <h2 class="h1">Müfredat</h2>
        <p class="muted"><?= e(setting('course_duration')) ?> · <?= e(setting('course_level')) ?></p>
      </div>
      <?php View::partial('curriculum', ['lessons' => array_slice($lessons, 0, 8)]); ?>
      <?php if (count($lessons) > 8): ?><a class="link-arrow mt-2" href="<?= e(url('egitim')) ?>#mufredat">Tüm dersleri gör →</a><?php endif; ?>
    </div>
    <aside class="sticky"><?php View::partial('price-card'); ?></aside>
  </div>
</section>

<?php if ($examples): ?>
<section class="section section-alt">
  <div class="container">
    <div class="section-head split">
      <div><span class="kicker">Örnekler</span><h2 class="h1">Derste <em>üretilenler</em></h2></div>
      <a href="<?= e(url('ornekler')) ?>" class="btn btn-outline">Tüm örnekler →</a>
    </div>
    <div class="works">
      <?php foreach (array_slice($examples, 0, 3) as $item) View::partial('example-card', ['item' => $item]); ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($testimonials): ?>
<section class="section">
  <div class="container">
    <div class="section-head split">
      <div><span class="kicker">Referanslar</span><h2 class="h1">Katılımcılar <em>ne diyor?</em></h2></div>
      <a href="<?= e(url('referanslar')) ?>" class="btn btn-outline">Tüm referanslar →</a>
    </div>
    <div class="quotes">
      <?php foreach (array_slice($testimonials, 0, 3) as $t) View::partial('testimonial', ['t' => $t]); ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($faqs): ?>
<section class="section section-alt">
  <div class="container narrow">
    <div class="section-head"><span class="kicker">SSS</span><h2 class="h1">Aklındaki sorular</h2></div>
    <?php View::partial('faq', ['faqs' => $faqs]); ?>
  </div>
</section>
<?php endif; ?>
