<?php

$router = new AltoRouter();

$router->addMatchTypes(['user' => '[0-9A-Za-z-_]++']);

$router->map( 'GET', '/', function() {
    App::view('index');
});

$router->map('GET', '/book', '/modules/book/index.php', 'book');
$router->map('POST', '/book/[complaint|add:action]', '/modules/book/index.php');
$router->map('GET|POST', '/book/[edit:action]/[i:id]', '/modules/book/index.php');

$router->map('GET', '/forum', '/modules/forum/index.php', 'forum');
$router->map('GET', '/forum/[i:fid]', '/modules/forum/forum.php');
$router->map('GET', '/topic/[i:tid]', '/modules/forum/topic.php');
$router->map('GET', '/topic/[i:tid]/[i:id]', '/modules/forum/topic.php@viewpost');
$router->map('GET', '/topic/[i:tid]/[end:action]', '/modules/forum/topic.php');
$router->map('POST', '/topic/[i:tid]/[create:action]', '/modules/forum/topic.php');
$router->map('GET', '/forum/new/[posts|themes:action]', '/modules/forum/new.php');
$router->map('GET', '/forum/active/[posts|themes:action]', '/modules/forum/active.php');
$router->map('POST', '/forum/active/[delete:action]', '/modules/forum/active.php');
$router->map('GET', '/forum/top/themes', '/modules/forum/top.php');
$router->map('GET', '/forum/search', '/modules/forum/search.php');
$router->map('GET|POST', '/forum/[create:action]', '/modules/forum/forum.php');
$router->map('GET', '/forum/bookmark', '/modules/forum/bookmark.php');
$router->map('POST', '/forum/bookmark/[delete|perform:action]', '/modules/forum/bookmark.php');

$router->map('GET|POST', '/login', '/modules/pages/login.php', 'login');
$router->map('GET|POST', '/register', '/modules/pages/registration.php', 'register');
$router->map('GET', '/logout', '/modules/pages/login.php@logout', 'logout');
$router->map('GET', '/user/[user:login]', '/modules/pages/user.php', 'profile');
$router->map('GET|POST', '/user/[user:login]/[note:action]', '/modules/pages/user.php', 'note');

$router->map('GET', '/tags', '/modules/pages/tags.php', 'tags');
$router->map('GET', '/rules', '/modules/pages/rules.php', 'smiles');
$router->map('GET', '/smiles', '/modules/pages/smiles.php', 'rules');
$router->map('GET', '/captcha', '/modules/gallery/protect.php', 'captcha');
$router->map('GET', '/online', '/modules/pages/online.php', 'online');
$router->map('GET', '/online/[all:action]', '/modules/pages/online.php', 'online_all');

$router->map('GET|POST', '/files', '/modules/files/index.php', 'files');

Registry::set('router', $router->match());
