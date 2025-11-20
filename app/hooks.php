<?php

use App\Classes\Hook;

/**
 * Хуки смотрите в исходном коде сайта, html комментарии начинаются с символа @, к примеру <!--@head-->
 */

// Пример css хука
/* Hook::add('head', function ($content) {
    return $content . '<link rel="stylesheet" href="/assets/styles.css">' . PHP_EOL;
});*/

// Пример js хука
/*Hook::add('footer', function ($content) {
    return $content . '<script type="module" src="/assets/scripts.js"></script>' . PHP_EOL;
});*/
