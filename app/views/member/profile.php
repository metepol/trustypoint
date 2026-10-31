<header class="member-head"><h1 class="h2">Profilim</h1></header>
<form method="post" class="form glass-card">
  <?= csrf_field() ?>
  <div class="grid-2">
    <div class="field"><label for="p-name">Ad Soyad</label><input id="p-name" name="name" required value="<?= e($user['name']) ?>"></div>
    <div class="field"><label for="p-email">E-posta</label><input id="p-email" type="email" name="email" required value="<?= e($user['email']) ?>"></div>
    <div class="field"><label for="p-phone">Telefon</label><input id="p-phone" name="phone" value="<?= e($user['phone']) ?>"></div>
  </div>
  <h2 class="h3 mt-2">Şifre değiştir <small class="muted">(opsiyonel)</small></h2>
  <div class="grid-2">
    <div class="field"><label for="p-cur">Mevcut şifre</label><input id="p-cur" type="password" name="current_password" autocomplete="current-password"></div>
    <div class="field"><label for="p-new">Yeni şifre</label><input id="p-new" type="password" name="new_password" minlength="8" autocomplete="new-password"></div>
  </div>
  <button class="btn btn-primary">Kaydet</button>
</form>
<p class="tiny muted mt-2">Üyelik tarihi: <?= e(tr_date($user['created_at'], false)) ?><?= $user['enrolled_at'] ? ' · Eğitim kaydı: ' . e(tr_date($user['enrolled_at'], false)) : '' ?></p>
