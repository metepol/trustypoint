<?php
declare(strict_types=1);

/**
 * Uygulama başlangıç dosyası.
 * PHP 8.5+ hedeflenmiştir (bkz. composer.json / install.php gereksinim kontrolü).
 */

const APP_MIN_PHP = '8.5.0';
const APP_VERSION = '1.0.0';

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', __DIR__);
define('STORAGE_PATH', BASE_PATH . '/storage');
define('UPLOAD_PATH', BASE_PATH . '/uploads');

spl_autoload_register(static function (string $class): void {
    if (!str_starts_with($class, 'App\\')) {
        return;
    }
    $file = APP_PATH . '/src/' . str_replace('\\', '/', substr($class, 4)) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

require APP_PATH . '/helpers.php';

$configFile = APP_PATH . '/config.php';
if (!is_file($configFile)) {
    $script = basename($_SERVER['SCRIPT_NAME'] ?? '');
    if ($script !== 'install.php' && PHP_SAPI !== 'cli') {
        header('Location: ' . rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/\\') . '/install.php');
        exit;
    }
    $GLOBALS['app_config'] = [];
    return;
}

$GLOBALS['app_config'] = require $configFile;
$cfg = $GLOBALS['app_config'];

date_default_timezone_set($cfg['timezone'] ?? 'Europe/Istanbul');
mb_internal_encoding('UTF-8');

$debug = (bool)($cfg['debug'] ?? false);
error_reporting(E_ALL);
ini_set('display_errors', $debug ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', STORAGE_PATH . '/logs/php-error.log');

App\Database::connect($cfg['db']);

if (PHP_SAPI !== 'cli') {
    $lifetime = 60 * 60 * 24 * 30; // 30 gün: üyeler sık sık giriş yapmak zorunda kalmasın
    ini_set('session.gc_maxlifetime', (string)$lifetime);
    ini_set('session.use_strict_mode', '1');
    ini_set('session.save_path', STORAGE_PATH . '/sessions');
    if (!is_dir(STORAGE_PATH . '/sessions')) {
        @mkdir(STORAGE_PATH . '/sessions', 0700, true);
    }
    session_name('tp_sess');
    session_set_cookie_params([
        'lifetime' => $lifetime,
        'path'     => '/',
        'secure'   => is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();

    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}
