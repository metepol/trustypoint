<?php
declare(strict_types=1);

namespace App;

/** Olay bazlı Telegram uyarıları ve e-posta bildirimleri. */
final class Notifier
{
    private static function tg(string $event, string $html): void
    {
        if (Telegram::enabled() && Settings::bool('tg_on_' . $event)) {
            Telegram::send($html);
        }
    }

    private static function adminMail(string $subject, string $html): void
    {
        $to = (string)setting('admin_notify_email', '') ?: (string)setting('contact_email', '');
        if ($to !== '' && Settings::bool('mail_admin_notify')) {
            Mailer::template($to, $subject, $subject, $html);
        }
    }

    private static function site(): string
    {
        return e((string)setting('site_name', 'Akademi'));
    }

    public static function userRegistered(array $user): void
    {
        self::tg('register', "🆕 <b>Yeni üye</b> — " . self::site() . "\n👤 " . e($user['name']) . "\n✉️ " . e($user['email']));
        Mailer::template(
            $user['email'],
            'Aramıza hoş geldin, ' . $user['name'] . '!',
            'Hoş geldin ' . $user['name'] . ' 👋',
            '<p>Hesabın oluşturuldu. Eğitime kaydolduğunda canlı yayınlar ve 1 yıllık tekrar erişimi üye panelinde açılacak.</p>',
            'Üye paneline git',
            full_url('panel')
        );
    }

    public static function orderCreated(array $order, array $user): void
    {
        $method = $order['method'] === 'stripe' ? 'Kredi kartı (Stripe)' : 'Havale / EFT';
        self::tg('order', "🛒 <b>Yeni sipariş #{$order['id']}</b>\n👤 " . e($user['name']) . ' (' . e($user['email']) . ")\n💳 {$method}\n💰 " . e(money($order['amount'], $order['currency'])));
    }

    public static function havaleNotified(array $order, array $user): void
    {
        $text = "🏦 <b>Havale bildirimi #{$order['id']}</b>\n👤 " . e($user['name']) . ' (' . e($user['email']) . ")\n"
            . '💰 ' . e(money($order['amount'], $order['currency'])) . "\n"
            . '🧾 Gönderen: ' . e($order['havale_sender']) . "\n"
            . ($order['havale_note'] ? '📝 ' . e($order['havale_note']) . "\n" : '')
            . '➡️ Onay: ' . e(full_url('yonetim/siparisler'));
        self::tg('havale', $text);
        self::adminMail('Yeni havale bildirimi #' . $order['id'], nl2br(strip_tags($text)));
        Mailer::template(
            $user['email'],
            'Havale bildiriminiz alındı',
            'Bildiriminiz bize ulaştı',
            '<p>Merhaba ' . e($user['name']) . ', <b>#' . (int)$order['id'] . '</b> numaralı siparişiniz için havale bildiriminizi aldık. Ödemeniz kontrol edildikten sonra erişiminiz açılacak ve size e-posta ile haber vereceğiz.</p>',
            'Siparişlerim',
            full_url('panel/siparisler')
        );
    }

    public static function paymentReceived(array $order, array $user): void
    {
        self::tg('payment', "✅ <b>Ödeme alındı #{$order['id']}</b>\n👤 " . e($user['name']) . ' (' . e($user['email']) . ")\n💰 " . e(money($order['amount'], $order['currency'])) . "\n💳 " . ($order['method'] === 'stripe' ? 'Stripe' : 'Havale'));
        self::adminMail('Ödeme alındı #' . $order['id'], '<p>' . e($user['name']) . ' (' . e($user['email']) . ') ödemesini tamamladı: ' . e(money($order['amount'], $order['currency'])) . '</p>');
        Mailer::template(
            $user['email'],
            'Kaydın tamamlandı — ' . setting('course_title', 'Eğitim'),
            'Kaydın tamamlandı 🚀',
            '<p>Merhaba ' . e($user['name']) . ', ödemen onaylandı ve eğitime erişimin açıldı.</p><p>Canlı yayınları yayın tarihinde üye panelinden izleyebilir, her yayının tekrarına <b>' . (int)setting('replay_days', '365') . ' gün</b> boyunca erişebilirsin.</p>',
            'Derslerime git',
            full_url('panel')
        );
    }

    public static function contactMessage(array $msg): void
    {
        $text = "📩 <b>Yeni iletişim mesajı</b>\n👤 " . e($msg['name']) . ' (' . e($msg['email']) . ")\n📌 " . e($msg['subject']) . "\n\n" . e(mb_substr($msg['body'], 0, 1500));
        self::tg('contact', $text);
        self::adminMail('İletişim: ' . $msg['subject'], '<p><b>' . e($msg['name']) . '</b> (' . e($msg['email']) . ')</p><p>' . nl2br(e($msg['body'])) . '</p>');
    }
}
