<?php

$router = new AltoRouter();

$router->addMatchTypes(['user' => '[0-9A-Za-z-_]++']);
$router->addMatchTypes(['letter'=> '[0-9a-z]']);

$routes = [
    ['GET',      '/', 'HomeController@index', 'home'],
    ['GET',      '/captcha', 'HomeController@captcha', 'captcha'],
    ['GET',      '/closed', 'HomeController@closed'],
    ['GET|POST', '/banip', 'HomeController@banip'],

    ['GET',      '/book', 'GuestController@index', 'book'],
    ['POST',     '/book/add', 'GuestController@add'],
    ['GET|POST', '/book/edit/[i:id]', 'GuestController@edit'],

    ['GET',      '/sitemap.xml', 'SitemapController@index'],
    ['GET',      '/sitemap/[a:action].xml', 'SitemapController'],

    ['GET',      '/blog', 'BlogController@index', 'blog'],
    ['GET',      '/blog/[i:id]', 'BlogController@blog'],
    ['GET',      '/article/[i:id]', 'BlogController@view'],
    ['GET|POST', '/article/edit/[i:id]', 'BlogController@edit'],
    ['GET',      '/article/print/[i:id]', 'BlogController@print'],
    ['GET',      '/blog/rss', 'BlogController@rss'],
    ['GET',      '/article/rss/[i:id]', 'BlogController@rssComments'],
    ['GET|POST', '/article/comments/[i:id]', 'BlogController@comments'],
    ['GET|POST', '/article/edit/[i:id]/[i:cid]', 'BlogController@editComment'],
    ['GET',      '/article/end/[i:id]', 'BlogController@end'],
    ['GET',      '/blog/tags/[*:tag]?', 'BlogController@tags'],
    ['GET|POST', '/blog/create', 'BlogController@create'],
    ['GET',      '/blog/blogs', 'BlogController@blogs'],
    ['GET',      '/blog/new/articles', 'BlogController@newArticles'],
    ['GET',      '/blog/new/comments', 'BlogController@newComments'],
    ['GET',      '/blog/active/articles', 'BlogController@userArticles'],
    ['GET',      '/blog/active/comments', 'BlogController@userComments'],
    ['GET',      '/blog/top', 'BlogController@top'],
    ['GET|POST', '/blog/search', 'BlogController@search'],
    ['GET',      '/article/[i:id]/[i:cid]', 'BlogController@viewcomment'],

    ['GET',      '/news', 'NewsController@index', 'news'],
    ['GET',      '/news/[i:id]', 'NewsController@view'],
    ['GET|POST', '/news/comments/[i:id]', 'NewsController@comments'],
    ['GET',      '/news/end/[i:id]', 'NewsController@end'],
    ['GET',      '/news/rss', 'NewsController@rss', 'news_rss'],
    ['GET|POST', '/news/edit/[i:id]/[i:cid]', 'NewsController@editComment'],
    ['GET',      '/news/allcomments', 'NewsController@allComments'],
    ['GET',      '/news/[i:id]/[i:cid]', 'NewsController@viewComment'],

    ['GET',      '/gallery', 'PhotoController@index', 'gallery'],
    ['GET',      '/gallery/[i:id]', 'PhotoController@view'],
    ['GET',      '/gallery/delete/[i:id]', 'PhotoController@delete'],
    ['GET',      '/gallery/end/[i:id]', 'PhotoController@end'],
    ['GET|POST', '/gallery/comments/[i:id]', 'PhotoController@comments'],
    ['GET|POST', '/gallery/create', 'PhotoController@create'],
    ['GET|POST', '/gallery/edit/[i:id]', 'PhotoController@edit'],
    ['GET|POST', '/gallery/edit/[i:id]/[i:cid]', 'PhotoController@editComment'],
    ['GET',      '/gallery/albums', 'PhotoController@albums'],
    ['GET',      '/gallery/album/[user:login]', 'PhotoController@album'],
    ['GET',      '/gallery/comments', 'PhotoController@allComments'],
    ['GET',      '/gallery/comments/[user:login]', 'PhotoController@userComments'],
    ['GET',      '/gallery/comment/[i:id]/[i:cid]', 'PhotoController@viewcomment'],
    ['GET|POST', '/gallery/top', 'PhotoController@top'],

    ['GET',      '/forum', 'Forum\ForumController@index', 'forum'],
    ['GET',      '/forum/[i:id]', 'Forum\ForumController@forum'],
    ['GET|POST', '/forum/create', 'Forum\ForumController@create'],
    ['GET',      '/topic/[i:id]', 'Forum\TopicController@index'],
    ['GET',      '/topic/[i:id]/[i:pid]', 'Forum\TopicController@viewpost'],
    ['POST',     '/topic/vote/[i:id]', 'Forum\TopicController@vote'],
    ['GET',      '/topic/end/[i:id]', 'Forum\TopicController@end'],
    ['GET',      '/topic/close/[i:id]', 'Forum\TopicController@close'],
    ['POST',     '/topic/create/[i:id]', 'Forum\TopicController@create'],
    ['POST',     '/topic/delete/[i:id]', 'Forum\TopicController@delete'],
    ['GET|POST', '/topic/edit/[i:id]', 'Forum\TopicController@edit'],
    ['GET|POST', '/post/edit/[i:id]', 'Forum\TopicController@editPost'],
    ['GET',      '/forum/search', 'Forum\ForumController@search'],
    ['GET',      '/forum/active/[posts|themes:action]', 'Forum\ActiveController'],
    ['POST',     '/forum/active/delete', 'Forum\ActiveController@delete'],
    ['GET',      '/forum/new/[posts|themes:action]', 'Forum\NewController'],
    ['GET',      '/forum/top/posts', 'Forum\ForumController@topPosts'],
    ['GET',      '/forum/top/themes', 'Forum\ForumController@topThemes'],
    ['GET',      '/topic/print/[i:id]', 'Forum\TopicController@print'],
    ['GET',      '/forum/rss', 'Forum\ForumController@rss'],
    ['GET',      '/topic/rss/[i:id]', 'Forum\ForumController@rssPosts'],
    ['GET',      '/forum/bookmark', 'BookmarkController@index'],
    ['POST',     '/forum/bookmark/[delete|perform:action]', 'BookmarkController'],

    ['GET',      '/user/[user:login]', 'User\UserController@index'],
    ['GET|POST', '/user/[user:login]/note', 'User\UserController@note', 'note'],
    ['GET|POST', '/login', 'User\UserController@login', 'login'],
    ['GET',      '/logout', 'User\UserController@logout', 'logout'],
    ['GET|POST', '/register', 'User\UserController@register', 'register'],
    ['GET|POST', '/profile', 'User\UserController@profile'],
    ['GET',      '/key', 'User\UserController@key'],
    ['GET|POST', '/setting', 'User\UserController@setting'],
    ['GET',      '/account', 'User\UserController@account'],
    ['POST',     '/account/changemail', 'User\UserController@changeMail'],
    ['GET',      '/account/editmail', 'User\UserController@editMail'],
    ['POST',     '/account/editstatus', 'User\UserController@editStatus'],
    ['POST',     '/account/editpassword', 'User\UserController@editPassword'],
    ['POST',     '/account/apikey', 'User\UserController@apikey'],

    ['GET',      '/searchuser', 'User\SearchController@index'],
    ['GET',      '/searchuser/[letter:letter]', 'User\SearchController@sort'],
    ['GET|POST', '/searchuser/search', 'User\SearchController@search'],

    ['GET',      '/rating/[user:login]/[received|gave:action]?', 'RatingController@received'],
    ['POST',     '/rating/delete', 'RatingController@delete'],
    ['GET|POST', '/user/[user:login]/rating', 'RatingController@index'],

    ['GET|POST', '/mail', 'MailController@index', 'mail'],
    ['GET|POST', '/recovery', 'MailController@recovery', 'recovery'],
    ['GET',      '/recovery/restore', 'MailController@restore'],
    ['GET|POST', '/unsubscribe', 'MailController@unsubscribe', 'unsubscribe'],

    ['GET',      '/menu', 'PageController@menu'],
    ['GET',      '/page/[a:action]?', 'PageController@index'],
    ['GET',      '/tags', 'PageController@tags', 'tags'],
    ['GET',      '/rules', 'PageController@rules', 'rules'],
    ['GET',      '/smiles', 'PageController@smiles', 'smiles'],
    ['GET',      '/online/[all:action]?', 'OnlineController@index', 'online'],

    ['POST',     '/ajax/bbcode', 'AjaxController@bbCode'],
    ['POST',     '/ajax/delcomment', 'AjaxController@delComment'],
    ['POST',     '/ajax/rating', 'AjaxController@rating'],
    ['POST',     '/ajax/vote', 'AjaxController@vote'],
    ['POST',     '/ajax/complaint', 'AjaxController@complaint'],
    ['POST',     '/ajax/image', 'AjaxController@uploadImage'],

    ['GET',      '/wall/[user:login]', 'WallController@index', 'wall'],
    ['POST',     '/wall/[user:login]/create', 'WallController@create'],
    ['POST',     '/wall/[user:login]/delete', 'WallController@delete'],

    ['GET',      '/private/[outbox|history|clear:action]?', 'PrivateController@index'],
    ['POST',     '/private/[delete:action]', 'PrivateController'],
    ['GET|POST', '/private/[send:action]', 'PrivateController'],

    ['GET',      '/votes', 'VoteController@index'],
    ['GET|POST', '/votes/[i:id]', 'VoteController@view'],
    ['GET',      '/votes/voters/[i:id]', 'VoteController@voters'],
    ['GET',      '/votes/history', 'VoteController@history'],
    ['GET',      '/votes/history/[i:id]', 'VoteController@viewHistory'],
    ['GET|POST', '/votes/create', 'VoteController@create'],

    ['GET|POST', '/ignore', 'IgnoreController@index'],
    ['GET|POST', '/ignore/note/[i:id]', 'IgnoreController@note'],
    ['POST',     '/ignore/delete', 'IgnoreController@delete'],

    ['GET|POST', '/contact', 'ContactController@index'],
    ['GET|POST', '/contact/note/[i:id]', 'ContactController@note'],
    ['POST',     '/contact/delete', 'ContactController@delete'],
    ['GET',      '/counter/[day|month:action]?', 'CounterController@index'],

    ['GET',      '/transfer', 'TransferController@index'],
    ['POST',     '/transfer/send', 'TransferController@send'],

    ['GET',      '/notebook', 'NotebookController@index'],
    ['GET|POST', '/notebook/edit', 'NotebookController@edit'],

    ['GET',      '/reklama', 'RekUserController@index'],
    ['GET|POST', '/reklama/create', 'RekUserController@create'],

    ['GET',      '/authlog', 'LoginController@index'],

    ['GET',      '/adminlist', 'User\ListController@adminlist'],
    ['GET|POST', '/userlist', 'User\ListController@userlist'],
    ['GET|POST', '/authoritylist', 'User\ListController@authoritylist'],
    ['GET|POST', '/ratinglist', 'User\ListController@ratinglist'],
    ['GET|POST', '/ban', 'User\BanController@ban'],
    ['GET|POST', '/who', 'User\UserController@who'],

    ['GET',      '/faq', 'PageController@faq'],
    ['GET',      '/statusfaq', 'PageController@statusfaq'],
    ['GET',      '/surprise', 'PageController@surprise'],

    ['GET',      '/offers/[offer|issue:type]?', 'OfferController@index'],
    ['GET',      '/offers/[i:id]', 'OfferController@view'],
    ['GET|POST', '/offers/create', 'OfferController@create'],
    ['GET|POST', '/offers/edit/[i:id]', 'OfferController@edit'],
    ['GET|POST', '/offers/comments/[i:id]', 'OfferController@comments'],
    ['GET',      '/offers/end/[i:id]', 'OfferController@end'],
    ['GET|POST', '/offers/edit/[i:id]/[i:cid]', 'OfferController@editComment'],

    ['GET|POST', '/pictures', 'PictureController@index'],
    ['GET',      '/pictures/delete', 'PictureController@delete'],

    ['GET|POST', '/files/[*:action]?', 'FileController', 'files'],

    ['GET',      '/load', 'Load\LoadController@index'],
    ['GET',      '/load/rss', 'Load\LoadController@rss'],
    ['GET',      '/load/[i:id]', 'Load\LoadController@load'],
    ['GET|POST', '/down/create', 'Load\DownController@create'],
    ['GET',      '/down/[i:id]', 'Load\DownController@index'],
    ['POST',     '/down/vote/[i:id]', 'Load\DownController@vote'],
    ['GET|POST', '/down/download/[i:id]', 'Load\DownController@download'],
    ['GET|POST', '/down/comments/[i:id]', 'Load\DownController@comments'],
    ['GET',      '/down/end/[i:id]', 'Load\DownController@end'],
    ['GET|POST', '/down/edit/[i:id]/[i:cid]', 'Load\DownController@editComment'],
    ['GET',      '/down/rss/[i:id]', 'Load\DownController@rss'],
    ['GET',      '/down/zip/[i:id]', 'Load\DownController@zip'],
    ['GET',      '/down/zip/[i:id]/[i:fid]', 'Load\DownController@zipView'],

    ['GET|POST', '/load/active', 'load/active.php'],
    ['GET|POST', '/load/add', 'load/add.php'],
    ['GET|POST', '/load/fresh', 'load/fresh.php'],
    ['GET|POST', '/load/new', 'load/new.php'],
    ['GET|POST', '/load/search', 'load/search.php'],
    ['GET|POST', '/load/top', 'load/top.php'],

    ['GET',      '/api', 'ApiController@index'],
    ['GET',      '/api/user', 'ApiController@user'],
    ['GET',      '/api/forum', 'ApiController@forum'],
    ['GET',      '/api/private', 'ApiController@private'],

    ['GET',      '/admin', 'Admin\AdminController@index', 'admin'],
    ['GET',      '/admin/spam', 'Admin\SpamController@index'],
    ['POST',     '/admin/spam/delete', 'Admin\SpamController@delete'],
    ['GET',      '/admin/log', 'Admin\LogController@index'],
    ['GET',      '/admin/log/clear', 'Admin\LogController@clear'],
    ['GET|POST', '/admin/antimat', 'Admin\AntimatController@index'],
    ['GET',      '/admin/antimat/[delete|clear:action]', 'Admin\AntimatController'],
    ['GET',      '/admin/status', 'Admin\StatusController@index'],
    ['GET|POST', '/admin/status/[create|edit:action]', 'Admin\StatusController'],
    ['GET',      '/admin/status/delete', 'Admin\StatusController@delete'],

    ['GET',      '/admin/rules', 'Admin\RuleController@index'],
    ['GET|POST', '/admin/rules/edit', 'Admin\RuleController@edit'],

    ['GET',      '/admin/upgrade', 'Admin\AdminController@upgrade'],
    ['GET',      '/admin/phpinfo', 'Admin\AdminController@phpinfo'],

    ['GET|POST', '/admin/setting', 'Admin\SettingController@index'],
    ['GET',      '/admin/cache', 'Admin\CacheController@index'],
    ['POST',     '/admin/cache/clear', 'Admin\CacheController@clear'],

    ['GET',      '/admin/backup', 'Admin\BackupController@index'],
    ['GET|POST', '/admin/backup/create', 'Admin\BackupController@create'],
    ['GET',      '/admin/backup/delete', 'Admin\BackupController@delete'],

    ['GET|POST', '/admin/checker', 'Admin\CheckerController@index'],
    ['GET|POST', '/admin/checker/scan', 'Admin\CheckerController@scan'],

    ['GET|POST', '/admin/delivery', 'Admin\DeliveryController@index'],

    ['GET',      '/admin/logadmin', 'Admin\LogAdminController@index'],
    ['GET',      '/admin/logadmin/clear', 'Admin\LogAdminController@clear'],

    ['GET',      '/admin/notice', 'Admin\NoticeController@index'],
    ['GET|POST', '/admin/notice/create', 'Admin\NoticeController@create'],
    ['GET|POST', '/admin/notice/edit/[i:id]', 'Admin\NoticeController@edit'],
    ['GET',      '/admin/notice/delete/[i:id]', 'Admin\NoticeController@delete'],

    ['GET|POST', '/admin/delusers', 'Admin\DelUserController@index'],
    ['POST',     '/admin/delusers/clear', 'Admin\DelUserController@clear'],

    ['GET',      '/admin/files', 'Admin\FilesController@index'],
    ['GET|POST', '/admin/files/edit', 'Admin\FilesController@edit'],
    ['GET|POST', '/admin/files/create', 'Admin\FilesController@create'],
    ['GET',      '/admin/files/delete', 'Admin\FilesController@delete'],

    ['GET',      '/admin/smiles', 'Admin\SmileController@index'],
    ['GET|POST', '/admin/smiles/create', 'Admin\SmileController@create'],
    ['GET|POST', '/admin/smiles/edit/[i:id]', 'Admin\SmileController@edit'],
    ['POST',     '/admin/smiles/delete', 'Admin\SmileController@delete'],

    ['GET|POST', '/admin/ipban', 'Admin\IpBanController@index'],
    ['POST',     '/admin/ipban/delete', 'Admin\IpBanController@delete'],
    ['GET',      '/admin/ipban/clear', 'Admin\IpBanController@clear'],

    ['GET|POST', '/admin/blacklist', 'Admin\BlacklistController@index'],
    ['POST',     '/admin/blacklist/delete', 'Admin\BlacklistController@delete'],

    ['GET',      '/admin/news', 'Admin\NewsController@index'],
    ['GET|POST', '/admin/news/edit/[i:id]', 'Admin\NewsController@edit'],
    ['GET|POST', '/admin/news/create', 'Admin\NewsController@create'],
    ['GET',      '/admin/news/restatement', 'Admin\NewsController@restatement'],
    ['POST',     '/admin/news/delete', 'Admin\NewsController@delete'],

    ['GET',      '/admin/book', 'Admin\GuestController@index'],
    ['GET|POST', '/admin/book/edit/[i:id]', 'Admin\GuestController@edit'],
    ['GET|POST', '/admin/book/reply/[i:id]', 'Admin\GuestController@reply'],
    ['POST',     '/admin/book/delete', 'Admin\GuestController@delete'],
    ['GET',      '/admin/book/clear', 'Admin\GuestController@clear'],

    ['GET',      '/admin/transfers', 'Admin\TransferController@index'],
    ['GET',      '/admin/transfers/view', 'Admin\TransferController@view'],

    ['GET',      '/admin/users', 'Admin\UserController@index'],
    ['GET',      '/admin/users/search', 'Admin\UserController@search'],
    ['GET|POST', '/admin/users/edit', 'Admin\UserController@edit'],
    ['GET|POST', '/admin/users/delete', 'Admin\UserController@delete'],

    ['GET',      '/admin/adminlist', 'Admin\AdminlistController@index'],

    ['GET',      '/admin/invitations', 'Admin\InvitationController@index'],
    ['GET|POST', '/admin/invitations/create', 'Admin\InvitationController@create'],
    ['GET',      '/admin/invitations/keys', 'Admin\InvitationController@keys'],
    ['POST',     '/admin/invitations/send', 'Admin\InvitationController@send'],
    ['POST',     '/admin/invitations/mail', 'Admin\InvitationController@mail'],
    ['POST',     '/admin/invitations/delete', 'Admin\InvitationController@delete'],

    ['GET|POST', '/admin/reglist', 'Admin\ReglistController@index'],

    ['GET|POST', '/admin/chat', 'Admin\ChatController@index'],
    ['GET|POST', '/admin/chat/edit/[i:id]', 'Admin\ChatController@edit'],
    ['GET',      '/admin/chat/clear', 'Admin\ChatController@clear'],

    ['GET',      '/admin/banlist', 'Admin\BanlistController@index'],

    ['GET',      '/admin/ban', 'Admin\BanController@index'],
    ['GET|POST', '/admin/ban/edit', 'Admin\BanController@edit'],
    ['GET|POST', '/admin/ban/change', 'Admin\BanController@change'],
    ['GET',      '/admin/ban/unban', 'Admin\BanController@unban'],

    ['GET',      '/admin/banhist', 'Admin\BanhistController@index'],
    ['GET',      '/admin/banhist/view', 'Admin\BanhistController@view'],
    ['POST',     '/admin/banhist/delete', 'Admin\BanhistController@delete'],

    ['GET',      '/admin/votes', 'Admin\VoteController@index'],
    ['GET',      '/admin/votes/history', 'Admin\VoteController@history'],
    ['GET|POST', '/admin/votes/edit/[i:id]', 'Admin\VoteController@edit'],
    ['GET',      '/admin/votes/close/[i:id]', 'Admin\VoteController@close'],
    ['GET',      '/admin/votes/delete/[i:id]', 'Admin\VoteController@delete'],
    ['GET',      '/admin/votes/close/[i:id]', 'Admin\VoteController@change'],
    ['GET',      '/admin/votes/restatement', 'Admin\VoteController@restatement'],

    ['GET',      '/admin/offers/[offer|issue:type]?', 'Admin\OfferController@index'],
    ['GET',      '/admin/offers/[i:id]', 'Admin\OfferController@view'],
    ['GET|POST', '/admin/offers/edit/[i:id]', 'Admin\OfferController@edit'],
    ['GET|POST', '/admin/offers/reply/[i:id]', 'Admin\OfferController@reply'],
    ['GET',      '/admin/offers/restatement', 'Admin\OfferController@restatement'],
    ['GET|POST', '/admin/offers/delete', 'Admin\OfferController@delete'],

    ['GET',      '/admin/gallery', 'Admin\PhotoController@index'],
    ['GET|POST', '/admin/gallery/edit/[i:id]', 'Admin\PhotoController@edit'],
    ['GET',      '/admin/gallery/restatement', 'Admin\PhotoController@restatement'],
    ['POST',     '/admin/gallery/delete', 'Admin\PhotoController@delete'],

    ['GET',      '/admin/reklama', 'Admin\RekUserController@index'],
    ['GET|POST', '/admin/reklama/edit/[i:id]', 'Admin\RekUserController@edit'],
    ['POST',     '/admin/reklama/delete', 'Admin\RekUserController@delete'],

    ['GET',      '/admin/forum', 'Admin\ForumController@index'],
    ['POST',     '/admin/forum/create', 'Admin\ForumController@create'],
    ['GET|POST', '/admin/forum/edit/[i:id]', 'Admin\ForumController@edit'],
    ['GET',      '/admin/forum/delete/[i:id]', 'Admin\ForumController@delete'],
    ['GET',      '/admin/forum/restatement', 'Admin\ForumController@restatement'],
    ['GET',      '/admin/forum/[i:id]', 'Admin\ForumController@forum'],
    ['GET|POST', '/admin/topic/edit/[i:id]', 'Admin\ForumController@editTopic'],
    ['GET|POST', '/admin/topic/move/[i:id]', 'Admin\ForumController@moveTopic'],
    ['GET',      '/admin/topic/action/[i:id]', 'Admin\ForumController@actionTopic'],
    ['GET',      '/admin/topic/delete/[i:id]', 'Admin\ForumController@deleteTopic'],
    ['GET',      '/admin/topic/[i:id]', 'Admin\ForumController@topic'],
    ['GET|POST', '/admin/post/edit/[i:id]', 'Admin\ForumController@editPost'],
    ['POST',     '/admin/post/delete', 'Admin\ForumController@deletePosts'],
    ['GET',      '/admin/topic/end/[i:id]', 'Admin\ForumController@end'],

    ['GET',      '/admin/blog', 'Admin\BlogController@index'],
    ['POST',     '/admin/blog/create', 'Admin\BlogController@create'],
    ['GET',      '/admin/blog/restatement', 'Admin\BlogController@restatement'],
    ['GET|POST', '/admin/blog/edit/[i:id]', 'Admin\BlogController@edit'],
    ['GET',      '/admin/blog/delete/[i:id]', 'Admin\BlogController@delete'],
    ['GET',      '/admin/blog/[i:id]', 'Admin\BlogController@blog'],
    ['GET|POST', '/admin/article/edit/[i:id]', 'Admin\BlogController@editBlog'],
    ['GET',      '/admin/article/delete/[i:id]', 'Admin\BlogController@deleteBlog'],

    ['GET|POST', '/admin/load', 'admin/load.php'],
    ['GET|POST', '/admin/newload', 'admin/newload.php'],

    ['GET', '/search', function() {
        return view('search/index');
    }],
];

$router->addRoutes($routes);

App\Classes\Registry::set('router', $router);
