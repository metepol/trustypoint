<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Access;
use App\Auth;
use App\Csrf;
use App\Database;
use App\Mailer;
use App\Payments;
use App\Settings;
use App\Telegram;
use App\Upload;

final class AdminController
{
    private array $admin;

    public function __construct()
    {
        $this->admin = Auth::requireAdmin();
    }

    private function render(string $tpl, array $data = []): void
    {
        view('admin/' . $tpl, $data + ['admin' => $this->admin, 'seo' => ['title' => 'Yönetim Paneli', 'robots' => 'noindex, nofollow']], 'layouts/admin');
    }

    /* ------------------------------------------------------------------ */
    /* Panel                                                               */
    /* ------------------------------------------------------------------ */

    public function dashboard(): void
    {
        $this->render('dashboard', [
            'stats' => [
                'members'  => (int)Database::value("SELECT COUNT(*) FROM users WHERE role = 'member'"),
                'enrolled' => (int)Database::value("SELECT COUNT(*) FROM users WHERE enrolled_at IS NOT NULL AND role = 'member'"),
                'revenue'  => (float)Database::value("SELECT COALESCE(SUM(amount), 0) FROM orders WHERE status = 'paid'"),
                'pending'  => (int)Database::value("SELECT COUNT(*) FROM orders WHERE status = 'pending' AND method = 'havale' AND havale_sender IS NOT NULL"),
                'messages' => (int)Database::value('SELECT COUNT(*) FROM messages WHERE is_read = 0'),
            ],
            'next'    => Access::nextLesson(),
            'orders'  => Database::all('SELECT o.*, u.name, u.email FROM orders o JOIN users u ON u.id = o.user_id ORDER BY o.id DESC LIMIT 8'),
            'users'   => Database::all('SELECT * FROM users ORDER BY id DESC LIMIT 8'),
            'checks'  => [
                'SMTP'     => setting('smtp_host') !== '',
                'Stripe'   => Settings::bool('pay_stripe_enabled') && setting('stripe_secret_key') !== '',
                'Webhook'  => setting('stripe_webhook_secret') !== '',
                'Havale'   => Settings::bool('pay_havale_enabled'),
                'Telegram' => Telegram::enabled(),
                'Arama motoru indeksleme' => Settings::bool('seo_index'),
            ],
        ]);
    }

    /* ------------------------------------------------------------------ */
    /* Genel içerik yönetimi (dersler, örnekler, referanslar, SSS, sayfalar) */
    /* ------------------------------------------------------------------ */

