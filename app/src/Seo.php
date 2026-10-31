<?php
declare(strict_types=1);

namespace App;

/** Meta etiketleri, Open Graph, Twitter kartları ve JSON-LD yapılandırılmış veri. */
final class Seo
{
    public static function meta(array $seo): array
    {
        $siteName = (string)setting('seo_site_title', '') ?: (string)setting('site_name', 'Akademi');
        $sep = (string)setting('seo_separator', '|');
        $title = trim((string)($seo['title'] ?? ''));
        $fullTitle = ($title === '' || !empty($seo['raw_title'])) ? ($title ?: $siteName) : $title . ' ' . $sep . ' ' . $siteName;
        $desc = trim((string)($seo['description'] ?? '')) ?: (string)setting('seo_description', '');
        $globalIndex = Settings::bool('seo_index');
        $robots = $seo['robots'] ?? 'index, follow, max-image-preview:large';
        if (!$globalIndex) {
            $robots = 'noindex, nofollow';
        }
        $canonical = $seo['canonical'] ?? full_url(ltrim(Router::currentPath(), '/'));
        $image = $seo['image'] ?? (string)setting('seo_og_image', '');

        return [
            'title'       => $fullTitle,
            'description' => mb_substr($desc, 0, 300),
            'keywords'    => $seo['keywords'] ?? (string)setting('seo_keywords', ''),
            'robots'      => $robots,
            'canonical'   => rtrim($canonical, '/') ?: full_url(),
            'image'       => $image !== '' ? (preg_match('#^https?://#', $image) ? $image : full_url($image)) : '',
            'type'        => $seo['type'] ?? 'website',
            'site_name'   => $siteName,
            'schema'      => $seo['schema'] ?? [],
            'breadcrumbs' => $seo['breadcrumbs'] ?? [],
        ];
    }

    public static function render(array $seo): string
    {
        $m = self::meta($seo);
        $h = [];
        $h[] = '<title>' . e($m['title']) . '</title>';
        $h[] = '<meta name="description" content="' . e($m['description']) . '">';
        if ($m['keywords'] !== '') {
            $h[] = '<meta name="keywords" content="' . e($m['keywords']) . '">';
        }
        $h[] = '<meta name="robots" content="' . e($m['robots']) . '">';
        $h[] = '<link rel="canonical" href="' . e($m['canonical']) . '">';
        $h[] = '<meta property="og:locale" content="tr_TR">';
        $h[] = '<meta property="og:type" content="' . e($m['type']) . '">';
        $h[] = '<meta property="og:title" content="' . e($m['title']) . '">';
        $h[] = '<meta property="og:description" content="' . e($m['description']) . '">';
        $h[] = '<meta property="og:url" content="' . e($m['canonical']) . '">';
        $h[] = '<meta property="og:site_name" content="' . e($m['site_name']) . '">';
        $h[] = '<meta name="twitter:card" content="' . ($m['image'] ? 'summary_large_image' : 'summary') . '">';
        $h[] = '<meta name="twitter:title" content="' . e($m['title']) . '">';
        $h[] = '<meta name="twitter:description" content="' . e($m['description']) . '">';
        if ($tw = (string)setting('social_twitter_handle', '')) {
            $h[] = '<meta name="twitter:site" content="' . e($tw) . '">';
        }
        if ($m['image']) {
            $h[] = '<meta property="og:image" content="' . e($m['image']) . '">';
            $h[] = '<meta name="twitter:image" content="' . e($m['image']) . '">';
        }
        if ($v = (string)setting('seo_google_verify', '')) {
            $h[] = '<meta name="google-site-verification" content="' . e($v) . '">';
        }
        if ($v = (string)setting('seo_bing_verify', '')) {
            $h[] = '<meta name="msvalidate.01" content="' . e($v) . '">';
        }
        if ($v = (string)setting('seo_yandex_verify', '')) {
            $h[] = '<meta name="yandex-verification" content="' . e($v) . '">';
        }

        $graph = [self::organization(), [
            '@type' => 'WebSite',
            '@id'   => full_url() . '#website',
            'url'   => full_url(),
            'name'  => $m['site_name'],
            'inLanguage' => 'tr-TR',
            'publisher' => ['@id' => full_url() . '#organization'],
        ]];
        if ($m['breadcrumbs'] !== []) {
            $items = [['@type' => 'ListItem', 'position' => 1, 'name' => 'Ana Sayfa', 'item' => full_url()]];
            foreach ($m['breadcrumbs'] as $i => [$name, $path]) {
                $items[] = ['@type' => 'ListItem', 'position' => $i + 2, 'name' => $name, 'item' => full_url($path)];
            }
            $graph[] = ['@type' => 'BreadcrumbList', 'itemListElement' => $items];
        }
        foreach ($m['schema'] as $node) {
            $graph[] = $node;
        }
        $h[] = '<script type="application/ld+json">' . json_encode(['@context' => 'https://schema.org', '@graph' => $graph], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) . '</script>';

        return implode("\n    ", $h);
    }

    public static function organization(): array
    {
        $same = array_values(array_filter([
            setting('social_instagram', ''), setting('social_youtube', ''), setting('social_linkedin', ''),
            setting('social_x', ''), setting('social_tiktok', ''),
        ]));
        $org = [
            '@type' => 'EducationalOrganization',
            '@id'   => full_url() . '#organization',
            'name'  => (string)setting('site_name', 'Akademi'),
            'url'   => full_url(),
        ];
        if ($logo = (string)setting('site_logo', '')) {
            $org['logo'] = preg_match('#^https?://#', $logo) ? $logo : full_url($logo);
        }
        if ($same !== []) {
            $org['sameAs'] = $same;
        }
        if ($email = (string)setting('contact_email', '')) {
            $org['email'] = $email;
        }
        if ($phone = (string)setting('contact_phone', '')) {
            $org['telephone'] = $phone;
        }
        return $org;
    }

    public static function courseSchema(array $lessons = []): array
    {
        $course = [
            '@type'       => 'Course',
            '@id'         => full_url('egitim') . '#course',
            'name'        => (string)setting('course_title', ''),
            'description' => excerpt((string)setting('course_subtitle', '') . ' ' . (string)setting('course_description', ''), 300),
            'inLanguage'  => 'tr-TR',
            'provider'    => ['@id' => full_url() . '#organization'],
            'offers'      => [
                '@type'         => 'Offer',
                'price'         => number_format(Payments::price(), 2, '.', ''),
                'priceCurrency' => Payments::currency(),
                'availability'  => 'https://schema.org/InStock',
                'url'           => full_url('egitim'),
                'category'      => 'Paid',
            ],
        ];
        $instances = [];
        foreach ($lessons as $l) {
            $instances[] = [
                '@type'      => 'CourseInstance',
                'name'       => $l['title'],
                'courseMode' => 'online',
                'startDate'  => date('c', strtotime($l['scheduled_at'])),
                'endDate'    => date('c', Access::endTs($l)),
                'location'   => ['@type' => 'VirtualLocation', 'url' => full_url('panel')],
                'courseSchedule' => ['@type' => 'Schedule', 'repeatFrequency' => 'P1W', 'duration' => 'PT' . (int)$l['duration_min'] . 'M'],
            ];
        }
        if ($instances !== []) {
            $course['hasCourseInstance'] = $instances;
        }
        return $course;
    }

    public static function faqSchema(array $faqs): array
    {
        return [
            '@type' => 'FAQPage',
            'mainEntity' => array_map(static fn($f) => [
                '@type' => 'Question',
                'name'  => $f['question'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => strip_tags((string)$f['answer'])],
            ], $faqs),
        ];
    }
}
