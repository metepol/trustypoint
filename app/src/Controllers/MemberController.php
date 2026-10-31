<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Access;
use App\Auth;
use App\Csrf;
use App\Database;

final class MemberController
{
    private const SEO = ['robots' => 'noindex, nofollow'];

    public function dashboard(): void
    {
        $user = Auth::requireLogin();
        $lessons = Database::all('SELECT * FROM lessons WHERE is_published = 1 ORDER BY scheduled_at ASC, sort ASC');
        view('member/dashboard', [
            'user'      => $user,
            'hasCourse' => Access::hasCourse($user),
            'lessons'   => $lessons,
            'next'      => Access::nextLesson(),
            'pending'   => Database::one("SELECT * FROM orders WHERE user_id = ? AND status = 'pending' AND method = 'havale' AND havale_sender IS NOT NULL ORDER BY id DESC", [$user['id']]),
            'seo'       => self::SEO + ['title' => 'Üye Paneli'],
        ], 'layouts/member');
    }

    public function lesson(string $id): void
    {
        $user = Auth::requireLogin();
        if (!Access::hasCourse($user)) {
            flash('info', 'Dersleri izlemek için eğitime kaydolmalısınız.');
            redirect('/odeme');
        }
        $lesson = Database::one('SELECT * FROM lessons WHERE id = ? AND is_published = 1', [(int)$id]);
        if ($lesson === null) {
            abort(404);
        }
        $all = Database::all('SELECT id, title, scheduled_at, duration_min, replay_embed FROM lessons WHERE is_published = 1 ORDER BY scheduled_at ASC, sort ASC');
        view('member/lesson', [
            'user'    => $user,
            'lesson'  => $lesson,
            'state'   => Access::state($lesson),
            'lessons' => $all,
            'seo'     => self::SEO + ['title' => $lesson['title']],
        ], 'layouts/member');
    }

    public function orders(): void
    {
        $user = Auth::requireLogin();
        view('member/orders', [
            'user'   => $user,
            'orders' => Database::all('SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC', [$user['id']]),
            'seo'    => self::SEO + ['title' => 'Siparişlerim'],
        ], 'layouts/member');
    }

    public function profile(): void
    {
        $user = Auth::requireLogin();
        if (is_post()) {
            Csrf::verify();
            $name = trim(mb_substr(input('name'), 0, 120));
            $email = mb_strtolower(input('email'));
            $phone = mb_substr(input('phone'), 0, 40);
            if (mb_strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                flash('error', 'Ad ve geçerli bir e-posta zorunludur.');
                redirect('/panel/profil');
            }
            if ($email !== $user['email'] && Database::value('SELECT COUNT(*) FROM users WHERE email = ? AND id <> ?', [$email, $user['id']]) > 0) {
                flash('error', 'Bu e-posta başka bir hesapta kullanılıyor.');
                redirect('/panel/profil');
            }
            $data = ['name' => $name, 'email' => $email, 'phone' => $phone];
            $new = (string)($_POST['new_password'] ?? '');
            if ($new !== '') {
                if (!password_verify((string)($_POST['current_password'] ?? ''), (string)$user['password_hash'])) {
                    flash('error', 'Mevcut şifreniz hatalı.');
                    redirect('/panel/profil');
                }
                if (mb_strlen($new) < 8) {
                    flash('error', 'Yeni şifre en az 8 karakter olmalı.');
                    redirect('/panel/profil');
                }
                $data['password_hash'] = password_hash($new, PASSWORD_DEFAULT);
            }
            Database::update('users', $data, 'id = :id', ['id' => $user['id']]);
            flash('success', 'Profiliniz güncellendi.');
            redirect('/panel/profil');
        }
        view('member/profile', ['user' => $user, 'seo' => self::SEO + ['title' => 'Profilim']], 'layouts/member');
    }
}
