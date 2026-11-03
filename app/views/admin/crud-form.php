<div class="adm-head">
  <h1 class="adm-title"><?= $row ? e($r['singular']) . ' düzenle' : 'Yeni ' . e(mb_strtolower($r['singular'])) ?></h1>
  <a href="<?= e(url('yonetim/' . $r['key'])) ?>" class="btn btn-ghost btn-sm">← Listeye dön</a>
</div>
<form method="post" enctype="multipart/form-data" class="form card">
  <?= csrf_field() ?>
  <?php foreach ($r['fields'] as $name => $f):
    $val = (string)($row[$name] ?? ($f['default'] ?? ''));
    App\View::partial('admin-field', ['name' => $name, 'label' => $f['label'], 'type' => $f['type'], 'value' => $val, 'help' => $f['help'] ?? null, 'options' => $f['options'] ?? [], 'required' => !empty($f['required'])]);
  endforeach; ?>
  <div class="form-actions"><button class="btn btn-primary">Kaydet</button></div>
</form>
