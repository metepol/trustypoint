<?php $site = (string)setting('site_name', 'Akademi'); ?>
<!doctype html>
<html lang="tr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width"><title><?= e($title ?? $site) ?></title></head>
<body style="margin:0;background:#f2f5fb;font-family:Arial,Helvetica,sans-serif;color:#0b1a3a">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f2f5fb;padding:32px 12px">
<tr><td align="center">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border-radius:20px;border:1px solid #e3e9f3;overflow:hidden">
    <tr><td style="padding:22px 32px;background:#13306f;background-image:linear-gradient(135deg,#081739,#13306f 55%,#2554c7)"><span style="color:#ffffff;font-weight:800;font-size:18px;letter-spacing:.5px">▲ <?= e($site) ?></span></td></tr>
    <tr><td style="padding:24px 32px 8px"><h1 style="margin:0;font-size:24px;line-height:1.3;color:#0d2352"><?= e($title ?? '') ?></h1></td></tr>
    <tr><td style="padding:8px 32px 8px;font-size:15px;line-height:1.65;color:#33415f"><?= $body ?? '' ?></td></tr>
    <?php if (!empty($ctaUrl)): ?>
    <tr><td style="padding:16px 32px 8px"><a href="<?= e($ctaUrl) ?>" style="display:inline-block;background:#13306f;background-image:linear-gradient(135deg,#0d2352,#2554c7);color:#ffffff;text-decoration:none;font-weight:700;padding:14px 26px;border-radius:999px"><?= e($ctaText ?? 'Devam et') ?> →</a></td></tr>
    <?php endif; ?>
    <tr><td style="padding:28px 32px;font-size:12px;color:#5d6987;border-top:1px solid #e3e9f3">Bu e-posta <?= e($site) ?> tarafından gönderildi · <a href="<?= e(full_url()) ?>" style="color:#2554c7"><?= e(parse_url(full_url(), PHP_URL_HOST) ?? '') ?></a></td></tr>
  </table>
</td></tr></table>
</body></html>
