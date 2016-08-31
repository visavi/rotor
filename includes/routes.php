<?php

$router = new AltoRouter();

$router->addMatchTypes(['user' => '[0-9A-Za-z-_]++', 'slug' => '[0-9a-z-_]++']);

$router->map( 'GET', '/', function() {
    App::view('index');
});

$router->map('GET', '/book', '/modules/book/index.php', 'book');
$router->map('POST', '/book/[add|edit:action]', '/modules/book/index.php');

Registry::set('router', $router->match());
