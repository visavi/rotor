<?php

$router = new AltoRouter();

$router->addMatchTypes(['user' => '[0-9A-Za-z-_]++']);

$router->map( 'GET', '/', function() {
    App::view('index');
});

$router->map('GET', '/book', '/modules/book/index.php', 'book');
$router->map('POST', '/book/[complaint|add:action]', '/modules/book/index.php');
$router->map('GET|POST', '/book/[edit:action]/[i:id]', '/modules/book/index.php');

$router->map('GET', '/news', '/modules/news/index.php', 'news');
$router->map('GET', '/news/[i:id]', '/modules/news/index.php@view');
$router->map('GET|POST', '/news/[i:id]/[delete|comments|end:action]', '/modules/news/index.php');
$router->map('POST', '/news/[i:id]/[create:action]', '/modules/news/index.php');
$router->map('GET', '/news/allcomments', '/modules/news/comments.php');
$router->map('GET', '/news/allcomments/[i:nid]/[i:id]', '/modules/news/comments.php@viewcomm');
$router->map('GET', '/news/rss', '/modules/news/rss.php', 'news_rss');

$router->map('GET|POST', '/gallery', '/modules/gallery/index.php', 'gallery');
$router->map('GET|POST', '/gallery/album', '/modules/gallery/album.php');
$router->map('GET|POST', '/gallery/comments', '/modules/gallery/comments.php');
$router->map('GET|POST', '/gallery/top', '/modules/gallery/top.php');

$router->map('GET', '/forum', '/modules/forum/index.php', 'forum');
$router->map('GET', '/forum/[i:fid]', '/modules/forum/forum.php');
$router->map('GET', '/forum/new/[posts|themes:action]', '/modules/forum/new.php');
$router->map('GET', '/forum/active/[posts|themes:action]', '/modules/forum/active.php');
$router->map('GET', '/forum/top/themes', '/modules/forum/top.php');
$router->map('GET', '/forum/search', '/modules/forum/search.php');
$router->map('GET', '/forum/bookmark', '/modules/forum/bookmark.php');
$router->map('POST', '/forum/active/[delete:action]', '/modules/forum/active.php');
$router->map('POST', '/forum/bookmark/[delete|perform:action]', '/modules/forum/bookmark.php');
$router->map('GET|POST', '/forum/[create:action]', '/modules/forum/forum.php');
$router->map('GET', '/topic/[i:tid]', '/modules/forum/topic.php');
$router->map('GET', '/topic/[i:tid]/[i:id]', '/modules/forum/topic.php@viewpost');
$router->map('GET', '/topic/[i:tid]/rss', '/modules/forum/rss.php');
$router->map('GET', '/topic/[i:tid]/print', '/modules/forum/print.php');
$router->map('GET', '/topic/[i:tid]/[end|close:action]', '/modules/forum/topic.php');
$router->map('POST', '/topic/[i:tid]/[create|delete|complaint:action]', '/modules/forum/topic.php');
$router->map('POST', '/topic/[complaint:action]', '/modules/forum/topic.php');
$router->map('GET|POST', '/topic/[i:tid]/[i:id]/edit', '/modules/forum/topic.php@editpost');
$router->map('GET|POST', '/topic/[i:tid]/[edit:action]', '/modules/forum/topic.php');

$router->map('GET', '/logout', '/modules/pages/login.php@logout', 'logout');
$router->map('GET', '/user/[user:login]', '/modules/pages/user.php');
$router->map('GET|POST', '/login', '/modules/pages/login.php', 'login');
$router->map('GET|POST', '/register', '/modules/pages/registration.php', 'register');
$router->map('GET|POST', '/user/[user:login]/[note:action]', '/modules/pages/user.php', 'note');

$router->map('GET|POST', '/mail', '/modules/mail/index.php', 'mail');
$router->map('GET|POST', '/lostpassword', '/modules/mail/lostpassword.php', 'lostpassword');
$router->map('GET|POST', '/unsubscribe', '/modules/mail/unsubscribe.php', 'unsubscribe');

$router->map('GET', '/menu', '/modules/pages/index.php@menu');
$router->map('GET', '/page/[recent:action]?', '/modules/pages/index.php');
$router->map('GET', '/tags', '/modules/pages/tags.php', 'tags');
$router->map('GET', '/rules', '/modules/pages/rules.php', 'smiles');
$router->map('GET', '/smiles', '/modules/pages/smiles.php', 'rules');
$router->map('GET', '/captcha', '/modules/gallery/protect.php', 'captcha');
$router->map('GET', '/online/[all:action]?', '/modules/pages/online.php', 'online');
$router->map('GET|POST', '/wall', '/modules/pages/wall.php', 'wall');
$router->map('GET|POST', '/setting', '/modules/pages/setting.php');
$router->map('GET|POST', '/private', '/modules/pages/private.php');
$router->map('GET|POST', '/ignore', '/modules/pages/ignore.php');
$router->map('GET|POST', '/contact', '/modules/pages/contact.php');
$router->map('GET|POST', '/profile', '/modules/pages/profile.php');
$router->map('GET|POST', '/account', '/modules/pages/account.php');
$router->map('GET|POST', '/pictures', '/modules/pages/pictures.php');
$router->map('GET|POST', '/offers', '/modules/pages/offers.php');

$router->map('GET|POST', '/events', '/modules/events/index.php', 'events');

$router->map('GET|POST', '/files', '/modules/files/index.php', 'files');

$router->map('GET|POST', '/chat', '/modules/chat/index.php', 'chat');

$router->map('GET|POST', '/games',           '/modules/games/index.php');
$router->map('GET|POST', '/games/bank',      '/modules/games/bank.php');
$router->map('GET|POST', '/games/credit',    '/modules/games/credit.php');
$router->map('GET|POST', '/games/transfer',  '/modules/games/transfer.php');
$router->map('GET|POST', '/games/livebank',  '/modules/games/livebank.php');
$router->map('GET|POST', '/games/livebank',  '/modules/games/livebank.php');
$router->map('GET|POST', '/games/safe',      '/modules/games/safe.php');
$router->map('GET|POST', '/games/loterea',   '/modules/games/loterea.php');
$router->map('GET|POST', '/games/21',        '/modules/games/21.php');
$router->map('GET|POST', '/games/hi',        '/modules/games/hi.php');
$router->map('GET|POST', '/games/bandit',    '/modules/games/bandit.php');
$router->map('GET|POST', '/games/kosti',     '/modules/games/kosti.php');
$router->map('GET|POST', '/games/naperstki', '/modules/games/naperstki.php');

$router->map('GET|POST', '/votes',         '/modules/votes/index.php');
$router->map('GET|POST', '/votes/history', '/modules/votes/history.php');

$router->map('GET', '/admin',              '/modules/admin/index.php', 'admin');
$router->map('GET', '/admin/cache',        '/modules/admin/cache.php');
$router->map('GET', '/admin/cache/[image|clear|clearimage:action]', '/modules/admin/cache.php');
$router->map('GET|POST', '/admin/forum',   '/modules/admin/forum.php');
$router->map('GET|POST', '/admin/news',    '/modules/admin/news.php');
$router->map('GET|POST', '/admin/gallery', '/modules/admin/gallery.php');

Registry::set('router', $router->match());
