<?php

$router = new AltoRouter();

$router->addMatchTypes(['user' => '[0-9A-Za-z-_]++']);

$router->map('GET', '/', 'HomeController@index', 'home');
$router->map('GET', '/captcha', 'HomeController@captcha', 'captcha');
$router->map('GET', '/closed', 'HomeController@closed');

$router->map('GET',      '/book', 'BookController@index', 'book');
$router->map('POST',     '/book/add', 'BookController@add');
$router->map('GET|POST', '/book/edit/[i:id]', 'BookController@edit');

$router->map('GET', '/sitemap.xml', 'SitemapController@index');
$router->map('GET', '/sitemap/[a:action].xml', 'SitemapController');

$router->map('GET',      '/blog', 'BlogController@index', 'blog');
$router->map('GET',      '/blog/[i:cid]', 'BlogController@blog');
$router->map('GET',      '/article/[i:id]', 'BlogController@view');
$router->map('GET|POST', '/article/[i:id]/edit', 'BlogController@edit');
$router->map('GET',      '/article/[i:id]/print', 'BlogController@print');
$router->map('GET',      '/blog/rss', 'BlogController@rss');
$router->map('GET',      '/article/[i:id]/rss', 'BlogController@rssComments');
$router->map('GET|POST', '/article/[i:id]/comments', 'BlogController@comments');
$router->map('GET|POST', '/article/[i:id]/[i:cid]/edit', 'BlogController@editComment');
$router->map('GET',      '/article/[i:id]/end', 'BlogController@end');
$router->map('GET',      '/blog/tags/[*:tag]?', 'BlogController@tags');
$router->map('GET|POST', '/blog/create', 'BlogController@create');
$router->map('GET',      '/blog/blogs', 'BlogController@blogs');
$router->map('GET',      '/blog/new/articles', 'BlogController@newArticles');
$router->map('GET',      '/blog/new/comments', 'BlogController@newComments');
$router->map('GET',      '/blog/active/articles', 'BlogController@userArticles');
$router->map('GET',      '/blog/active/comments', 'BlogController@userComments');
$router->map('GET',      '/blog/top', 'BlogController@top');
$router->map('GET|POST', '/blog/search', 'BlogController@search');
$router->map('GET',      '/article/[i:id]/[i:cid]', 'BlogController@viewcomment');

$router->map('GET',      '/news', 'NewsController@index', 'news');
$router->map('GET',      '/news/[i:id]', 'NewsController@view');
$router->map('GET|POST', '/news/[i:id]/comments', 'NewsController@comments');
$router->map('GET',      '/news/[i:id]/end', 'NewsController@end');
$router->map('GET',      '/news/rss', 'NewsController@rss', 'news_rss');
$router->map('GET|POST', '/news/[i:nid]/[i:id]/edit', 'NewsController@editComment');
$router->map('GET',      '/news/allcomments', 'NewsController@allComments');
$router->map('GET',      '/news/[i:nid]/[i:id]', 'NewsController@viewComment');

$router->map('GET',      '/gallery', 'PhotoController@index', 'gallery');
$router->map('GET',      '/gallery/[i:gid]', 'PhotoController@view');
$router->map('GET',      '/gallery/[i:gid]/[delete|end:action]', 'PhotoController');
$router->map('GET|POST', '/gallery/[i:gid]/[comments:action]', 'PhotoController');
$router->map('GET|POST', '/gallery/[create:action]', 'PhotoController');
$router->map('GET|POST', '/gallery/[i:gid]/[edit:action]', 'PhotoController');
$router->map('GET|POST', '/gallery/[i:gid]/[i:id]/edit', 'PhotoController@editComment');
$router->map('GET',      '/gallery/albums', 'PhotoController@albums');
$router->map('GET',      '/gallery/album/[user:login]', 'PhotoController@album');
$router->map('GET',      '/gallery/comments', 'PhotoController@allComments');
$router->map('GET',      '/gallery/comments/[user:login]', 'PhotoController@userComments');
$router->map('GET',      '/gallery/[i:gid]/[i:id]/comment', 'PhotoController@viewcomment');
$router->map('GET|POST', '/gallery/top', 'PhotoController@top');

