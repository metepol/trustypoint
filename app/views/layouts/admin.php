<?php
use App\Controllers\AdminController;
use App\Database;
$unread = (int)Database::value('SELECT COUNT(*) FROM messages WHERE is_read = 0');
$pending = (int)Database::value("SELECT COUNT(*) FROM orders WHERE status = 'pending' AND method = 'havale' AND havale_sender IS NOT NULL");
$menu = [
    ['yonetim', 'chart', 'Genel bakış', null],
    ['yonetim/siparisler', 'wallet', 'Siparişler', $pending],
    ['yonetim/uyeler', 'users', 'Üyeler', null],
];
foreach (AdminController::resources() as $k => $r) {
    $menu[] = ['yonetim/' . $k, $r['icon'], $r['title'], null];
}
$menu[] = ['yonetim/mesajlar', 'mail', 'Mesajlar', $unread];
$current = App\Router::currentPath();
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Yönetim · <?= e(setting('site_name')) ?></title>
    <link rel="icon" href="<?= e(asset('assets/img/favicon.svg')) ?>">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=JetBrains+Mono:wght@500&display=swap">
    <link rel="stylesheet" href="<?= e(asset('assets/css/style.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('assets/css/admin.css')) ?>">
</head>
<body class="admin">
<div class="adm">
  <aside class="adm-side" id="adm-side">
    <a href="<?= e(url('yonetim')) ?>" class="logo"><span class="logo-mark">▲</span><span class="logo-text">Yönetim</span></a>
    <nav class="adm-nav">
      <?php foreach ($menu as [$path, $icon, $label, $badge]):
        $is = $path === 'yonetim' ? $current === '/yonetim' : str_starts_with($current, '/' . $path); ?>
        <a href="<?= e(url($path)) ?>" class="<?= $is ? 'is-active' : '' ?>"><span><?= icon($icon, 18) ?></span><?= e($label) ?><?php if ($badge): ?><em class="badge"><?= (int)$badge ?></em><?php endif; ?></a>
      <?php endforeach; ?>
      <div class="adm-sep">Ayarlar</div>
      <?php foreach (AdminController::settingTabs() as $k => $t): ?>
        <a href="<?= e(url('yonetim/ayarlar/' . $k)) ?>" class="<?= ($current === '/yonetim/ayarlar/' . $k || ($k === 'genel' && $current === '/yonetim/ayarlar')) ? 'is-active' : '' ?>"><span><?= icon($t['icon'], 18) ?></span><?= e($t['title']) ?></a>
      <?php endforeach; ?>
    </nav>
    <div class="adm-foot">
      <a href="<?= e(url('/')) ?>" target="_blank">↗ Siteyi görüntüle</a>
      <form method="post" action="<?= e(url('cikis')) ?>"><?= csrf_field() ?><button>↩ Çıkış</button></form>
    </div>
  </aside>
  <div class="adm-main">
    <header class="adm-top">
      <button class="burger" type="button" data-adm-toggle aria-label="Menü"><span></span><span></span></button>
      <span class="muted">Hoş geldin, <b><?= e($admin['name']) ?></b></span>
    </header>
    <?php App\View::partial('flash'); ?>
    <div class="adm-content"><?= $content ?></div>
  </div>
</div>
<script src="<?= e(asset('assets/js/app.js')) ?>" defer></script>
</body>
</html>
