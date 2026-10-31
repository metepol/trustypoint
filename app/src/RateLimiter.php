<?php
declare(strict_types=1);

namespace App;

final class RateLimiter
{
    /** Belirtilen süre içinde izin verilen deneme sayısı aşıldıysa true döner. */
    public static function tooMany(string $key, int $max, int $decaySeconds): bool
    {
        $row = Database::one('SELECT hits, reset_at FROM throttle WHERE tkey = ?', [$key]);
        if ($row === null || strtotime($row['reset_at']) < time()) {
            return false;
        }
        return (int)$row['hits'] >= $max;
    }

    public static function hit(string $key, int $decaySeconds): void
    {
        $row = Database::one('SELECT hits, reset_at FROM throttle WHERE tkey = ?', [$key]);
        $reset = date('Y-m-d H:i:s', time() + $decaySeconds);
        if ($row === null) {
            Database::run('INSERT INTO throttle (tkey, hits, reset_at) VALUES (?, 1, ?)', [$key, $reset]);
        } elseif (strtotime($row['reset_at']) < time()) {
            Database::run('UPDATE throttle SET hits = 1, reset_at = ? WHERE tkey = ?', [$reset, $key]);
        } else {
            Database::run('UPDATE throttle SET hits = hits + 1 WHERE tkey = ?', [$key]);
        }
    }

    public static function clear(string $key): void
    {
        Database::run('DELETE FROM throttle WHERE tkey = ?', [$key]);
    }
}
