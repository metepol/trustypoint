<figure class="quote-card reveal">
  <blockquote>“<?= e($t['quote']) ?>”</blockquote>
  <figcaption>
    <?php if ($t['logo']): ?>
      <img src="<?= e(media($t['logo'])) ?>" alt="<?= e($t['company'] ?: $t['name']) ?>" width="44" height="44" loading="lazy">
    <?php else: ?>
      <span class="q-avatar" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($t['name'], 0, 1))) ?></span>
    <?php endif; ?>
    <span><b><?= e($t['name']) ?></b><small><?= e(trim($t['position'] . ($t['position'] && $t['company'] ? ' · ' : '') . $t['company'])) ?></small></span>
  </figcaption>
</figure>
