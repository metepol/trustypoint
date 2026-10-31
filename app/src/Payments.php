<?php
declare(strict_types=1);

namespace App;

final class Payments
{
    public static function price(): float
    {
        return (float)str_replace(',', '.', (string)setting('course_price', '0'));
    }

    public static function currency(): string
    {
        return strtoupper((string)setting('course_currency', 'TRY'));
    }

    public static function createOrder(array $user, string $method, array $extra = []): array
    {
        $id = Database::insert('orders', [
            'user_id'    => $user['id'],
            'method'     => $method,
            'amount'     => self::price(),
            'currency'   => self::currency(),
            'status'     => 'pending',
            'created_at' => now(),
        ] + $extra);
        return Database::one('SELECT * FROM orders WHERE id = ?', [$id]);
    }

    /** Siparişi ödendi olarak işaretler ve erişimi açar (idempotent). */
    public static function markPaid(int $orderId, ?string $reference = null): bool
    {
        $changed = Database::transaction(static function () use ($orderId, $reference): bool {
            $order = Database::one('SELECT * FROM orders WHERE id = ?', [$orderId]);
            if ($order === null || $order['status'] === 'paid') {
                return false;
            }
            $data = ['status' => 'paid', 'paid_at' => now()];
            if ($reference !== null) {
                $data['payment_ref'] = $reference;
            }
            Database::update('orders', $data, 'id = :id', ['id' => $orderId]);
            Database::run('UPDATE users SET enrolled_at = ? WHERE id = ? AND enrolled_at IS NULL', [now(), $order['user_id']]);
            return true;
        });

        if ($changed) {
            $order = Database::one('SELECT * FROM orders WHERE id = ?', [$orderId]);
            $user = Database::one('SELECT * FROM users WHERE id = ?', [$order['user_id']]);
            Notifier::paymentReceived($order, $user);
        }
        return $changed;
    }

    /** Stripe Checkout oturumunu doğrular ve ödendiyse siparişi tamamlar. */
    public static function syncStripeSession(array $session): bool
    {
        $orderId = (int)($session['metadata']['order_id'] ?? $session['client_reference_id'] ?? 0);
        if ($orderId <= 0 || ($session['payment_status'] ?? '') !== 'paid') {
            return false;
        }
        $order = Database::one('SELECT * FROM orders WHERE id = ?', [$orderId]);
        if ($order === null || $order['stripe_session_id'] !== ($session['id'] ?? null)) {
            return false;
        }
        $expected = (int)round((float)$order['amount'] * 100);
        if ((int)($session['amount_total'] ?? 0) !== $expected || strtoupper((string)($session['currency'] ?? '')) !== strtoupper((string)$order['currency'])) {
            log_message('stripe', "Tutar uyuşmazlığı sipariş #{$orderId}");
            return false;
        }
        self::markPaid($orderId, (string)($session['payment_intent'] ?? $session['id']));
        return true;
    }
}
