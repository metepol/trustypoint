<section class="page-hero error-hero">
  <div class="container center narrow">
    <?php if ($paid): ?>
      <p class="result-ic ok"><?= icon('check', 40) ?></p>
      <h1 class="h1">Kaydın <em>tamamlandı!</em></h1>
      <p class="lead">Ödemen onaylandı. Canlı yayın takvimini ve dersleri üye panelinde bulabilirsin. Onay e-postası yolda.</p>
      <a href="<?= e(url('panel')) ?>" class="btn btn-primary btn-lg mt-2">Derslerime git →</a>
    <?php else: ?>
      <p class="result-ic"><?= icon('clock', 40) ?></p>
      <h1 class="h1">Ödemen kontrol ediliyor</h1>
      <p class="lead">Ödeme sağlayıcıdan onay bekleniyor. Birkaç dakika içinde erişimin otomatik açılacak.</p>
      <a href="<?= e(url('panel')) ?>" class="btn btn-primary btn-lg mt-2">Üye paneline git</a>
    <?php endif; ?>
  </div>
</section>
