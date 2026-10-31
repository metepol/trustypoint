<section class="auth-wrap">
  <div class="auth-card">
    <span class="kicker">Yeni şifre</span>
    <h1 class="h2">Yeni şifreni <em>belirle</em></h1>
    <form method="post" class="form">
      <?= csrf_field() ?>
      <div class="field"><label for="p-new">Yeni şifre (en az 8 karakter)</label>
        <div class="pass-wrap"><input id="p-new" type="password" name="password" required minlength="8" autofocus autocomplete="new-password"><button type="button" class="pass-toggle" data-toggle-pass aria-label="Şifreyi göster">👁</button></div></div>
      <button class="btn btn-primary btn-lg btn-block">Şifreyi kaydet</button>
    </form>
  </div>
</section>
