<?php
use App\Seo;
$favicon = (string)setting('site_favicon', '');
$ga = trim((string)setting('seo_ga_id', ''));
$gtm = trim((string)setting('seo_gtm_id', ''));
$pixel = trim((string)setting('seo_meta_pixel', ''));
?>
<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0a0a0f">
    <meta name="csrf-token" content="<?= e(App\Csrf::token()) ?>">
    <?= Seo::render($seo ?? []) ?>

    <link rel="icon" href="<?= $favicon ? e(media($favicon)) : e(asset('assets/img/favicon.svg')) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Unbounded:wght@500;700;900&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500&display=swap">
    <link rel="stylesheet" href="<?= e(asset('assets/css/style.css')) ?>">
    <link rel="sitemap" type="application/xml" href="<?= e(url('sitemap.xml')) ?>">
<?php if ($gtm !== '' && preg_match('/^GTM-[A-Z0-9]+$/', $gtm)): ?>
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','<?= e($gtm) ?>');</script>
<?php endif; ?>
<?php if ($ga !== '' && preg_match('/^G-[A-Z0-9]+$/', $ga)): ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= e($ga) ?>"></script>
    <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','<?= e($ga) ?>');</script>
<?php endif; ?>
<?php if ($pixel !== '' && ctype_digit($pixel)): ?>
    <script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init','<?= e($pixel) ?>');fbq('track','PageView');</script>
<?php endif; ?>
<?= (string)setting('seo_head_code', '') ?>
