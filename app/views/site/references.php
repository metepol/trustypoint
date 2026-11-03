<?php use App\View; $logos = array_filter($items, static fn($i) => $i['logo']); ?>
<?php View::partial('page-hero', ['title' => 'Referanslar', 'title_html' => 'Bize <em>güvenenler.</em>', 'crumb' => 'Referanslar', 'kicker' => 'Mezunlar & markalar', 'lead' => 'Ajanslardan markalara, freelance tasarımcılardan kurumsal ekiplere — eğitime katılanların deneyimleri.']); ?>
<?php if ($logos): ?>
<section class="section pt-0">
  <div class="container logo-wall">
    <?php foreach ($logos as $l): ?>
      <?php if ($l['website']): ?><a href="<?= e($l['website']) ?>" target="_blank" rel="noopener nofollow"><?php endif; ?>
      <img src="<?= e(media($l['logo'])) ?>" alt="<?= e($l['company'] ?: $l['name']) ?>" loading="lazy" height="48">
      <?php if ($l['website']): ?></a><?php endif; ?>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>
<section class="section">
  <div class="container">
    <div class="quotes masonry">
      <?php foreach ($items as $t) View::partial('testimonial', ['t' => $t]); ?>
    </div>
    <?php if (!$items): ?><p class="muted center">Henüz referans eklenmedi.</p><?php endif; ?>
  </div>
</section>
