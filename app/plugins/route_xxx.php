<?php

$router = new AltoRouter();
$router->map('GET', '/xxx',    '/modules/book/index.php');

//Registry::set('router', $router->match());


$routes = $router->getRoutes();

var_dump($routes);
