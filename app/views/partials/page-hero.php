<section class="page-hero">
  <div class="container">
    <nav class="crumbs" aria-label="Sayfa yolu"><a href="<?= e(url('/')) ?>">Ana Sayfa</a><span>/</span><span aria-current="page"><?= e($crumb ?? $title) ?></span></nav>
    <?php if (!empty($kicker)): ?><span class="kicker"><?= e($kicker) ?></span><?php endif; ?>
    <h1 class="display"><?= $title_html ?? e($title) ?></h1>
    <?php if (!empty($lead)): ?><p class="lead"><?= e($lead) ?></p><?php endif; ?>
  </div>
</section>
