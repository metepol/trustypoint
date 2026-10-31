<?php use App\View; ?>
<?php View::partial('page-hero', ['title' => $page['title'], 'kicker' => setting('site_tagline'), 'lead' => setting('footer_text')]); ?>
<section class="section">
  <div class="container about-grid">
    <div class="prose reveal"><?= $page['content'] ?></div>
    <aside class="about-aside">
      <div class="glass-card">
        <span class="kicker">Tek odak</span>
        <p class="h3">Sadece bir eğitim satıyoruz — ve onu en iyi şekilde yapıyoruz.</p>
        <a class="btn btn-primary btn-block mt-2" href="<?= e(url('egitim')) ?>">Eğitimi incele →</a>
      </div>
    </aside>
  </div>
</section>
<?php if ($testimonials): ?>
<section class="section section-alt">
  <div class="container">
    <div class="section-head"><span class="kicker">Referanslar</span><h2 class="h1">Birlikte <em>üretenler</em></h2></div>
    <div class="quotes"><?php foreach ($testimonials as $t) View::partial('testimonial', ['t' => $t]); ?></div>
  </div>
</section>
<?php endif; ?>
