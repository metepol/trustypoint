<section class="auth-wrap">
  <div class="auth-card">
    <span class="kicker">Şifre sıfırlama</span>
    <h1 class="h2">Şifreni mi <em>unuttun?</em></h1>
    <p class="muted">E-posta adresini yaz, sıfırlama bağlantısını gönderelim.</p>
    <form method="post" class="form">
      <?= csrf_field() ?>
      <div class="field"><label for="f-email">E-posta</label><input id="f-email" type="email" name="email" required autofocus autocomplete="email"></div>
      <button class="btn btn-primary btn-lg btn-block">Bağlantı gönder</button>
    </form>
    <p class="center muted mt-2"><a href="<?= e(url('giris')) ?>">← Girişe dön</a></p>
  </div>
</section>