    public static function resources(): array
    {
        return [
            'dersler' => [
                'table' => 'lessons', 'title' => 'Canlı Dersler', 'singular' => 'Ders', 'icon' => 'film',
                'order' => 'scheduled_at ASC, sort ASC',
                'list'  => ['title' => 'Başlık', 'scheduled_at' => 'Yayın tarihi', 'is_published' => 'Yayında', '_state' => 'Durum'],
                'intro' => 'Her ders yayın tarihinde üyelere canlı olarak açılır; bittikten sonra tekrar kaydı yayın tarihinden itibaren ' . Access::replayDays() . ' gün izlenebilir.',
                'fields' => [
                    'title'        => ['label' => 'Ders başlığı', 'type' => 'text', 'required' => true],
                    'summary'      => ['label' => 'Kısa özet (müfredatta görünür)', 'type' => 'textarea'],
                    'scheduled_at' => ['label' => 'Yayın tarihi ve saati', 'type' => 'datetime', 'required' => true],
                    'duration_min' => ['label' => 'Süre (dakika)', 'type' => 'number', 'default' => '120'],
                    'live_embed'   => ['label' => 'Canlı yayın bağlantısı veya iframe kodu', 'type' => 'embed', 'help' => 'YouTube (liste dışı) canlı yayın, Vimeo Event, Zoom web, OBS/HLS (.m3u8) bağlantısı veya hazır <iframe> kodu. Yalnızca kayıtlı üyeler görür.'],
                    'replay_embed' => ['label' => 'Tekrar (kayıt) bağlantısı veya iframe kodu', 'type' => 'embed', 'help' => 'Yayın bitince üyelere tekrar olarak gösterilir. YouTube liste dışı / Vimeo özel video önerilir.'],
                    'description'  => ['label' => 'Ders açıklaması (üye panelinde)', 'type' => 'html'],
                    'resources'    => ['label' => 'Kaynaklar (her satıra: Başlık | https://bağlantı)', 'type' => 'textarea'],
                    'sort'         => ['label' => 'Sıra', 'type' => 'number', 'default' => '0'],
                    'is_published' => ['label' => 'Yayında (müfredatta göster)', 'type' => 'checkbox', 'default' => '1'],
                ],
            ],
            'ornekler' => [
                'table' => 'examples', 'title' => 'Örnek Projeler', 'singular' => 'Örnek', 'icon' => 'gamepad',
                'order' => 'sort ASC, id ASC',
                'list'  => ['image' => 'Görsel', 'title' => 'Başlık', 'category' => 'Etiket', 'sort' => 'Sıra'],
                'fields' => [
                    'title'       => ['label' => 'Proje adı', 'type' => 'text', 'required' => true],
                    'category'    => ['label' => 'Etiket (ör. Kampanya Oyunu)', 'type' => 'text'],
                    'description' => ['label' => 'Açıklama', 'type' => 'textarea'],
                    'image'       => ['label' => 'Kapak görseli', 'type' => 'image'],
                    'demo_url'    => ['label' => 'Canlı demo bağlantısı', 'type' => 'url'],
                    'video_url'   => ['label' => 'Video bağlantısı (YouTube / Vimeo)', 'type' => 'url'],
                    'sort'        => ['label' => 'Sıra', 'type' => 'number', 'default' => '0'],
                ],
            ],
            'referanslar' => [
                'table' => 'testimonials', 'title' => 'Referanslar', 'singular' => 'Referans', 'icon' => 'star',
                'order' => 'sort ASC, id ASC',
                'list'  => ['logo' => 'Logo', 'name' => 'Ad', 'company' => 'Kurum', 'sort' => 'Sıra'],
                'fields' => [
                    'name'     => ['label' => 'Ad Soyad', 'type' => 'text', 'required' => true],
                    'position' => ['label' => 'Ünvan', 'type' => 'text'],
                    'company'  => ['label' => 'Kurum / Marka', 'type' => 'text'],
                    'quote'    => ['label' => 'Yorum', 'type' => 'textarea'],
                    'logo'     => ['label' => 'Logo / fotoğraf', 'type' => 'image'],
                    'website'  => ['label' => 'Web sitesi', 'type' => 'url'],
                    'sort'     => ['label' => 'Sıra', 'type' => 'number', 'default' => '0'],
                ],
            ],
            'sss' => [
                'table' => 'faqs', 'title' => 'Sık Sorulan Sorular', 'singular' => 'Soru', 'icon' => 'help',
                'order' => 'sort ASC, id ASC',
                'list'  => ['question' => 'Soru', 'sort' => 'Sıra'],
                'fields' => [
                    'question' => ['label' => 'Soru', 'type' => 'text', 'required' => true],
                    'answer'   => ['label' => 'Cevap', 'type' => 'textarea', 'required' => true],
                    'sort'     => ['label' => 'Sıra', 'type' => 'number', 'default' => '0'],
                ],
            ],
            'sayfalar' => [
                'table' => 'pages', 'title' => 'Sayfalar', 'singular' => 'Sayfa', 'icon' => 'file',
                'order' => 'id ASC',
                'list'  => ['title' => 'Başlık', 'slug' => 'Adres', 'show_footer' => 'Footer'],
                'intro' => '“hakkimizda” adresli sayfa Hakkımızda menüsünde gösterilir. Diğer sayfalar /sayfa/adres şeklinde yayınlanır (KVKK, sözleşmeler vb.).',
                'fields' => [
                    'title'            => ['label' => 'Sayfa başlığı', 'type' => 'text', 'required' => true],
                    'slug'             => ['label' => 'SEO uyumlu adres (boş bırakılırsa başlıktan üretilir)', 'type' => 'slug'],
                    'content'          => ['label' => 'İçerik (HTML)', 'type' => 'html'],
                    'meta_title'       => ['label' => 'SEO başlığı (title)', 'type' => 'text', 'help' => 'Önerilen: 50-60 karakter'],
                    'meta_description' => ['label' => 'SEO açıklaması (meta description)', 'type' => 'textarea', 'help' => 'Önerilen: 140-160 karakter'],
                    'show_footer'      => ['label' => 'Footer bağlantılarında göster', 'type' => 'checkbox'],
                ],
            ],
        ];
    }

