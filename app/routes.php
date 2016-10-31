<?php

$router = new AltoRouter();

$router->addMatchTypes(['user' => '[0-9A-Za-z-_]++']);

$router->map( 'GET', '/', function() {
    App::view('index');
});

$router->map('GET', '/book', '/modules/book/index.php', 'book');
$router->map('POST', '/book/[complaint|add:action]', '/modules/book/index.php');
$router->map('GET|POST', '/book/[edit:action]/[i:id]', '/modules/book/index.php');

$router->map('GET',      '/blog',        '/modules/blog/index.php', 'blog');
$router->map('GET|POST', '/blog/active', '/modules/blog/active.php');
$router->map('GET|POST', '/blog/blog',   '/modules/blog/blog.php');
$router->map('GET|POST', '/blog/print',  '/modules/blog/print.php');
$router->map('GET|POST', '/blog/rss',    '/modules/blog/rss.php');
$router->map('GET|POST', '/blog/search', '/modules/blog/search.php');
$router->map('GET|POST', '/blog/tags',   '/modules/blog/tags.php');
$router->map('GET|POST', '/blog/top',    '/modules/blog/top.php');

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
$router->map('GET', '/page/[a:action]?', '/modules/pages/index.php');
$router->map('GET', '/tags', '/modules/pages/tags.php', 'tags');
$router->map('GET', '/rules', '/modules/pages/rules.php', 'smiles');
$router->map('GET', '/smiles', '/modules/pages/smiles.php', 'rules');
$router->map('GET', '/captcha', '/modules/gallery/protect.php', 'captcha');
$router->map('GET', '/online/[all:action]?', '/modules/pages/online.php', 'online');

$router->map('POST', '/ajax/bbcode', '/modules/ajax/bbcode.php');

$router->map('GET|POST', '/wall',     '/modules/pages/wall.php', 'wall');
$router->map('GET|POST', '/setting',  '/modules/pages/setting.php');
$router->map('GET|POST', '/private',  '/modules/pages/private.php');
$router->map('GET|POST', '/ignore',   '/modules/pages/ignore.php');
$router->map('GET|POST', '/contact',  '/modules/pages/contact.php');
$router->map('GET|POST', '/profile',  '/modules/pages/profile.php');
$router->map('GET|POST', '/account',  '/modules/pages/account.php');
$router->map('GET|POST', '/pictures', '/modules/pages/pictures.php');
$router->map('GET|POST', '/offers',   '/modules/pages/offers.php');
$router->map('GET|POST', '/notebook',  '/modules/pages/notebook.php');
$router->map('GET|POST', '/rathist',   '/modules/pages/rathist.php');
$router->map('GET',      '/reklama',   '/modules/pages/reklama.php');
$router->map('GET|POST', '/reklama/[create:action]',   '/modules/pages/reklama.php');
$router->map('GET|POST', '/authlog',   '/modules/pages/authlog.php');
$router->map('GET|POST', '/userlist',  '/modules/pages/userlist.php');
$router->map('GET|POST', '/onlinewho',  '/modules/pages/onlinewho.php');
$router->map('GET|POST', '/who',  '/modules/pages/who.php');
$router->map('GET|POST', '/adminlist',  '/modules/pages/adminlist.php');
$router->map('GET|POST', '/searchuser',  '/modules/pages/searchuser.php');
$router->map('GET', '/counter/[24|31:action]?',  '/modules/pages/counter.php');
$router->map('GET|POST', '/authoritylist',  '/modules/pages/authoritylist.php');
$router->map('GET|POST', '/ban',  '/modules/pages/ban.php');
$router->map('GET|POST', '/razban',  '/modules/pages/razban.php');
$router->map('GET|POST', '/banhist',  '/modules/pages/banhist.php');
$router->map('GET|POST', '/statusfaq',  '/modules/pages/statusfaq.php');
$router->map('GET|POST', '/rating',  '/modules/pages/rating.php');
$router->map('GET|POST', '/ratinglist',  '/modules/pages/ratinglist.php');
$router->map('GET|POST', '/key',  '/modules/pages/key.php');
$router->map('GET|POST', '/faq',  '/modules/pages/faq.php');
$router->map('GET|POST', '/closed',  '/modules/pages/closed.php');

$router->map('GET|POST', '/events', '/modules/events/index.php', 'events');

$router->map('GET|POST', '/files/[*:page]?', '/modules/files/index.php', 'files');

$router->map('GET|POST', '/chat', '/modules/chat/index.php', 'chat');

$router->map('GET|POST', '/board', '/modules/board/index.php', 'board');

$router->map('GET|POST', '/games',           '/modules/games/index.php');
$router->map('GET|POST', '/games/bank',      '/modules/games/bank.php');
$router->map('GET|POST', '/games/credit',    '/modules/games/credit.php');
$router->map('GET|POST', '/games/transfer',  '/modules/games/transfer.php');
$router->map('GET|POST', '/games/livebank',  '/modules/games/livebank.php');
$router->map('GET|POST', '/games/safe',      '/modules/games/safe.php');
$router->map('GET|POST', '/games/loterea',   '/modules/games/loterea.php');
$router->map('GET|POST', '/games/21',        '/modules/games/21.php');
$router->map('GET|POST', '/games/hi',        '/modules/games/hi.php');
$router->map('GET|POST', '/games/bandit',    '/modules/games/bandit.php');
$router->map('GET|POST', '/games/kosti',     '/modules/games/kosti.php');
$router->map('GET|POST', '/games/naperstki', '/modules/games/naperstki.php');

