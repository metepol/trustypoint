<?php
declare(strict_types=1);

namespace App;

/**
 * Bağımlılıksız SMTP istemcisi (SSL / STARTTLS / AUTH LOGIN).
 * SMTP ayarı yoksa PHP mail() fonksiyonuna düşer.
 */
final class Mailer
{
    /** @var resource|null */
    private $socket = null;

    public static function send(string $to, string $subject, string $html, ?string $toName = null): bool
    {
        try {
            (new self())->deliver($to, $subject, $html, $toName);
            return true;
        } catch (\Throwable $e) {
            log_message('mail', 'Gönderilemedi (' . $to . '): ' . $e->getMessage());
            return false;
        }
    }

    /** Yönetim panelindeki test butonu için: hata mesajını döndürür. */
    public static function test(string $to): true|string
    {
        try {
            (new self())->deliver($to, 'SMTP test e-postası', View::capture('emails/layout', [
                'title' => 'SMTP bağlantısı çalışıyor 🎉',
                'body'  => '<p>Bu e-posta, yönetim panelindeki SMTP ayarlarını doğrulamak için gönderildi.</p>',
            ]));
            return true;
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
    }

    /** Şablonlu e-posta gönderimi. */
    public static function template(string $to, string $subject, string $title, string $bodyHtml, ?string $ctaText = null, ?string $ctaUrl = null): bool
    {
        $html = View::capture('emails/layout', [
            'title'   => $title,
            'body'    => $bodyHtml,
            'ctaText' => $ctaText,
            'ctaUrl'  => $ctaUrl,
        ]);
        return self::send($to, $subject, $html);
    }

    private function deliver(string $to, string $subject, string $html, ?string $toName = null): void
    {
        $fromEmail = (string)setting('smtp_from_email', '') ?: (string)setting('contact_email', 'no-reply@' . (parse_url(base_url(), PHP_URL_HOST) ?: 'localhost'));
        $fromName  = (string)setting('smtp_from_name', '') ?: (string)setting('site_name', 'Akademi');

        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Geçersiz alıcı adresi.');
        }

        $boundary = 'b_' . bin2hex(random_bytes(12));
        $text = trim(html_entity_decode(strip_tags(preg_replace('#<br\s*/?>|</p>#i', "\n", $html) ?? ''), ENT_QUOTES, 'UTF-8'));
        $headers = [
            'Date: ' . date('r'),
            'From: ' . self::encodeName($fromName) . ' <' . $fromEmail . '>',
            'To: ' . ($toName ? self::encodeName($toName) . ' <' . $to . '>' : $to),
            'Subject: ' . self::encodeHeader($subject),
            'Message-ID: <' . bin2hex(random_bytes(10)) . '@' . (parse_url(base_url(), PHP_URL_HOST) ?: 'localhost') . '>',
            'MIME-Version: 1.0',
            'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
        ];
        $replyTo = (string)setting('contact_email', '');
        if ($replyTo !== '' && $replyTo !== $fromEmail) {
            $headers[] = 'Reply-To: ' . $replyTo;
        }

        $body = "--{$boundary}\r\n"
            . "Content-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n"
            . chunk_split(base64_encode($text))
            . "--{$boundary}\r\n"
            . "Content-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n"
            . chunk_split(base64_encode($html))
            . "--{$boundary}--\r\n";

        $host = trim((string)setting('smtp_host', ''));
        if ($host === '') {
            // SMTP tanımlı değilse yerel mail() kullanılır
            $mailHeaders = array_filter($headers, static fn($h) => !str_starts_with($h, 'To:') && !str_starts_with($h, 'Subject:'));
            if (!mail($to, self::encodeHeader($subject), $body, implode("\r\n", $mailHeaders))) {
                throw new \RuntimeException('mail() başarısız oldu. Lütfen SMTP ayarlarını yapın.');
            }
            return;
        }

        $port = (int)setting('smtp_port', '587');
        $secure = (string)setting('smtp_secure', 'tls');
        $user = (string)setting('smtp_user', '');
        $pass = (string)setting('smtp_pass', '');

        $remote = ($secure === 'ssl' ? 'ssl://' : 'tcp://') . $host . ':' . $port;
        $context = stream_context_create(['ssl' => ['verify_peer' => true, 'verify_peer_name' => true, 'SNI_enabled' => true, 'peer_name' => $host]]);
        $socket = @stream_socket_client($remote, $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $context);
        if ($socket === false) {
            throw new \RuntimeException("SMTP sunucusuna bağlanılamadı: {$errstr} ({$errno})");
        }
        $this->socket = $socket;
        stream_set_timeout($socket, 20);

        try {
            $this->expect([220]);
            $ehloHost = parse_url(base_url(), PHP_URL_HOST) ?: 'localhost';
            $this->command('EHLO ' . $ehloHost, [250]);
            if ($secure === 'tls') {
                $this->command('STARTTLS', [220]);
                $crypto = STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT;
                if (!stream_socket_enable_crypto($socket, true, $crypto)) {
                    throw new \RuntimeException('STARTTLS şifrelemesi başlatılamadı.');
                }
                $this->command('EHLO ' . $ehloHost, [250]);
            }
            if ($user !== '') {
                $this->command('AUTH LOGIN', [334]);
                $this->command(base64_encode($user), [334]);
                $this->command(base64_encode($pass), [235]);
            }
            $this->command('MAIL FROM:<' . $fromEmail . '>', [250]);
            $this->command('RCPT TO:<' . $to . '>', [250, 251]);
            $this->command('DATA', [354]);
            $message = implode("\r\n", $headers) . "\r\n\r\n" . $body;
            $message = preg_replace('/^\./m', '..', $message) ?? $message; // dot-stuffing
            $this->command($message . "\r\n.", [250]);
            $this->command('QUIT', [221]);
        } finally {
            fclose($socket);
            $this->socket = null;
        }
    }

    private function command(string $cmd, array $codes): string
    {
        fwrite($this->socket, $cmd . "\r\n");
        return $this->expect($codes);
    }

    private function expect(array $codes): string
    {
        $response = '';
        while (($line = fgets($this->socket, 1024)) !== false) {
            $response .= $line;
            if (strlen($line) < 4 || $line[3] === ' ') {
                break;
            }
        }
        $code = (int)substr($response, 0, 3);
        if (!in_array($code, $codes, true)) {
            throw new \RuntimeException('SMTP hatası: ' . trim($response ?: 'yanıt yok'));
        }
        return $response;
    }

    private static function encodeHeader(string $value): string
    {
        return preg_match('/[^\x20-\x7E]/', $value) ? '=?UTF-8?B?' . base64_encode($value) . '?=' : $value;
    }

    private static function encodeName(string $name): string
    {
        $name = str_replace(['"', "\r", "\n"], '', $name);
        return preg_match('/[^\x20-\x7E]/', $name) ? self::encodeHeader($name) : '"' . $name . '"';
    }
}
