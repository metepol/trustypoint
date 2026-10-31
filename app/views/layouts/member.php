<?php
$u = App\Auth::user();
ob_start(); ?>
<section class="member-shell">
  <div class="container member-grid">
    <aside class="member-side">
      <div class="member-id">
        <span class="avatar"><?= e(mb_strtoupper(mb_substr($u['name'], 0, 1))) ?></span>
        <div><b><?= e($u['name']) ?></b><small><?= App\Access::hasCourse($u) ? '✓ Eğitime kayıtlı' : 'Ücretsiz üye' ?></small></div>
      </div>
      <nav class="side-nav" aria-label="Üye menüsü">
        <a href="<?= e(url('panel')) ?>" class="<?= trim(active('/panel/ders')) || App\Router::currentPath() === '/panel' ? 'is-active' : '' ?>">🎬 Derslerim</a>
        <a href="<?= e(url('panel/siparisler')) ?>" class="<?= trim(active('/panel/siparisler')) ?>">🧾 Siparişlerim</a>
        <a href="<?= e(url('panel/profil')) ?>" class="<?= trim(active('/panel/profil')) ?>">👤 Profilim</a>
        <?php if ($u['role'] === 'admin'): ?><a href="<?= e(url('yonetim')) ?>">⚙️ Yönetim paneli</a><?php endif; ?>
        <form method="post" action="<?= e(url('cikis')) ?>"><?= csrf_field() ?><button type="submit">↩ Çıkış yap</button></form>
      </nav>
    </aside>
    <div class="member-main"><?= $content ?></div>
  </div>
</section>
<?php
$shell = (string)ob_get_clean();
echo App\View::capture('layouts/site', ['content' => $shell, 'seo' => $seo ?? [], 'bodyClass' => 'is-member']);
