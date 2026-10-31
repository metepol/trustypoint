<!doctype html>
<html lang="tr">
<head>
    <?php App\View::partial('head', ['seo' => $seo ?? []]); ?>
</head>
<body class="<?= e($bodyClass ?? '') ?>">
<?php if (($gtm = trim((string)setting('seo_gtm_id', ''))) && preg_match('/^GTM-[A-Z0-9]+$/', $gtm)): ?>
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?= e($gtm) ?>" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<?php endif; ?>
<a class="skip" href="#main">İçeriğe geç</a>
<div class="noise" aria-hidden="true"></div>
<?php App\View::partial('header'); ?>
<?php App\View::partial('flash'); ?>
<main id="main">
<?= $content ?>
</main>
<?php App\View::partial('footer'); ?>
<script src="<?= e(asset('assets/js/app.js')) ?>" defer></script>
<?= (string)setting('seo_body_code', '') ?>
</body>
</html>
