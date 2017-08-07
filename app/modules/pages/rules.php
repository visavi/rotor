<?php

$rules = Rule::first();

if ($rules) {
    $rules['text'] = str_replace(
        ['%SITENAME%', '%MAXBAN%'],
        [Setting::get('title'), round(Setting::get('maxbantime') / 1440)],
        $rules['text']
    );
}

App::view('pages/rules', compact('rules'));