$router->map('GET',      '/forum', 'ForumController@index', 'forum');
$router->map('GET',      '/forum/[i:fid]', 'ForumController@forum');
$router->map('GET|POST', '/forum/create', 'ForumController@create');
$router->map('GET',      '/topic/[i:tid]', 'TopicController@index');
$router->map('GET',      '/topic/[i:tid]/[i:id]', 'TopicController@viewpost');
$router->map('POST',     '/topic/[i:tid]/vote', 'TopicController@vote');
$router->map('GET',      '/topic/[i:tid]/[end|close:action]', 'TopicController');
$router->map('POST',     '/topic/[i:tid]/[create|delete:action]', 'TopicController');
$router->map('GET|POST', '/topic/[i:tid]/[i:id]/edit', 'TopicController@editPost');
$router->map('GET|POST', '/topic/[i:tid]/[edit:action]', 'TopicController');
$router->map('GET',      '/forum/bookmark', 'BookmarkController@index');
$router->map('POST',     '/forum/bookmark/[delete|perform:action]', 'BookmarkController');
$router->map('GET',      '/forum/search', 'ForumController@search');
$router->map('GET',      '/forum/active/[posts|themes:action]', 'ForumActiveController');
$router->map('POST',     '/forum/active/delete', 'ForumActiveController@delete');
$router->map('GET',      '/forum/new/[posts|themes:action]', 'ForumNewController');
$router->map('GET',      '/forum/top/posts', 'ForumController@topPosts');
$router->map('GET',      '/forum/top/themes', 'ForumController@topThemes');
$router->map('GET',      '/topic/[i:tid]/print', 'TopicController@print');
$router->map('GET',      '/forum/rss', 'ForumController@rss');
$router->map('GET',      '/topic/[i:tid]/rss', 'ForumController@rssPosts');

$router->map('GET',      '/user/[user:login]', 'UserController@index');
$router->map('GET|POST', '/user/[user:login]/note', 'UserController@note', 'note');
$router->map('GET|POST', '/login', 'UserController@login', 'login');
$router->map('GET',      '/logout', 'UserController@logout', 'logout');
$router->map('GET|POST', '/register', 'UserController@register', 'register');
$router->map('GET|POST', '/user/[user:login]/rating', 'UserController@rating');
$router->map('GET|POST', '/profile', 'UserController@profile');
$router->map('GET',      '/key', 'UserController@key');
$router->map('GET|POST', '/setting', 'UserController@setting');
$router->map('GET',      '/account', 'UserController@account');
$router->map('POST',     '/account/changemail', 'UserController@changeMail');
$router->map('GET',      '/account/editmail', 'UserController@editMail');
$router->map('POST',     '/account/editstatus', 'UserController@editStatus');
$router->map('POST',     '/account/editpassword', 'UserController@editPassword');
$router->map('POST',     '/account/apikey', 'UserController@apikey');

$router->map('GET',  '/rating/[user:login]/[received|gave:action]?', 'RatingController@received');
$router->map('POST', '/rating/delete', 'RatingController@delete');

$router->map('GET|POST', '/mail', 'MailController@index', 'mail');
$router->map('GET|POST', '/recovery', 'MailController@recovery', 'recovery');
$router->map('GET',      '/recovery/restore', 'MailController@restore');
$router->map('GET|POST', '/unsubscribe', 'MailController@unsubscribe', 'unsubscribe');

$router->map('GET', '/menu', 'PageController@menu');
$router->map('GET', '/page/[a:action]?', 'PageController@index');
$router->map('GET', '/tags', 'PageController@tags', 'tags');
$router->map('GET', '/rules', 'PageController@rules', 'rules');
$router->map('GET', '/smiles', 'PageController@smiles', 'smiles');
$router->map('GET', '/online/[all:action]?', 'OnlineController@index', 'online');

