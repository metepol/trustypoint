<section class="auth-wrap">
  <div class="auth-card">
    <span class="kicker">Üye girişi</span>
    <h1 class="h2">Tekrar <em>hoş geldin</em></h1>
    <form method="post" action="<?= e(url('giris')) ?>" class="form">
      <?= csrf_field() ?>
      <input type="hidden" name="next" value="<?= e($next) ?>">
      <div class="field"><label for="l-email">E-posta</label><input id="l-email" type="email" name="email" required autofocus autocomplete="email" value="<?= e(old('email')) ?>"></div>
      <div class="field"><label for="l-pass">Şifre <a href="<?= e(url('sifremi-unuttum')) ?>" class="field-link">Unuttum</a></label>
        <div class="pass-wrap"><input id="l-pass" type="password" name="password" required autocomplete="current-password"><button type="button" class="pass-toggle" data-toggle-pass aria-label="Şifreyi göster">👁</button></div></div>
      <button class="btn btn-primary btn-lg btn-block">Giriş yap</button>
    </form>
    <div class="divider"><span>veya şifresiz</span></div>
    <form method="post" action="<?= e(url('giris/baglanti')) ?>" class="form inline-form">
      <?= csrf_field() ?>
      <input type="email" name="email" placeholder="E-posta adresin" required aria-label="E-posta adresi">
      <button class="btn btn-outline">Giriş linki gönder</button>
    </form>
    <p class="center muted mt-2">Hesabın yok mu? <a href="<?= e(url('kayit' . ($next ? '?next=' . rawurlencode($next) : ''))) ?>">10 saniyede üye ol →</a></p>
  </div>
</section>
