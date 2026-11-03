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
        if (self::isFile($value)) {
            return '<div class="player"><video src="' . e(media($value)) . '" controls controlsList="nodownload" playsinline></video></div>';
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

    public static function youtubeId(string $value): ?string
    {
        return preg_match('#(?:youtube\.com/(?:watch\?v=|live/|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{6,})#', $value, $m) ? $m[1] : null;
    }

    public static function isFile(string $value): bool
    {
        return (bool)preg_match('#^(https://|uploads/)[^\s"\'<>]+\.(mp4|webm)(\?.*)?$#i', $value);
    }

    /**
     * Ana sayfa hero videosu: YouTube/Vimeo için kapak + oynat butonu (tıklanınca yüklenir),
     * dosya videoları için sessiz döngüde oynayan önizleme + "Sesli izle" butonu.
     */
    public static function hero(string $value, string $poster = '', string $label = 'Tanıtım videosunu izle'): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }
        $playIcon = '<svg width="26" height="26" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5.14v13.72a1 1 0 0 0 1.52.85l10.6-6.86a1 1 0 0 0 0-1.7L9.52 4.29A1 1 0 0 0 8 5.14z"/></svg>';

        if (self::isFile($value)) {
            $src = media($value);
            return '<div class="hero-video is-file">'
                . '<video src="' . e($src) . '"' . ($poster ? ' poster="' . e($poster) . '"' : '') . ' muted autoplay loop playsinline preload="metadata"></video>'
                . '<button type="button" class="hv-play hv-sound" data-hv-sound><span class="hv-btn">' . $playIcon . '</span><span class="hv-label">' . e($label) . '</span></button>'
                . '</div>';
        }

        $src = self::src($value);
        if ($src === '') {
            return '';
        }
        $auto = $src . (str_contains($src, '?') ? '&' : '?') . 'autoplay=1';
        if ($poster === '' && ($id = self::youtubeId($value))) {
            $poster = 'https://i.ytimg.com/vi/' . $id . '/hqdefault.jpg';
        }
        return '<div class="hero-video" data-hv-embed="' . e($auto) . '">'
            . ($poster ? '<img src="' . e($poster) . '" alt="" class="hv-poster" loading="eager">' : '<span class="hv-poster hv-fallback"></span>')
            . '<button type="button" class="hv-play" data-hv-open><span class="hv-btn">' . $playIcon . '</span><span class="hv-label">' . e($label) . '</span></button>'
            . '</div>';
    }
}
