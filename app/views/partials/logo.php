<?php $logo = (string)setting('site_logo', ''); $name = (string)setting('site_name', 'Akademi'); ?>
<a href="<?= e(url('/')) ?>" class="logo" aria-label="<?= e($name) ?> ana sayfa">
  <?php if ($logo): ?>
    <img src="<?= e(media($logo)) ?>" alt="<?= e($name) ?>" height="36">
  <?php else: ?>
    <span class="logo-mark" aria-hidden="true">▲</span><span class="logo-text"><?= e($name) ?></span>
  <?php endif; ?>
</a>
