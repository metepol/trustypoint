<?php
declare(strict_types=1);

namespace App;

final class Auth
{
    private static ?array $user = null;
    private static bool $loaded = false;

    public static function user(): ?array
    {
        if (!self::$loaded) {
            self::$loaded = true;
            $id = (int)($_SESSION['uid'] ?? 0);
            if ($id > 0) {
                self::$user = Database::one('SELECT * FROM users WHERE id = ?', [$id]);
                if (self::$user === null) {
                    unset($_SESSION['uid']);
                }
            }
        }
        return self::$user;
    }

    public static function id(): int
    {
        return (int)(self::user()['id'] ?? 0);
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function isAdmin(): bool
    {
        return (self::user()['role'] ?? '') === 'admin';
    }

    public static function login(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['uid'] = (int)$user['id'];
        Database::run('UPDATE users SET last_login_at = ? WHERE id = ?', [now(), $user['id']]);
        self::$user = null;
        self::$loaded = false;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        session_regenerate_id(true);
        self::$user = null;
        self::$loaded = true;
    }

    public static function attempt(string $email, string $password): ?array
    {
        $user = Database::one('SELECT * FROM users WHERE email = ?', [mb_strtolower(trim($email))]);
        if ($user === null || !password_verify($password, (string)$user['password_hash'])) {
            return null;
        }
        if (password_needs_rehash((string)$user['password_hash'], PASSWORD_DEFAULT)) {
            Database::run('UPDATE users SET password_hash = ? WHERE id = ?', [password_hash($password, PASSWORD_DEFAULT), $user['id']]);
        }
        return $user;
    }

    public static function requireLogin(): array
    {
        $user = self::user();
        if ($user === null) {
            flash('info', 'Devam etmek için giriş yapın.');
            redirect('/giris?next=' . rawurlencode(Router::currentPath()));
        }
        return $user;
    }

    public static function requireAdmin(): array
    {
        $user = self::requireLogin();
        if ($user['role'] !== 'admin') {
            abort(403, 'Bu alana erişim yetkiniz yok.');
        }
        return $user;
    }

    public static function refresh(): void
    {
        self::$loaded = false;
        self::$user = null;
    }
}
