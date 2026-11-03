<?php use App\Access; ?>
<div class="adm-head">
  <h1 class="adm-title"><?= e($r['title']) ?></h1>
  <a href="<?= e(url('yonetim/' . $r['key'] . '/yeni')) ?>" class="btn btn-primary btn-sm">+ <?= e($r['singular']) ?> ekle</a>
</div>
<?php if (!empty($r['intro'])): ?><p class="muted"><?= e($r['intro']) ?></p><?php endif; ?>
<div class="card table-wrap">
  <table class="table">
    <thead><tr><?php foreach ($r['list'] as $label): ?><th><?= e($label) ?></th><?php endforeach; ?><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $row): ?>
      <tr>
        <?php foreach ($r['list'] as $col => $label): ?>
          <td>
            <?php if ($col === '_state'): $s = Access::state($row); ?>
              <span class="pill pill-<?= e($s) ?>"><?= e(Access::label($s)) ?></span>
            <?php elseif (in_array($col, ['image', 'logo'], true)): ?>
              <?php if ($row[$col]): ?><img src="<?= e(media($row[$col])) ?>" alt="" class="thumb"><?php endif; ?>
            <?php elseif (in_array($col, ['is_published', 'show_footer'], true)): ?>
              <?= $row[$col] ? '✓' : '—' ?>
            <?php elseif ($col === 'scheduled_at'): ?>
              <?= e(tr_date($row[$col])) ?>
            <?php else: ?>
              <?= e(excerpt((string)$row[$col], 80)) ?>
            <?php endif; ?>
          </td>
        <?php endforeach; ?>
        <td class="actions">
          <?php if ($r['table'] === 'pages'): ?><a href="<?= e(url($row['slug'] === 'hakkimizda' ? 'hakkimizda' : 'sayfa/' . $row['slug'])) ?>" target="_blank" class="btn btn-ghost btn-sm">Gör ↗</a><?php endif; ?>
          <a href="<?= e(url('yonetim/' . $r['key'] . '/' . $row['id'])) ?>" class="btn btn-outline btn-sm">Düzenle</a>
          <form method="post" action="<?= e(url('yonetim/' . $r['key'] . '/' . $row['id'] . '/sil')) ?>" data-confirm="Silmek istediğinize emin misiniz?"><?= csrf_field() ?><button class="btn btn-danger btn-sm">Sil</button></form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="<?= count($r['list']) + 1 ?>" class="muted">Kayıt yok.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
