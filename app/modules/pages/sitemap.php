<?php

switch ($act):

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
            'loc' => App::setting('home').'/sitemap/'.$page,
            'lastmod' => date('c', SITETIME),
        ];
    }

    App::view('sitemap/index', compact('locs'));
break;

/**
 * Генерируем блоги
 */
case 'blogs':
    $blogs = DBM::run()->query("SELECT b.*, MAX(c.time) last_time FROM blogs b LEFT JOIN comments c ON b.id = c.relate_id AND relate_type='blog' GROUP BY b.id;");

    $locs = [];
    foreach ($blogs as $blog) {

        $changeTime = ($blog['last_time'] > $blog['time']) ? $blog['last_time'] : $blog['time'];

        // Обновлено менее 1 месяца
        $new = (SITETIME < $changeTime + 3600 * 24 * 30) ? true : false;

        $locs[] = [
            'loc' => App::setting('home').'/blog/blog?act=view&id='.$blog['id'],
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
    $newses = DBM::run()->query("SELECT n.*, MAX(c.time) last_time FROM news n LEFT JOIN comments c ON n.id = c.relate_id AND relate_type='news' GROUP BY n.id;");

    $locs = [];
    foreach ($newses as $news) {

        $changeTime = ($news['last_time'] > $news['time']) ? $news['last_time'] : $news['time'];

        // Обновлено менее 1 месяца
        $new = (SITETIME < $changeTime + 3600 * 24 * 30) ? true : false;

        $locs[] = [
            'loc' => App::setting('home').'/news/'.$news['id'],
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
    $events = DBM::run()->query("SELECT e.*, MAX(c.time) last_time FROM events e LEFT JOIN comments c ON e.id = c.relate_id AND relate_type='event' GROUP BY e.id;");
    $locs = [];
    foreach ($events as $event) {

        $changeTime = ($event['last_time'] > $event['time']) ? $event['last_time'] : $event['time'];

        // Обновлено менее 1 месяца
        $new = (SITETIME < $changeTime + 3600 * 24 * 30) ? true : false;

        $locs[] = [
            'loc' => App::setting('home').'/events?act=read&id='.$event['id'],
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
    $topics = DBM::run()->select('topics', null, 25000);

    $locs = [];
    foreach ($topics as $topic) {

        // Обновлено менее 1 месяца
        $new = (SITETIME < $topic['last_time'] + 3600 * 24 * 30) ? true : false;

        $locs[] = [
            'loc' => App::setting('home').'/topic/'.$topic['id'],
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
        $downs = DBM::run()->query("SELECT d.*, MAX(c.time) last_time FROM downs d LEFT JOIN comments c ON d.id = c.relate_id AND relate_type='down' GROUP BY d.id;");

        $locs = [];
        foreach ($downs as $down) {

            $changeTime = ($down['last_time'] > $down['time']) ? $down['last_time'] : $down['time'];

            // Обновлено менее 1 месяца
            $new = (SITETIME < $changeTime + 3600 * 24 * 30) ? true : false;

            $locs[] = [
                'loc' => App::setting('home').'/load/down?act=view&id='.$down['id'],
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
