<section class="page-hero error-hero">
  <div class="container center">
    <p class="err-code"><?= (int)$code ?></p>
    <h1 class="h1"><?= $code === 404 ? 'Bu sayfa oyundan çıkmış.' : ($code === 403 ? 'Erişim yok.' : 'Bir şeyler ters gitti.') ?></h1>
    <p class="lead"><?= e($message ?: 'Aradığınız sayfa taşınmış ya da hiç var olmamış olabilir.') ?></p>
    <a href="<?= e(url('/')) ?>" class="btn btn-primary btn-lg mt-2">Ana sayfaya dön</a>
  </div>
</section>
