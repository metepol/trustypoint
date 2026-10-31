<?php
declare(strict_types=1);

namespace App;

/** YouTube / Vimeo / herhangi bir iframe yayını için güvenli gömme. */
final class Embed
{
    public static function src(string $value): string
    {
        $value = trim($value);
        if (preg_match('#(?:youtube\.com/(?:watch\?v=|live/|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{6,})#', $value, $m)) {
            return 'https://www.youtube-nocookie.com/embed/' . $m[1] . '?rel=0&modestbranding=1';
        }
        if (preg_match('#vimeo\.com/(?:event/)?(\d+)#', $value, $m)) {
            return str_contains($value, '/event/')
                ? 'https://vimeo.com/event/' . $m[1] . '/embed'
                : 'https://player.vimeo.com/video/' . $m[1];
        }
        return preg_match('#^https://#i', $value) ? $value : '';
    }

    public static function render(?string $value, string $title = 'Video'): string
    {
        $value = trim((string)$value);
        if ($value === '') {
            return '';
        }
        // Yönetici tarafından girilen hazır iframe kodu
        if (str_starts_with($value, '<')) {
            return '<div class="player">' . $value . '</div>';
        }
        $src = self::src($value);
        if ($src === '') {
            return '';
        }
        if (preg_match('#\.(mp4|webm|m3u8)(\?|$)#i', $src)) {
            return '<div class="player"><video src="' . e($src) . '" controls controlsList="nodownload" playsinline></video></div>';
        }
        return '<div class="player"><iframe src="' . e($src) . '" title="' . e($title) . '" allow="autoplay; fullscreen; picture-in-picture; encrypted-media" allowfullscreen loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe></div>';
    }
}
