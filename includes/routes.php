<?php

$router = new AltoRouter();

$router->addMatchTypes(['user' => '[0-9A-Za-z-_]++']);

$router->map( 'GET', '/', function() {
    App::view('index');
});

$router->map('GET', '/book', '/modules/book/index.php', 'book');
$router->map('POST', '/book/[spam|add:action]', '/modules/book/index.php');
$router->map('GET|POST', '/book/[edit:action]/[i:id]', '/modules/book/index.php');

Registry::set('router', $router->match());
