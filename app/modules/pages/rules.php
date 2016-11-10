<?php
App::view($config['themes'].'/index');

show_title('Правила сайта');

$rules = DB::run() -> queryFetch("SELECT * FROM `rules`;");

if (!empty($rules)) {
    $rules['text'] = str_replace(
        ['%SITENAME%', '%MAXBAN%'],
        [$config['title'], round($config['maxbantime'] / 1440)],
        $rules['text']
    );

    echo App::bbCode($rules['text']).'<br />';
} else {
    show_error('Правила сайта еще не установлены!');
}

App::view($config['themes'].'/foot');
