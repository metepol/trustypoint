<?php
use App\Payments;
$old = trim((string)setting('course_old_price', ''));
$terms = '<a href="' . e(url('sayfa/mesafeli-satis-sozlesmesi')) . '" target="_blank">Mesafeli satış sözleşmesini</a> ve <a href="' . e(url('sayfa/iade-politikasi')) . '" target="_blank">iade koşullarını</a> okudum, kabul ediyorum.';
?>
<section class="section">
  <div class="container checkout">
    <div>
      <span class="kicker">Adım 2 / 2 · Ödeme</span>
      <h1 class="h1">Kaydını <em>tamamla</em></h1>
      <p class="muted">Merhaba <?= e($user['name']) ?>, ödeme yöntemini seç. Ödemen onaylandığı an derslerin üye panelinde açılır.</p>

      <?php if (!$stripe && !$havale): ?>
        <div class="alert alert-error mt-2">Şu anda ödeme alınamıyor. Lütfen <a href="<?= e(url('iletisim')) ?>">bizimle iletişime geçin</a>.</div>
      <?php endif; ?>

      <div class="pay-options mt-2">
        <?php if ($stripe): ?>
        <form method="post" action="<?= e(url('odeme/stripe')) ?>" class="pay-option glass-card">
          <?= csrf_field() ?>
          <div class="pay-head"><span class="pay-icon">💳</span><div><h2 class="h3">Kredi / Banka Kartı</h2><p class="muted tiny">Stripe güvenli ödeme · Anında erişim</p></div></div>
          <label class="check"><input type="checkbox" name="terms" required> <span><?= $terms ?></span></label>
          <button class="btn btn-primary btn-lg btn-block">Kartla öde · <?= e(money(Payments::price())) ?></button>
        </form>
        <?php endif; ?>
        <?php if ($havale): ?>
        <div class="pay-option glass-card">
          <div class="pay-head"><span class="pay-icon">🏦</span><div><h2 class="h3">Havale / EFT</h2><p class="muted tiny">Banka hesabımıza gönder · Onay sonrası erişim</p></div></div>
          <p class="muted">IBAN bilgilerini ve sipariş numaranı bir sonraki adımda göreceksin.</p>
          <a href="<?= e(url('odeme/havale')) ?>" class="btn btn-outline btn-lg btn-block">Havale ile öde →</a>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <aside class="summary glass-card">
      <h2 class="h3">Sipariş özeti</h2>
      <div class="sum-row"><span><?= e(setting('course_title')) ?></span></div>
      <div class="sum-row muted"><span><?= e(setting('course_duration')) ?></span></div>
      <?php if ($old !== '' && (float)$old > Payments::price()): ?><div class="sum-row"><span>Liste fiyatı</span><s><?= e(money($old)) ?></s></div><?php endif; ?>
      <div class="sum-row total"><span>Toplam</span><b><?= e(money(Payments::price())) ?></b></div>
      <ul class="ticks tiny mt-2"><?php foreach (lines((string)setting('course_includes')) as $i): ?><li><?= e($i) ?></li><?php endforeach; ?></ul>
    </aside>
  </div>
</section>
