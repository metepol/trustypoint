<?php
declare(strict_types=1);

namespace App;

/** Stripe Checkout (REST API) — SDK gerektirmez. */
final class Stripe
{
    private const API = 'https://api.stripe.com/v1/';

    public static function enabled(): bool
    {
        return Settings::bool('pay_stripe_enabled') && self::secret() !== '';
    }

    private static function secret(): string
    {
        return trim((string)setting('stripe_secret_key', ''));
    }

    private static function call(string $method, string $endpoint, array $fields = []): array
    {
        $res = Http::request($method, self::API . $endpoint, $fields, ['Stripe-Version: 2024-06-20'], self::secret() . ':', 25);
        if ($res['status'] < 200 || $res['status'] >= 300 || $res['json'] === null) {
            $msg = $res['json']['error']['message'] ?? $res['error'] ?: ('HTTP ' . $res['status']);
            throw new \RuntimeException('Stripe: ' . $msg);
        }
        return $res['json'];
    }

    public static function createCheckout(array $order, array $user): array
    {
        $currency = strtolower((string)$order['currency']);
        // Stripe tutarları en küçük birimle ister (kuruş/cent)
        $unitAmount = (int)round((float)$order['amount'] * 100);
        return self::call('POST', 'checkout/sessions', [
            'mode'                 => 'payment',
            'customer_email'       => $user['email'],
            'client_reference_id'  => (string)$order['id'],
            'metadata'             => ['order_id' => (string)$order['id'], 'user_id' => (string)$user['id']],
            'payment_intent_data'  => ['metadata' => ['order_id' => (string)$order['id']]],
            'line_items'           => [[
                'quantity'   => 1,
                'price_data' => [
                    'currency'     => $currency,
                    'unit_amount'  => $unitAmount,
                    'product_data' => [
                        'name'        => (string)setting('course_title', 'Eğitim'),
                        'description' => mb_substr((string)setting('course_subtitle', ''), 0, 250) ?: null,
                    ],
                ],
            ]],
            'locale'      => 'tr',
            'success_url' => full_url('odeme/basarili') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => full_url('odeme') . '?iptal=1',
        ]);
    }

    public static function retrieve(string $sessionId): array
    {
        if (!preg_match('/^cs_[A-Za-z0-9_]+$/', $sessionId)) {
            throw new \InvalidArgumentException('Geçersiz oturum.');
        }
        return self::call('GET', 'checkout/sessions/' . $sessionId);
    }

    /** Stripe-Signature başlığını doğrular. */
    public static function verifyWebhook(string $payload, string $header, int $tolerance = 300): bool
    {
        $secret = trim((string)setting('stripe_webhook_secret', ''));
        if ($secret === '' || $header === '') {
            return false;
        }
        $timestamp = null;
        $signatures = [];
        foreach (explode(',', $header) as $part) {
            [$k, $v] = array_pad(explode('=', trim($part), 2), 2, '');
            if ($k === 't') {
                $timestamp = (int)$v;
            } elseif ($k === 'v1') {
                $signatures[] = $v;
            }
        }
        if ($timestamp === null || abs(time() - $timestamp) > $tolerance) {
            return false;
        }
        $expected = hash_hmac('sha256', $timestamp . '.' . $payload, $secret);
        foreach ($signatures as $sig) {
            if (hash_equals($expected, $sig)) {
                return true;
            }
        }
        return false;
    }
}
