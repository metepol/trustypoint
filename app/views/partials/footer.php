<?php
use App\Database;
$footerPages = Database::all("SELECT slug, title FROM pages WHERE show_footer = 1 AND slug <> 'hakkimizda' ORDER BY id");
$socials = array_filter([
    'Instagram' => setting('social_instagram', ''), 'YouTube' => setting('social_youtube', ''),
    'LinkedIn' => setting('social_linkedin', ''), 'X' => setting('social_x', ''), 'TikTok' => setting('social_tiktok', ''),
]);
?>
<footer class="site-footer">
  <div class="container">
    <div class="footer-cta">
      <p class="footer-cta-title">Bir sonraki <em>canlı derste</em> görüşelim.</p>
      <a href="<?= e(url('egitim')) ?>" class="btn btn-primary btn-lg">Eğitimi incele →</a>
    </div>
    <div class="footer-grid">
      <div>
        <?php App\View::partial('logo'); ?>
        <p class="muted mt-2"><?= e(setting('footer_text')) ?></p>
        <?php if ($socials): ?>
        <ul class="socials">
          <?php foreach ($socials as $name => $link): ?>
            <li><a href="<?= e($link) ?>" target="_blank" rel="noopener me"><?= e($name) ?></a></li>
          <?php endforeach; ?>
        </ul>
        <?php endif; ?>
      </div>
      <div>
        <h3 class="footer-h">Menü</h3>
        <ul class="footer-links">
          <li><a href="<?= e(url('hakkimizda')) ?>">Hakkımızda</a></li>
          <li><a href="<?= e(url('egitim')) ?>">Eğitim</a></li>
          <li><a href="<?= e(url('ornekler')) ?>">Örnekler</a></li>
          <li><a href="<?= e(url('referanslar')) ?>">Referanslar</a></li>
          <li><a href="<?= e(url('iletisim')) ?>">İletişim</a></li>
        </ul>
      </div>
      <div>
        <h3 class="footer-h">Üyelik</h3>
        <ul class="footer-links">
          <li><a href="<?= e(url('giris')) ?>">Üye Girişi</a></li>
          <li><a href="<?= e(url('kayit')) ?>">Üye Ol</a></li>
          <li><a href="<?= e(url('panel')) ?>">Üye Paneli</a></li>
          <?php foreach ($footerPages as $p): ?>
            <li><a href="<?= e(url('sayfa/' . $p['slug'])) ?>"><?= e($p['title']) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div>
        <h3 class="footer-h">İletişim</h3>
        <ul class="footer-links">
          <?php if ($m = setting('contact_email')): ?><li><a href="mailto:<?= e($m) ?>"><?= e($m) ?></a></li><?php endif; ?>
          <?php if ($p = setting('contact_phone')): ?><li><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', (string)$p)) ?>"><?= e($p) ?></a></li><?php endif; ?>
          <?php if ($a = setting('contact_address')): ?><li class="muted"><?= nl2br(e($a)) ?></li><?php endif; ?>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <span>© <?= date('Y') ?> <?= e(setting('site_name')) ?>. Tüm hakları saklıdır.</span>
      <a href="#top" class="to-top">Yukarı ↑</a>
    </div>
  </div>
</footer>
<?php if ($wa = preg_replace('/\D/', '', (string)setting('contact_whatsapp', ''))): ?>
<a class="wa-float" href="https://wa.me/<?= e($wa) ?>" target="_blank" rel="noopener" aria-label="WhatsApp ile yazın">
  <svg viewBox="0 0 24 24" width="26" height="26" fill="currentColor" aria-hidden="true"><path d="M20.5 3.5A11.8 11.8 0 0 0 1.7 17.6L0 24l6.6-1.7A11.8 11.8 0 0 0 24 12a11.7 11.7 0 0 0-3.5-8.5ZM12 21.8c-1.8 0-3.5-.5-5-1.4l-.4-.2-3.9 1 1-3.8-.2-.4A9.8 9.8 0 1 1 12 21.8Zm5.4-7.3c-.3-.1-1.8-.9-2-1-.3-.1-.5-.1-.7.2l-.9 1.1c-.2.2-.3.2-.6.1a8 8 0 0 1-4-3.5c-.3-.5.3-.5.9-1.6.1-.2 0-.4 0-.5l-.9-2.2c-.2-.6-.5-.5-.7-.5h-.6a1.1 1.1 0 0 0-.8.4 3.4 3.4 0 0 0-1 2.5 5.9 5.9 0 0 0 1.2 3.1 13.5 13.5 0 0 0 5.2 4.6c1.9.8 2.7.9 3.6.7a3.1 3.1 0 0 0 2-1.4 2.5 2.5 0 0 0 .2-1.4c-.1-.1-.3-.2-.6-.3Z"/></svg>
</a>
<?php endif; ?>