$router->map('POST', '/ajax/bbcode', 'AjaxController@bbCode');
$router->map('POST', '/ajax/delcomment', 'AjaxController@delComment');
$router->map('POST', '/ajax/rating', 'AjaxController@rating');
$router->map('POST', '/ajax/complaint', 'AjaxController@complaint');

$router->map('GET',  '/wall/[user:login]', 'WallController@index', 'wall');
$router->map('POST', '/wall/[user:login]/create', 'WallController@create');
$router->map('POST', '/wall/[user:login]/delete', 'WallController@delete');

$router->map('GET',      '/private/[outbox|history|clear:action]?', 'PrivateController@index');
$router->map('POST',     '/private/[delete:action]', 'PrivateController');
$router->map('GET|POST', '/private/[send:action]', 'PrivateController');

$router->map('GET',      '/votes', 'VoteController@index');
$router->map('GET|POST', '/votes/[i:id]', 'VoteController@view');
$router->map('GET',      '/votes/[i:id]/voters', 'VoteController@voters');
$router->map('GET',      '/votes/history', 'VoteController@history');
$router->map('GET',      '/votes/history/[i:id]', 'VoteController@viewHistory');

$router->map('GET|POST', '/ignore', 'IgnoreController@index');
$router->map('GET|POST', '/ignore/note/[i:id]', 'IgnoreController@note');
$router->map('POST',     '/ignore/delete', 'IgnoreController@delete');

$router->map('GET|POST', '/contact', 'ContactController@index');
$router->map('GET|POST', '/contact/note/[i:id]', 'ContactController@note');
$router->map('POST',     '/contact/delete', 'ContactController@delete');
$router->map('GET',      '/counter/[day|month:action]?', 'CounterController@index');

$router->map('GET',      '/transfer', 'TransferController@index');
$router->map('POST',     '/transfer/send', 'TransferController@send');

$router->map('GET',      '/notebook', 'NotebookController@index');
$router->map('GET|POST', '/notebook/edit', 'NotebookController@edit');

$router->map('GET',      '/reklama', 'RekUserController@index');
$router->map('GET|POST', '/reklama/create', 'RekUserController@create');

$router->map('GET', '/authlog', 'LoginController@index');

$router->map('GET',  '/userlist', 'UserController@userlist');
$router->map('POST', '/userlist/search', 'UserController@searchUser');
$router->map('GET',  '/adminlist', 'UserController@adminlist');

$router->map('GET|POST', '/offers', 'pages/offers.php');
$router->map('GET|POST', '/onlinewho', 'pages/onlinewho.php');
$router->map('GET|POST', '/who', 'pages/who.php');
$router->map('GET|POST', '/searchuser', 'pages/searchuser.php');
$router->map('GET|POST', '/authoritylist', 'pages/authoritylist.php');
$router->map('GET|POST', '/ban', 'pages/ban.php');
$router->map('GET|POST', '/banip', 'pages/banip.php');
$router->map('GET|POST', '/razban', 'pages/razban.php');
$router->map('GET|POST', '/banhist', 'pages/banhist.php');
$router->map('GET|POST', '/statusfaq', 'pages/statusfaq.php');
$router->map('GET|POST', '/ratinglist', 'pages/ratinglist.php');
$router->map('GET|POST', '/faq', 'pages/faq.php');
$router->map('GET|POST', '/pictures', 'pages/pictures.php');
$router->map('GET',      '/pictures/[delete:action]', 'pages/pictures.php');
$router->map('GET',      '/surprise', 'pages/surprise.php');

$router->map('GET|POST', '/files/[*:action]?', 'FileController@', 'files');

$router->map('GET',      '/load', 'LoadController@index');

