<?php

use App\Classes\Hook;

/**
 * Хуки смотрите в исходном коде сайта, html комментарии начинаются с символа @, к примеру <!--@head-->
 * Хуки имеют приоритет 0 по умолчанию, чем выше приоритет, тем раньше вызывается хук
 */

// Пример css хука
/* Hook::add('head', function ($content) {
    return $content . '<link rel="stylesheet" href="/assets/styles.css">' . PHP_EOL;
});*/

// Пример js хука
/*Hook::add('footer', function ($content) {
    return $content . '<script type="module" src="/assets/scripts.js"></script>' . PHP_EOL;
});*/

// Пример вставки счетчика в футер только для главной страницы
/*Hook::add('footerEnd', function ($content) {
    if (request()->routeIs('home')) {
        return $content . '<a href="/"><img src="/assets/img/images/logo.png" alt="text"></a>' . PHP_EOL;
    }

    return $content;
});*/

// Пример вставки ссылки в sidebar (левое меню) сайта с приоритетом 10
/*Hook::add('sidebarMenuEnd', function ($content) {
    return $content . '<li>
        <a class="app-menu__item' . (request()->is('page*') ? ' active' : '') . '" href="/">
            <i class="app-menu__icon fa-solid fa-home"></i>
            <span class="app-menu__label">Текст в меню</span>
        </a>
    </li>' . PHP_EOL;
}, 10);*/
