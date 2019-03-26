<?php

use App\Models\Module;
use FastRoute\RouteCollector;

return FastRoute\cachedDispatcher(function(RouteCollector $r) {
    $r->get('/', [App\Controllers\HomeController::class, 'index']);
    $r->get('/closed', [App\Controllers\HomeController::class, 'closed']);
    $r->get('/search',[App\Controllers\HomeController::class, 'search']);

    $r->get('/captcha', [App\Controllers\HomeController::class, 'captcha']);
    $r->get('/language/{lang:[a-z]+}',[App\Controllers\HomeController::class, 'language']);
    $r->addRoute(['GET', 'POST'], '/banip', [App\Controllers\HomeController::class, 'banip']);

    /* Карта сайта */
    $r->get('/sitemap.xml', [App\Controllers\SitemapController::class, 'index']);
    $r->get('/sitemap/{page:[a-z]+}.xml', [App\Controllers\SitemapController::class, 'page']);

    /* Категории объявления */
    $r->addGroup('/boards', static function (RouteCollector $r) {
        $r->get('[/{id:\d+}]', [App\Controllers\BoardController::class, 'index']);
        $r->get('/active', [App\Controllers\BoardController::class, 'active']);
    });

    /* Объявления */
    $r->addGroup('/items', static function (RouteCollector $r) {
        $r->get('/{id:\d+}', [App\Controllers\BoardController::class, 'view']);
        $r->get('/close/{id:\d+}', [App\Controllers\BoardController::class, 'close']);
        $r->get('/delete/{id:\d+}', [App\Controllers\BoardController::class, 'delete']);
        $r->addRoute(['GET', 'POST'], '/create', [App\Controllers\BoardController::class, 'create']);
        $r->addRoute(['GET', 'POST'], '/edit/{id:\d+}', [App\Controllers\BoardController::class, 'edit']);
    });

    /* Гостевая книга */
    $r->addGroup('/guestbooks', static function (RouteCollector $r) {
        $r->get('', [App\Controllers\GuestbookController::class, 'index']);
        $r->post('/add', [App\Controllers\GuestbookController::class, 'add']);
        $r->addRoute(['GET', 'POST'], '/edit/{id:\d+}', [App\Controllers\GuestbookController::class, 'edit']);
    });

    /* Категория блогов */
    $r->addGroup('/blogs', static function (RouteCollector $r) {
        $r->get('', [App\Controllers\BlogController::class, 'index']);
        $r->get('/{id:\d+}', [App\Controllers\BlogController::class, 'blog']);
        $r->get('/tags', [App\Controllers\BlogController::class, 'tags']);
        $r->get('/tags/{tag:.*}', [App\Controllers\BlogController::class, 'searchTag']);
        $r->get('/authors', [App\Controllers\BlogController::class, 'authors']);
        $r->get('/active/articles', [App\Controllers\BlogController::class, 'userArticles']);
        $r->get('/active/comments', [App\Controllers\BlogController::class, 'userComments']);
        $r->get('/top', [App\Controllers\BlogController::class, 'top']);
        $r->get('/rss', [App\Controllers\BlogController::class, 'rss']);
        $r->addRoute(['GET', 'POST'], '/create', [App\Controllers\BlogController::class, 'create']);
        $r->addRoute(['GET', 'POST'], '/search', [App\Controllers\BlogController::class, 'search']);
    });

    /* Статьи блогов */
    $r->addGroup('/articles', static function (RouteCollector $r) {
        $r->get('', [App\Controllers\BlogController::class, 'newArticles']);
        $r->get('/{id:\d+}', [App\Controllers\BlogController::class, 'view']);
        $r->get('/print/{id:\d+}', [App\Controllers\BlogController::class, 'print']);
        $r->get('/rss/{id:\d+}', [App\Controllers\BlogController::class, 'rssComments']);
        $r->get('/comments', [App\Controllers\BlogController::class, 'newComments']);
        $r->get('/end/{id:\d+}', [App\Controllers\BlogController::class, 'end']);
        $r->get('/comment/{id:\d+}/{cid:\d+}', [App\Controllers\BlogController::class, 'viewComment']);
        $r->addRoute(['GET', 'POST'], '/edit/{id:\d+}', [App\Controllers\BlogController::class, 'edit']);
        $r->addRoute(['GET', 'POST'], '/comments/{id:\d+}', [App\Controllers\BlogController::class, 'comments']);
        $r->addRoute(['GET', 'POST'], '/edit/{id:\d+}/{cid:\d+}', [App\Controllers\BlogController::class, 'editComment']);
    });

    /* Новости */
    $r->addGroup('/news', static function (RouteCollector $r) {
        $r->get('', [App\Controllers\NewsController::class, 'index']);
        $r->get('/{id:\d+}', [App\Controllers\NewsController::class, 'view']);
        $r->get('/end/{id:\d+}', [App\Controllers\NewsController::class, 'end']);
        $r->get('/rss', [App\Controllers\NewsController::class, 'rss']);
        $r->get('/allcomments', [App\Controllers\NewsController::class, 'allComments']);
        $r->get('/comment/{id:\d+}/{cid:\d+}', [App\Controllers\NewsController::class, 'viewComment']);
        $r->addRoute(['GET', 'POST'], '/comments/{id:\d+}', [App\Controllers\NewsController::class, 'comments']);
        $r->addRoute(['GET', 'POST'], '/edit/{id:\d+}/{cid:\d+}', [App\Controllers\NewsController::class, 'editComment']);
    });

    /* Фотогалерея */
    $r->addGroup('/photos', static function (RouteCollector $r) {
        $r->get('', [App\Controllers\PhotoController::class, 'index']);
        $r->get('/{id:\d+}', [App\Controllers\PhotoController::class, 'view']);
        $r->get('/delete/{id:\d+}', [App\Controllers\PhotoController::class, 'delete']);
        $r->get('/end/{id:\d+}', [App\Controllers\PhotoController::class, 'end']);
        $r->get('/albums', [App\Controllers\PhotoController::class, 'albums']);
        $r->get('/albums/{login:[\w\-]+}', [App\Controllers\PhotoController::class, 'album']);
        $r->get('/comments', [App\Controllers\PhotoController::class, 'allComments']);
        $r->get('/comments/active/{login:[\w\-]+}', [App\Controllers\PhotoController::class, 'userComments']);
        $r->get('/comment/{id:\d+}/{cid:\d+}', [App\Controllers\PhotoController::class, 'viewComment']);
        $r->addRoute(['GET', 'POST'], '/comments/{id:\d+}', [App\Controllers\PhotoController::class, 'comments']);
        $r->addRoute(['GET', 'POST'], '/create', [App\Controllers\PhotoController::class, 'create']);
        $r->addRoute(['GET', 'POST'], '/edit/{id:\d+}', [App\Controllers\PhotoController::class, 'edit']);
        $r->addRoute(['GET', 'POST'], '/edit/{id:\d+}/{cid:\d+}', [App\Controllers\PhotoController::class, 'editComment']);
        $r->addRoute(['GET', 'POST'], '/top', [App\Controllers\PhotoController::class, 'top']);
    });

    /* Категория форума */
    $r->addGroup('/forums', static function (RouteCollector $r) {
        $r->get('', [App\Controllers\Forum\ForumController::class, 'index']);
        $r->get('/{id:\d+}', [App\Controllers\Forum\ForumController::class, 'forum']);
        $r->get('/search', [App\Controllers\Forum\ForumController::class, 'search']);
        $r->get('/active/{action:posts|topics}', [App\Controllers\Forum\ActiveController::class]);
        $r->post('/active/delete', [App\Controllers\Forum\ActiveController::class, 'delete']);
        $r->get('/top/posts', [App\Controllers\Forum\ForumController::class, 'topPosts']);
        $r->get('/top/topics', [App\Controllers\Forum\ForumController::class, 'topTopics']);
        $r->get('/rss', [App\Controllers\Forum\ForumController::class, 'rss']);
        $r->get('/bookmarks', [App\Controllers\BookmarkController::class, 'index']);
        $r->post('/bookmarks/{action:delete|perform}', [App\Controllers\BookmarkController::class]);
        $r->addRoute(['GET', 'POST'], '/create', [App\Controllers\Forum\ForumController::class, 'create']);
    });

    /* Темы форума */
    $r->addGroup('/topics', static function (RouteCollector $r) {
        $r->get('', [App\Controllers\Forum\NewController::class, 'topics']);
        $r->get('/{id:\d+}', [App\Controllers\Forum\TopicController::class, 'index']);
        $r->get('/{id:\d+}/{pid:\d+}', [App\Controllers\Forum\TopicController::class, 'viewpost']);
        $r->post('/votes/{id:\d+}', [App\Controllers\Forum\TopicController::class, 'vote']);
        $r->get('/end/{id:\d+}', [App\Controllers\Forum\TopicController::class, 'end']);
        $r->get('/close/{id:\d+}', [App\Controllers\Forum\TopicController::class, 'close']);
        $r->post('/create/{id:\d+}', [App\Controllers\Forum\TopicController::class, 'create']);
        $r->post('/delete/{id:\d+}', [App\Controllers\Forum\TopicController::class, 'delete']);
        $r->get('/print/{id:\d+}', [App\Controllers\Forum\TopicController::class, 'print']);
        $r->get('/rss/{id:\d+}', [App\Controllers\Forum\ForumController::class, 'rssPosts']);
        $r->addRoute(['GET', 'POST'], '/edit/{id:\d+}', [App\Controllers\Forum\TopicController::class, 'edit']);
    });

    /* Посты форума */
    $r->addGroup('/posts', static function (RouteCollector $r) {
        $r->get('', [App\Controllers\Forum\NewController::class, 'posts']);
        $r->addRoute(['GET', 'POST'], '/edit/{id:\d+}', [App\Controllers\Forum\TopicController::class, 'editPost']);
    });

    /* Категории загрузок */
    $r->addGroup('/loads', static function (RouteCollector $r) {
        $r->get('', [App\Controllers\Load\LoadController::class, 'index']);
        $r->get('/rss', [App\Controllers\Load\LoadController::class, 'rss']);
        $r->get('/{id:\d+}', [App\Controllers\Load\LoadController::class, 'load']);
        $r->get('/top', [App\Controllers\Load\TopController::class, 'index']);
        $r->get('/search', [App\Controllers\Load\SearchController::class, 'index']);
    });

    /* Загрузки */
    $r->addGroup('/downs', static function (RouteCollector $r) {
        $r->get('', [App\Controllers\Load\NewController::class, 'files']);
        $r->get('/{id:\d+}', [App\Controllers\Load\DownController::class, 'index']);
        $r->get('/delete/{id:\d+}/{fid:\d+}', [App\Controllers\Load\DownController::class, 'deleteFile']);
        $r->post('/votes/{id:\d+}', [App\Controllers\Load\DownController::class, 'vote']);
        $r->get('/comment/{id:\d+}/{cid:\d+}', [App\Controllers\Load\DownController::class, 'viewComment']);
        $r->get('/end/{id:\d+}', [App\Controllers\Load\DownController::class, 'end']);
        $r->get('/rss/{id:\d+}', [App\Controllers\Load\DownController::class, 'rss']);
        $r->get('/zip/{id:\d+}', [App\Controllers\Load\DownController::class, 'zip']);
        $r->get('/zip/{id:\d+}/{fid:\d+}', [App\Controllers\Load\DownController::class, 'zipView']);
        $r->get('/comments', [App\Controllers\Load\NewController::class, 'comments']);
        $r->get('/active/files', [App\Controllers\Load\ActiveController::class, 'files']);
        $r->get('/active/comments', [App\Controllers\Load\ActiveController::class, 'comments']);
        $r->addRoute(['GET', 'POST'], '/edit/{id:\d+}', [App\Controllers\Load\DownController::class, 'edit']);
        $r->addRoute(['GET', 'POST'], '/create', [App\Controllers\Load\DownController::class, 'create']);
        $r->addRoute(['GET', 'POST'], '/download/{id:\d+}', [App\Controllers\Load\DownController::class, 'download']);
        $r->addRoute(['GET', 'POST'], '/comments/{id:\d+}', [App\Controllers\Load\DownController::class, 'comments']);
        $r->addRoute(['GET', 'POST'], '/edit/{id:\d+}/{cid:\d+}', [App\Controllers\Load\DownController::class, 'editComment']);
    });

    /* Предложения и проблемы */
    $r->addGroup('/offers', static function (RouteCollector $r) {
        $r->get('[/{type:offer|issue}]', [App\Controllers\OfferController::class, 'index']);
        $r->get('/{id:\d+}', [App\Controllers\OfferController::class, 'view']);
        $r->get('/end/{id:\d+}', [App\Controllers\OfferController::class, 'end']);
        $r->get('/comment/{id:\d+}/{cid:\d+}', [App\Controllers\OfferController::class, 'viewComment']);
        $r->addRoute(['GET', 'POST'], '/create', [App\Controllers\OfferController::class, 'create']);
        $r->addRoute(['GET', 'POST'], '/edit/{id:\d+}', [App\Controllers\OfferController::class, 'edit']);
        $r->addRoute(['GET', 'POST'], '/comments/{id:\d+}', [App\Controllers\OfferController::class, 'comments']);
        $r->addRoute(['GET', 'POST'], '/edit/{id:\d+}/{cid:\d+}', [App\Controllers\OfferController::class, 'editComment']);
    });

    /* Ajax */
    $r->addGroup('/ajax', static function (RouteCollector $r) {
        $r->post('/bbcode', [App\Controllers\AjaxController::class, 'bbCode']);
        $r->post('/delcomment', [App\Controllers\AjaxController::class, 'delComment']);
        $r->post('/rating', [App\Controllers\AjaxController::class, 'rating']);
        $r->post('/vote', [App\Controllers\AjaxController::class, 'vote']);
        $r->post('/complaint', [App\Controllers\AjaxController::class, 'complaint']);
        $r->post('/image/upload', [App\Controllers\AjaxController::class, 'uploadImage']);
        $r->post('/image/delete', [App\Controllers\AjaxController::class, 'deleteImage']);
    });

    /* Голосования */
    $r->addGroup('/votes', static function (RouteCollector $r) {
        $r->get('', [App\Controllers\VoteController::class, 'index']);
        $r->get('/voters/{id:\d+}', [App\Controllers\VoteController::class, 'voters']);
        $r->get('/history', [App\Controllers\VoteController::class, 'history']);
        $r->get('/history/{id:\d+}', [App\Controllers\VoteController::class, 'viewHistory']);
        $r->addRoute(['GET', 'POST'], '/{id:\d+}', [App\Controllers\VoteController::class, 'view']);
        $r->addRoute(['GET', 'POST'], '/create', [App\Controllers\VoteController::class, 'create']);
    });

    /* Мои данные */
    $r->addGroup('/accounts', static function (RouteCollector $r) {
        $r->get('', [App\Controllers\User\UserController::class, 'account']);
        $r->get('/editmail', [App\Controllers\User\UserController::class, 'editMail']);
        $r->post('/changemail', [App\Controllers\User\UserController::class, 'changeMail']);
        $r->post('/editstatus', [App\Controllers\User\UserController::class, 'editStatus']);
        $r->post('/editpassword', [App\Controllers\User\UserController::class, 'editPassword']);
        $r->post('/apikey', [App\Controllers\User\UserController::class, 'apikey']);
    });

    /* Фото профиля */
    $r->addGroup('/pictures', static function (RouteCollector $r) {
        $r->addRoute(['GET', 'POST'], '', [App\Controllers\PictureController::class, 'index']);
        $r->get('/delete', [App\Controllers\PictureController::class, 'delete']);
    });

    /* Социальные сети */
    $r->addGroup('/socials', static function (RouteCollector $r) {
        $r->addRoute(['GET', 'POST'], '', [App\Controllers\SocialController::class, 'index']);
        $r->get('/delete/{id:\d+}', [App\Controllers\SocialController::class, 'delete']);
    });

    /* Поиск пользователя */
    $r->addGroup('/searchusers', static function (RouteCollector $r) {
        $r->get('', [App\Controllers\User\SearchController::class, 'index']);
        $r->get('/{letter:[0-9a-z]}', [App\Controllers\User\SearchController::class, 'sort']);
        $r->addRoute(['GET', 'POST'], '/search', [App\Controllers\User\SearchController::class, 'search']);
    });

    /* Стена сообщений */
    $r->addGroup('/walls', static function (RouteCollector $r) {
        $r->get('/{login:[\w\-]+}', [App\Controllers\WallController::class, 'index']);
        $r->post('/{login:[\w\-]+}/create', [App\Controllers\WallController::class, 'create']);
        $r->post('/{login:[\w\-]+}/delete', [App\Controllers\WallController::class, 'delete']);
    });

    /* Личные сообщения */
    $r->addGroup('/messages', static function (RouteCollector $r) {
        $r->get('', [App\Controllers\MessageController::class, 'index']);
        $r->get('/talk[/{login:[\w\-]+}]', [App\Controllers\MessageController::class, 'talk']);
        $r->get('/delete/{uid:\d+}', [App\Controllers\MessageController::class, 'delete']);
        $r->addRoute(['GET', 'POST'], '/send', [App\Controllers\MessageController::class, 'send']);
    });

    /* Игнор-лист */
    $r->addGroup('/ignores', static function (RouteCollector $r) {
        $r->post('/delete', [App\Controllers\IgnoreController::class, 'delete']);
        $r->addRoute(['GET', 'POST'], '', [App\Controllers\IgnoreController::class, 'index']);
        $r->addRoute(['GET', 'POST'], '/note/{id:\d+}', [App\Controllers\IgnoreController::class, 'note']);
    });

    /* Контакт-лист */
    $r->addGroup('/contacts', static function (RouteCollector $r) {
        $r->post('/delete', [App\Controllers\ContactController::class, 'delete']);
        $r->addRoute(['GET', 'POST'], '', [App\Controllers\ContactController::class, 'index']);
        $r->addRoute(['GET', 'POST'], '/note/{id:\d+}', [App\Controllers\ContactController::class, 'note']);
    });

    /* Перевод денег */
    $r->addGroup('/transfers', static function (RouteCollector $r) {
        $r->get('', [App\Controllers\TransferController::class, 'index']);
        $r->post('/send', [App\Controllers\TransferController::class, 'send']);
    });

    /* Личные заметки */
    $r->addGroup('/notebooks', static function (RouteCollector $r) {
        $r->get('', [App\Controllers\NotebookController::class, 'index']);
        $r->addRoute(['GET', 'POST'], '/edit', [App\Controllers\NotebookController::class, 'edit']);
    });

    /* Реклама */
    $r->addGroup('/reklama', static function (RouteCollector $r) {
        $r->get('', [App\Controllers\RekUserController::class, 'index']);
        $r->addRoute(['GET', 'POST'], '/create', [App\Controllers\RekUserController::class, 'create']);
    });

    /* Репутация пользователя */
    $r->addGroup('/ratings', static function (RouteCollector $r) {
        $r->get('/{login:[\w\-]+}[/{action:received|gave}]', [App\Controllers\RatingController::class, 'received']);
        $r->post('/delete', [App\Controllers\RatingController::class, 'delete']);
    });

    /* API */
    $r->addGroup('/api', static function (RouteCollector $r) {
        $r->get('', [App\Controllers\ApiController::class, 'index']);
        $r->get('/users', [App\Controllers\ApiController::class, 'users']);
        $r->get('/forums', [App\Controllers\ApiController::class, 'forums']);
        $r->get('/messages', [App\Controllers\ApiController::class, 'messages']);
    });

    $r->get('/restore', [App\Controllers\MailController::class, 'restore']);
    $r->addRoute(['GET', 'POST'], '/recovery', [App\Controllers\MailController::class, 'recovery']);
    $r->addRoute(['GET', 'POST'], '/mails', [App\Controllers\MailController::class, 'index']);
    $r->addRoute(['GET', 'POST'], '/unsubscribe', [App\Controllers\MailController::class, 'unsubscribe']);

    $r->get('/authlogs', [App\Controllers\LoginController::class, 'index']);

    $r->get('/administrators', [App\Controllers\User\ListController::class, 'adminlist']);
    $r->addRoute(['GET', 'POST'], '/authoritylists', [App\Controllers\User\ListController::class, 'authoritylist']);
    $r->addRoute(['GET', 'POST'], '/ratinglists', [App\Controllers\User\ListController::class, 'ratinglist']);
    $r->addRoute(['GET', 'POST'], '/ban', [App\Controllers\User\BanController::class, 'ban']);
    $r->addRoute(['GET', 'POST'], '/who', [App\Controllers\User\UserController::class, 'who']);

    $r->get('/faq', [App\Controllers\PageController::class, 'faq']);
    $r->get('/statusfaq', [App\Controllers\PageController::class, 'statusfaq']);
    $r->get('/surprise', [App\Controllers\PageController::class, 'surprise']);


    $r->get('/users/{login:[\w\-]+}', [App\Controllers\User\UserController::class, 'index']);
    $r->addRoute(['GET', 'POST'], '/users', [App\Controllers\User\ListController::class, 'userlist']);
    $r->addRoute(['GET', 'POST'], '/users/{login:[\w\-]+}/rating', [App\Controllers\RatingController::class, 'index']);

    $r->get('/logout', [App\Controllers\User\UserController::class, 'logout']);
    $r->addRoute(['GET', 'POST'], '/key', [App\Controllers\User\UserController::class, 'key']);
    $r->addRoute(['GET', 'POST'], '/users/{login:[\w\-]+}/note', [App\Controllers\User\UserController::class, 'note']);
    $r->addRoute(['GET', 'POST'], '/login', [App\Controllers\User\UserController::class, 'login']);
    $r->addRoute(['GET', 'POST'], '/register', [App\Controllers\User\UserController::class, 'register']);
    $r->addRoute(['GET', 'POST'], '/profile', [App\Controllers\User\UserController::class, 'profile']);
    $r->addRoute(['GET', 'POST'], '/settings', [App\Controllers\User\UserController::class, 'setting']);

    $r->get('/pages[/{page:[\w\-]+}]', [App\Controllers\PageController::class, 'index']);
    $r->get('/menu', [App\Controllers\PageController::class, 'menu']);
    $r->get('/tags', [App\Controllers\PageController::class, 'tags']);
    $r->get('/rules', [App\Controllers\PageController::class, 'rules']);
    $r->get('/stickers', [App\Controllers\PageController::class, 'stickers']);
    $r->get('/stickers/{id:\d+}', [App\Controllers\PageController::class, 'stickersCategory']);
    $r->get('/online[/{action:all}]', [App\Controllers\OnlineController::class, 'index']);
    $r->get('/counters', [App\Controllers\CounterController::class, 'index']);

    $r->get('/files[/{page:.+}]', [App\Controllers\FileController::class, 'index']);

    /* Админ-панель */
    $r->addGroup('/admin', static function (RouteCollector $r) {
        $r->get('/loads', [App\Controllers\Admin\LoadController::class, 'index']);
        $r->post('/loads/create', [App\Controllers\Admin\LoadController::class, 'create']);
        $r->addRoute(['GET', 'POST'], '/loads/edit/{id:\d+}', [App\Controllers\Admin\LoadController::class, 'edit']);
        $r->get('/loads/delete/{id:\d+}', [App\Controllers\Admin\LoadController::class, 'delete']);
        $r->get('/loads/restatement', [App\Controllers\Admin\LoadController::class, 'restatement']);
        $r->get('/loads/{id:\d+}', [App\Controllers\Admin\LoadController::class, 'load']);
        $r->addRoute(['GET', 'POST'], '/downs/edit/{id:\d+}', [App\Controllers\Admin\LoadController::class, 'editDown']);
        $r->addRoute(['GET', 'POST'], '/downs/delete/{id:\d+}', [App\Controllers\Admin\LoadController::class, 'deleteDown']);
        $r->get('/downs/delete/{id:\d+}/{fid:\d+}', [App\Controllers\Admin\LoadController::class, 'deleteFile']);
        $r->get('/downs/new', [App\Controllers\Admin\LoadController::class, 'new']);
        $r->get('/downs/publish/{id:\d+}', [App\Controllers\Admin\LoadController::class, 'publish']);

        $r->get('', [App\Controllers\Admin\AdminController::class, 'main']);
        $r->get('/spam', [App\Controllers\Admin\SpamController::class, 'index']);
        $r->post('/spam/delete', [App\Controllers\Admin\SpamController::class, 'delete']);
        $r->get('/errors', [App\Controllers\Admin\ErrorController::class, 'index']);
        $r->get('/errors/clear', [App\Controllers\Admin\ErrorController::class, 'clear']);
        $r->addRoute(['GET', 'POST'], '/antimat', [App\Controllers\Admin\AntimatController::class, 'index']);
        $r->get('/antimat/{action:delete|clear}', [App\Controllers\Admin\AntimatController::class]);
        $r->get('/status', [App\Controllers\Admin\StatusController::class, 'index']);
        $r->addRoute(['GET', 'POST'], '/status/{action:create|edit}', [App\Controllers\Admin\StatusController::class]);
        $r->get('/status/delete', [App\Controllers\Admin\StatusController::class, 'delete']);

        $r->get('/rules', [App\Controllers\Admin\RuleController::class, 'index']);
        $r->addRoute(['GET', 'POST'], '/rules/edit', [App\Controllers\Admin\RuleController::class, 'edit']);

        $r->get('/upgrade', [App\Controllers\Admin\AdminController::class, 'upgrade']);
        $r->get('/phpinfo', [App\Controllers\Admin\AdminController::class, 'phpinfo']);

        $r->addRoute(['GET', 'POST'], '/settings', [App\Controllers\Admin\SettingController::class, 'index']);
        $r->get('/caches', [App\Controllers\Admin\CacheController::class, 'index']);
        $r->post('/caches/clear', [App\Controllers\Admin\CacheController::class, 'clear']);

        $r->get('/backups', [App\Controllers\Admin\BackupController::class, 'index']);
        $r->addRoute(['GET', 'POST'], '/backups/create', [App\Controllers\Admin\BackupController::class, 'create']);
        $r->get('/backups/delete', [App\Controllers\Admin\BackupController::class, 'delete']);

        $r->addRoute(['GET', 'POST'], '/checkers', [App\Controllers\Admin\CheckerController::class, 'index']);
        $r->addRoute(['GET', 'POST'], '/checkers/scan', [App\Controllers\Admin\CheckerController::class, 'scan']);

        $r->addRoute(['GET', 'POST'], '/delivery', [App\Controllers\Admin\DeliveryController::class, 'index']);

        $r->get('/logs', [App\Controllers\Admin\LogController::class, 'index']);
        $r->get('/logs/clear', [App\Controllers\Admin\LogController::class, 'clear']);

        $r->get('/notices', [App\Controllers\Admin\NoticeController::class, 'index']);
        $r->addRoute(['GET', 'POST'], '/notices/create', [App\Controllers\Admin\NoticeController::class, 'create']);
        $r->addRoute(['GET', 'POST'], '/notices/edit/{id:\d+}', [App\Controllers\Admin\NoticeController::class, 'edit']);
        $r->get('/notices/delete/{id:\d+}', [App\Controllers\Admin\NoticeController::class, 'delete']);

        $r->addRoute(['GET', 'POST'], '/delusers', [App\Controllers\Admin\DelUserController::class, 'index']);
        $r->post('/delusers/clear', [App\Controllers\Admin\DelUserController::class, 'clear']);

        $r->get('/files', [App\Controllers\Admin\FilesController::class, 'index']);
        $r->addRoute(['GET', 'POST'], '/files/edit', [App\Controllers\Admin\FilesController::class, 'edit']);
        $r->addRoute(['GET', 'POST'], '/files/create', [App\Controllers\Admin\FilesController::class, 'create']);
        $r->get('/files/delete', [App\Controllers\Admin\FilesController::class, 'delete']);

        $r->get('/stickers', [App\Controllers\Admin\StickerController::class, 'index']);
        $r->get('/stickers/{id:\d+}', [App\Controllers\Admin\StickerController::class, 'category']);

        $r->post('/stickers/create', [App\Controllers\Admin\StickerController::class, 'create']);
        $r->addRoute(['GET', 'POST'], '/stickers/edit/{id:\d+}', [App\Controllers\Admin\StickerController::class, 'edit']);
        $r->get('/stickers/delete/{id:\d+}', [App\Controllers\Admin\StickerController::class, 'delete']);

        $r->addRoute(['GET', 'POST'], '/stickers/sticker/create', [App\Controllers\Admin\StickerController::class, 'createSticker']);
        $r->addRoute(['GET', 'POST'], '/stickers/sticker/edit/{id:\d+}', [App\Controllers\Admin\StickerController::class, 'editSticker']);
        $r->get('/stickers/sticker/delete/{id:\d+}', [App\Controllers\Admin\StickerController::class, 'deleteSticker']);


        $r->addRoute(['GET', 'POST'], '/ipbans', [App\Controllers\Admin\IpBanController::class, 'index']);
        $r->post('/ipbans/delete', [App\Controllers\Admin\IpBanController::class, 'delete']);
        $r->get('/ipbans/clear', [App\Controllers\Admin\IpBanController::class, 'clear']);

        $r->addRoute(['GET', 'POST'], '/blacklists', [App\Controllers\Admin\BlacklistController::class, 'index']);
        $r->post('/blacklists/delete', [App\Controllers\Admin\BlacklistController::class, 'delete']);

        $r->get('/news', [App\Controllers\Admin\NewsController::class, 'index']);
        $r->addRoute(['GET', 'POST'], '/news/edit/{id:\d+}', [App\Controllers\Admin\NewsController::class, 'edit']);
        $r->addRoute(['GET', 'POST'], '/news/create', [App\Controllers\Admin\NewsController::class, 'create']);
        $r->get('/news/restatement', [App\Controllers\Admin\NewsController::class, 'restatement']);
        $r->get('/news/delete/{id:\d+}', [App\Controllers\Admin\NewsController::class, 'delete']);

        $r->get('/guestbooks', [App\Controllers\Admin\GuestbookController::class, 'index']);
        $r->addRoute(['GET', 'POST'], '/guestbooks/edit/{id:\d+}', [App\Controllers\Admin\GuestbookController::class, 'edit']);
        $r->addRoute(['GET', 'POST'], '/guestbooks/reply/{id:\d+}', [App\Controllers\Admin\GuestbookController::class, 'reply']);
        $r->post('/guestbooks/delete', [App\Controllers\Admin\GuestbookController::class, 'delete']);
        $r->get('/guestbooks/clear', [App\Controllers\Admin\GuestbookController::class, 'clear']);

        $r->get('/transfers', [App\Controllers\Admin\TransferController::class, 'index']);
        $r->get('/transfers/view', [App\Controllers\Admin\TransferController::class, 'view']);

        $r->get('/users', [App\Controllers\Admin\UserController::class, 'index']);
        $r->get('/users/search', [App\Controllers\Admin\UserController::class, 'search']);
        $r->addRoute(['GET', 'POST'], '/users/edit', [App\Controllers\Admin\UserController::class, 'edit']);
        $r->addRoute(['GET', 'POST'], '/users/delete', [App\Controllers\Admin\UserController::class, 'delete']);

        $r->get('/administrators', [App\Controllers\Admin\AdminlistController::class, 'index']);

        $r->get('/invitations', [App\Controllers\Admin\InvitationController::class, 'index']);
        $r->addRoute(['GET', 'POST'], '/invitations/create', [App\Controllers\Admin\InvitationController::class, 'create']);
        $r->get('/invitations/keys', [App\Controllers\Admin\InvitationController::class, 'keys']);
        $r->post('/invitations/send', [App\Controllers\Admin\InvitationController::class, 'send']);
        $r->post('/invitations/mail', [App\Controllers\Admin\InvitationController::class, 'mail']);
        $r->post('/invitations/delete', [App\Controllers\Admin\InvitationController::class, 'delete']);

        $r->addRoute(['GET', 'POST'], '/reglists', [App\Controllers\Admin\ReglistController::class, 'index']);

        $r->addRoute(['GET', 'POST'], '/chats', [App\Controllers\Admin\ChatController::class, 'index']);
        $r->addRoute(['GET', 'POST'], '/chats/edit/{id:\d+}', [App\Controllers\Admin\ChatController::class, 'edit']);
        $r->get('/chats/clear', [App\Controllers\Admin\ChatController::class, 'clear']);

        $r->get('/banlists', [App\Controllers\Admin\BanlistController::class, 'index']);

        $r->get('/bans', [App\Controllers\Admin\BanController::class, 'index']);
        $r->addRoute(['GET', 'POST'], '/bans/edit', [App\Controllers\Admin\BanController::class, 'edit']);
        $r->addRoute(['GET', 'POST'], '/bans/change', [App\Controllers\Admin\BanController::class, 'change']);
        $r->get('/bans/unban', [App\Controllers\Admin\BanController::class, 'unban']);

        $r->get('/banhists', [App\Controllers\Admin\BanhistController::class, 'index']);
        $r->get('/banhists/view', [App\Controllers\Admin\BanhistController::class, 'view']);
        $r->post('/banhists/delete', [App\Controllers\Admin\BanhistController::class, 'delete']);

        $r->get('/votes', [App\Controllers\Admin\VoteController::class, 'index']);
        $r->get('/votes/history', [App\Controllers\Admin\VoteController::class, 'history']);
        $r->addRoute(['GET', 'POST'], '/votes/edit/{id:\d+}', [App\Controllers\Admin\VoteController::class, 'edit']);
        $r->get('/votes/close/{id:\d+}', [App\Controllers\Admin\VoteController::class, 'close']);
        $r->get('/votes/delete/{id:\d+}', [App\Controllers\Admin\VoteController::class, 'delete']);
        $r->get('/votes/restatement', [App\Controllers\Admin\VoteController::class, 'restatement']);

        $r->get('/offers[/{type:offer|issue}]', [App\Controllers\Admin\OfferController::class, 'index']);
        $r->get('/offers/{id:\d+}', [App\Controllers\Admin\OfferController::class, 'view']);
        $r->addRoute(['GET', 'POST'], '/offers/edit/{id:\d+}', [App\Controllers\Admin\OfferController::class, 'edit']);
        $r->addRoute(['GET', 'POST'], '/offers/reply/{id:\d+}', [App\Controllers\Admin\OfferController::class, 'reply']);
        $r->get('/offers/restatement', [App\Controllers\Admin\OfferController::class, 'restatement']);
        $r->addRoute(['GET', 'POST'], '/offers/delete', [App\Controllers\Admin\OfferController::class, 'delete']);

        $r->get('/photos', [App\Controllers\Admin\PhotoController::class, 'index']);
        $r->addRoute(['GET', 'POST'], '/photos/edit/{id:\d+}', [App\Controllers\Admin\PhotoController::class, 'edit']);
        $r->get('/photos/restatement', [App\Controllers\Admin\PhotoController::class, 'restatement']);
        $r->get('/photos/delete/{id:\d+}', [App\Controllers\Admin\PhotoController::class, 'delete']);

        $r->get('/reklama', [App\Controllers\Admin\RekUserController::class, 'index']);
        $r->addRoute(['GET', 'POST'], '/reklama/edit/{id:\d+}', [App\Controllers\Admin\RekUserController::class, 'edit']);
        $r->post('/reklama/delete', [App\Controllers\Admin\RekUserController::class, 'delete']);

        $r->get('/forums', [App\Controllers\Admin\ForumController::class, 'index']);
        $r->post('/forums/create', [App\Controllers\Admin\ForumController::class, 'create']);
        $r->addRoute(['GET', 'POST'], '/forums/edit/{id:\d+}', [App\Controllers\Admin\ForumController::class, 'edit']);
        $r->get('/forums/delete/{id:\d+}', [App\Controllers\Admin\ForumController::class, 'delete']);
        $r->get('/forums/restatement', [App\Controllers\Admin\ForumController::class, 'restatement']);
        $r->get('/forums/{id:\d+}', [App\Controllers\Admin\ForumController::class, 'forum']);
        $r->addRoute(['GET', 'POST'], '/topics/edit/{id:\d+}', [App\Controllers\Admin\ForumController::class, 'editTopic']);
        $r->addRoute(['GET', 'POST'], '/topics/move/{id:\d+}', [App\Controllers\Admin\ForumController::class, 'moveTopic']);
        $r->get('/topics/action/{id:\d+}', [App\Controllers\Admin\ForumController::class, 'actionTopic']);
        $r->get('/topics/delete/{id:\d+}', [App\Controllers\Admin\ForumController::class, 'deleteTopic']);
        $r->get('/topics/{id:\d+}', [App\Controllers\Admin\ForumController::class, 'topic']);
        $r->addRoute(['GET', 'POST'], '/posts/edit/{id:\d+}', [App\Controllers\Admin\ForumController::class, 'editPost']);
        $r->post('/posts/delete', [App\Controllers\Admin\ForumController::class, 'deletePosts']);
        $r->get('/topics/end/{id:\d+}', [App\Controllers\Admin\ForumController::class, 'end']);

        $r->get('/blogs', [App\Controllers\Admin\BlogController::class, 'index']);
        $r->post('/blogs/create', [App\Controllers\Admin\BlogController::class, 'create']);
        $r->get('/blogs/restatement', [App\Controllers\Admin\BlogController::class, 'restatement']);
        $r->addRoute(['GET', 'POST'], '/blogs/edit/{id:\d+}', [App\Controllers\Admin\BlogController::class, 'edit']);
        $r->get('/blogs/delete/{id:\d+}', [App\Controllers\Admin\BlogController::class, 'delete']);
        $r->get('/blogs/{id:\d+}', [App\Controllers\Admin\BlogController::class, 'blog']);
        $r->addRoute(['GET', 'POST'], '/articles/edit/{id:\d+}', [App\Controllers\Admin\BlogController::class, 'editBlog']);
        $r->addRoute(['GET', 'POST'], '/articles/move/{id:\d+}', [App\Controllers\Admin\BlogController::class, 'moveBlog']);
        $r->get('/articles/delete/{id:\d+}', [App\Controllers\Admin\BlogController::class, 'deleteBlog']);

        /* Доска объявлений */
        $r->get('/boards[/{id:\d+}]', [App\Controllers\Admin\BoardController::class, 'index']);
        $r->get('/boards/restatement', [App\Controllers\Admin\BoardController::class, 'restatement']);
        $r->addRoute(['GET', 'POST'], '/items/edit/{id:\d+}', [App\Controllers\Admin\BoardController::class, 'editItem']);
        $r->get('/items/delete/{id:\d+}', [App\Controllers\Admin\BoardController::class, 'deleteItem']);
        $r->get('/boards/categories', [App\Controllers\Admin\BoardController::class, 'categories']);
        $r->post('/boards/create', [App\Controllers\Admin\BoardController::class, 'create']);
        $r->addRoute(['GET', 'POST'], '/boards/edit/{id:\d+}', [App\Controllers\Admin\BoardController::class, 'edit']);
        $r->get('/boards/delete/{id:\d+}', [App\Controllers\Admin\BoardController::class, 'delete']);

        /* Модули */
        $r->get('/modules', [App\Controllers\Admin\ModuleController::class, 'index']);
        $r->get('/modules/module', [App\Controllers\Admin\ModuleController::class, 'module']);
        $r->get('/modules/install', [App\Controllers\Admin\ModuleController::class, 'install']);
        $r->get('/modules/uninstall', [App\Controllers\Admin\ModuleController::class, 'uninstall']);
    });

    $modules = Module::query()->get();

    foreach ($modules as $module) {
        if (file_exists(APP . '/Modules/' . $module->name . '/routes.php')) {
            include_once APP . '/Modules/' . $module->name . '/routes.php';
        }
    }
}, [
    'cacheFile'     => STORAGE . '/temp/routes.dat',
    'cacheDisabled' => env('APP_DEBUG'),
]);