$router->map('GET|POST', '/load/active', 'load/active.php');
$router->map('GET|POST', '/load/add', 'load/add.php');
$router->map('GET|POST', '/load/down', 'load/down.php');
$router->map('GET|POST', '/load/fresh', 'load/fresh.php');
$router->map('GET|POST', '/load/new', 'load/new.php');
$router->map('GET|POST', '/load/rss', 'load/rss.php');
$router->map('GET|POST', '/load/search', 'load/search.php');
$router->map('GET|POST', '/load/top', 'load/top.php');
$router->map('GET|POST', '/load/zip', 'load/zip.php');

$router->map('GET', '/api', 'ApiController@index');
$router->map('GET', '/api/user', 'ApiController@user');
$router->map('GET', '/api/forum', 'ApiController@forum');
$router->map('GET', '/api/private', 'ApiController@private');

$router->map('GET',      '/admin', 'Admin\AdminController@index', 'admin');
$router->map('GET',      '/admin/spam', 'Admin\SpamController@index');
$router->map('POST',     '/admin/spam/delete', 'Admin\SpamController@delete');

$router->map('GET|POST', '/admin/book', 'admin/book.php');
$router->map('GET|POST', '/admin/blog', 'admin/blog.php');
$router->map('GET|POST', '/admin/chat', 'admin/chat.php');
$router->map('GET',      '/admin/cache/[image|clear|clearimage:action]?', 'admin/cache.php');
$router->map('GET|POST', '/admin/forum', 'admin/forum.php');
$router->map('GET|POST', '/admin/news', 'admin/news.php');
$router->map('GET|POST', '/admin/gallery', 'admin/gallery.php');
$router->map('GET|POST', '/admin/load', 'admin/load.php');
$router->map('GET|POST', '/admin/newload', 'admin/newload.php');
$router->map('GET|POST', '/admin/setting', 'admin/setting.php');
$router->map('GET|POST', '/admin/reklama', 'admin/reklama.php');
$router->map('GET|POST', '/admin/ban', 'admin/ban.php');
$router->map('GET|POST', '/admin/banhist', 'admin/banhist.php');
$router->map('GET|POST', '/admin/banlist', 'admin/banlist.php');
$router->map('GET|POST', '/admin/ipban', 'admin/ipban.php');
$router->map('GET|POST', '/admin/adminlist', 'admin/adminlist.php');
$router->map('GET|POST', '/admin/users', 'admin/users.php');
$router->map('GET|POST', '/admin/logs', 'admin/logs.php');
$router->map('GET|POST', '/admin/reglist', 'admin/reglist.php');
$router->map('GET|POST', '/admin/votes', 'admin/votes.php');
$router->map('GET|POST', '/admin/antimat', 'admin/antimat.php');
$router->map('GET|POST', '/admin/invitations', 'admin/invitations.php');
$router->map('GET|POST', '/admin/transfers', 'admin/transfers.php');
$router->map('GET|POST', '/admin/rules', 'admin/rules.php');
$router->map('GET|POST', '/admin/users', 'admin/users.php');
$router->map('GET|POST', '/admin/phpinfo', 'admin/phpinfo.php');
$router->map('GET|POST', '/admin/blacklist', 'admin/blacklist.php');
$router->map('GET|POST', '/admin/offers', 'admin/offers.php');
$router->map('GET|POST', '/admin/smiles', 'admin/smiles.php');
$router->map('GET|POST', '/admin/status', 'admin/status.php');
$router->map('GET|POST', '/admin/backup', 'admin/backup.php');
$router->map('GET|POST', '/admin/checker', 'admin/checker.php');
$router->map('GET|POST', '/admin/delivery', 'admin/delivery.php');
$router->map('GET|POST', '/admin/logadmin', 'admin/logadmin.php');
$router->map('GET|POST', '/admin/notice', 'admin/notice.php');
$router->map('GET|POST', '/admin/files', 'admin/files.php');
$router->map('GET|POST', '/admin/delusers', 'admin/delusers.php');
$router->map('GET',      '/admin/upgrade', 'admin/upgrade.php');

$router->map( 'GET', '/search', function() {
    return view('search/index');
});

App\Classes\Registry::set('router', $router);
