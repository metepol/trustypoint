<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Access;
use App\Auth;
use App\Csrf;
use App\Database;
use App\Notifier;
use App\Payments;
use App\Settings;
use App\Stripe;

final class CheckoutController
{
    private const SEO = ['robots' => 'noindex, nofollow'];

    private function guard(): array
    {
        $user = Auth::user();
        if ($user === null) {
            flash('info', 'Kayıt için önce ücretsiz hesabını oluştur — 10 saniye sürer.');
            redirect('/kayit?next=' . rawurlencode('/odeme'));
        }
        if (Access::hasCourse($user)) {
            flash('success', 'Eğitime zaten kayıtlısınız.');
            redirect('/panel');
        }
        return $user;
    }

    public function index(): void
    {
        $user = $this->guard();
        if (isset($_GET['iptal'])) {
            flash('info', 'Ödeme iptal edildi. Dilediğiniz zaman tekrar deneyebilirsiniz.');
        }
        view('site/checkout', [
            'user'   => $user,
            'stripe' => Stripe::enabled(),
            'havale' => Settings::bool('pay_havale_enabled'),
            'seo'    => self::SEO + ['title' => 'Kayıt & Ödeme'],
        ]);
    }

    public function stripe(): void
    {
        Csrf::verify();
        $user = $this->guard();
        if (!Stripe::enabled()) {
            flash('error', 'Kartla ödeme şu anda kullanılamıyor.');
            redirect('/odeme');
        }
        if (!isset($_POST['terms'])) {
            flash('error', 'Mesafeli satış sözleşmesini onaylayın.');
            redirect('/odeme');
        }
        $order = Payments::createOrder($user, 'stripe');
        try {
            $session = Stripe::createCheckout($order, $user);
        } catch (\Throwable $e) {
            log_message('stripe', $e->getMessage());
            Database::run("UPDATE orders SET status = 'cancelled', admin_note = ? WHERE id = ?", [mb_substr($e->getMessage(), 0, 500), $order['id']]);
            flash('error', 'Ödeme sayfası açılamadı. Lütfen daha sonra tekrar deneyin veya havale ile ödeyin.');
            redirect('/odeme');
        }
        Database::run('UPDATE orders SET stripe_session_id = ? WHERE id = ?', [$session['id'], $order['id']]);
        Notifier::orderCreated($order, $user);
        redirect((string)$session['url'], 303);
    }

    public function success(): void
    {
        $user = Auth::requireLogin();
        $sessionId = (string)($_GET['session_id'] ?? '');
        $paid = false;
        try {
            $session = Stripe::retrieve($sessionId);
            $owner = (int)Database::value('SELECT user_id FROM orders WHERE stripe_session_id = ?', [$sessionId]);
            if ($owner === (int)$user['id']) {
                $paid = Payments::syncStripeSession($session) || (($session['payment_status'] ?? '') === 'paid');
            }
        } catch (\Throwable $e) {
            log_message('stripe', 'success: ' . $e->getMessage());
        }
        Auth::refresh();
        view('site/checkout-result', [
            'paid' => $paid,
            'seo'  => self::SEO + ['title' => $paid ? 'Kaydınız tamamlandı' : 'Ödeme kontrol ediliyor'],
        ]);
    }

    public function havale(): void
    {
        $user = $this->guard();
        if (!Settings::bool('pay_havale_enabled')) {
            redirect('/odeme');
        }
        // Kullanıcının açık havale siparişini tekrar kullan (sipariş numarası açıklamaya yazılacak)
        $order = Database::one("SELECT * FROM orders WHERE user_id = ? AND method = 'havale' AND status = 'pending' ORDER BY id DESC", [$user['id']]);

        if (is_post()) {
            Csrf::verify();
            $sender = mb_substr(input('sender'), 0, 190);
            if (mb_strlen($sender) < 3 || !isset($_POST['terms'])) {
                flash('error', 'Gönderen adını yazın ve mesafeli satış sözleşmesini onaylayın.');
                redirect('/odeme/havale');
            }
            $order ??= Payments::createOrder($user, 'havale');
            $isNew = $order['havale_sender'] === null;
            Database::run('UPDATE orders SET havale_sender = ?, havale_note = ? WHERE id = ?', [$sender, mb_substr(input('note'), 0, 500), $order['id']]);
            $order = Database::one('SELECT * FROM orders WHERE id = ?', [$order['id']]);
            if ($isNew) {
                Notifier::orderCreated($order, $user);
            }
            Notifier::havaleNotified($order, $user);
            flash('success', 'Havale bildiriminiz alındı. Ödemeniz onaylanınca erişiminiz açılacak ve e-posta ile bilgilendirileceksiniz.');
            redirect('/panel');
        }

        $order ??= Payments::createOrder($user, 'havale');
        view('site/havale', [
            'user'  => $user,
            'order' => $order,
            'seo'   => self::SEO + ['title' => 'Havale / EFT ile Ödeme'],
        ]);
    }

    public function webhook(): void
    {
        $payload = (string)file_get_contents('php://input');
        if (!Stripe::verifyWebhook($payload, (string)($_SERVER['HTTP_STRIPE_SIGNATURE'] ?? ''))) {
            http_response_code(400);
            echo 'invalid signature';
            return;
        }
        $event = json_decode($payload, true);
        $type = (string)($event['type'] ?? '');
        if (in_array($type, ['checkout.session.completed', 'checkout.session.async_payment_succeeded'], true)) {
            try {
                Payments::syncStripeSession((array)($event['data']['object'] ?? []));
            } catch (\Throwable $e) {
                log_message('stripe', 'webhook: ' . $e->getMessage());
                http_response_code(500);
                return;
            }
        }
        http_response_code(200);
        header('Content-Type: application/json');
        echo '{"received":true}';
    }
}
