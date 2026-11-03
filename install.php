<?php
declare(strict_types=1);

/**
 * Kurulum sihirbazı. Kurulum tamamlandıktan sonra app/config.php oluşur ve bu dosya kilitlenir.
 * Güvenlik için kurulumdan sonra install.php dosyasını silebilirsiniz.
 */

require __DIR__ . '/app/bootstrap.php';

use App\Database;
use App\Schema;

if (is_file(APP_PATH . '/config.php')) {
    http_response_code(403);
    exit('Kurulum zaten tamamlanmış. Yeniden kurmak için app/config.php dosyasını silin.');
}

session_name('tp_install');
session_start();
if (empty($_SESSION['install_csrf'])) {
    $_SESSION['install_csrf'] = bin2hex(random_bytes(16));
}

$requirements = [
    'PHP ' . APP_MIN_PHP . ' veya üzeri (mevcut: ' . PHP_VERSION . ')' => version_compare(PHP_VERSION, APP_MIN_PHP, '>=') || getenv('TP_ALLOW_OLD_PHP') === '1',
    'PDO eklentisi'                 => extension_loaded('pdo'),
    'PDO MySQL veya SQLite sürücüsü' => extension_loaded('pdo_mysql') || extension_loaded('pdo_sqlite'),
    'cURL eklentisi (Stripe, Telegram)' => extension_loaded('curl'),
    'OpenSSL eklentisi (SMTP TLS)'  => extension_loaded('openssl'),
    'mbstring eklentisi'            => extension_loaded('mbstring'),
    'fileinfo eklentisi (yükleme)'  => extension_loaded('fileinfo'),
    'app/ klasörü yazılabilir'      => is_writable(APP_PATH),
    'storage/ klasörü yazılabilir'  => is_writable(STORAGE_PATH),
    'uploads/ klasörü yazılabilir'  => is_writable(UPLOAD_PATH),
];
$ready = !in_array(false, $requirements, true);
$errors = [];

