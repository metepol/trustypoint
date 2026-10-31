<?php use App\View; ?>
<?php View::partial('page-hero', ['title' => 'Örnekler', 'title_html' => 'Vibe coding ile <em>üretildi.</em>', 'crumb' => 'Örnekler', 'kicker' => 'Proje vitrini', 'lead' => 'Eğitim boyunca canlı yayında birlikte geliştirdiğimiz kurumsal oyun, 3D deneyim ve animasyon projelerinden seçkiler.']); ?>
<section class="section">
  <div class="container">
    <?php if (!$items): ?><p class="muted center">Yakında burada örnek projeler olacak.</p><?php endif; ?>
    <div class="works">
      <?php foreach ($items as $item) View::partial('example-card', ['item' => $item]); ?>
    </div>
  </div>
</section>
<section class="section section-alt"><div class="container center"><h2 class="h1">Sıradaki proje <em>seninki</em> olsun.</h2><a href="<?= e(url('egitim')) ?>#kayit" class="btn btn-primary btn-lg mt-2"><?= e(setting('course_cta', 'Hemen Kaydol')) ?> →</a></div></section>
