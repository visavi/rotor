<?php

$router = new AltoRouter();

$router->addMatchTypes(['user' => '[0-9A-Za-z-_]++']);

$router->map( 'GET', '/', function() {
    App::view('index');
});

$router->map( 'GET', '/search', function() {
    App::view('search/index');
});

$router->map('GET', '/book', 'book/index.php', 'book');
$router->map('POST', '/book/[complaint|add:action]', 'book/index.php');
$router->map('GET|POST', '/book/[edit:action]/[i:id]', 'book/index.php');

$router->map('GET', '/sitemap.xml', 'pages/sitemap.php');
$router->map('GET', '/sitemap/[a:action].xml', 'pages/sitemap.php');

$router->map('GET', '/blog', 'blog/index.php', 'blog');
$router->map('GET', '/blog/[i:cid]', 'blog/blog.php');
$router->map('GET', '/article/[i:id]', 'blog/blog.php@view');
$router->map('GET', '/article/[i:id]/print', 'blog/print.php');
$router->map('GET', '/blog/rss', 'blog/rss.php');
$router->map('GET', '/article/[i:id]/rss', 'blog/rss.php@comments');
$router->map('GET', '/article/[i:id]/[comments|end:action]', 'blog/blog.php');
$router->map('GET', '/blog/tags/[*:tag]?', 'blog/tags.php');



$router->map('GET|POST', '/blog/active', 'blog/active.php');
$router->map('GET|POST', '/blog/new',    'blog/new.php');
$router->map('GET|POST', '/blog/search', 'blog/search.php');
$router->map('GET|POST', '/blog/top',    'blog/top.php');

$router->map('GET', '/news', 'news/index.php', 'news');
$router->map('GET', '/news/[i:id]', 'news/index.php@view');
$router->map('GET|POST', '/news/[i:id]/[delete|comments|end:action]', 'news/index.php');
$router->map('POST', '/news/[i:id]/[create:action]', 'news/index.php');
$router->map('GET', '/news/allcomments', 'news/comments.php');
$router->map('GET', '/news/allcomments/[i:nid]/[i:id]', 'news/comments.php@viewcomm');
$router->map('GET', '/news/rss', 'news/rss.php', 'news_rss');

$router->map('GET', '/gallery', 'gallery/index.php', 'gallery');
$router->map('GET', '/gallery/[i:gid]', 'gallery/index.php@view');
$router->map('GET', '/gallery/[i:gid]/comments', 'gallery/comments.php');
$router->map('GET', '/gallery/[i:gid]/[end:action]', 'gallery/index.php');


$router->map('GET|POST', '/gallery/album', 'gallery/album.php');
$router->map('GET|POST', '/gallery/top', 'gallery/top.php');

$router->map('GET', '/forum', 'forum/index.php', 'forum');
$router->map('GET', '/forum/[i:fid]', 'forum/forum.php');
$router->map('GET', '/forum/new/[posts|themes:action]', 'forum/new.php');
$router->map('GET', '/forum/active/[posts|themes:action]', 'forum/active.php');
$router->map('GET', '/forum/top/[posts|themes:action]', 'forum/top.php');
$router->map('GET', '/forum/search', 'forum/search.php');
$router->map('GET', '/forum/bookmark', 'forum/bookmark.php');
$router->map('POST', '/forum/active/[delete:action]', 'forum/active.php');
$router->map('POST', '/forum/bookmark/[delete|perform:action]', 'forum/bookmark.php');
$router->map('GET|POST', '/forum/[create:action]', 'forum/forum.php');
$router->map('GET', '/topic/[i:tid]', 'forum/topic.php');
$router->map('GET', '/topic/[i:tid]/[i:id]', 'forum/topic.php@viewpost');
$router->map('GET', '/forum/rss', 'forum/rss.php');
$router->map('GET', '/topic/[i:tid]/rss', 'forum/rss.php@posts');
$router->map('GET', '/topic/[i:tid]/print', 'forum/print.php');
$router->map('POST', '/topic/[i:tid]/vote', 'forum/topic.php@vote');
$router->map('GET', '/topic/[i:tid]/[end|close:action]', 'forum/topic.php');
$router->map('POST', '/topic/[i:tid]/[create|delete|complaint:action]', 'forum/topic.php');
$router->map('POST', '/topic/[complaint:action]', 'forum/topic.php');
$router->map('GET|POST', '/topic/[i:tid]/[i:id]/edit', 'forum/topic.php@editpost');
$router->map('GET|POST', '/topic/[i:tid]/[edit:action]', 'forum/topic.php');

$router->map('GET', '/logout', 'pages/login.php@logout', 'logout');
$router->map('GET', '/user/[user:login]', 'pages/user.php');
$router->map('GET|POST', '/login', 'pages/login.php', 'login');
$router->map('GET|POST', '/register', 'pages/registration.php', 'register');
$router->map('GET|POST', '/user/[user:login]/[note:action]', 'pages/user.php', 'note');

