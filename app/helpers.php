<?php
declare(strict_types=1);

use App\Settings;
use App\Csrf;

function config(string $key, mixed $default = null): mixed
{
    $value = $GLOBALS['app_config'] ?? [];
    foreach (explode('.', $key) as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) {
            return $default;
        }
        $value = $value[$part];
    }
    return $value;
}

function e(mixed $value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function setting(string $key, mixed $default = ''): mixed
{
    return Settings::get($key, $default);
}

function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'
        || (int)($_SERVER['SERVER_PORT'] ?? 0) === 443;
}

function base_url(): string
{
    $url = rtrim((string)config('base_url', ''), '/');
    if ($url !== '') {
        return $url;
    }
    $scheme = is_https() ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    return $scheme . '://' . $host . $dir;
}

/** Uygulamanın alt klasörde çalışabilmesi için yol öneki. */
function base_path(): string
{
    return rtrim((string)parse_url(base_url(), PHP_URL_PATH), '/');
}

function url(string $path = ''): string
{
    return base_path() . '/' . ltrim($path, '/');
}

function full_url(string $path = ''): string
{
    return base_url() . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    $file = BASE_PATH . '/' . ltrim($path, '/');
    $v = is_file($file) ? (string)filemtime($file) : APP_VERSION;
    return url($path) . '?v=' . $v;
}

function media(?string $path): string
{
    if ($path === null || $path === '') {
        return '';
    }
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }
    return url($path);
}

function redirect(string $to, int $code = 302): never
{
    if (!preg_match('#^https?://#i', $to)) {
        $to = url($to);
    }
    header('Location: ' . $to, true, $code);
    exit;
}

function back(): never
{
    $ref = $_SERVER['HTTP_REFERER'] ?? '';
    $host = parse_url(base_url(), PHP_URL_HOST);
    if ($ref !== '' && parse_url($ref, PHP_URL_HOST) === $host) {
        header('Location: ' . $ref);
        exit;
    }
    redirect('/');
}

/** Açık yönlendirme açığını önlemek için yalnızca site içi yollar. */
function safe_next(?string $next, string $default = '/panel'): string
{
    $next = (string)$next;
    if ($next === '' || !str_starts_with($next, '/') || str_starts_with($next, '//') || str_contains($next, '\\')) {
        return $default;
    }
    return $next;
}

function flash(string $type, string $message): void
{
    $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
}

function flashes(): array
{
    $f = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $f;
}

function with_old(array $input): void
{
    unset($input['password'], $input['password_confirm'], $input['_csrf']);
    $_SESSION['_old'] = $input;
}

function old(string $key, string $default = ''): string
{
    return (string)($_SESSION['_old_current'][$key] ?? $default);
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(Csrf::token()) . '">';
}

function input(string $key, string $default = ''): string
{
    $v = $_POST[$key] ?? $_GET[$key] ?? $default;
    return is_string($v) ? trim($v) : $default;
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function client_ip(): string
{
    return (string)($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
}

function now(): string
{
    return date('Y-m-d H:i:s');
}

function money(float|int|string $amount, ?string $currency = null): string
{
    $currency = strtoupper($currency ?? (string)setting('course_currency', 'TRY'));
    $symbols = ['TRY' => '₺', 'USD' => '$', 'EUR' => '€', 'GBP' => '£'];
    $formatted = number_format((float)$amount, 2, ',', '.');
    $formatted = str_ends_with($formatted, ',00') ? substr($formatted, 0, -3) : $formatted;
    $sym = $symbols[$currency] ?? $currency . ' ';
    return $currency === 'TRY' ? $formatted . ' ' . $sym : $sym . $formatted;
}

function tr_date(?string $datetime, bool $withTime = true): string
{
    if (!$datetime) {
        return '-';
    }
    $ts = strtotime($datetime);
    if ($ts === false) {
        return '-';
    }
    $months = ['', 'Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];
    $days = ['Pazar', 'Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi'];
    $out = date('j', $ts) . ' ' . $months[(int)date('n', $ts)] . ' ' . date('Y', $ts) . ', ' . $days[(int)date('w', $ts)];
    return $withTime ? $out . ' · ' . date('H:i', $ts) : $out;
}

function slugify(string $text): string
{
    $map = ['ç' => 'c', 'Ç' => 'c', 'ğ' => 'g', 'Ğ' => 'g', 'ı' => 'i', 'I' => 'i', 'İ' => 'i', 'ö' => 'o', 'Ö' => 'o', 'ş' => 's', 'Ş' => 's', 'ü' => 'u', 'Ü' => 'u'];
    $text = strtr($text, $map);
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
    return trim($text, '-') ?: 'sayfa';
}

function excerpt(?string $html, int $length = 160): string
{
    $text = trim(preg_replace('/\s+/u', ' ', strip_tags((string)$html)) ?? '');
    return mb_strlen($text) > $length ? rtrim(mb_substr($text, 0, $length - 1)) . '…' : $text;
}

function lines(?string $text): array
{
    return array_values(array_filter(array_map('trim', preg_split('/\R/u', (string)$text) ?: [])));
}

function view(string $template, array $data = [], ?string $layout = 'layouts/site'): void
{
    App\View::render($template, $data, $layout);
}

function abort(int $code = 404, string $message = ''): never
{
    http_response_code($code);
    view('site/error', ['code' => $code, 'message' => $message, 'seo' => ['title' => 'Sayfa bulunamadı', 'robots' => 'noindex']]);
    exit;
}

function log_message(string $channel, string $message): void
{
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
    @file_put_contents(STORAGE_PATH . '/logs/' . preg_replace('/[^a-z0-9_-]/i', '', $channel) . '.log', $line, FILE_APPEND | LOCK_EX);
}

function active(string $path): string
{
    $current = App\Router::currentPath();
    $is = $path === '/' ? $current === '/' : str_starts_with($current, $path);
    return $is ? ' is-active' : '';
}
