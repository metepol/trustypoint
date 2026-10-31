<?php $labels = ['pending' => 'Bekliyor', 'paid' => 'Ödendi', 'cancelled' => 'İptal']; ?>
<header class="member-head"><h1 class="h2">Siparişlerim</h1></header>
<?php if (!$orders): ?>
  <p class="muted">Henüz siparişiniz yok. <a href="<?= e(url('egitim')) ?>">Eğitimi inceleyin →</a></p>
<?php else: ?>
<div class="table-wrap">
  <table class="table">
    <thead><tr><th>No</th><th>Tarih</th><th>Yöntem</th><th>Tutar</th><th>Durum</th></tr></thead>
    <tbody>
    <?php foreach ($orders as $o): ?>
      <tr>
        <td>#<?= (int)$o['id'] ?></td>
        <td><?= e(tr_date($o['created_at'])) ?></td>
        <td><?= $o['method'] === 'stripe' ? 'Kart' : 'Havale/EFT' ?></td>
        <td><?= e(money($o['amount'], $o['currency'])) ?></td>
        <td><span class="pill pill-<?= e($o['status']) ?>"><?= e($labels[$o['status']] ?? $o['status']) ?></span>
          <?php if ($o['status'] === 'pending' && $o['method'] === 'havale' && !$o['havale_sender']): ?> <a href="<?= e(url('odeme/havale')) ?>" class="tiny">Bildirim yap →</a><?php endif; ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>
