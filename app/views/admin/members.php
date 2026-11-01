<div class="adm-head">
  <h1 class="adm-title">Üyeler</h1>
  <a href="<?= e(url('yonetim/uyeler/disa-aktar')) ?>" class="btn btn-ghost btn-sm">CSV indir</a>
</div>
<form class="filters" method="get">
  <input name="q" value="<?= e($q) ?>" placeholder="Ad, e-posta veya telefon ara">
  <select name="filtre">
    <option value="">Tümü</option>
    <option value="kayitli"<?= $filter === 'kayitli' ? ' selected' : '' ?>>Eğitime kayıtlı</option>
    <option value="kayitsiz"<?= $filter === 'kayitsiz' ? ' selected' : '' ?>>Kayıtsız</option>
  </select>
  <button class="btn btn-outline btn-sm">Filtrele</button>
</form>
<div class="card table-wrap">
  <table class="table">
    <thead><tr><th>Ad</th><th>E-posta</th><th>Üyelik</th><th>Eğitim</th><th>Son giriş</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $u): ?>
      <tr>
        <td><b><?= e($u['name']) ?></b><?= $u['role'] === 'admin' ? ' <span class="pill">Yönetici</span>' : '' ?></td>
        <td><?= e($u['email']) ?></td>
        <td class="muted"><?= e(tr_date($u['created_at'], false)) ?></td>
        <td><?= $u['enrolled_at'] ? '<span class="pill pill-paid">Kayıtlı</span>' : '<span class="pill">—</span>' ?></td>
        <td class="muted"><?= e(tr_date($u['last_login_at'])) ?></td>
        <td class="actions"><a class="btn btn-outline btn-sm" href="<?= e(url('yonetim/uyeler/' . $u['id'])) ?>">Detay</a></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="6" class="muted">Üye bulunamadı.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
