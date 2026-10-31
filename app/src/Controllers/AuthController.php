<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Auth;
use App\Csrf;
use App\Database;
use App\Mailer;
use App\Notifier;
use App\RateLimiter;

final class AuthController
{
    private const NOINDEX = 'noindex, nofollow';

    public function login(): void
    {
        if (Auth::check()) {
            redirect(safe_next($_GET['next'] ?? null, Auth::isAdmin() ? '/yonetim' : '/panel'));
        }
        if (is_post()) {
            Csrf::verify();
            $email = mb_strtolower(input('email'));
            $key = 'login:' . client_ip();
            if (RateLimiter::tooMany($key, 8, 900)) {
                flash('error', 'Çok fazla hatalı deneme. 15 dakika sonra tekrar deneyin veya e-posta ile giriş bağlantısı isteyin.');
                redirect('/giris');
            }
            $user = Auth::attempt($email, (string)($_POST['password'] ?? ''));
            if ($user === null) {
                RateLimiter::hit($key, 900);
                with_old(['email' => $email]);
                flash('error', 'E-posta veya şifre hatalı.');
                redirect('/giris?next=' . rawurlencode(input('next')));
            }
            RateLimiter::clear($key);
            Auth::login($user);
            redirect(safe_next(input('next'), $user['role'] === 'admin' ? '/yonetim' : '/panel'));
        }
        view('auth/login', ['next' => safe_next($_GET['next'] ?? null, ''), 'seo' => ['title' => 'Üye Girişi', 'robots' => self::NOINDEX]]);
    }

    public function register(): void
    {
        if (Auth::check()) {
            redirect('/panel');
        }
        if (is_post()) {
            Csrf::verify();
            $name = trim(mb_substr(input('name'), 0, 120));
            $email = mb_strtolower(input('email'));
            $password = (string)($_POST['password'] ?? '');
            $next = safe_next(input('next'), '/panel');

            $errors = [];
            if (mb_strlen($name) < 2) {
                $errors[] = 'Adınızı ve soyadınızı yazın.';
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Geçerli bir e-posta adresi yazın.';
            }
            if (mb_strlen($password) < 8) {
                $errors[] = 'Şifreniz en az 8 karakter olmalı.';
            }
            if (!isset($_POST['terms'])) {
                $errors[] = 'Üyelik ve KVKK koşullarını onaylayın.';
            }
            if (!$errors && Database::value('SELECT COUNT(*) FROM users WHERE email = ?', [$email]) > 0) {
                $errors[] = 'Bu e-posta ile zaten bir hesap var. Giriş yapabilirsiniz.';
            }
            $key = 'register:' . client_ip();
            if (RateLimiter::tooMany($key, 10, 3600)) {
                $errors[] = 'Çok fazla kayıt denemesi. Lütfen daha sonra tekrar deneyin.';
            }
            if ($errors) {
                with_old(['name' => $name, 'email' => $email]);
                foreach ($errors as $err) {
                    flash('error', $err);
                }
                redirect('/kayit?next=' . rawurlencode($next));
            }
            RateLimiter::hit($key, 3600);
            $id = Database::insert('users', [
                'name'          => $name,
                'email'         => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'role'          => 'member',
                'created_at'    => now(),
            ]);
            $user = Database::one('SELECT * FROM users WHERE id = ?', [$id]);
            Auth::login($user);
            Notifier::userRegistered($user);
            flash('success', 'Hoş geldin ' . $name . '! Hesabın hazır.');
            redirect($next);
        }
        view('auth/register', ['next' => safe_next($_GET['next'] ?? null, ''), 'seo' => ['title' => 'Ücretsiz Üye Ol', 'robots' => self::NOINDEX]]);
    }

    public function logout(): void
    {
        Csrf::verify();
        Auth::logout();
        flash('success', 'Güvenle çıkış yaptınız.');
        redirect('/');
    }

    /** Tek kullanımlık token üretir; veritabanında yalnızca hash'i tutulur. */
    private function issueToken(array $user, string $type, int $minutes): string
    {
        $token = bin2hex(random_bytes(32));
        Database::run('UPDATE users SET token_hash = ?, token_type = ?, token_expires = ? WHERE id = ?', [
            hash('sha256', $token), $type, date('Y-m-d H:i:s', time() + $minutes * 60), $user['id'],
        ]);
        return $token;
    }

