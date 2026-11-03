<?php $t = $tabs[$tab]; ?>
<div class="adm-head"><h1 class="adm-title"><?= e($t['title']) ?> ayarları</h1></div>
<nav class="tabs">
  <?php foreach ($tabs as $k => $x): ?><a href="<?= e(url('yonetim/ayarlar/' . $k)) ?>" class="<?= $k === $tab ? 'is-active' : '' ?>"><?= e($x['title']) ?></a><?php endforeach; ?>
</nav>
<?php if ($tab === 'seo'): ?>
  <div class="alert alert-info">Site haritası: <a href="<?= e(full_url('sitemap.xml')) ?>" target="_blank"><?= e(full_url('sitemap.xml')) ?></a> · Robots: <a href="<?= e(full_url('robots.txt')) ?>" target="_blank"><?= e(full_url('robots.txt')) ?></a><br>Sayfa bazlı başlık/açıklama için <a href="<?= e(url('yonetim/sayfalar')) ?>">Sayfalar</a> bölümünü kullanın. Tüm sayfalarda canonical, Open Graph, Twitter Card ve schema.org (Course, FAQPage, Breadcrumb, Organization) otomatik üretilir.</div>
<?php elseif ($tab === 'odeme'): ?>
  <div class="alert alert-info">Stripe webhook adresi: <code><?= e(full_url('stripe/webhook')) ?></code> — Olay: <code>checkout.session.completed</code>. Webhook tanımlanmasa da başarılı ödeme dönüşünde sipariş API üzerinden doğrulanır; yine de webhook önerilir.</div>
<?php endif; ?>
<form method="post" enctype="multipart/form-data" class="form card">
  <?= csrf_field() ?>
  <?php foreach ($t['fields'] as $key => $def):
    [$label, $type] = $def;
    App\View::partial('admin-field', ['name' => $key, 'label' => $label, 'type' => $type, 'value' => (string)setting($key, ''), 'options' => $def[2] ?? [], 'help' => $def[3] ?? null]);
  endforeach; ?>
  <div class="form-actions"><button class="btn btn-primary">Ayarları kaydet</button></div>
</form>
<?php if (($t['test'] ?? '') === 'smtp'): ?>
  <form method="post" action="<?= e(url('yonetim/ayarlar/smtp/test')) ?>" class="card inline-form">
    <?= csrf_field() ?>
    <input type="email" name="test_email" value="<?= e($admin['email']) ?>" aria-label="Test e-posta adresi">
    <button class="btn btn-outline">Test e-postası gönder</button>
  </form>
<?php elseif (($t['test'] ?? '') === 'telegram'): ?>
  <form method="post" action="<?= e(url('yonetim/ayarlar/telegram/test')) ?>" class="card inline-form">
    <?= csrf_field() ?>
    <span class="muted">Kaydettikten sonra bağlantıyı test edin.</span>
    <button class="btn btn-outline">Test mesajı gönder</button>
  </form>
<?php endif; ?>
