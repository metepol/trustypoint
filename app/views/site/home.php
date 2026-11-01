<?php
use App\View;
use App\Access;
$heroTitle = preg_replace('/\*(.+?)\*/u', '<em>$1</em>', e(setting('home_hero_title')));
$stats = array_map(static fn($l) => array_pad(explode('|', $l, 2), 2, ''), lines((string)setting('home_stats')));
$marquee = array_filter(array_map('trim', explode('·', (string)setting('home_marquee'))));
$outcomeIcons = ['sparkles', 'gamepad', 'layers', 'trophy', 'rocket', 'award', 'code', 'globe'];
?>
<section class="hero">
  <div class="hero-bg" aria-hidden="true"><span class="mesh m1"></span><span class="mesh m2"></span><span class="mesh m3"></span><span class="dots"></span></div>
  <div class="container hero-grid">
    <div class="hero-copy">
      <span class="badge-pill"><span class="dot-live"></span> <?= e(setting('home_hero_kicker')) ?></span>
      <h1 class="hero-title"><?= $heroTitle ?></h1>
      <p class="hero-sub"><?= e(setting('course_title')) ?></p>
      <p class="lead"><?= e(setting('home_hero_text')) ?></p>
      <div class="hero-cta">
        <a href="<?= e(url('egitim')) ?>#kayit" class="btn btn-primary btn-lg"><?= e(setting('course_cta', 'Hemen Kaydol')) ?> <?= icon('arrow', 18) ?></a>
        <a href="<?= e(url('egitim')) ?>#mufredat" class="btn btn-glass btn-lg"><?= icon('play', 16) ?> Müfredatı gör</a>
      </div>
      <ul class="hero-points">
        <li><?= icon('live', 18) ?> Canlı yayın</li>
        <li><?= icon('repeat', 18) ?> <?= Access::replayDays() ?> gün tekrar</li>
        <li><?= icon('code', 18) ?> Kaynak kod + prompt kütüphanesi</li>
      </ul>
    </div>

    <div class="hero-visual">
      <div class="live-mock" aria-hidden="true">
        <div class="lm-head">
          <span class="lm-live"><span class="dot-live"></span> CANLI</span>
          <span class="lm-title">Kampanya oyunu: brief'ten prototipe</span>
          <span class="lm-viewers"><?= icon('eye', 14) ?> 248</span>
        </div>
        <div class="lm-body">
          <div class="lm-screen">
            <div class="lm-game">
              <span class="g-sky"></span>
              <span class="g-coin c1"></span><span class="g-coin c2"></span><span class="g-coin c3"></span>
              <span class="g-player"></span>
              <span class="g-ground"></span>
              <span class="g-score">SKOR 1 240</span>
            </div>
            <div class="lm-bar"><span class="lm-progress"></span><span class="lm-time">42:18</span></div>
          </div>
          <div class="lm-chat">
            <div class="msg"><b>Elif</b> Skor tablosunu nasıl ekledik?</div>
            <div class="msg me"><b>Eğitmen</b> Prompt'u ekrana yazıyorum 👇</div>
            <div class="msg"><b>Mert</b> Çalıştı! 🚀</div>
            <div class="lm-input">Mesaj yaz…</div>
          </div>
        </div>
      </div>
      <div class="float-chip fc-1"><?= icon('repeat', 16) ?> <span><b><?= Access::replayDays() ?> gün</b> tekrar erişimi</span></div>
      <div class="float-chip fc-2"><?= icon('award', 16) ?> <span><b>Sertifika</b> program sonunda</span></div>
      <?php View::partial('countdown', ['next' => $next]); ?>
    </div>
  </div>

  <?php if ($marquee): ?>
  <div class="container">
    <div class="tech-strip" aria-label="Kullanılan teknolojiler">
      <span class="ts-label">Derste kullanılan araçlar</span>
      <div class="ts-viewport"><div class="marquee-track">
        <?php for ($i = 0; $i < 2; $i++): foreach ($marquee as $m): ?><span><?= e($m) ?></span><?php endforeach; endfor; ?>
      </div></div>
    </div>
  </div>
  <?php endif; ?>
</section>

<?php if ($stats): ?>
<section class="section-tight">
  <div class="container">
    <div class="stats">
      <?php foreach ($stats as [$val, $label]): ?>
        <div class="stat"><b><?= e($val) ?></b><span><?= e($label) ?></span></div>
      <?php endforeach; ?>
    </div>
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
      <div class="step reveal"><span class="step-ic"><?= icon('user', 22) ?></span><span class="step-no">Adım 1</span><h3>Kaydol</h3><p>E-posta ve şifrenle 10 saniyede üye ol, kart ya da havale ile eğitime katıl.</p></div>
      <div class="step reveal"><span class="step-ic"><?= icon('live', 22) ?></span><span class="step-no">Adım 2</span><h3>Canlı izle</h3><p>Yayın günü üye panelinde yayın ekranı açılır. Soru sor, birlikte kodlayalım.</p></div>
      <div class="step reveal"><span class="step-ic"><?= icon('repeat', 22) ?></span><span class="step-no">Adım 3</span><h3>1 yıl tekrar</h3><p>Kaçırdığın veya tekrar görmek istediğin her ders <?= Access::replayDays() ?> gün boyunca panelinde.</p></div>
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
        <div class="bento-item reveal b-<?= $i % 6 ?>"><span class="bento-ic"><?= icon($outcomeIcons[$i % count($outcomeIcons)], 22) ?></span><p><?= e($o) ?></p></div>
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
      <a href="<?= e(url('ornekler')) ?>" class="btn btn-glass">Tüm örnekler <?= icon('arrow', 16) ?></a>
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
      <a href="<?= e(url('referanslar')) ?>" class="btn btn-glass">Tüm referanslar <?= icon('arrow', 16) ?></a>
    </div>
    <div class="quotes">
      <?php foreach (array_slice($testimonials, 0, 3) as $t) View::partial('testimonial', ['t' => $t]); ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($faqs): ?>
<section class="section section-alt">
  <div class="container faq-split">
    <div class="faq-intro">
      <span class="kicker">SSS</span>
      <h2 class="h1">Aklındaki sorular</h2>
      <p class="muted">Cevabını bulamadığın bir soru mu var? Bize yaz, aynı gün dönelim.</p>
      <a href="<?= e(url('iletisim')) ?>" class="btn btn-glass mt-1"><?= icon('chat', 16) ?> Soru sor</a>
    </div>
    <?php View::partial('faq', ['faqs' => $faqs]); ?>
  </div>
</section>
<?php endif; ?>