    private function consumeToken(string $token, string $type): ?array
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            return null;
        }
        $user = Database::one('SELECT * FROM users WHERE token_hash = ? AND token_type = ?', [hash('sha256', $token), $type]);
        if ($user === null || strtotime((string)$user['token_expires']) < time()) {
            return null;
        }
        return $user;
    }

    private function clearToken(int $userId): void
    {
        Database::run('UPDATE users SET token_hash = NULL, token_type = NULL, token_expires = NULL WHERE id = ?', [$userId]);
    }

    /** Şifresiz giriş: e-postaya tek tıklık giriş bağlantısı gönderir. */
    public function magicRequest(): void
    {
        Csrf::verify();
        $this->sendLink('magic');
        flash('success', 'Kayıtlı bir hesap varsa giriş bağlantısı e-posta adresinize gönderildi (15 dk geçerli).');
        redirect('/giris');
    }

    public function magicLogin(string $token): void
    {
        $user = $this->consumeToken($token, 'magic');
        if ($user === null) {
            flash('error', 'Bağlantı geçersiz veya süresi dolmuş. Lütfen yeni bir bağlantı isteyin.');
            redirect('/giris');
        }
        $this->clearToken((int)$user['id']);
        Auth::login($user);
        redirect($user['role'] === 'admin' ? '/yonetim' : '/panel');
    }

    public function forgot(): void
    {
        if (is_post()) {
            Csrf::verify();
            $this->sendLink('reset');
            flash('success', 'Kayıtlı bir hesap varsa şifre sıfırlama bağlantısı e-posta adresinize gönderildi.');
            redirect('/sifremi-unuttum');
        }
        view('auth/forgot', ['seo' => ['title' => 'Şifremi Unuttum', 'robots' => self::NOINDEX]]);
    }

    private function sendLink(string $type): void
    {
        $email = mb_strtolower(input('email'));
        $key = 'link:' . client_ip();
        if (RateLimiter::tooMany($key, 5, 900) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return;
        }
        RateLimiter::hit($key, 900);
        $user = Database::one('SELECT * FROM users WHERE email = ?', [$email]);
        if ($user === null) {
            return; // hesap varlığını sızdırmamak için aynı yanıt
        }
        if ($type === 'magic') {
            $token = $this->issueToken($user, 'magic', 15);
            Mailer::template($user['email'], 'Giriş bağlantınız', 'Tek tıkla giriş yap', '<p>Merhaba ' . e($user['name']) . ', aşağıdaki butona tıklayarak şifre girmeden hesabına giriş yapabilirsin. Bağlantı 15 dakika geçerlidir.</p>', 'Giriş yap', full_url('giris/baglanti/' . $token));
        } else {
            $token = $this->issueToken($user, 'reset', 60);
            Mailer::template($user['email'], 'Şifre sıfırlama', 'Şifreni sıfırla', '<p>Merhaba ' . e($user['name']) . ', şifreni sıfırlamak için aşağıdaki butona tıkla. Bu isteği sen yapmadıysan bu e-postayı yok sayabilirsin. Bağlantı 60 dakika geçerlidir.</p>', 'Yeni şifre belirle', full_url('sifre-sifirla/' . $token));
        }
    }

    public function reset(string $token): void
    {
        $user = $this->consumeToken($token, 'reset');
        if ($user === null) {
            flash('error', 'Bağlantı geçersiz veya süresi dolmuş.');
            redirect('/sifremi-unuttum');
        }
        if (is_post()) {
            Csrf::verify();
            $password = (string)($_POST['password'] ?? '');
            if (mb_strlen($password) < 8) {
                flash('error', 'Şifreniz en az 8 karakter olmalı.');
                redirect('/sifre-sifirla/' . $token);
            }
            Database::run('UPDATE users SET password_hash = ? WHERE id = ?', [password_hash($password, PASSWORD_DEFAULT), $user['id']]);
            $this->clearToken((int)$user['id']);
            Auth::login($user);
            flash('success', 'Şifreniz güncellendi.');
            redirect('/panel');
        }
        view('auth/reset', ['token' => $token, 'seo' => ['title' => 'Yeni Şifre', 'robots' => self::NOINDEX]]);
    }
}