    private function resource(string $key): array
    {
        $r = self::resources()[$key] ?? null;
        if ($r === null) {
            abort(404);
        }
        return $r + ['key' => $key];
    }

    public function crudIndex(string $res): void
    {
        $r = $this->resource($res);
        $this->render('crud-index', ['r' => $r, 'rows' => Database::all("SELECT * FROM {$r['table']} ORDER BY {$r['order']}")]);
    }

    public function crudForm(string $res, string $id): void
    {
        $r = $this->resource($res);
        $row = $id === 'yeni' ? null : Database::one("SELECT * FROM {$r['table']} WHERE id = ?", [(int)$id]);
        if ($id !== 'yeni' && $row === null) {
            abort(404);
        }

        if (is_post()) {
            Csrf::verify();
            $data = [];
            try {
                foreach ($r['fields'] as $name => $f) {
                    $data[$name] = $this->fieldValue($name, $f, $row);
                    if (!empty($f['required']) && ($data[$name] === null || $data[$name] === '')) {
                        throw new \RuntimeException($f['label'] . ' alanı zorunludur.');
                    }
                }
                if ($r['table'] === 'pages') {
                    $data['slug'] = $this->uniqueSlug($data['slug'] ?: slugify((string)$data['title']), (int)($row['id'] ?? 0));
                    $data['updated_at'] = now();
                }
            } catch (\RuntimeException $e) {
                flash('error', $e->getMessage());
                redirect('/yonetim/' . $res . '/' . $id);
            }

            if ($row === null) {
                $newId = Database::insert($r['table'], $data);
                flash('success', $r['singular'] . ' eklendi.');
                redirect('/yonetim/' . $res . '/' . $newId);
            }
            Database::update($r['table'], $data, 'id = :id', ['id' => $row['id']]);
            flash('success', 'Değişiklikler kaydedildi.');
            redirect('/yonetim/' . $res . '/' . $row['id']);
        }

        $this->render('crud-form', ['r' => $r, 'row' => $row]);
    }

    private function fieldValue(string $name, array $f, ?array $row): ?string
    {
        $raw = $_POST[$name] ?? null;
        $raw = is_string($raw) ? trim($raw) : null;
        return match ($f['type']) {
            'checkbox' => isset($_POST[$name]) ? '1' : '0',
            'number'   => (string)(int)($raw ?? 0),
            'datetime' => $raw ? date('Y-m-d H:i:s', strtotime($raw) ?: time()) : null,
            'url'      => ($raw && !preg_match('#^https?://#i', $raw)) ? throw new \RuntimeException($f['label'] . ' http(s):// ile başlamalı.') : ($raw ?: ''),
            'slug'     => $raw ? slugify($raw) : '',
            'image'    => $this->imageValue($name, $row),
            default    => $raw ?? '',
        };
    }

