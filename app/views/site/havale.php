<?php
$terms = '<a href="' . e(url('sayfa/mesafeli-satis-sozlesmesi')) . '" target="_blank">Mesafeli satış sözleşmesini</a> okudum, kabul ediyorum.';
?>
<section class="section">
  <div class="container checkout">
    <div>
      <a href="<?= e(url('odeme')) ?>" class="link-arrow">← Ödeme yöntemleri</a>
      <h1 class="h1 mt-1">Havale / <em>EFT</em></h1>
      <p class="muted">Aşağıdaki hesaba <b><?= e(money($order['amount'], $order['currency'])) ?></b> gönder, ardından formu doldurarak bize bildir.</p>

      <div class="bank glass-card mt-2">
        <div class="bank-row"><span>Banka</span><b><?= e(setting('havale_bank')) ?></b></div>
        <div class="bank-row"><span>Alıcı</span><b><?= e(setting('havale_holder')) ?></b></div>
        <div class="bank-row"><span>IBAN</span><b class="mono" id="iban"><?= e(setting('havale_iban')) ?></b><button type="button" class="btn btn-ghost btn-sm" data-copy="#iban">Kopyala</button></div>
        <div class="bank-row"><span>Tutar</span><b><?= e(money($order['amount'], $order['currency'])) ?></b></div>
        <div class="bank-row hl"><span>Açıklama</span><b class="mono" id="ref">#<?= (int)$order['id'] ?> <?= e($user['email']) ?></b><button type="button" class="btn btn-ghost btn-sm" data-copy="#ref">Kopyala</button></div>
        <?php if ($info = setting('havale_info')): ?><p class="tiny muted mt-1"><?= e($info) ?></p><?php endif; ?>
      </div>

      <form method="post" class="form glass-card mt-2">
        <?= csrf_field() ?>
        <h2 class="h3">Ödemeyi yaptım, bildir</h2>
        <div class="field"><label for="h-sender">Gönderen ad soyad / firma</label><input id="h-sender" name="sender" required value="<?= e($order['havale_sender'] ?? $user['name']) ?>"></div>
        <div class="field"><label for="h-note">Not <small>(opsiyonel: banka, tarih, saat)</small></label><input id="h-note" name="note" value="<?= e($order['havale_note'] ?? '') ?>"></div>
        <label class="check"><input type="checkbox" name="terms" required> <span><?= $terms ?></span></label>
        <button class="btn btn-primary btn-lg btn-block mt-1">Havale bildirimini gönder</button>
      </form>
    </div>
    <aside class="summary glass-card">
      <h2 class="h3">Sipariş #<?= (int)$order['id'] ?></h2>
      <div class="sum-row"><span><?= e(setting('course_title')) ?></span></div>
      <div class="sum-row total"><span>Toplam</span><b><?= e(money($order['amount'], $order['currency'])) ?></b></div>
      <p class="tiny muted mt-2">Havale onayı genellikle mesai saatleri içinde 1-3 saat sürer. Onaylandığında e-posta alırsın.</p>
    </aside>
  </div>
</section>
