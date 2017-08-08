<?php

switch ($action):

/**
 * Генерируем главную страницу
 */
case 'index':
    $pages = [
        'news.xml',
        'blogs.xml',
        'events.xml',
        'topics.xml',
        'downs.xml',
    ];
    $locs = [];
    foreach ($pages as $page) {
        $locs[] = [
            'loc' => Setting::get('home').'/sitemap/'.$page,
            'lastmod' => date('c', SITETIME),
        ];
    }

    App::view('sitemap/index', compact('locs'));
break;

/**
 * Генерируем блоги
 */
case 'blogs':
    $blogs = Blog::raw_query("SELECT b.*, MAX(c.time) last_time FROM blogs b LEFT JOIN comments c ON b.id = c.relate_id AND relate_type='blog' GROUP BY b.id ORDER BY last_time DESC;")->find_many();

    $locs = [];
    foreach ($blogs as $blog) {

        $changeTime = ($blog['last_time'] > $blog['time']) ? $blog['last_time'] : $blog['time'];

        // Обновлено менее 1 месяца
        $new = (SITETIME < $changeTime + 3600 * 24 * 30) ? true : false;

        $locs[] = [
            'loc' => Setting::get('home').'/blog/blog?act=view&id='.$blog['id'],
            'lastmod' => date('c', $changeTime),
            'changefreq' => $new ? 'weekly' : 'monthly',
            'priority' => $new ? '1.0' : '0.5',
        ];
    }
    App::view('sitemap/url', compact('locs'));
break;
/**
 * Генерируем новости
 */
case 'news':
    $newses = News::raw_query("SELECT n.*, MAX(c.time) last_time FROM news n LEFT JOIN comments c ON n.id = c.relate_id AND relate_type='news' GROUP BY n.id ORDER BY last_time DESC;")->find_many();

    $locs = [];
    foreach ($newses as $news) {

        $changeTime = ($news['last_time'] > $news['time']) ? $news['last_time'] : $news['time'];

        // Обновлено менее 1 месяца
        $new = (SITETIME < $changeTime + 3600 * 24 * 30) ? true : false;

        $locs[] = [
            'loc' => Setting::get('home').'/news/'.$news['id'],
            'lastmod' => date('c', $changeTime),
            'changefreq' => $new ? 'weekly' : 'monthly',
            'priority' => $new ? '1.0' : '0.5',
        ];
    }
    App::view('sitemap/url', compact('locs'));
break;

/**
 * Генерируем события
 */
case 'events':
    $events = Event::raw_query("SELECT e.*, MAX(c.time) last_time FROM events e LEFT JOIN comments c ON e.id = c.relate_id AND relate_type='event' GROUP BY e.id ORDER BY last_time DESC;")->find_many();

    $locs = [];
    foreach ($events as $event) {

        $changeTime = ($event['last_time'] > $event['time']) ? $event['last_time'] : $event['time'];

        // Обновлено менее 1 месяца
        $new = (SITETIME < $changeTime + 3600 * 24 * 30) ? true : false;

        $locs[] = [
            'loc' => Setting::get('home').'/events?act=read&id='.$event['id'],
            'lastmod' => date('c', $changeTime),
            'changefreq' => $new ? 'weekly' : 'monthly',
            'priority' => $new ? '1.0' : '0.5',
        ];
    }
    App::view('sitemap/url', compact('locs'));
break;

/**
 * Генерируем темы форума
 */
case 'topics':
    $topics = Topic::order_by_desc('last_time')->limit(25000)->find_many();

    $locs = [];
    foreach ($topics as $topic) {

        // Обновлено менее 1 месяца
        $new = (SITETIME < $topic['last_time'] + 3600 * 24 * 30) ? true : false;

        $locs[] = [
            'loc' => Setting::get('home').'/topic/'.$topic['id'],
            'lastmod' => date('c', $topic['last_time']),
            'changefreq' => $new ? 'weekly' : 'monthly',
            'priority' => $new ? '1.0' : '0.5',
        ];
    }
    App::view('sitemap/url', compact('locs'));
break;

    /**
     * Генерируем загрузки
     */
    case 'downs':
        $downs = Down::raw_query("SELECT d.*, MAX(c.time) last_time FROM downs d LEFT JOIN comments c ON d.id = c.relate_id AND relate_type='down' GROUP BY d.id ORDER BY last_time DESC;")->find_many();

        $locs = [];
        foreach ($downs as $down) {

            $changeTime = ($down['last_time'] > $down['time']) ? $down['last_time'] : $down['time'];

            // Обновлено менее 1 месяца
            $new = (SITETIME < $changeTime + 3600 * 24 * 30) ? true : false;

            $locs[] = [
                'loc' => Setting::get('home').'/load/down?act=view&id='.$down['id'],
                'lastmod' => date('c', $changeTime),
                'changefreq' => $new ? 'weekly' : 'monthly',
                'priority' => $new ? '1.0' : '0.5',
            ];
        }
        App::view('sitemap/url', compact('locs'));
        break;

default:
    App::abort(404);
endswitch;