    private function imageValue(string $name, ?array $row): string
    {
        if (isset($_POST[$name . '_remove'])) {
            return '';
        }
        $uploaded = Upload::image($name);
        if ($uploaded !== null) {
            return $uploaded;
        }
        $url = trim((string)($_POST[$name . '_url'] ?? ''));
        if ($url !== '' && preg_match('#^(https?://|uploads/)#i', $url)) {
            return $url;
        }
        return (string)($row[$name] ?? '');
    }

    private function uniqueSlug(string $slug, int $ignoreId): string
    {
        $base = $slug;
        $i = 2;
        while ((int)Database::value('SELECT COUNT(*) FROM pages WHERE slug = ? AND id <> ?', [$slug, $ignoreId]) > 0) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }

    public function crudDelete(string $res, string $id): void
    {
        Csrf::verify();
        $r = $this->resource($res);
        if ($r['table'] === 'pages' && Database::value('SELECT slug FROM pages WHERE id = ?', [(int)$id]) === 'hakkimizda') {
            flash('error', 'Hakkımızda sayfası silinemez, yalnızca düzenlenebilir.');
            redirect('/yonetim/' . $res);
        }
        Database::run("DELETE FROM {$r['table']} WHERE id = ?", [(int)$id]);
        flash('success', $r['singular'] . ' silindi.');
        redirect('/yonetim/' . $res);
    }

    /* ------------------------------------------------------------------ */
    /* Üyeler                                                              */
    /* ------------------------------------------------------------------ */

    public function members(): void
    {
        $q = input('q');
        $filter = input('filtre');
        $where = ['1=1'];
        $params = [];
        if ($q !== '') {
            $where[] = '(name LIKE ? OR email LIKE ? OR phone LIKE ?)';
            array_push($params, "%{$q}%", "%{$q}%", "%{$q}%");
        }
        if ($filter === 'kayitli') {
            $where[] = 'enrolled_at IS NOT NULL';
        } elseif ($filter === 'kayitsiz') {
            $where[] = 'enrolled_at IS NULL';
        }
        $this->render('members', [
            'rows'   => Database::all('SELECT * FROM users WHERE ' . implode(' AND ', $where) . ' ORDER BY id DESC LIMIT 500', $params),
            'q'      => $q,
            'filter' => $filter,
        ]);
    }

    public function member(string $id): void
    {
        $user = Database::one('SELECT * FROM users WHERE id = ?', [(int)$id]);
        if ($user === null) {
            abort(404);
        }
        if (is_post()) {
            Csrf::verify();
            $action = input('action');
            $isSelf = (int)$user['id'] === (int)$this->admin['id'];
            switch ($action) {
                case 'grant':
                    Database::run('UPDATE users SET enrolled_at = ? WHERE id = ?', [now(), $user['id']]);
                    flash('success', 'Eğitim erişimi tanımlandı.');
                    break;
                case 'revoke':
                    Database::run('UPDATE users SET enrolled_at = NULL WHERE id = ?', [$user['id']]);
                    flash('success', 'Eğitim erişimi kaldırıldı.');
                    break;
                case 'role':
                    if ($isSelf) {
                        flash('error', 'Kendi yetkinizi değiştiremezsiniz.');
                        break;
                    }
                    Database::run('UPDATE users SET role = ? WHERE id = ?', [$user['role'] === 'admin' ? 'member' : 'admin', $user['id']]);
                    flash('success', 'Kullanıcı yetkisi güncellendi.');
                    break;
                case 'password':
                    $p = (string)($_POST['password'] ?? '');
                    if (mb_strlen($p) < 8) {
                        flash('error', 'Şifre en az 8 karakter olmalı.');
                        break;
                    }
                    Database::run('UPDATE users SET password_hash = ? WHERE id = ?', [password_hash($p, PASSWORD_DEFAULT), $user['id']]);
                    flash('success', 'Şifre güncellendi.');
                    break;
                case 'delete':
                    if ($isSelf) {
                        flash('error', 'Kendi hesabınızı silemezsiniz.');
                        break;
                    }
                    Database::run('DELETE FROM users WHERE id = ?', [$user['id']]);
                    flash('success', 'Üye silindi.');
                    redirect('/yonetim/uyeler');
            }
            redirect('/yonetim/uyeler/' . $user['id']);
        }
        $this->render('member', [
            'user'   => $user,
            'orders' => Database::all('SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC', [$user['id']]),
        ]);
    }

