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
    $blogs = DBM::run()->select('blogs');

    $locs = [];
    foreach ($blogs as $blog) {

        // Обновлено менее 1 месяца
        $new = (SITETIME < $blog['time'] + 3600 * 24 * 30) ? true : false;

        $locs[] = [
            'loc' => App::setting('home').'/blog/blog?act=view&id='.$blog['id'],
            'lastmod' => date('c', $blog['time']),
            'changefreq' => $new ? 'weekly' : 'monthly',
            'priority' => $new ? '1.0' : '0.8',
        ];
    }
    App::view('sitemap/url', compact('locs'));
break;
/**
 * Генерируем новости
 */
case 'news':
    $newses = DBM::run()->select('news');

    $locs = [];
    foreach ($newses as $news) {

        // Обновлено менее 1 месяца
        $new = (SITETIME < $news['time'] + 3600 * 24 * 30) ? true : false;

        $locs[] = [
            'loc' => App::setting('home').'/news/'.$news['id'],
            'lastmod' => date('c', $news['time']),
            'changefreq' => $new ? 'weekly' : 'monthly',
            'priority' => $new ? '1.0' : '0.8',
        ];
    }
    App::view('sitemap/url', compact('locs'));
break;

/**
 * Генерируем события
 */
case 'events':
    $events = DBM::run()->select('events');

    $locs = [];
    foreach ($events as $event) {

        // Обновлено менее 1 месяца
        $new = (SITETIME < $event['time'] + 3600 * 24 * 30) ? true : false;

        $locs[] = [
            'loc' => App::setting('home').'/events?act=read&id='.$event['id'],
            'lastmod' => date('c', SITETIME),
            'changefreq' => $new ? 'weekly' : 'monthly',
            'priority' => $new ? '1.0' : '0.8',
        ];
    }
    App::view('sitemap/url', compact('locs'));
break;

/**
 * Генерируем темы форума
 */
case 'topics':
    $topics = DBM::run()->select('topics');

    $locs = [];
    foreach ($topics as $topic) {

        // Обновлено менее 1 месяца
        $new = (SITETIME < $topic['last_time'] + 3600 * 24 * 30) ? true : false;

        $locs[] = [
            'loc' => App::setting('home').'/topic/'.$topic['id'],
            'lastmod' => date('c', $topic['last_time']),
            'changefreq' => $new ? 'weekly' : 'monthly',
            'priority' => $new ? '1.0' : '0.8',
        ];
    }
    App::view('sitemap/url', compact('locs'));
break;

default:
    App::abort(404);
endswitch;