$router->map('GET|POST', '/load',        '/modules/load/index.php');
$router->map('GET|POST', '/load/active', '/modules/load/active.php');
$router->map('GET|POST', '/load/add',    '/modules/load/add.php');
$router->map('GET|POST', '/load/down',   '/modules/load/down.php');
$router->map('GET|POST', '/load/fresh',  '/modules/load/fresh.php');
$router->map('GET|POST', '/load/new',    '/modules/load/new.php');
$router->map('GET|POST', '/load/rss',    '/modules/load/rss.php');
$router->map('GET|POST', '/load/search', '/modules/load/search.php');
$router->map('GET|POST', '/load/top',    '/modules/load/top.php');
$router->map('GET|POST', '/load/zip',    '/modules/load/zip.php');

$router->map('GET|POST', '/votes',         '/modules/votes/index.php');
$router->map('GET|POST', '/votes/history', '/modules/votes/history.php');

$router->map('GET', '/api', '/modules/api/index.php');
$router->map('GET', '/api/forum', '/modules/api/forum.php');
$router->map('GET', '/api/private', '/modules/api/private.php');
$router->map('GET', '/api/user', '/modules/api/user.php');

$router->map('GET', '/admin',              '/modules/admin/index.php', 'admin');
$router->map('GET|POST', '/admin/board',    '/modules/admin/board.php');
$router->map('GET|POST', '/admin/book',    '/modules/admin/book.php');
$router->map('GET|POST', '/admin/blog',    '/modules/admin/blog.php');
$router->map('GET|POST', '/admin/chat',    '/modules/admin/chat.php');
$router->map('GET', '/admin/cache/[image|clear|clearimage:action]?',        '/modules/admin/cache.php');
$router->map('GET|POST', '/admin/events',    '/modules/admin/events.php');
$router->map('GET|POST', '/admin/forum',   '/modules/admin/forum.php');
$router->map('GET|POST', '/admin/minichat',    '/modules/admin/minichat.php');
$router->map('GET|POST', '/admin/news',    '/modules/admin/news.php');
$router->map('GET|POST', '/admin/gallery', '/modules/admin/gallery.php');
$router->map('GET|POST', '/admin/load',    '/modules/admin/load.php');
$router->map('GET|POST', '/admin/newload',    '/modules/admin/newload.php');
$router->map('GET|POST', '/admin/setting',    '/modules/admin/setting.php');
$router->map('GET|POST', '/admin/reklama',    '/modules/admin/reklama.php');
$router->map('GET|POST', '/admin/ban',    '/modules/admin/ban.php');
$router->map('GET|POST', '/admin/banhist',    '/modules/admin/banhist.php');
$router->map('GET|POST', '/admin/banlist',    '/modules/admin/banlist.php');
$router->map('GET|POST', '/admin/ipban',    '/modules/admin/ipban.php');
$router->map('GET|POST', '/admin/adminlist',    '/modules/admin/adminlist.php');
$router->map('GET|POST', '/admin/users',    '/modules/admin/users.php');
$router->map('GET|POST', '/admin/logs',    '/modules/admin/logs.php');
$router->map('GET|POST', '/admin/spam',    '/modules/admin/spam.php');
$router->map('GET|POST', '/admin/reglist',    '/modules/admin/reglist.php');
$router->map('GET|POST', '/admin/votes',    '/modules/admin/votes.php');
$router->map('GET|POST', '/admin/antimat',    '/modules/admin/antimat.php');
$router->map('GET|POST', '/admin/invitations',    '/modules/admin/invitations.php');
$router->map('GET|POST', '/admin/transfers',    '/modules/admin/transfers.php');
$router->map('GET|POST', '/admin/rules',    '/modules/admin/rules.php');
$router->map('GET|POST', '/admin/users',    '/modules/admin/users.php');
$router->map('GET|POST', '/admin/phpinfo',    '/modules/admin/phpinfo.php');
$router->map('GET|POST', '/admin/blacklist',    '/modules/admin/blacklist.php');
$router->map('GET|POST', '/admin/offers',    '/modules/admin/offers.php');
$router->map('GET|POST', '/admin/smiles',    '/modules/admin/smiles.php');
$router->map('GET|POST', '/admin/status',    '/modules/admin/status.php');
$router->map('GET|POST', '/admin/backup',    '/modules/admin/backup.php');
$router->map('GET|POST', '/admin/checker',    '/modules/admin/checker.php');
$router->map('GET|POST', '/admin/delivery',    '/modules/admin/delivery.php');
$router->map('GET|POST', '/admin/logadmin',    '/modules/admin/logadmin.php');
$router->map('GET|POST', '/admin/notice',    '/modules/admin/notice.php');
$router->map('GET|POST', '/admin/files',    '/modules/admin/files.php');
$router->map('GET|POST', '/admin/delusers',    '/modules/admin/delusers.php');

Registry::set('router', $router->match());
