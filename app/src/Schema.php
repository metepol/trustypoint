<?php
declare(strict_types=1);

namespace App;

/** MySQL ve SQLite ile uyumlu şema + varsayılan içerik. */
final class Schema
{
    public static function statements(string $driver): array
    {
        $pk = $driver === 'sqlite' ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'INT UNSIGNED AUTO_INCREMENT PRIMARY KEY';
        $fk = $driver === 'sqlite' ? 'INTEGER' : 'INT UNSIGNED';
        $text = $driver === 'sqlite' ? 'TEXT' : 'MEDIUMTEXT';
        $tail = $driver === 'sqlite' ? '' : ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

        return [
            "CREATE TABLE IF NOT EXISTS settings (
                skey VARCHAR(100) NOT NULL PRIMARY KEY,
                svalue {$text} NULL
            ){$tail}",
            "CREATE TABLE IF NOT EXISTS users (
                id {$pk},
                name VARCHAR(120) NOT NULL,
                email VARCHAR(190) NOT NULL UNIQUE,
                phone VARCHAR(40) NULL,
                password_hash VARCHAR(255) NOT NULL,
                role VARCHAR(20) NOT NULL DEFAULT 'member',
                enrolled_at DATETIME NULL,
                token_hash VARCHAR(64) NULL,
                token_type VARCHAR(20) NULL,
                token_expires DATETIME NULL,
                last_login_at DATETIME NULL,
                created_at DATETIME NOT NULL
            ){$tail}",
            "CREATE TABLE IF NOT EXISTS orders (
                id {$pk},
                user_id {$fk} NOT NULL,
                method VARCHAR(20) NOT NULL,
                amount DECIMAL(12,2) NOT NULL,
                currency VARCHAR(3) NOT NULL,
                status VARCHAR(20) NOT NULL DEFAULT 'pending',
                stripe_session_id VARCHAR(255) NULL,
                payment_ref VARCHAR(255) NULL,
                havale_sender VARCHAR(190) NULL,
                havale_note VARCHAR(500) NULL,
                admin_note VARCHAR(500) NULL,
                created_at DATETIME NOT NULL,
                paid_at DATETIME NULL,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ){$tail}",
            "CREATE TABLE IF NOT EXISTS lessons (
                id {$pk},
                title VARCHAR(190) NOT NULL,
                summary VARCHAR(500) NULL,
                description {$text} NULL,
                scheduled_at DATETIME NOT NULL,
                duration_min INT NOT NULL DEFAULT 90,
                live_embed {$text} NULL,
                replay_embed {$text} NULL,
                resources {$text} NULL,
                is_published TINYINT NOT NULL DEFAULT 1,
                sort INT NOT NULL DEFAULT 0
            ){$tail}",
            "CREATE TABLE IF NOT EXISTS pages (
                id {$pk},
                slug VARCHAR(120) NOT NULL UNIQUE,
                title VARCHAR(190) NOT NULL,
                content {$text} NULL,
                meta_title VARCHAR(190) NULL,
                meta_description VARCHAR(320) NULL,
                show_footer TINYINT NOT NULL DEFAULT 0,
                updated_at DATETIME NULL
            ){$tail}",
            "CREATE TABLE IF NOT EXISTS examples (
                id {$pk},
                title VARCHAR(190) NOT NULL,
                category VARCHAR(80) NULL,
                description VARCHAR(600) NULL,
                image VARCHAR(255) NULL,
                demo_url VARCHAR(255) NULL,
                video_url VARCHAR(255) NULL,
                sort INT NOT NULL DEFAULT 0
            ){$tail}",
            "CREATE TABLE IF NOT EXISTS testimonials (
                id {$pk},
                name VARCHAR(120) NOT NULL,
                company VARCHAR(160) NULL,
                position VARCHAR(120) NULL,
                quote VARCHAR(1000) NULL,
                logo VARCHAR(255) NULL,
                website VARCHAR(255) NULL,
                sort INT NOT NULL DEFAULT 0
            ){$tail}",
            "CREATE TABLE IF NOT EXISTS faqs (
                id {$pk},
                question VARCHAR(300) NOT NULL,
                answer {$text} NOT NULL,
                sort INT NOT NULL DEFAULT 0
            ){$tail}",
            "CREATE TABLE IF NOT EXISTS messages (
                id {$pk},
                name VARCHAR(120) NOT NULL,
                email VARCHAR(190) NOT NULL,
                phone VARCHAR(40) NULL,
                subject VARCHAR(190) NOT NULL,
                body {$text} NOT NULL,
                ip VARCHAR(45) NULL,
                is_read TINYINT NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL
            ){$tail}",
            "CREATE TABLE IF NOT EXISTS throttle (
                tkey VARCHAR(190) NOT NULL PRIMARY KEY,
                hits INT NOT NULL DEFAULT 0,
                reset_at DATETIME NOT NULL
            ){$tail}",
        ];
    }

    public static function install(string $driver): void
    {
        foreach (self::statements($driver) as $sql) {
            Database::pdo()->exec($sql);
        }
        $idx = [
            'CREATE INDEX idx_orders_user ON orders (user_id)',
            'CREATE INDEX idx_orders_status ON orders (status)',
            'CREATE INDEX idx_lessons_date ON lessons (scheduled_at)',
        ];
        foreach ($idx as $sql) {
            try {
                Database::pdo()->exec($sql);
            } catch (\Throwable) {
                // indeks zaten var
            }
        }
    }

    public static function defaultSettings(string $siteName, string $email): array
    {
        return [
            // Genel
            'site_name'        => $siteName,
            'site_tagline'     => 'Vibe Coding ile kurumsal oyun & animasyon',
            'site_logo'        => '',
            'site_favicon'     => '',
            'contact_email'    => $email,
            'contact_phone'    => '+90 (500) 000 00 00',
            'contact_whatsapp' => '',
            'contact_address'  => 'İstanbul, Türkiye',
            'contact_hours'    => 'Hafta içi 09:00 – 18:00',
            'contact_map'      => '',
            'social_instagram' => '', 'social_youtube' => '', 'social_linkedin' => '', 'social_x' => '', 'social_tiktok' => '',
            'social_twitter_handle' => '',
            'footer_text'      => 'Yapay zekâ destekli "vibe coding" yaklaşımıyla markalar için oyun ve animasyon üretmeyi, yayınlamayı öğreten canlı eğitim programı.',
            'announcement'     => '🔴 Yeni dönem kayıtları açık — kontenjan sınırlı!',

            // Eğitim
            'course_title'       => 'Vibe Coding ile Kurumsal Oyun & Animasyon Geliştirme',
            'course_subtitle'    => 'Yapay zekâyla birlikte kodlayarak markan için oyun, interaktif deneyim ve animasyon üret; web, mobil ve mağazalarda yayınla.',
            'course_description' => '<p>Bu program; kod yazmayı satır satır ezberlemek yerine yapay zekâ ile <strong>konuşarak üretmeyi</strong> öğretir. Her hafta canlı yayında gerçek bir kurumsal brief alıyor, onu oynanabilir bir oyuna ya da yayına hazır bir animasyona dönüştürüyoruz.</p><p>Program sonunda; kampanya oyunları, ürün tanıtım animasyonları, eğitim simülasyonları ve etkinlik deneyimleri üretip bunları web, mobil mağazalar ve sosyal platformlarda yayınlayabilecek seviyeye geleceksin.</p>',
            'course_outcomes'    => "Yapay zekâ ile prompt-tabanlı oyun prototipleme\nHTML5 Canvas, Phaser ve Three.js ile kurumsal oyunlar\nLottie, GSAP ve Rive ile marka animasyonları\nKampanya oyunlarında skor tablosu, çekiliş ve veri toplama\nWeb, App Store / Google Play ve sosyal medyada yayınlama\nMüşteriye teklif, sunum ve teslim süreçleri",
            'course_audience'    => "Ajans çalışanları ve kreatif ekipler\nPazarlama ve marka yöneticileri\nFreelance tasarımcı ve geliştiriciler\nKendi oyun/animasyon işini kurmak isteyenler",
            'course_includes'    => "Canlı yayın dersleri\nHer dersin 1 yıl boyunca tekrarı\nKaynak kodlar ve prompt kütüphanesi\nKatılım sertifikası\nTopluluk ve soru-cevap desteği",
            'course_price'       => '4990',
            'course_old_price'   => '7990',
            'course_currency'    => 'TRY',
            'course_level'       => 'Başlangıç → İleri',
            'course_duration'    => '8 hafta · 16 canlı ders',
            'course_preview'     => '',
            'course_image'       => '',
            'course_cta'         => 'Hemen Kaydol',
            'replay_days'        => '365',
            'live_open_minutes'  => '30',

            // Ana sayfa
            'home_hero_kicker'   => 'Canlı eğitim · Yeni dönem',
            'home_hero_title'    => 'Kod yazma. *Vibe* yaz.',
            'home_hero_text'     => 'Yapay zekâyla birlikte kodlayarak markalar için oyun ve animasyon üret, yayınla, sat. Tek eğitim, net bir yol haritası.',
            'home_stats'         => "16|Canlı ders\n365|Gün tekrar erişimi\n12+|Yayınlanmış proje\n%100|Uygulamalı",
            'home_marquee'       => 'Phaser · Three.js · GSAP · Lottie · Rive · WebGL · Unity Web · Prompt Engineering · App Store · Google Play',

            // SMTP
            'smtp_host' => '', 'smtp_port' => '587', 'smtp_secure' => 'tls', 'smtp_user' => '', 'smtp_pass' => '',
            'smtp_from_email' => $email, 'smtp_from_name' => $siteName,
            'admin_notify_email' => $email, 'mail_admin_notify' => '1',

            // Ödeme
            'pay_stripe_enabled' => '0', 'stripe_public_key' => '', 'stripe_secret_key' => '', 'stripe_webhook_secret' => '',
            'pay_havale_enabled' => '1',
            'havale_bank'    => 'Örnek Bankası',
            'havale_holder'  => $siteName,
            'havale_iban'    => 'TR00 0000 0000 0000 0000 0000 00',
            'havale_info'    => 'Açıklama kısmına sipariş numaranızı ve e-posta adresinizi yazmayı unutmayın.',

            // Telegram
            'tg_enabled' => '0', 'tg_bot_token' => '', 'tg_chat_id' => '',
            'tg_on_register' => '1', 'tg_on_order' => '1', 'tg_on_havale' => '1', 'tg_on_payment' => '1', 'tg_on_contact' => '1',

            // SEO
            'seo_site_title'   => $siteName,
            'seo_separator'    => '|',
            'seo_home_title'   => 'Vibe Coding ile Kurumsal Oyun ve Animasyon Eğitimi',
            'seo_description'  => 'Yapay zekâ destekli vibe coding ile kurumsal oyun ve animasyon kodlamayı, yayınlamayı canlı derslerle öğrenin. 1 yıl tekrar erişimi.',
            'seo_keywords'     => 'vibe coding, oyun geliştirme eğitimi, animasyon eğitimi, kurumsal oyun, advergame, yapay zeka ile kodlama, canlı eğitim',
            'seo_og_image'     => '',
            'seo_index'        => '1',
            'seo_google_verify' => '', 'seo_bing_verify' => '', 'seo_yandex_verify' => '',
            'seo_ga_id' => '', 'seo_gtm_id' => '', 'seo_meta_pixel' => '',
            'seo_head_code' => '', 'seo_body_code' => '',
            'seo_robots_extra' => '',
        ];
    }

    public static function seed(string $siteName, string $email): void
    {
        Settings::setMany(self::defaultSettings($siteName, $email));

        $pages = [
            ['hakkimizda', 'Hakkımızda', '<h2>Oyunu ciddiye alan bir ekip</h2><p>10 yılı aşkın süredir markalar için kampanya oyunları, interaktif deneyimler ve hareketli grafikler üretiyoruz. Yapay zekâ ile kodlamanın üretim hızımızı nasıl katladığını gördükten sonra bu bilgiyi canlı ve uygulamalı bir programa dönüştürdük.</p><h3>Misyonumuz</h3><p>Kod bilgisi sınırlı olan yaratıcı profesyonellerin bile, yapay zekâyla birlikte çalışarak yayına hazır kurumsal oyun ve animasyonlar üretebilmesini sağlamak.</p><h3>Nasıl çalışıyoruz?</h3><ul><li>Her ders canlı ve gerçek bir müşteri brief\'iyle başlar.</li><li>Teori değil üretim: her hafta bir proje teslim edilir.</li><li>Tüm derslerin tekrarı 1 yıl boyunca üye panelinde.</li></ul>', 'Hakkımızda', 'Kurumsal oyun ve animasyon alanında deneyimli ekibimizi ve vibe coding eğitim yaklaşımımızı tanıyın.', 0],
            ['gizlilik-politikasi', 'Gizlilik ve KVKK Aydınlatma Metni', '<p>Bu metin, 6698 sayılı Kişisel Verilerin Korunması Kanunu kapsamında kişisel verilerinizin işlenmesine ilişkin bilgilendirme amacıyla hazırlanmıştır. Lütfen yönetim panelinden kendi metninizle güncelleyin.</p>', 'Gizlilik Politikası ve KVKK', 'Kişisel verilerin korunması ve gizlilik politikamız.', 1],
            ['mesafeli-satis-sozlesmesi', 'Mesafeli Satış Sözleşmesi', '<p>Mesafeli satış sözleşmesi metninizi yönetim panelinden ekleyin.</p>', 'Mesafeli Satış Sözleşmesi', 'Eğitim satışına ilişkin mesafeli satış sözleşmesi.', 1],
            ['iade-politikasi', 'İptal ve İade Koşulları', '<p>İptal ve iade koşullarınızı yönetim panelinden ekleyin.</p>', 'İptal ve İade Koşulları', 'Eğitim iptal ve iade koşulları.', 1],
        ];
        foreach ($pages as [$slug, $title, $content, $mt, $md, $footer]) {
            Database::insert('pages', ['slug' => $slug, 'title' => $title, 'content' => $content, 'meta_title' => $mt, 'meta_description' => $md, 'show_footer' => $footer, 'updated_at' => now()]);
        }

        $topics = [
            ['Vibe coding zihniyeti & araç seti', 'Yapay zekâ ile kodlamaya giriş, editör kurulumu, ilk oynanabilir prototip.'],
            ['Kampanya oyunu: Brief\'ten prototipe', 'Bir marka brief\'ini 60 dakikada oynanabilir HTML5 oyununa dönüştürüyoruz.'],
            ['Phaser ile 2D kurumsal oyunlar', 'Sahne, fizik, skor ve ödül mekanikleri.'],
            ['Three.js ile 3D ürün deneyimleri', 'Ürün tanıtımı için etkileşimli 3D sahneler.'],
            ['GSAP & Lottie ile marka animasyonları', 'Logo animasyonu, mikro etkileşimler ve sosyal medya videoları.'],
            ['Skor tablosu, çekiliş ve veri toplama', 'KVKK uyumlu form, liderlik tablosu ve kampanya raporları.'],
            ['Mobil mağazalarda yayınlama', 'PWA, Capacitor ile App Store / Google Play yayını.'],
            ['Teslim, fiyatlandırma ve portfolyo', 'Müşteriye sunum, teklif hazırlama ve demo günü.'],
        ];
        $start = strtotime('next monday 20:00');
        foreach ($topics as $i => [$t, $s]) {
            Database::insert('lessons', [
                'title' => ($i + 1) . '. Hafta — ' . $t,
                'summary' => $s,
                'description' => '<p>' . e($s) . '</p>',
                'scheduled_at' => date('Y-m-d H:i:s', $start + $i * 7 * 86400),
                'duration_min' => 120,
                'live_embed' => '',
                'replay_embed' => '',
                'resources' => '',
                'is_published' => 1,
                'sort' => $i + 1,
            ]);
        }

        $examples = [
            ['Çark-ı Felek Kampanyası', 'Kampanya Oyunu', 'Bir perakende markası için mağaza içi QR ile açılan, günlük ödül kotası olan şans çarkı.'],
            ['Ürün Konfigüratörü 3D', '3D Deneyim', 'Kullanıcının renk ve parça seçerek ürünü 360° incelediği Three.js deneyimi.'],
            ['Logo Reveal Paketi', 'Animasyon', 'Lottie + GSAP ile sosyal medya, web ve sunumlar için marka açılış animasyonları.'],
            ['İSG Eğitim Simülasyonu', 'Eğitim Oyunu', 'Çalışanlar için senaryo tabanlı iş güvenliği eğitim oyunu ve raporlama paneli.'],
            ['Fuar Dokunmatik Oyunu', 'Etkinlik', 'Fuar standı için dokunmatik ekranda skor tablolu refleks oyunu.'],
            ['Kampanya Runner', 'Mobil Oyun', 'Marka maskotuyla sonsuz koşu oyunu; App Store ve Google Play\'de yayınlandı.'],
        ];
        foreach ($examples as $i => [$t, $c, $d]) {
            Database::insert('examples', ['title' => $t, 'category' => $c, 'description' => $d, 'image' => '', 'demo_url' => '', 'video_url' => '', 'sort' => $i + 1]);
        }

        $refs = [
            ['Elif Y.', 'Kreatif Ajans', 'Kreatif Direktör', 'Ekibimiz programın 3. haftasında ilk kampanya oyununu müşteriye sundu. Yapay zekâyla kodlama üretim süremizi yarıya indirdi.'],
            ['Mert A.', 'Perakende Markası', 'Dijital Pazarlama Müdürü', 'Ajans beklemeden kendi kampanya oyunlarımızı üretebiliyoruz. Canlı derslerin tekrarlarını hâlâ izliyorum.'],
            ['Zeynep K.', 'Freelance', 'Motion Designer', 'Animasyon bilgimi interaktif projelere taşıdım; artık oyun projeleri için de teklif veriyorum.'],
        ];
        foreach ($refs as $i => [$n, $c, $p, $q]) {
            Database::insert('testimonials', ['name' => $n, 'company' => $c, 'position' => $p, 'quote' => $q, 'logo' => '', 'website' => '', 'sort' => $i + 1]);
        }

        $faqs = [
            ['Daha önce hiç kod yazmadım, katılabilir miyim?', 'Evet. Program vibe coding yaklaşımıyla tasarlandı; yapay zekâya ne istediğini doğru anlatmayı ve çıkan kodu yönetmeyi adım adım öğretiyoruz.'],
            ['Canlı yayını kaçırırsam ne olur?', 'Her yayın sona erdikten sonra tekrarı üye panelinize eklenir ve yayın tarihinden itibaren 1 yıl boyunca dilediğiniz kadar izleyebilirsiniz.'],
            ['Canlı derslere nasıl katılırım?', 'Satın alma sonrası üye panelinize giriş yaptığınızda, yayın saatinden kısa süre önce yayın ekranı otomatik olarak açılır.'],
            ['Hangi ödeme yöntemleri var?', 'Kredi / banka kartı (Stripe) ve banka havalesi / EFT ile ödeme yapabilirsiniz. Havale ödemeleri onaylandıktan sonra erişiminiz açılır.'],
            ['Kurumsal fatura alabilir miyim?', 'Evet, ödeme sonrası iletişim sayfasından fatura bilgilerinizi iletebilirsiniz.'],
        ];
        foreach ($faqs as $i => [$q, $a]) {
            Database::insert('faqs', ['question' => $q, 'answer' => $a, 'sort' => $i + 1]);
        }
    }
}
