<?php
declare(strict_types=1);

namespace App;

/** Harici bağımlılık gerektirmeyen küçük cURL istemcisi. */
final class Http
{
    /**
     * @return array{status:int, body:string, json:?array, error:string}
     */
    public static function request(string $method, string $url, array $fields = [], array $headers = [], ?string $basicAuth = null, int $timeout = 15): array
    {
        $ch = curl_init();
        $opts = [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_CUSTOMREQUEST  => strtoupper($method),
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_USERAGENT      => 'TrustyPoint/' . APP_VERSION,
        ];
        if ($fields !== [] && strtoupper($method) !== 'GET') {
            $opts[CURLOPT_POSTFIELDS] = http_build_query($fields);
        } elseif ($fields !== []) {
            $opts[CURLOPT_URL] = $url . (str_contains($url, '?') ? '&' : '?') . http_build_query($fields);
        }
        if ($basicAuth !== null) {
            $opts[CURLOPT_USERPWD] = $basicAuth;
        }
        curl_setopt_array($ch, $opts);
        $body = curl_exec($ch);
        $error = curl_error($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        // PHP 8.5'te curl_close() kullanımdan kaldırıldı; tutamaç kapsam dışına çıkınca serbest bırakılır.
        unset($ch);

        $body = is_string($body) ? $body : '';
        $json = json_decode($body, true);
        return [
            'status' => $status,
            'body'   => $body,
            'json'   => is_array($json) ? $json : null,
            'error'  => $error,
        ];
    }
}
