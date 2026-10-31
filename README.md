# TrustyPoint Akademi — Vibe Coding Canlı Eğitim Platformu

Tek bir eğitimin satıldığı site: **Vibe coding ile kurumsal oyun & animasyon kodlama ve yayınlama**.
Eğitimi satın alan üyeler canlı yayınları yayın gününde izler, sonra her yayının tekrarına **1 yıl** (ayarlanabilir) erişir.

- **PHP 8.5+**, framework ya da Composer paketi gerektirmez (saf PHP, PDO, cURL)
- MySQL/MariaDB (önerilen) veya SQLite
- Marjinal/modern koyu tema, mobil uyumlu, tüm metinler Türkçe

## Özellikler

| Alan | İçerik |
|---|---|
| Menü | Ana Sayfa · Hakkımızda · Eğitim · Örnekler · Referanslar · İletişim · Üye Girişi / Üye Paneli |
| Üyelik | 3 alanla kayıt (ad, e-posta, şifre) ve otomatik giriş · e-posta + şifre ile giriş · **şifresiz e-posta bağlantısıyla giriş** · şifre sıfırlama · 30 gün açık kalan oturum |
| Canlı yayın | Her ders yayın tarihinden *X* dk önce (varsayılan 30) üye panelinde açılır. YouTube (liste dışı), Vimeo Event, HLS/MP4 bağlantısı ya da hazır iframe kodu kullanılabilir |
| Tekrar | Yayın bitince tekrar kaydı eklenir; yayın tarihinden itibaren `replay_days` (varsayılan 365) gün izlenebilir, sonra kapanır |
| Ödeme | **Stripe Checkout** (webhook + dönüşte API doğrulaması, tutar kontrolü) · **Havale/EFT** (IBAN, sipariş no'lu açıklama, bildirim formu, yöneticiden tek tıkla onay) |
| Bildirim | **SMTP** (bağımlılıksız; SSL/STARTTLS/AUTH LOGIN; test butonu) · **Telegram** (bot uyarıları; olay bazında aç/kapa; test butonu) |
| SEO | SEO uyumlu adresler (`/egitim`, `/hakkimizda`, `/sayfa/kvkk`…) · sayfa bazlı title/description · canonical · Open Graph · Twitter Card · JSON-LD (Organization, WebSite, Course + CourseInstance, FAQPage, BreadcrumbList) · dinamik `sitemap.xml` ve `robots.txt` · Search Console/Bing/Yandex doğrulama · GA4, GTM, Meta Pixel · özel head/body kodu · indekslemeyi tek tıkla kapatma |
| Yönetim paneli | Genel bakış (gelir, üye, onay bekleyen havale) · Siparişler · Üyeler (manuel erişim, yetki, şifre, CSV) · Canlı Dersler · Örnekler · Referanslar · SSS · Sayfalar · Mesajlar · Ayarlar (Genel, Eğitim & Fiyat, Ana Sayfa, SMTP, Ödeme, Telegram, SEO) |
| Güvenlik | CSRF koruması, hazırlanmış SQL ifadeleri, `password_hash`, oturum sabitleme koruması, giriş/kayıt/iletişim hız sınırı, açık yönlendirme koruması, tek kullanımlık hash'li token'lar, yükleme klasöründe betik çalıştırma kapalı |

## Kurulum

1. Dosyaları sunucunuzun web köküne (veya bir alt klasöre) yükleyin.
2. `app/`, `storage/` ve `uploads/` klasörlerinin yazılabilir olduğundan emin olun.
3. MySQL kullanacaksanız boş bir veritabanı oluşturun (`utf8mb4`).
4. Tarayıcıdan siteyi açın → otomatik olarak **`/install.php`** sihirbazına yönlenirsiniz. Veritabanı, site adresi ve yönetici hesabını girin.
5. Kurulum bitince **`install.php` dosyasını silin**.
6. `/giris` adresinden yönetici olarak girin → **Ayarlar** menüsünden SMTP, ödeme, Telegram ve SEO ayarlarını yapın; **Canlı Dersler**'den yayın takvimini girin.

### Apache
Kökteki `.htaccess` hazırdır (`mod_rewrite` gerekli). SSL sonrası dosyadaki HTTPS yönlendirme satırlarını açın.

### Nginx
```nginx
server {
    server_name ornek.com;
    root /var/www/trustypoint;
    index index.php;

    location ~ ^/(app|storage)/ { deny all; }
    location ~ /\. { deny all; }
    location ~ ^/uploads/.*\.(php|phtml|phar)$ { deny all; }

    location / { try_files $uri $uri/ /index.php?$query_string; }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_pass unix:/run/php/php8.5-fpm.sock;
    }
}
```

### Yerel geliştirme
```bash
php -S 127.0.0.1:8000 index.php
```

## Entegrasyon notları

**Stripe** — Ayarlar → Ödeme: *Secret key* ve *Webhook signing secret* girin. Stripe Dashboard → Developers → Webhooks:
- Endpoint: `https://alanadiniz.com/stripe/webhook`
- Olaylar: `checkout.session.completed`, `checkout.session.async_payment_succeeded`

**Telegram** — @BotFather ile bot oluşturun, token'ı girin. Bota `/start` yazın, `https://api.telegram.org/bot<TOKEN>/getUpdates` adresinden `chat.id` değerini alın (gruplarda `-` ile başlar). Birden fazla alıcı için virgülle ayırın.

**SMTP** — Örnek: Yandex `smtp.yandex.com.tr:465 SSL`, Gmail `smtp.gmail.com:587 STARTTLS` (uygulama şifresi). SMTP boş bırakılırsa PHP `mail()` kullanılır.

**Canlı yayın güvenliği** — Yayın ve tekrar bağlantıları yalnızca giriş yapmış ve eğitime kayıtlı üyelere gösterilir. YouTube'da *liste dışı*, Vimeo'da *alan adı kısıtlamalı* yayın kullanmanız önerilir.

## Klasör yapısı
```
index.php           Ön denetleyici + rotalar
install.php         Kurulum sihirbazı
app/
  bootstrap.php     Başlatma, oturum, güvenlik başlıkları
  helpers.php       Yardımcı fonksiyonlar
  src/              Database, Auth, Mailer (SMTP), Stripe, Telegram, Seo, Access (yayın/tekrar kuralları), …
  src/Controllers/  Site, Auth, Member, Checkout, Admin
  views/            Şablonlar (site, auth, member, admin, emails)
assets/             CSS, JS, görseller
uploads/            Yönetim panelinden yüklenen görseller
storage/            SQLite, oturumlar, loglar (web'den erişime kapalı)
```