$router->map('GET|POST', '/mail', 'mail/index.php', 'mail');
$router->map('GET|POST', '/recovery', 'mail/recovery.php', 'recovery');
$router->map('GET', '/recovery/restore', 'mail/recovery.php@restore');
$router->map('GET|POST', '/unsubscribe', 'mail/unsubscribe.php', 'unsubscribe');

$router->map('GET', '/menu', 'pages/index.php@menu');
$router->map('GET', '/page/[a:action]?', 'pages/index.php');
$router->map('GET', '/tags', 'pages/tags.php', 'tags');
$router->map('GET', '/rules', 'pages/rules.php', 'smiles');
$router->map('GET', '/smiles', 'pages/smiles.php', 'rules');
$router->map('GET', '/captcha', 'gallery/protect.php', 'captcha');
$router->map('GET', '/online/[all:action]?', 'pages/online.php', 'online');

$router->map('POST', '/ajax/bbcode', 'ajax/bbcode.php');
$router->map('POST', '/ajax/rating', 'ajax/rating.php');

$router->map('GET|POST', '/wall',     'pages/wall.php', 'wall');
$router->map('GET|POST', '/setting',  'pages/setting.php');

$router->map('GET', '/private/[outbox|trash|history|clear:action]?', 'pages/private.php');
$router->map('POST', '/private/[complaint|delete:action]', 'pages/private.php');
$router->map('GET|POST', '/private/[send:action]', 'pages/private.php');

$router->map('GET', '/ignore',   'pages/ignore.php');
$router->map('GET|POST', '/ignore/[note:action]/[i:id]',  'pages/ignore.php');
$router->map('POST', '/ignore/[create|delete:action]',  'pages/ignore.php');

$router->map('GET', '/contact',  'pages/contact.php');
$router->map('GET|POST', '/contact/[note:action]/[i:id]',  'pages/contact.php');
$router->map('POST', '/contact/[create|delete:action]',  'pages/contact.php');

$router->map('GET', '/key',  'pages/key.php');

$router->map('GET|POST', '/profile',  'pages/profile.php');
$router->map('GET|POST', '/account',  'pages/account.php');
$router->map('GET|POST', '/offers',   'pages/offers.php');
$router->map('GET|POST', '/notebook',  'pages/notebook.php');
$router->map('GET|POST', '/rathist',   'pages/rathist.php');
$router->map('GET',      '/reklama',   'pages/reklama.php');
$router->map('GET|POST', '/reklama/[create:action]',   'pages/reklama.php');
$router->map('GET|POST', '/authlog',   'pages/authlog.php');
$router->map('GET|POST', '/userlist',  'pages/userlist.php');
$router->map('GET|POST', '/onlinewho',  'pages/onlinewho.php');
$router->map('GET|POST', '/who',  'pages/who.php');
$router->map('GET|POST', '/adminlist',  'pages/adminlist.php');
$router->map('GET|POST', '/searchuser',  'pages/searchuser.php');
$router->map('GET', '/counter/[24|31:action]?',  'pages/counter.php');
$router->map('GET|POST', '/authoritylist',  'pages/authoritylist.php');
$router->map('GET|POST', '/ban',  'pages/ban.php');
$router->map('GET|POST', '/banip',  'pages/banip.php');
$router->map('GET|POST', '/razban',  'pages/razban.php');
$router->map('GET|POST', '/banhist',  'pages/banhist.php');
$router->map('GET|POST', '/statusfaq',  'pages/statusfaq.php');
$router->map('GET|POST', '/user/[user:login]/[rating:action]', 'pages/rating.php');
$router->map('GET|POST', '/ratinglist',  'pages/ratinglist.php');
$router->map('GET|POST', '/faq',  'pages/faq.php');
$router->map('GET|POST', '/closed',  'pages/closed.php');
$router->map('GET|POST', '/pictures', 'pages/pictures.php');
$router->map('GET', '/pictures/[delete:action]', 'pages/pictures.php');

$router->map('GET|POST', '/events', 'events/index.php', 'events');

$router->map('GET|POST', '/files/[*:page]?', 'files/index.php', 'files');

$router->map('GET|POST', '/chat', 'chat/index.php', 'chat');

$router->map('GET|POST', '/board', 'board/index.php', 'board');

$router->map('GET|POST', '/games',           'games/index.php');
$router->map('GET|POST', '/games/bank',      'games/bank.php');
$router->map('GET|POST', '/games/credit',    'games/credit.php');
$router->map('GET|POST', '/games/transfer',  'games/transfer.php');
$router->map('GET|POST', '/games/livebank',  'games/livebank.php');
$router->map('GET|POST', '/games/safe',      'games/safe.php');
$router->map('GET|POST', '/games/loterea',   'games/loterea.php');
$router->map('GET|POST', '/games/21',        'games/21.php');
$router->map('GET|POST', '/games/hi',        'games/hi.php');
$router->map('GET|POST', '/games/bandit',    'games/bandit.php');
$router->map('GET|POST', '/games/kosti',     'games/kosti.php');
$router->map('GET|POST', '/games/naperstki', 'games/naperstki.php');

