<?php
declare(strict_types=1);

// PHP yerleşik sunucusu (php -S) için statik dosyaları doğrudan sun
if (PHP_SAPI === 'cli-server') {
    $file = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (is_file($file) && !preg_match('#^/(app|storage)/#', (string)parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
        return false;
    }
}

require __DIR__ . '/app/bootstrap.php';

use App\Controllers\AdminController as A;
use App\Controllers\AuthController as Au;
use App\Controllers\CheckoutController as C;
use App\Controllers\MemberController as M;
use App\Controllers\SiteController as S;
use App\Router;

// Eski form verisini bir sonraki istekte kullanılabilir yap
$_SESSION['_old_current'] = $_SESSION['_old'] ?? [];
unset($_SESSION['_old']);

$r = new Router();

// Genel sayfalar
$r->get('/', [S::class, 'home']);
$r->get('/hakkimizda', [S::class, 'about']);
$r->get('/egitim', [S::class, 'course']);
$r->get('/ornekler', [S::class, 'examples']);
$r->get('/referanslar', [S::class, 'references']);
$r->any('/iletisim', [S::class, 'contact']);
$r->get('/sayfa/{slug}', [S::class, 'staticPage']);
$r->get('/sitemap.xml', [S::class, 'sitemap']);
$r->get('/robots.txt', [S::class, 'robots']);

// Üyelik
$r->any('/giris', [Au::class, 'login']);
$r->post('/giris/baglanti', [Au::class, 'magicRequest']);
$r->get('/giris/baglanti/{token}', [Au::class, 'magicLogin']);
$r->any('/kayit', [Au::class, 'register']);
$r->post('/cikis', [Au::class, 'logout']);
$r->any('/sifremi-unuttum', [Au::class, 'forgot']);
$r->any('/sifre-sifirla/{token}', [Au::class, 'reset']);

// Ödeme
$r->get('/odeme', [C::class, 'index']);
$r->post('/odeme/stripe', [C::class, 'stripe']);
$r->get('/odeme/basarili', [C::class, 'success']);
$r->any('/odeme/havale', [C::class, 'havale']);
$r->post('/stripe/webhook', [C::class, 'webhook']);

// Üye paneli
$r->get('/panel', [M::class, 'dashboard']);
$r->get('/panel/ders/{id}', [M::class, 'lesson']);
$r->get('/panel/siparisler', [M::class, 'orders']);
$r->any('/panel/profil', [M::class, 'profile']);

// Yönetim paneli
$r->get('/yonetim', [A::class, 'dashboard']);
$r->get('/yonetim/uyeler', [A::class, 'members']);
$r->get('/yonetim/uyeler/disa-aktar', [A::class, 'exportMembers']);
$r->any('/yonetim/uyeler/{id}', [A::class, 'member']);
$r->get('/yonetim/siparisler', [A::class, 'orders']);
$r->post('/yonetim/siparisler/{id}', [A::class, 'orderAction']);
$r->get('/yonetim/mesajlar', [A::class, 'messages']);
$r->any('/yonetim/mesajlar/{id}', [A::class, 'message']);
$r->post('/yonetim/ayarlar/smtp/test', [A::class, 'testSmtp']);
$r->post('/yonetim/ayarlar/telegram/test', [A::class, 'testTelegram']);
$r->any('/yonetim/ayarlar', [A::class, 'settings']);
$r->any('/yonetim/ayarlar/{tab}', [A::class, 'settings']);
$r->get('/yonetim/{res}', [A::class, 'crudIndex']);
$r->any('/yonetim/{res}/{id}', [A::class, 'crudForm']);
$r->post('/yonetim/{res}/{id}/sil', [A::class, 'crudDelete']);

try {
    $r->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
} catch (Throwable $e) {
    log_message('app', $e::class . ': ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    if (config('debug')) {
        throw $e;
    }
    abort(500, 'Beklenmeyen bir hata oluştu. Lütfen daha sonra tekrar deneyin.');
}
