<?php
use App\Auth;
$u = Auth::user();
$nav = [
    '/'            => 'Ana Sayfa',
    '/hakkimizda'  => 'Hakkımızda',
    '/egitim'      => 'Eğitim',
    '/ornekler'    => 'Örnekler',
    '/referanslar' => 'Referanslar',
    '/iletisim'    => 'İletişim',
];
$announcement = trim((string)setting('announcement', ''));
?>
<?php if ($announcement !== ''): ?>
<div class="announce"><a href="<?= e(url('egitim')) ?>"><?= e($announcement) ?> <span aria-hidden="true">→</span></a></div>
<?php endif; ?>
<header class="site-header" id="top">
  <div class="container header-inner">
    <?php App\View::partial('logo'); ?>
    <nav class="main-nav" id="main-nav" aria-label="Ana menü">
      <ul>
        <?php foreach ($nav as $path => $label): ?>
          <li><a href="<?= e(url($path)) ?>" class="nav-link<?= active($path) ?>"<?= active($path) ? ' aria-current="page"' : '' ?>><?= e($label) ?></a></li>
        <?php endforeach; ?>
      </ul>
      <div class="nav-actions">
        <?php if ($u): ?>
          <?php if ($u['role'] === 'admin'): ?><a href="<?= e(url('yonetim')) ?>" class="btn btn-ghost btn-sm">Yönetim</a><?php endif; ?>
          <a href="<?= e(url('panel')) ?>" class="btn btn-primary btn-sm"><span class="avatar-sm"><?= e(mb_strtoupper(mb_substr($u['name'], 0, 1))) ?></span> Üye Paneli</a>
        <?php else: ?>
          <a href="<?= e(url('giris')) ?>" class="btn btn-ghost btn-sm">Üye Girişi</a>
          <a href="<?= e(url('kayit')) ?>" class="btn btn-primary btn-sm">Ücretsiz Üye Ol</a>
        <?php endif; ?>
      </div>
    </nav>
    <button class="burger" type="button" aria-controls="main-nav" aria-expanded="false" aria-label="Menüyü aç"><span></span><span></span></button>
  </div>
</header>