$router->map('GET|POST', '/load',        'load/index.php');
$router->map('GET|POST', '/load/active', 'load/active.php');
$router->map('GET|POST', '/load/add',    'load/add.php');
$router->map('GET|POST', '/load/down',   'load/down.php');
$router->map('GET|POST', '/load/fresh',  'load/fresh.php');
$router->map('GET|POST', '/load/new',    'load/new.php');
$router->map('GET|POST', '/load/rss',    'load/rss.php');
$router->map('GET|POST', '/load/search', 'load/search.php');
$router->map('GET|POST', '/load/top',    'load/top.php');
$router->map('GET|POST', '/load/zip',    'load/zip.php');

$router->map('GET|POST', '/votes',         'votes/index.php');
$router->map('GET|POST', '/votes/history', 'votes/history.php');

$router->map('GET', '/api', 'api/index.php');
$router->map('GET', '/api/forum', 'api/forum.php');
$router->map('GET', '/api/private', 'api/private.php');
$router->map('GET', '/api/user', 'api/user.php');

$router->map('GET', '/admin',              'admin/index.php', 'admin');
$router->map('GET|POST', '/admin/board',    'admin/board.php');
$router->map('GET|POST', '/admin/book',    'admin/book.php');
$router->map('GET|POST', '/admin/blog',    'admin/blog.php');
$router->map('GET|POST', '/admin/chat',    'admin/chat.php');
$router->map('GET', '/admin/cache/[image|clear|clearimage:action]?',        'admin/cache.php');
$router->map('GET|POST', '/admin/events',    'admin/events.php');
$router->map('GET|POST', '/admin/forum',   'admin/forum.php');
$router->map('GET|POST', '/admin/minichat',    'admin/minichat.php');
$router->map('GET|POST', '/admin/news',    'admin/news.php');
$router->map('GET|POST', '/admin/gallery', 'admin/gallery.php');
$router->map('GET|POST', '/admin/load',    'admin/load.php');
$router->map('GET|POST', '/admin/newload',    'admin/newload.php');
$router->map('GET|POST', '/admin/setting',    'admin/setting.php');
$router->map('GET|POST', '/admin/reklama',    'admin/reklama.php');
$router->map('GET|POST', '/admin/ban',    'admin/ban.php');
$router->map('GET|POST', '/admin/banhist',    'admin/banhist.php');
$router->map('GET|POST', '/admin/banlist',    'admin/banlist.php');
$router->map('GET|POST', '/admin/ipban',    'admin/ipban.php');
$router->map('GET|POST', '/admin/adminlist',    'admin/adminlist.php');
$router->map('GET|POST', '/admin/users',    'admin/users.php');
$router->map('GET|POST', '/admin/logs',    'admin/logs.php');
$router->map('GET|POST', '/admin/spam',    'admin/spam.php');
$router->map('GET|POST', '/admin/reglist',    'admin/reglist.php');
$router->map('GET|POST', '/admin/votes',    'admin/votes.php');
$router->map('GET|POST', '/admin/antimat',    'admin/antimat.php');
$router->map('GET|POST', '/admin/invitations',    'admin/invitations.php');
$router->map('GET|POST', '/admin/transfers',    'admin/transfers.php');
$router->map('GET|POST', '/admin/rules',    'admin/rules.php');
$router->map('GET|POST', '/admin/users',    'admin/users.php');
$router->map('GET|POST', '/admin/phpinfo',    'admin/phpinfo.php');
$router->map('GET|POST', '/admin/blacklist',    'admin/blacklist.php');
$router->map('GET|POST', '/admin/offers',    'admin/offers.php');
$router->map('GET|POST', '/admin/smiles',    'admin/smiles.php');
$router->map('GET|POST', '/admin/status',    'admin/status.php');
$router->map('GET|POST', '/admin/backup',    'admin/backup.php');
$router->map('GET|POST', '/admin/checker',    'admin/checker.php');
$router->map('GET|POST', '/admin/delivery',    'admin/delivery.php');
$router->map('GET|POST', '/admin/logadmin',    'admin/logadmin.php');
$router->map('GET|POST', '/admin/notice',    'admin/notice.php');
$router->map('GET|POST', '/admin/files',    'admin/files.php');
$router->map('GET|POST', '/admin/delusers',    'admin/delusers.php');
$router->map('GET',      '/admin/upgrade',    'admin/upgrade.php');

$router->map('GET',      '/surprise',    'pages/surprise.php');

Registry::set('router', $router);
