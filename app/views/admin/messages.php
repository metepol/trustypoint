<div class="adm-head"><h1 class="adm-title">📩 Mesajlar</h1></div>
<div class="card table-wrap">
  <table class="table">
    <thead><tr><th>Gönderen</th><th>Konu</th><th>Mesaj</th><th>Tarih</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $m): ?>
      <tr class="<?= $m['is_read'] ? '' : 'row-hl' ?>">
        <td><a href="<?= e(url('yonetim/mesajlar/' . $m['id'])) ?>"><b><?= e($m['name']) ?></b></a><br><small class="muted"><?= e($m['email']) ?></small></td>
        <td><?= e($m['subject']) ?></td>
        <td class="muted"><?= e(excerpt($m['body'], 90)) ?></td>
        <td class="muted"><?= e(tr_date($m['created_at'])) ?></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="4" class="muted">Mesaj yok.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
