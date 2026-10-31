<?php
declare(strict_types=1);

namespace App;

final class Settings
{
    private static ?array $cache = null;

    private static function load(): array
    {
        if (self::$cache === null) {
            self::$cache = [];
            try {
                foreach (Database::all('SELECT skey, svalue FROM settings') as $row) {
                    self::$cache[$row['skey']] = $row['svalue'];
                }
            } catch (\Throwable) {
                // kurulum öncesi
            }
        }
        return self::$cache;
    }

    public static function get(string $key, mixed $default = ''): mixed
    {
        $all = self::load();
        return array_key_exists($key, $all) && $all[$key] !== null ? $all[$key] : $default;
    }

    public static function bool(string $key): bool
    {
        return (string)self::get($key, '0') === '1';
    }

    public static function set(string $key, ?string $value): void
    {
        $exists = Database::value('SELECT COUNT(*) FROM settings WHERE skey = ?', [$key]);
        if ((int)$exists > 0) {
            Database::run('UPDATE settings SET svalue = ? WHERE skey = ?', [$value, $key]);
        } else {
            Database::run('INSERT INTO settings (skey, svalue) VALUES (?, ?)', [$key, $value]);
        }
        self::load();
        self::$cache[$key] = $value;
    }

    public static function setMany(array $values): void
    {
        foreach ($values as $k => $v) {
            self::set((string)$k, $v === null ? null : (string)$v);
        }
    }
}
