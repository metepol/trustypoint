<?php
declare(strict_types=1);

namespace App;

final class Upload
{
    private const TYPES = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
        'image/svg+xml' => 'svg',
        'image/x-icon' => 'ico',
        'image/vnd.microsoft.icon' => 'ico',
    ];

    /** Başarılıysa site köküne göre göreli yol (uploads/...) döner. */
    public static function image(string $field, int $maxBytes = 5_242_880): ?string
    {
        $file = $_FILES[$field] ?? null;
        if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
            throw new \RuntimeException('Dosya yüklenemedi.');
        }
        if ($file['size'] > $maxBytes) {
            throw new \RuntimeException('Dosya çok büyük (en fazla 5 MB).');
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']) ?: '';
        if (!isset(self::TYPES[$mime])) {
            throw new \RuntimeException('Yalnızca JPG, PNG, WEBP, GIF, SVG veya ICO yükleyebilirsiniz.');
        }
        if ($mime === 'image/svg+xml') {
            $svg = (string)file_get_contents($file['tmp_name']);
            if (preg_match('/<script|on\w+\s*=|javascript:/i', $svg)) {
                throw new \RuntimeException('SVG dosyası güvenli değil.');
            }
        }
        $dir = date('Y/m');
        if (!is_dir(UPLOAD_PATH . '/' . $dir)) {
            mkdir(UPLOAD_PATH . '/' . $dir, 0755, true);
        }
        $name = $dir . '/' . bin2hex(random_bytes(10)) . '.' . self::TYPES[$mime];
        if (!move_uploaded_file($file['tmp_name'], UPLOAD_PATH . '/' . $name)) {
            throw new \RuntimeException('Dosya kaydedilemedi.');
        }
        return 'uploads/' . $name;
    }
}
