<?php

$requestUrl = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
$requestMethod = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';

$router = new TreeRoute\Router();

$router->addRoute('GET', '/book', '/modules/book/index.php');
$router->addRoute('POST', '/book/{action:[complaint|add]}', '/modules/book/index.php');
$router->addRoute(['GET','POST'], '/book/{action:[edit|eee]+}/{id:[0-9]+}', '/modules/book/index.php');

$router->addRoute('GET', '/news', '/modules/news/index.php');
$router->addRoute('GET', '/news/create', '/modules/news/index.php');

$router->addRoute('GET', '/files/{page:[a-zA-Z\-]+}', '/modules/files/index.php');
$router->addRoute('GET', '/files/{page:[a-zA-Z\-]+}/{page2:[a-zA-Z\-]+}', '/modules/files/index.php');


$routes = $router->getRoutes();

$result = $router->dispatch($requestMethod, $requestUrl);

//var_dump($result);
