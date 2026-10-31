<?php $id = 'f-' . $name; $req = !empty($required) ? ' required' : ''; ?>
<?php if ($type === 'heading'): ?>
  <h3 class="form-h"><?= e($label) ?></h3>
<?php elseif ($type === 'checkbox'): ?>
  <label class="check switch"><input type="checkbox" name="<?= e($name) ?>" value="1"<?= $value === '1' ? ' checked' : '' ?>> <span><?= e($label) ?></span></label>
  <?php if ($help): ?><p class="help"><?= e($help) ?></p><?php endif; ?>
<?php else: ?>
<div class="field">
  <label for="<?= e($id) ?>"><?= e($label) ?></label>
  <?php switch ($type):
    case 'textarea': case 'embed': ?>
      <textarea id="<?= e($id) ?>" name="<?= e($name) ?>" rows="<?= $type === 'embed' ? 3 : 4 ?>"<?= $req ?>><?= e($value) ?></textarea>
      <?php break;
    case 'html': case 'code': ?>
      <textarea id="<?= e($id) ?>" name="<?= e($name) ?>" rows="<?= $type === 'html' ? 12 : 6 ?>" class="mono"<?= $req ?>><?= e($value) ?></textarea>
      <?php if ($type === 'html'): ?><p class="help">HTML kullanabilirsiniz: &lt;h2&gt;, &lt;p&gt;, &lt;ul&gt;&lt;li&gt;, &lt;strong&gt;, &lt;a href=""&gt;, &lt;img src=""&gt;</p><?php endif; ?>
      <?php break;
    case 'datetime': ?>
      <input id="<?= e($id) ?>" type="datetime-local" name="<?= e($name) ?>" value="<?= $value ? e(date('Y-m-d\TH:i', strtotime($value))) : '' ?>"<?= $req ?>>
      <?php break;
    case 'number': ?>
      <input id="<?= e($id) ?>" type="number" name="<?= e($name) ?>" value="<?= e($value) ?>"<?= $req ?>>
      <?php break;
    case 'password': ?>
      <input id="<?= e($id) ?>" type="password" name="<?= e($name) ?>" value="" autocomplete="new-password" placeholder="<?= $value !== '' ? '•••••••• (kayıtlı — değiştirmek için yazın)' : '' ?>">
      <?php break;
    case 'select': ?>
      <select id="<?= e($id) ?>" name="<?= e($name) ?>"><?php foreach ($options as $k => $l): ?><option value="<?= e($k) ?>"<?= (string)$k === $value ? ' selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select>
      <?php break;
    case 'image': ?>
      <div class="img-field">
        <?php if ($value): ?><img src="<?= e(media($value)) ?>" alt="" class="thumb-lg"><label class="check"><input type="checkbox" name="<?= e($name) ?>_remove"> Kaldır</label><?php endif; ?>
        <input id="<?= e($id) ?>" type="file" name="<?= e($name) ?>" accept="image/*">
        <input type="url" name="<?= e($name) ?>_url" placeholder="veya görsel URL'si (https://...)">
      </div>
      <?php break;
    default: ?>
      <input id="<?= e($id) ?>" type="<?= $type === 'url' ? 'url' : 'text' ?>" name="<?= e($name) ?>" value="<?= e($value) ?>"<?= $req ?>>
  <?php endswitch; ?>
  <?php if ($help): ?><p class="help"><?= e($help) ?></p><?php endif; ?>
</div>
<?php endif; ?>
