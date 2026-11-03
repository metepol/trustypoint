<?php
declare(strict_types=1);

namespace App;

/**
 * Ders erişim kuralları:
 *  - Eğitimi satın alan üye, yayın gününde canlı yayını izler.
 *  - Yayın bittikten sonra tekrar kaydı, yayın tarihinden itibaren `replay_days` (varsayılan 365) gün izlenebilir.
 */
final class Access
{
    public const UPCOMING   = 'upcoming';
    public const LIVE       = 'live';
    public const PROCESSING = 'processing';
    public const REPLAY     = 'replay';
    public const EXPIRED    = 'expired';

    public static function hasCourse(?array $user): bool
    {
        if ($user === null) {
            return false;
        }
        return $user['role'] === 'admin' || !empty($user['enrolled_at']);
    }

    public static function replayDays(): int
    {
        return max(1, (int)setting('replay_days', '365'));
    }

    public static function startTs(array $lesson): int
    {
        return (int)strtotime((string)$lesson['scheduled_at']);
    }

    public static function endTs(array $lesson): int
    {
        return self::startTs($lesson) + max(15, (int)$lesson['duration_min']) * 60;
    }

    public static function replayUntilTs(array $lesson): int
    {
        return self::startTs($lesson) + self::replayDays() * 86400;
    }

    public static function state(array $lesson, ?int $now = null): string
    {
        $now ??= time();
        $openBefore = max(0, (int)setting('live_open_minutes', '30')) * 60;
        $start = self::startTs($lesson);
        if ($now < $start - $openBefore) {
            return self::UPCOMING;
        }
        if ($now <= self::endTs($lesson)) {
            return self::LIVE;
        }
        if ($now > self::replayUntilTs($lesson)) {
            return self::EXPIRED;
        }
        return trim((string)$lesson['replay_embed']) !== '' ? self::REPLAY : self::PROCESSING;
    }

    public static function label(string $state): string
    {
        return match ($state) {
            self::UPCOMING   => 'Yakında',
            self::LIVE       => 'Canlı',
            self::PROCESSING => 'Tekrar hazırlanıyor',
            self::REPLAY     => 'Tekrar izle',
            self::EXPIRED    => 'Süresi doldu',
            default          => $state,
        };
    }

    public static function nextLesson(): ?array
    {
        foreach (Database::all('SELECT * FROM lessons WHERE is_published = 1 ORDER BY scheduled_at ASC') as $l) {
            if (self::endTs($l) >= time()) {
                return $l;
            }
        }
        return null;
    }
}
