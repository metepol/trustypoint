<?php $labels = ['pending' => 'Bekliyor', 'paid' => 'Ödendi', 'cancelled' => 'İptal']; ?>
<div class="adm-head">
  <h1 class="adm-title"><?= e($user['name']) ?></h1>
  <a href="<?= e(url('yonetim/uyeler')) ?>" class="btn btn-ghost btn-sm">← Üyeler</a>
</div>
<div class="adm-grid-2">
  <div class="card">
    <h2 class="card-title">Bilgiler</h2>
    <dl class="dl">
      <dt>E-posta</dt><dd><a href="mailto:<?= e($user['email']) ?>"><?= e($user['email']) ?></a></dd>
      <dt>Telefon</dt><dd><?= e($user['phone'] ?: '—') ?></dd>
      <dt>Yetki</dt><dd><?= $user['role'] === 'admin' ? 'Yönetici' : 'Üye' ?></dd>
      <dt>Üyelik</dt><dd><?= e(tr_date($user['created_at'])) ?></dd>
      <dt>Son giriş</dt><dd><?= e(tr_date($user['last_login_at'])) ?></dd>
      <dt>Eğitim erişimi</dt><dd><?= $user['enrolled_at'] ? '<span class="pill pill-paid">Açık · ' . e(tr_date($user['enrolled_at'], false)) . '</span>' : '<span class="pill pill-cancelled">Kapalı</span>' ?></dd>
    </dl>
  </div>
  <div class="card">
    <h2 class="card-title">İşlemler</h2>
    <div class="btn-row">
      <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="<?= $user['enrolled_at'] ? 'revoke' : 'grant' ?>"><button class="btn btn-sm <?= $user['enrolled_at'] ? 'btn-danger' : 'btn-primary' ?>"><?= $user['enrolled_at'] ? 'Erişimi kaldır' : 'Ücretsiz erişim tanımla' ?></button></form>
      <form method="post" data-confirm="Yetki değiştirilsin mi?"><?= csrf_field() ?><input type="hidden" name="action" value="role"><button class="btn btn-outline btn-sm"><?= $user['role'] === 'admin' ? 'Üye yap' : 'Yönetici yap' ?></button></form>
      <form method="post" data-confirm="Üye ve tüm siparişleri silinecek. Emin misiniz?"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><button class="btn btn-danger btn-sm">Üyeyi sil</button></form>
    </div>
    <form method="post" class="inline-form mt-2"><?= csrf_field() ?><input type="hidden" name="action" value="password"><input type="text" name="password" placeholder="Yeni şifre (min. 8)" minlength="8"><button class="btn btn-outline btn-sm">Şifre ata</button></form>
  </div>
</div>
<div class="card">
  <h2 class="card-title">Siparişler</h2>
  <table class="table compact"><tbody>
  <?php foreach ($orders as $o): ?>
    <tr><td>#<?= (int)$o['id'] ?></td><td><?= e(tr_date($o['created_at'])) ?></td><td><?= $o['method'] === 'stripe' ? 'Kart' : 'Havale' ?></td><td><?= e(money($o['amount'], $o['currency'])) ?></td><td><span class="pill pill-<?= e($o['status']) ?>"><?= e($labels[$o['status']] ?? '') ?></span></td></tr>
  <?php endforeach; ?>
  <?php if (!$orders): ?><tr><td class="muted">Sipariş yok.</td></tr><?php endif; ?>
  </tbody></table>
</div>