$guessUrl = (is_https() ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
$v = static fn(string $k, string $d = ''): string => htmlspecialchars((string)($_POST[$k] ?? $d), ENT_QUOTES, 'UTF-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $ready) {
    if (!hash_equals($_SESSION['install_csrf'], (string)($_POST['_csrf'] ?? ''))) {
        $errors[] = 'Oturum doğrulanamadı, sayfayı yenileyin.';
    }
    $driver = ($_POST['driver'] ?? 'mysql') === 'sqlite' ? 'sqlite' : 'mysql';
    $db = $driver === 'sqlite'
        ? ['driver' => 'sqlite', 'path' => STORAGE_PATH . '/database.sqlite']
        : [
            'driver' => 'mysql',
            'host'   => trim((string)($_POST['db_host'] ?? 'localhost')),
            'port'   => (int)($_POST['db_port'] ?? 3306),
            'name'   => trim((string)($_POST['db_name'] ?? '')),
            'user'   => trim((string)($_POST['db_user'] ?? '')),
            'pass'   => (string)($_POST['db_pass'] ?? ''),
        ];
    $baseUrl = rtrim(trim((string)($_POST['base_url'] ?? $guessUrl)), '/');
    $siteName = trim((string)($_POST['site_name'] ?? '')) ?: 'TrustyPoint Akademi';
    $adminName = trim((string)($_POST['admin_name'] ?? ''));
    $adminEmail = mb_strtolower(trim((string)($_POST['admin_email'] ?? '')));
    $adminPass = (string)($_POST['admin_pass'] ?? '');

    if (!filter_var($baseUrl, FILTER_VALIDATE_URL)) {
        $errors[] = 'Site adresi geçerli bir URL olmalı.';
    }
    if (mb_strlen($adminName) < 2 || !filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Yönetici adı ve geçerli bir e-posta girin.';
    }
    if (mb_strlen($adminPass) < 8) {
        $errors[] = 'Yönetici şifresi en az 8 karakter olmalı.';
    }

    if (!$errors) {
        try {
            $GLOBALS['app_config'] = ['base_url' => $baseUrl];
            Database::connect($db);
            Schema::install($driver);
            if ((int)Database::value('SELECT COUNT(*) FROM settings') === 0) {
                Schema::seed($siteName, $adminEmail);
            }
            $existing = Database::one('SELECT id FROM users WHERE email = ?', [$adminEmail]);
            if ($existing) {
                Database::run("UPDATE users SET role = 'admin', password_hash = ?, name = ? WHERE id = ?", [password_hash($adminPass, PASSWORD_DEFAULT), $adminName, $existing['id']]);
            } else {
                Database::insert('users', [
                    'name' => $adminName, 'email' => $adminEmail, 'password_hash' => password_hash($adminPass, PASSWORD_DEFAULT),
                    'role' => 'admin', 'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
            $config = [
                'base_url' => $baseUrl,
                'timezone' => 'Europe/Istanbul',
                'debug'    => false,
                'db'       => $db,
            ];
            $php = "<?php\n// Kurulum sihirbazı tarafından " . date('Y-m-d H:i') . " tarihinde oluşturuldu.\nreturn " . var_export($config, true) . ";\n";
            if (file_put_contents(APP_PATH . '/config.php', $php, LOCK_EX) === false) {
                throw new RuntimeException('app/config.php yazılamadı.');
            }
            @chmod(APP_PATH . '/config.php', 0640);
            $done = true;
        } catch (Throwable $e) {
            $errors[] = 'Kurulum hatası: ' . $e->getMessage();
        }
    }
}
?><!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Kurulum</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-body">
<main class="auth-wrap" style="max-width:720px">
  <div class="auth-card">
    <span class="kicker">Kurulum sihirbazı</span>
    <h1 class="h2">Platformu 1 dakikada kur</h1>
    <?php if (!empty($done)): ?>
      <div class="alert alert-success">Kurulum tamamlandı! Güvenlik için sunucudan <b>install.php</b> dosyasını silin.</div>
      <p><a class="btn btn-primary" href="<?= htmlspecialchars($baseUrl) ?>/giris">Yönetici girişi →</a></p>
    <?php else: ?>
      <ul class="checklist">
        <?php foreach ($requirements as $label => $ok): ?>
          <li class="<?= $ok ? 'ok' : 'fail' ?>"><?= $ok ? '✓' : '✗' ?> <?= htmlspecialchars($label) ?></li>
        <?php endforeach; ?>
      </ul>
      <?php foreach ($errors as $err): ?><div class="alert alert-error"><?= htmlspecialchars($err) ?></div><?php endforeach; ?>
      <?php if ($ready): ?>
      <form method="post" class="form">
        <input type="hidden" name="_csrf" value="<?= $_SESSION['install_csrf'] ?>">
        <h3>Veritabanı</h3>
        <div class="field"><label>Sürücü</label>
          <select name="driver" onchange="document.getElementById('mysql').style.display=this.value==='mysql'?'':'none'">
            <option value="mysql" <?= ($_POST['driver'] ?? '') !== 'sqlite' ? 'selected' : '' ?>>MySQL / MariaDB (önerilen)</option>
            <option value="sqlite" <?= ($_POST['driver'] ?? '') === 'sqlite' ? 'selected' : '' ?>>SQLite (küçük kurulumlar)</option>
          </select></div>
        <div id="mysql" class="grid-2" style="<?= ($_POST['driver'] ?? '') === 'sqlite' ? 'display:none' : '' ?>">
          <div class="field"><label>Sunucu</label><input name="db_host" value="<?= $v('db_host', 'localhost') ?>"></div>
          <div class="field"><label>Port</label><input name="db_port" value="<?= $v('db_port', '3306') ?>"></div>
          <div class="field"><label>Veritabanı adı</label><input name="db_name" value="<?= $v('db_name') ?>"></div>
          <div class="field"><label>Kullanıcı</label><input name="db_user" value="<?= $v('db_user') ?>"></div>
          <div class="field"><label>Şifre</label><input type="password" name="db_pass"></div>
        </div>
        <h3>Site</h3>
        <div class="field"><label>Site adresi</label><input name="base_url" value="<?= $v('base_url', $guessUrl) ?>"></div>
        <div class="field"><label>Site adı</label><input name="site_name" value="<?= $v('site_name', 'TrustyPoint Akademi') ?>"></div>
        <h3>Yönetici hesabı</h3>
        <div class="grid-2">
          <div class="field"><label>Ad Soyad</label><input name="admin_name" value="<?= $v('admin_name') ?>" required></div>
          <div class="field"><label>E-posta</label><input type="email" name="admin_email" value="<?= $v('admin_email') ?>" required></div>
        </div>
        <div class="field"><label>Şifre (en az 8 karakter)</label><input type="password" name="admin_pass" minlength="8" required></div>
        <button class="btn btn-primary btn-block">Kurulumu başlat</button>
      </form>
      <?php else: ?>
        <div class="alert alert-error">Lütfen eksik gereksinimleri giderip sayfayı yenileyin.</div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</main>
</body>
</html>
