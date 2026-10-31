<?php $site = (string)setting('site_name', 'Akademi'); ?>
<!doctype html>
<html lang="tr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width"><title><?= e($title ?? $site) ?></title></head>
<body style="margin:0;background:#0a0a0f;font-family:Arial,Helvetica,sans-serif;color:#e9e9f1">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#0a0a0f;padding:32px 12px">
<tr><td align="center">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#14141c;border-radius:20px;border:1px solid #26263a">
    <tr><td style="padding:28px 32px 0"><span style="color:#c6ff3d;font-weight:800;font-size:18px;letter-spacing:.5px">▲ <?= e($site) ?></span></td></tr>
    <tr><td style="padding:20px 32px 8px"><h1 style="margin:0;font-size:24px;line-height:1.3;color:#fff"><?= e($title ?? '') ?></h1></td></tr>
    <tr><td style="padding:8px 32px 8px;font-size:15px;line-height:1.65;color:#c9c9d6"><?= $body ?? '' ?></td></tr>
    <?php if (!empty($ctaUrl)): ?>
    <tr><td style="padding:16px 32px 8px"><a href="<?= e($ctaUrl) ?>" style="display:inline-block;background:#c6ff3d;color:#0a0a0f;text-decoration:none;font-weight:700;padding:14px 26px;border-radius:999px"><?= e($ctaText ?? 'Devam et') ?> →</a></td></tr>
    <?php endif; ?>
    <tr><td style="padding:28px 32px;font-size:12px;color:#7d7d92;border-top:1px solid #26263a">Bu e-posta <?= e($site) ?> tarafından gönderildi · <a href="<?= e(full_url()) ?>" style="color:#9d8cff"><?= e(parse_url(full_url(), PHP_URL_HOST) ?? '') ?></a></td></tr>
  </table>
</td></tr></table>
</body></html>