    public function exportMembers(): void
    {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="uyeler-' . date('Ymd') . '.csv"');
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, ['ID', 'Ad', 'E-posta', 'Telefon', 'Kayıt tarihi', 'Eğitim erişimi'], ';', '"', '');
        foreach (Database::all('SELECT * FROM users ORDER BY id') as $u) {
            fputcsv($out, [$u['id'], $u['name'], $u['email'], $u['phone'], $u['created_at'], $u['enrolled_at'] ?? ''], ';', '"', '');
        }
        fclose($out);
    }

    /* ------------------------------------------------------------------ */
    /* Siparişler                                                          */
    /* ------------------------------------------------------------------ */

    public function orders(): void
    {
        $status = input('durum');
        $where = '1=1';
        $params = [];
        if (in_array($status, ['pending', 'paid', 'cancelled'], true)) {
            $where = 'o.status = ?';
            $params[] = $status;
        }
        $this->render('orders', [
            'rows'   => Database::all("SELECT o.*, u.name, u.email FROM orders o JOIN users u ON u.id = o.user_id WHERE {$where} ORDER BY (o.status = 'pending' AND o.havale_sender IS NOT NULL) DESC, o.id DESC LIMIT 500", $params),
            'status' => $status,
        ]);
    }

    public function orderAction(string $id): void
    {
        Csrf::verify();
        $order = Database::one('SELECT * FROM orders WHERE id = ?', [(int)$id]);
        if ($order === null) {
            abort(404);
        }
        $action = input('action');
        if ($action === 'approve') {
            Payments::markPaid((int)$order['id'], 'manuel-onay:' . $this->admin['email']);
            flash('success', "#{$order['id']} onaylandı, üyenin erişimi açıldı ve e-posta gönderildi.");
        } elseif ($action === 'cancel') {
            Database::run("UPDATE orders SET status = 'cancelled' WHERE id = ?", [$order['id']]);
            flash('success', "#{$order['id']} iptal edildi.");
        } elseif ($action === 'note') {
            Database::run('UPDATE orders SET admin_note = ? WHERE id = ?', [mb_substr(input('admin_note'), 0, 500), $order['id']]);
            flash('success', 'Not kaydedildi.');
        }
        redirect('/yonetim/siparisler');
    }

    /* ------------------------------------------------------------------ */
    /* Mesajlar                                                            */
    /* ------------------------------------------------------------------ */

    public function messages(): void
    {
        $this->render('messages', ['rows' => Database::all('SELECT * FROM messages ORDER BY id DESC LIMIT 500')]);
    }

    public function message(string $id): void
    {
        $msg = Database::one('SELECT * FROM messages WHERE id = ?', [(int)$id]);
        if ($msg === null) {
            abort(404);
        }
        if (is_post()) {
            Csrf::verify();
            Database::run('DELETE FROM messages WHERE id = ?', [$msg['id']]);
            flash('success', 'Mesaj silindi.');
            redirect('/yonetim/mesajlar');
        }
        Database::run('UPDATE messages SET is_read = 1 WHERE id = ?', [$msg['id']]);
        $this->render('message', ['msg' => $msg]);
    }

    /* ------------------------------------------------------------------ */
    /* Ayarlar                                                             */
    /* ------------------------------------------------------------------ */

    public static function settingTabs(): array
    {
        return [
            'genel' => ['title' => 'Genel', 'icon' => 'settings', 'fields' => [
                'site_name'        => ['Site adı', 'text'],
                'site_tagline'     => ['Slogan', 'text'],
                'site_logo'        => ['Logo (boşsa site adı yazılır)', 'image'],
                'site_favicon'     => ['Favicon', 'image'],
                'announcement'     => ['Üst duyuru şeridi (boş = gizli)', 'text'],
                'footer_text'      => ['Footer açıklaması', 'textarea'],
                '_h1'              => ['İletişim bilgileri', 'heading'],
                'contact_email'    => ['E-posta', 'text'],
                'contact_phone'    => ['Telefon', 'text'],
                'contact_whatsapp' => ['WhatsApp numarası (ör. 905000000000)', 'text'],
                'contact_address'  => ['Adres', 'textarea'],
                'contact_hours'    => ['Çalışma saatleri', 'text'],
                'contact_map'      => ['Google Maps embed (iframe src bağlantısı)', 'text'],
                '_h2'              => ['Sosyal medya', 'heading'],
                'social_instagram' => ['Instagram URL', 'text'],
                'social_youtube'   => ['YouTube URL', 'text'],
                'social_linkedin'  => ['LinkedIn URL', 'text'],
                'social_x'         => ['X (Twitter) URL', 'text'],
                'social_tiktok'    => ['TikTok URL', 'text'],
            ]],
            'egitim' => ['title' => 'Eğitim & Fiyat', 'icon' => 'cap', 'fields' => [
                'course_title'       => ['Eğitim adı', 'text'],
                'course_subtitle'    => ['Alt başlık', 'textarea'],
                'course_description' => ['Detaylı açıklama (HTML)', 'html'],
                'course_price'       => ['Fiyat', 'text'],
                'course_old_price'   => ['Üstü çizili eski fiyat (boş = gizli)', 'text'],
                'course_currency'    => ['Para birimi', 'select', ['TRY' => 'TRY ₺', 'USD' => 'USD $', 'EUR' => 'EUR €', 'GBP' => 'GBP £']],
                'course_cta'         => ['Satın al butonu yazısı', 'text'],
                'course_duration'    => ['Süre bilgisi (ör. 8 hafta · 16 canlı ders)', 'text'],
                'course_level'       => ['Seviye', 'text'],
                'course_outcomes'    => ['Neler öğreneceksin? (her satır bir madde)', 'textarea'],
                'course_audience'    => ['Kimler için? (her satır bir madde)', 'textarea'],
                'course_includes'    => ['Fiyata dahil olanlar (her satır bir madde)', 'textarea'],
                'course_preview'     => ['Tanıtım videosu (YouTube/Vimeo bağlantısı)', 'text'],
                'course_image'       => ['Eğitim görseli (paylaşım görseli)', 'image'],
                '_h1'                => ['Yayın kuralları', 'heading'],
                'replay_days'        => ['Tekrar izleme süresi (gün, yayın tarihinden itibaren)', 'number'],
                'live_open_minutes'  => ['Canlı yayın ekranı kaç dk önce açılsın?', 'number'],
            ]],
            'anasayfa' => ['title' => 'Ana Sayfa', 'icon' => 'home', 'fields' => [
                'home_hero_kicker' => ['Hero üst etiketi', 'text'],
                'home_hero_title'  => ['Hero başlığı (*kelime* vurgulanır)', 'text'],
                'home_hero_text'   => ['Hero açıklaması', 'textarea'],
                '_hv'              => ['Hero tanıtım videosu', 'heading'],
                'home_hero_video'  => ['Tanıtım videosu bağlantısı', 'text', null, 'YouTube, Vimeo ya da doğrudan .mp4/.webm bağlantısı (ör. https://site.com/tanitim.mp4 veya uploads/tanitim.mp4). Boş bırakılırsa Eğitim ayarlarındaki tanıtım videosu kullanılır; o da boşsa canlı ders görseli gösterilir.'],
                'home_hero_poster' => ['Video kapak görseli (16:10 önerilir)', 'image', null, 'YouTube videolarında boş bırakılırsa videonun kendi kapağı kullanılır.'],
                'home_hero_video_label' => ['Oynat butonu yazısı', 'text'],
                'home_stats'       => ['İstatistikler (her satır: değer|etiket)', 'textarea'],
                'home_marquee'     => ['Kayan teknoloji şeridi ( · ile ayırın)', 'textarea'],
            ]],
            'smtp' => ['title' => 'SMTP / E-posta', 'icon' => 'mail', 'test' => 'smtp', 'fields' => [
                'smtp_host'          => ['SMTP sunucusu (ör. smtp.yandex.com.tr)', 'text'],
                'smtp_port'          => ['Port', 'number'],
                'smtp_secure'        => ['Güvenlik', 'select', ['tls' => 'STARTTLS (587)', 'ssl' => 'SSL (465)', 'none' => 'Yok (25)']],
                'smtp_user'          => ['Kullanıcı adı', 'text'],
                'smtp_pass'          => ['Şifre', 'password'],
                'smtp_from_email'    => ['Gönderen e-posta', 'text'],
                'smtp_from_name'     => ['Gönderen adı', 'text'],
                '_h1'                => ['Yönetici bildirimleri', 'heading'],
                'admin_notify_email' => ['Bildirim e-postası', 'text'],
                'mail_admin_notify'  => ['Havale, ödeme ve iletişim mesajlarında yöneticiye e-posta gönder', 'checkbox'],
            ]],
            'odeme' => ['title' => 'Ödeme', 'icon' => 'card', 'fields' => [
                '_h1'                   => ['Stripe (kredi / banka kartı)', 'heading'],
                'pay_stripe_enabled'    => ['Stripe ile ödemeyi aç', 'checkbox'],
                'stripe_public_key'     => ['Publishable key (pk_...)', 'text'],
                'stripe_secret_key'     => ['Secret key (sk_...)', 'password'],
                'stripe_webhook_secret' => ['Webhook signing secret (whsec_...)', 'password', null, 'Stripe Dashboard → Developers → Webhooks → Endpoint: ' . full_url('stripe/webhook') . ' · Olay: checkout.session.completed'],
                '_h2'                   => ['Havale / EFT', 'heading'],
                'pay_havale_enabled'    => ['Havale / EFT ile ödemeyi aç', 'checkbox'],
                'havale_bank'           => ['Banka adı', 'text'],
                'havale_holder'         => ['Hesap sahibi', 'text'],
                'havale_iban'           => ['IBAN', 'text'],
                'havale_info'           => ['Ek açıklama', 'textarea'],
            ]],
            'telegram' => ['title' => 'Telegram', 'icon' => 'send', 'test' => 'telegram', 'fields' => [
                'tg_enabled'     => ['Telegram uyarılarını aç', 'checkbox'],
                'tg_bot_token'   => ['Bot token (@BotFather)', 'password'],
                'tg_chat_id'     => ['Chat ID (birden fazla için virgülle ayırın)', 'text', null, 'Bota /start yazın, ardından https://api.telegram.org/bot<TOKEN>/getUpdates adresinden chat.id değerini alın. Grup için botu gruba ekleyin (ID “-” ile başlar).'],
                '_h1'            => ['Hangi olaylarda uyarı gelsin?', 'heading'],
                'tg_on_register' => ['Yeni üye kaydı', 'checkbox'],
                'tg_on_order'    => ['Yeni sipariş', 'checkbox'],
                'tg_on_havale'   => ['Havale bildirimi', 'checkbox'],
                'tg_on_payment'  => ['Ödeme onaylandı', 'checkbox'],
                'tg_on_contact'  => ['İletişim formu mesajı', 'checkbox'],
            ]],
            'seo' => ['title' => 'SEO', 'icon' => 'search', 'fields' => [
                'seo_index'         => ['Arama motorlarının siteyi indekslemesine izin ver', 'checkbox'],
                'seo_site_title'    => ['Site başlığı (title sonu)', 'text'],
                'seo_separator'     => ['Başlık ayırıcı', 'text'],
                'seo_home_title'    => ['Ana sayfa başlığı', 'text', null, 'Önerilen: 50-60 karakter'],
                'seo_description'   => ['Varsayılan meta açıklama', 'textarea', null, 'Önerilen: 140-160 karakter'],
                'seo_keywords'      => ['Anahtar kelimeler (virgülle)', 'textarea'],
                'seo_og_image'      => ['Varsayılan paylaşım görseli (1200×630)', 'image'],
                'social_twitter_handle' => ['X kullanıcı adı (@hesap)', 'text'],
                '_h1'               => ['Doğrulama & analiz', 'heading'],
                'seo_google_verify' => ['Google Search Console doğrulama kodu', 'text'],
                'seo_bing_verify'   => ['Bing Webmaster doğrulama kodu', 'text'],
                'seo_yandex_verify' => ['Yandex doğrulama kodu', 'text'],
                'seo_ga_id'         => ['Google Analytics 4 ölçüm kimliği (G-XXXX)', 'text'],
                'seo_gtm_id'        => ['Google Tag Manager (GTM-XXXX)', 'text'],
                'seo_meta_pixel'    => ['Meta (Facebook) Pixel ID', 'text'],
                '_h2'               => ['Gelişmiş', 'heading'],
                'seo_robots_extra'  => ['robots.txt ek kurallar', 'textarea'],
                'seo_head_code'     => ['<head> içine özel kod', 'code'],
                'seo_body_code'     => ['</body> öncesine özel kod', 'code'],
            ]],
        ];
    }

    public function settings(string $tab = 'genel'): void
    {
        $tabs = self::settingTabs();
        if (!isset($tabs[$tab])) {
            abort(404);
        }
        if (is_post()) {
            Csrf::verify();
            $values = [];
            try {
                foreach ($tabs[$tab]['fields'] as $key => $def) {
                    $type = $def[1];
                    if ($type === 'heading') {
                        continue;
                    }
                    if ($type === 'checkbox') {
                        $values[$key] = isset($_POST[$key]) ? '1' : '0';
                    } elseif ($type === 'image') {
                        $values[$key] = $this->imageValue($key, [$key => setting($key)]);
                    } elseif ($type === 'password') {
                        $v = (string)($_POST[$key] ?? '');
                        if ($v !== '') { // boş bırakılırsa mevcut değer korunur
                            $values[$key] = trim($v);
                        }
                    } else {
                        $values[$key] = trim((string)($_POST[$key] ?? ''));
                    }
                }
            } catch (\RuntimeException $e) {
                flash('error', $e->getMessage());
                redirect('/yonetim/ayarlar/' . $tab);
            }
            Settings::setMany($values);
            flash('success', 'Ayarlar kaydedildi.');
            redirect('/yonetim/ayarlar/' . $tab);
        }
        $this->render('settings', ['tabs' => $tabs, 'tab' => $tab]);
    }

    public function testSmtp(): void
    {
        Csrf::verify();
        $to = input('test_email') ?: $this->admin['email'];
        $result = Mailer::test($to);
        flash($result === true ? 'success' : 'error', $result === true ? "Test e-postası {$to} adresine gönderildi." : 'SMTP hatası: ' . $result);
        redirect('/yonetim/ayarlar/smtp');
    }

    public function testTelegram(): void
    {
        Csrf::verify();
        $result = Telegram::send('✅ <b>' . e((string)setting('site_name')) . "</b>\nTelegram uyarı entegrasyonu çalışıyor.", true);
        flash($result === true ? 'success' : 'error', $result === true ? 'Test mesajı Telegram\'a gönderildi.' : (string)$result);
        redirect('/yonetim/ayarlar/telegram');
    }
}
