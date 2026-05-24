<?php

use App\Classes\Hook;

/**
 * Хуки смотрите в исходном коде сайта, html комментарии начинаются с символа @, к примеру <!--@head-->
 * Хуки имеют приоритет 0 по умолчанию, чем выше приоритет, тем раньше вызывается хук
 *
 * Callback возвращает свой фрагмент (или null/'' чтобы ничего не добавлять).
 */

// Пример css хука
// Hook::add('head', '<link rel="stylesheet" href="/assets/styles.css">');

// Пример js хука
// Hook::add('footer', '<script type="module" src="/assets/scripts.js"></script>');

// Пример вставки счетчика в футер только для главной страницы
/*Hook::add('footerEnd', function () {
    if (request()->routeIs('home')) {
        return '<a href="/"><img src="/assets/img/images/logo.png" alt="text"></a>';
    }

    return null;
});*/

// Пример вставки ссылки в меню сайта с приоритетом 10
/*Hook::add('sidebarMenu', function () {
    return '<li>
        <a class="menu-item' . (request()->is('page*') ? ' active' : '') . '" href="/">
            <i class="menu-icon fa-solid fa-home"></i>
            <span class="menu-label">Текст в меню</span>
            <span class="badge menu-badge">5</span>
        </a>
    </li>';
}, 10);*/

// Пример вставки ссылки в футер сайта
// Hook::add('footerColumnMiddle', '<li><a class="footer-item" href="/page">Текст ссылки</a></li>');
