<?php
declare(strict_types=1);

namespace App;

final class Telegram
{
    public static function enabled(): bool
    {
        return Settings::bool('tg_enabled') && setting('tg_bot_token') !== '' && setting('tg_chat_id') !== '';
    }

    /** @return true|string Başarılıysa true, değilse hata mesajı */
    public static function send(string $html, bool $force = false): bool|string
    {
        if (!$force && !self::enabled()) {
            return 'Telegram entegrasyonu kapalı.';
        }
        $token = (string)setting('tg_bot_token');
        $chatIds = array_filter(array_map('trim', explode(',', (string)setting('tg_chat_id'))));
        if ($token === '' || $chatIds === []) {
            return 'Bot token veya Chat ID eksik.';
        }
        $error = true;
        foreach ($chatIds as $chatId) {
            $res = Http::request('POST', 'https://api.telegram.org/bot' . $token . '/sendMessage', [
                'chat_id'                  => $chatId,
                'text'                     => $html,
                'parse_mode'               => 'HTML',
                'disable_web_page_preview' => 'true',
            ], [], null, 8);
            if (($res['json']['ok'] ?? false) !== true) {
                $error = 'Telegram hatası: ' . ($res['json']['description'] ?? $res['error'] ?: ('HTTP ' . $res['status']));
                log_message('telegram', $error);
            }
        }
        return $error;
    }
}
