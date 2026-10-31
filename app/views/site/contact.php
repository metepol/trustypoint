<?php use App\View; $map = trim((string)setting('contact_map', '')); ?>
<?php View::partial('page-hero', ['title' => 'İletişim', 'title_html' => 'Merhaba <em>de.</em>', 'crumb' => 'İletişim', 'kicker' => 'Bize ulaşın', 'lead' => 'Kurumsal kayıt, fatura, iş birliği ya da aklına takılan her şey için yaz.']); ?>
<section class="section pt-0">
  <div class="container contact-grid">
    <div class="contact-info">
      <?php if ($m = setting('contact_email')): ?><a class="info-tile" href="mailto:<?= e($m) ?>"><span>E-posta</span><b><?= e($m) ?></b></a><?php endif; ?>
      <?php if ($p = setting('contact_phone')): ?><a class="info-tile" href="tel:<?= e(preg_replace('/[^0-9+]/', '', (string)$p)) ?>"><span>Telefon</span><b><?= e($p) ?></b></a><?php endif; ?>
      <?php if ($w = preg_replace('/\D/', '', (string)setting('contact_whatsapp'))): ?><a class="info-tile" href="https://wa.me/<?= e($w) ?>" target="_blank" rel="noopener"><span>WhatsApp</span><b>Hemen yaz →</b></a><?php endif; ?>
      <?php if ($a = setting('contact_address')): ?><div class="info-tile"><span>Adres</span><b><?= nl2br(e($a)) ?></b></div><?php endif; ?>
      <?php if ($h = setting('contact_hours')): ?><div class="info-tile"><span>Çalışma saatleri</span><b><?= e($h) ?></b></div><?php endif; ?>
    </div>
    <form method="post" action="<?= e(url('iletisim')) ?>" class="form glass-card" novalidate>
      <?= csrf_field() ?>
      <div class="hp" aria-hidden="true"><label>Web sitesi<input name="website" tabindex="-1" autocomplete="off"></label></div>
      <div class="grid-2">
        <div class="field"><label for="c-name">Ad Soyad</label><input id="c-name" name="name" required value="<?= e(old('name', $user['name'] ?? '')) ?>" autocomplete="name"></div>
        <div class="field"><label for="c-email">E-posta</label><input id="c-email" type="email" name="email" required value="<?= e(old('email', $user['email'] ?? '')) ?>" autocomplete="email"></div>
        <div class="field"><label for="c-phone">Telefon <small>(opsiyonel)</small></label><input id="c-phone" name="phone" value="<?= e(old('phone')) ?>" autocomplete="tel"></div>
        <div class="field"><label for="c-subject">Konu</label>
          <select id="c-subject" name="subject">
            <?php foreach (['Eğitim hakkında', 'Kurumsal / toplu kayıt', 'Ödeme & fatura', 'İş birliği', 'Diğer'] as $s): ?><option<?= old('subject') === $s ? ' selected' : '' ?>><?= e($s) ?></option><?php endforeach; ?>
          </select></div>
      </div>
      <div class="field"><label for="c-msg">Mesajın</label><textarea id="c-msg" name="message" rows="6" required><?= e(old('message')) ?></textarea></div>
      <label class="check"><input type="checkbox" name="kvkk" required> <span><a href="<?= e(url('sayfa/gizlilik-politikasi')) ?>" target="_blank">KVKK aydınlatma metnini</a> okudum, onaylıyorum.</span></label>
      <button class="btn btn-primary btn-lg btn-block mt-2">Mesajı gönder →</button>
    </form>
  </div>
  <?php if ($map !== '' && preg_match('#^https://(www\.)?google\.[a-z.]+/maps/embed#', $map)): ?>
  <div class="container mt-3"><iframe class="map" src="<?= e($map) ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Harita"></iframe></div>
  <?php endif; ?>
</section>
