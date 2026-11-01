<section class="auth-wrap">
  <div class="auth-card">
    <span class="kicker">Ücretsiz üyelik</span>
    <h1 class="h2">Hesabını <em>oluştur</em></h1>
    <p class="muted">Sadece 3 bilgi. Kart bilgisi istemiyoruz.</p>
    <form method="post" action="<?= e(url('kayit')) ?>" class="form">
      <?= csrf_field() ?>
      <input type="hidden" name="next" value="<?= e($next) ?>">
      <div class="field"><label for="r-name">Ad Soyad</label><input id="r-name" name="name" required autofocus autocomplete="name" value="<?= e(old('name')) ?>"></div>
      <div class="field"><label for="r-email">E-posta</label><input id="r-email" type="email" name="email" required autocomplete="email" value="<?= e(old('email')) ?>"></div>
      <div class="field"><label for="r-pass">Şifre <small>(en az 8 karakter)</small></label>
        <div class="pass-wrap"><input id="r-pass" type="password" name="password" required minlength="8" autocomplete="new-password"><button type="button" class="pass-toggle" data-toggle-pass aria-label="Şifreyi göster"><?= icon('eye', 18) ?></button></div></div>
      <label class="check"><input type="checkbox" name="terms" required> <span><a href="<?= e(url('sayfa/gizlilik-politikasi')) ?>" target="_blank">KVKK aydınlatma metnini</a> okudum ve üyelik koşullarını kabul ediyorum.</span></label>
      <button class="btn btn-primary btn-lg btn-block mt-1">Üye ol →</button>
    </form>
    <p class="center muted mt-2">Zaten üye misin? <a href="<?= e(url('giris' . ($next ? '?next=' . rawurlencode($next) : ''))) ?>">Giriş yap</a></p>
  </div>
</section>
