<article class="work-card reveal">
  <div class="work-media">
    <?php if ($item['image']): ?>
      <img src="<?= e(media($item['image'])) ?>" alt="<?= e($item['title']) ?>" loading="lazy" width="640" height="420">
    <?php else: ?>
      <div class="work-placeholder" style="--h: <?= (205 + crc32($item['title']) % 30) ?>" aria-hidden="true"><span><?= e(mb_substr($item['title'], 0, 1)) ?></span></div>
    <?php endif; ?>
    <?php if ($item['category']): ?><span class="work-tag"><?= e($item['category']) ?></span><?php endif; ?>
  </div>
  <div class="work-body">
    <h3><?= e($item['title']) ?></h3>
    <p class="muted"><?= e($item['description']) ?></p>
    <div class="work-links">
      <?php if ($item['demo_url']): ?><a href="<?= e($item['demo_url']) ?>" target="_blank" rel="noopener" class="link-arrow">Canlı demo ↗</a><?php endif; ?>
      <?php if ($item['video_url']): ?><a href="<?= e($item['video_url']) ?>" target="_blank" rel="noopener" class="link-arrow">Videoyu izle ↗</a><?php endif; ?>
    </div>
  </div>
</article>
