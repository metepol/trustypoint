<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Access;
use App\Auth;
use App\Csrf;
use App\Database;
use App\Notifier;
use App\RateLimiter;
use App\Seo;
use App\Settings;

final class SiteController
{
    private function lessons(): array
    {
        return Database::all('SELECT * FROM lessons WHERE is_published = 1 ORDER BY scheduled_at ASC, sort ASC');
    }

    private function page(string $slug): ?array
    {
        return Database::one('SELECT * FROM pages WHERE slug = ?', [$slug]);
    }

    public function home(): void
    {
        $lessons = $this->lessons();
        $faqs = Database::all('SELECT * FROM faqs ORDER BY sort, id');
        view('site/home', [
            'lessons'      => $lessons,
            'next'         => Access::nextLesson(),
            'examples'     => Database::all('SELECT * FROM examples ORDER BY sort, id LIMIT 6'),
            'testimonials' => Database::all('SELECT * FROM testimonials ORDER BY sort, id LIMIT 6'),
            'faqs'         => $faqs,
            'seo'          => [
                'title'     => (string)setting('seo_home_title', '') ?: (string)setting('course_title'),
                'canonical' => full_url(),
                'schema'    => [Seo::courseSchema($lessons), Seo::faqSchema($faqs)],
            ],
        ]);
    }

    public function about(): void
    {
        $page = $this->page('hakkimizda') ?? ['title' => 'Hakkımızda', 'content' => '', 'meta_title' => '', 'meta_description' => ''];
        view('site/about', [
            'page' => $page,
            'testimonials' => Database::all('SELECT * FROM testimonials ORDER BY sort, id LIMIT 3'),
            'seo'  => [
                'title'       => $page['meta_title'] ?: $page['title'],
                'description' => $page['meta_description'] ?: excerpt($page['content']),
                'breadcrumbs' => [['Hakkımızda', 'hakkimizda']],
            ],
        ]);
    }

    public function course(): void
    {
        $lessons = $this->lessons();
        $faqs = Database::all('SELECT * FROM faqs ORDER BY sort, id');
        view('site/course', [
            'lessons' => $lessons,
            'faqs'    => $faqs,
            'next'    => Access::nextLesson(),
            'seo'     => [
                'title'       => (string)setting('course_title'),
                'description' => excerpt((string)setting('course_subtitle'), 300),
                'image'       => (string)setting('course_image', '') ?: null,
                'breadcrumbs' => [['Eğitim', 'egitim']],
                'schema'      => [Seo::courseSchema($lessons), Seo::faqSchema($faqs)],
            ],
        ]);
    }

    public function examples(): void
    {
        $items = Database::all('SELECT * FROM examples ORDER BY sort, id');
        view('site/examples', [
            'items' => $items,
            'seo'   => [
                'title'       => 'Örnek Projeler',
                'description' => 'Eğitim boyunca vibe coding ile üretilen kurumsal oyun, 3D deneyim ve animasyon örneklerini inceleyin.',
                'breadcrumbs' => [['Örnekler', 'ornekler']],
            ],
        ]);
    }

    public function references(): void
    {
        view('site/references', [
            'items' => Database::all('SELECT * FROM testimonials ORDER BY sort, id'),
            'seo'   => [
                'title'       => 'Referanslar',
                'description' => 'Eğitimimize katılan ajans, marka ve profesyonellerin deneyimleri ve referanslarımız.',
                'breadcrumbs' => [['Referanslar', 'referanslar']],
            ],
        ]);
    }

