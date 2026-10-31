<?php $labels = ['pending' => 'Bekliyor', 'paid' => 'Ödendi', 'cancelled' => 'İptal']; ?>
<div class="adm-head"><h1 class="adm-title">💰 Siparişler</h1></div>
<nav class="tabs">
  <?php foreach (['' => 'Tümü', 'pending' => 'Bekleyen', 'paid' => 'Ödenen', 'cancelled' => 'İptal'] as $k => $l): ?>
    <a href="<?= e(url('yonetim/siparisler' . ($k ? '?durum=' . $k : ''))) ?>" class="<?= $status === $k ? 'is-active' : '' ?>"><?= e($l) ?></a>
  <?php endforeach; ?>
</nav>
<div class="card table-wrap">
  <table class="table">
    <thead><tr><th>No</th><th>Üye</th><th>Yöntem</th><th>Tutar</th><th>Tarih</th><th>Durum</th><th>İşlem</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $o): $needs = $o['status'] === 'pending' && $o['method'] === 'havale' && $o['havale_sender']; ?>
      <tr class="<?= $needs ? 'row-hl' : '' ?>">
        <td>#<?= (int)$o['id'] ?></td>
        <td><a href="<?= e(url('yonetim/uyeler/' . $o['user_id'])) ?>"><?= e($o['name']) ?></a><br><small class="muted"><?= e($o['email']) ?></small></td>
        <td><?= $o['method'] === 'stripe' ? '💳 Stripe' : '🏦 Havale' ?>
          <?php if ($o['havale_sender']): ?><br><small>Gönderen: <b><?= e($o['havale_sender']) ?></b></small><?php endif; ?>
          <?php if ($o['havale_note']): ?><br><small class="muted"><?= e($o['havale_note']) ?></small><?php endif; ?>
          <?php if ($o['method'] === 'havale' && !$o['havale_sender'] && $o['status'] === 'pending'): ?><br><small class="muted">Bildirim yapılmadı</small><?php endif; ?>
        </td>
        <td><b><?= e(money($o['amount'], $o['currency'])) ?></b></td>
        <td class="muted"><?= e(tr_date($o['created_at'])) ?><?php if ($o['paid_at']): ?><br><small>Ödeme: <?= e(tr_date($o['paid_at'])) ?></small><?php endif; ?></td>
        <td><span class="pill pill-<?= e($o['status']) ?>"><?= e($labels[$o['status']] ?? $o['status']) ?></span></td>
        <td class="actions">
          <?php if ($o['status'] !== 'paid'): ?>
            <form method="post" action="<?= e(url('yonetim/siparisler/' . $o['id'])) ?>" data-confirm="Ödeme onaylansın ve erişim açılsın mı?"><?= csrf_field() ?><input type="hidden" name="action" value="approve"><button class="btn btn-primary btn-sm">Onayla</button></form>
          <?php endif; ?>
          <?php if ($o['status'] === 'pending'): ?>
            <form method="post" action="<?= e(url('yonetim/siparisler/' . $o['id'])) ?>" data-confirm="Sipariş iptal edilsin mi?"><?= csrf_field() ?><input type="hidden" name="action" value="cancel"><button class="btn btn-danger btn-sm">İptal</button></form>
          <?php endif; ?>
          <details class="note-pop"><summary class="btn btn-ghost btn-sm">Not</summary>
            <form method="post" action="<?= e(url('yonetim/siparisler/' . $o['id'])) ?>"><?= csrf_field() ?><input type="hidden" name="action" value="note"><textarea name="admin_note" rows="2"><?= e($o['admin_note']) ?></textarea><button class="btn btn-outline btn-sm">Kaydet</button></form>
          </details>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="7" class="muted">Sipariş yok.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
