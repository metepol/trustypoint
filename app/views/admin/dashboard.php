<?php use App\Access; $labels = ['pending' => 'Bekliyor', 'paid' => 'Ödendi', 'cancelled' => 'İptal']; ?>
<h1 class="adm-title">Genel bakış</h1>
<div class="kpis">
  <div class="kpi"><span>Toplam üye</span><b><?= $stats['members'] ?></b></div>
  <div class="kpi"><span>Eğitime kayıtlı</span><b><?= $stats['enrolled'] ?></b></div>
  <div class="kpi"><span>Toplam gelir</span><b><?= e(money($stats['revenue'])) ?></b></div>
  <a class="kpi <?= $stats['pending'] ? 'warn' : '' ?>" href="<?= e(url('yonetim/siparisler?durum=pending')) ?>"><span>Onay bekleyen havale</span><b><?= $stats['pending'] ?></b></a>
  <a class="kpi" href="<?= e(url('yonetim/mesajlar')) ?>"><span>Okunmamış mesaj</span><b><?= $stats['messages'] ?></b></a>
</div>

<div class="adm-grid-2">
  <div class="card">
    <h2 class="card-title">Sıradaki canlı yayın</h2>
    <?php if ($next): ?>
      <p><b><?= e($next['title']) ?></b><br><span class="muted"><?= e(tr_date($next['scheduled_at'])) ?> · <?= e(Access::label(Access::state($next))) ?></span></p>
      <?php if (!trim((string)$next['live_embed'])): ?><p class="alert alert-info">Bu ders için henüz canlı yayın bağlantısı girilmedi.</p><?php endif; ?>
      <a class="btn btn-sm btn-outline" href="<?= e(url('yonetim/dersler/' . $next['id'])) ?>">Dersi düzenle</a>
    <?php else: ?>
      <p class="muted">Planlanmış ders yok. <a href="<?= e(url('yonetim/dersler/yeni')) ?>">Ders ekle →</a></p>
    <?php endif; ?>
  </div>
  <div class="card">
    <h2 class="card-title">Entegrasyon durumu</h2>
    <ul class="status-list">
      <?php foreach ($checks as $label => $ok): ?><li><span class="sdot <?= $ok ? 'on' : '' ?>"></span><?= e($label) ?> <em><?= $ok ? 'Aktif' : 'Kapalı' ?></em></li><?php endforeach; ?>
    </ul>
  </div>
</div>

<div class="adm-grid-2">
  <div class="card">
    <h2 class="card-title">Son siparişler <a href="<?= e(url('yonetim/siparisler')) ?>">Tümü →</a></h2>
    <table class="table compact"><tbody>
      <?php foreach ($orders as $o): ?><tr><td>#<?= (int)$o['id'] ?></td><td><?= e($o['name']) ?></td><td><?= e(money($o['amount'], $o['currency'])) ?></td><td><span class="pill pill-<?= e($o['status']) ?>"><?= e($labels[$o['status']] ?? '') ?></span></td></tr><?php endforeach; ?>
      <?php if (!$orders): ?><tr><td class="muted">Henüz sipariş yok.</td></tr><?php endif; ?>
    </tbody></table>
  </div>
  <div class="card">
    <h2 class="card-title">Son üyeler <a href="<?= e(url('yonetim/uyeler')) ?>">Tümü →</a></h2>
    <table class="table compact"><tbody>
      <?php foreach ($users as $u): ?><tr><td><a href="<?= e(url('yonetim/uyeler/' . $u['id'])) ?>"><?= e($u['name']) ?></a></td><td class="muted"><?= e($u['email']) ?></td><td><?= $u['enrolled_at'] ? '<span class="pill pill-paid">Kayıtlı</span>' : '' ?></td></tr><?php endforeach; ?>
    </tbody></table>
  </div>
</div>