    public function contact(): void
    {
        if (is_post()) {
            Csrf::verify();
            if (input('website') !== '') { // bal küpü (bot koruması)
                redirect('/iletisim');
            }
            $key = 'contact:' . client_ip();
            if (RateLimiter::tooMany($key, 5, 3600)) {
                flash('error', 'Çok fazla mesaj gönderdiniz. Lütfen daha sonra tekrar deneyin.');
                redirect('/iletisim');
            }
            $data = [
                'name'    => mb_substr(input('name'), 0, 120),
                'email'   => mb_strtolower(input('email')),
                'phone'   => mb_substr(input('phone'), 0, 40),
                'subject' => mb_substr(input('subject') ?: 'Genel', 0, 190),
                'body'    => mb_substr(input('message'), 0, 5000),
            ];
            $errors = [];
            if (mb_strlen($data['name']) < 2) {
                $errors[] = 'Adınızı yazın.';
            }
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Geçerli bir e-posta adresi yazın.';
            }
            if (mb_strlen($data['body']) < 10) {
                $errors[] = 'Mesajınız en az 10 karakter olmalı.';
            }
            if (!isset($_POST['kvkk'])) {
                $errors[] = 'KVKK aydınlatma metnini onaylayın.';
            }
            if ($errors) {
                with_old($_POST);
                foreach ($errors as $err) {
                    flash('error', $err);
                }
                redirect('/iletisim');
            }
            RateLimiter::hit($key, 3600);
            Database::insert('messages', $data + ['ip' => client_ip(), 'is_read' => 0, 'created_at' => now()]);
            Notifier::contactMessage($data);
            flash('success', 'Mesajınız bize ulaştı. En kısa sürede dönüş yapacağız.');
            redirect('/iletisim');
        }

        view('site/contact', [
            'user' => Auth::user(),
            'seo'  => [
                'title'       => 'İletişim',
                'description' => 'Eğitim, kurumsal kayıt ve iş birlikleri için bizimle iletişime geçin.',
                'breadcrumbs' => [['İletişim', 'iletisim']],
            ],
        ]);
    }

    public function staticPage(string $slug): void
    {
        $page = $this->page($slug);
        if ($page === null || $slug === 'hakkimizda') {
            if ($slug === 'hakkimizda') {
                redirect('/hakkimizda', 301);
            }
            abort(404);
        }
        view('site/page', [
            'page' => $page,
            'seo'  => [
                'title'       => $page['meta_title'] ?: $page['title'],
                'description' => $page['meta_description'] ?: excerpt($page['content']),
                'breadcrumbs' => [[$page['title'], 'sayfa/' . $page['slug']]],
            ],
        ]);
    }

    public function sitemap(): void
    {
        header('Content-Type: application/xml; charset=UTF-8');
        $urls = [
            ['', '1.0', 'weekly'],
            ['egitim', '0.9', 'weekly'],
            ['hakkimizda', '0.7', 'monthly'],
            ['ornekler', '0.7', 'monthly'],
            ['referanslar', '0.6', 'monthly'],
            ['iletisim', '0.6', 'yearly'],
        ];
        foreach (Database::all("SELECT slug FROM pages WHERE slug <> 'hakkimizda'") as $p) {
            $urls[] = ['sayfa/' . $p['slug'], '0.3', 'yearly'];
        }
        $lastmod = date('Y-m-d');
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as [$path, $prio, $freq]) {
            $loc = rtrim(full_url($path), '/');
            echo "  <url><loc>" . e($loc ?: full_url()) . "</loc><lastmod>{$lastmod}</lastmod><changefreq>{$freq}</changefreq><priority>{$prio}</priority></url>\n";
        }
        echo '</urlset>';
    }

    public function robots(): void
    {
        header('Content-Type: text/plain; charset=UTF-8');
        if (!Settings::bool('seo_index')) {
            echo "User-agent: *\nDisallow: /\n";
            return;
        }
        $bp = base_path();
        echo "User-agent: *\n";
        echo "Allow: {$bp}/\n";
        foreach (['/yonetim', '/panel', '/odeme', '/giris', '/kayit', '/sifremi-unuttum', '/sifre-sifirla', '/app/', '/storage/'] as $p) {
            echo 'Disallow: ' . $bp . $p . "\n";
        }
        $extra = trim((string)setting('seo_robots_extra', ''));
        if ($extra !== '') {
            echo $extra . "\n";
        }
        echo "\nSitemap: " . full_url('sitemap.xml') . "\n";
    }
}
