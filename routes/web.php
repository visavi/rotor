<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\AdminAdvertController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdvertController as AdminUserAdvertController;
use App\Http\Controllers\Admin\AntimatController;
use App\Http\Controllers\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\BanController as AdminBanController;
use App\Http\Controllers\Admin\BanhistController;
use App\Http\Controllers\Admin\BanlistController;
use App\Http\Controllers\Admin\BlacklistController;
use App\Http\Controllers\Admin\BoardController as AdminBoardController;
use App\Http\Controllers\Admin\CacheController;
use App\Http\Controllers\Admin\ChatController;
use App\Http\Controllers\Admin\CheckerController;
use App\Http\Controllers\Admin\DeliveryController;
use App\Http\Controllers\Admin\DelUserController;
use App\Http\Controllers\Admin\ErrorController;
use App\Http\Controllers\Admin\FileController as AdminFileController;
use App\Http\Controllers\Admin\ForumController as AdminForumController;
use App\Http\Controllers\Admin\GuestbookController as AdminGuestbookController;
use App\Http\Controllers\Admin\InvitationController as AdminInvitationController;
use App\Http\Controllers\Admin\IpBanController;
use App\Http\Controllers\Admin\LoadController as AdminLoadController;
use App\Http\Controllers\Admin\LogController;
use App\Http\Controllers\Admin\ModuleController;
use App\Http\Controllers\Admin\NewsController as AdminNewsController;
use App\Http\Controllers\Admin\NoticeController;
use App\Http\Controllers\Admin\OfferController as AdminOfferController;
use App\Http\Controllers\Admin\PaidAdvertController;
use App\Http\Controllers\Admin\PhotoController as AdminPhotoController;
use App\Http\Controllers\Admin\ReglistController;
use App\Http\Controllers\Admin\RuleController;
use App\Http\Controllers\Admin\SearchController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SpamController;
use App\Http\Controllers\Admin\StatusController;
use App\Http\Controllers\Admin\StickerController;
use App\Http\Controllers\Admin\TransferController as AdminTransferController;
use App\Http\Controllers\Admin\UpgradeController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\UserFieldController;
use App\Http\Controllers\Admin\VoteController as AdminVoteController;
use App\Http\Controllers\AdvertController;
use App\Http\Controllers\AjaxController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CounterController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\Forum\ActiveController;
use App\Http\Controllers\Forum\BookmarkController;
use App\Http\Controllers\Forum\ForumController;
use App\Http\Controllers\Forum\NewController;
use App\Http\Controllers\Forum\TopicController;
use App\Http\Controllers\GuestbookController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\IgnoreController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\Load\ActiveController as LoadActiveController;
use App\Http\Controllers\Load\DownController;
use App\Http\Controllers\Load\LoadController;
use App\Http\Controllers\Load\NewController as LoadNewController;
use App\Http\Controllers\Load\TopController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\NotebookController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\OnlineController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SocialController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\User\BanController;
use App\Http\Controllers\User\ListController;
use App\Http\Controllers\User\PictureController;
use App\Http\Controllers\User\SearchController as UserSearchController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\VoteController;
use App\Http\Controllers\WallController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/*Route::get('/', function () {
    return view('welcome');
});*/
Route::pattern('id', '\d+');
Route::pattern('login', '[\w\-]+');

Route::controller(HomeController::class)
    ->group(function () {
        Route::get('/', 'index');
        Route::get('/closed', 'closed');
        Route::get('/search', 'search')->name('search');
        Route::get('/captcha', 'captcha');
        Route::get('/language/{lang}', 'language')->where('lang', '[a-z]+');
        Route::match(['get', 'post'], '/ipban', 'ipban');

        Route::get('/403', 'error403');
        Route::get('/404', 'error404');
    });

/* Карта сайта */
Route::controller(SitemapController::class)
    ->group(function () {
        Route::get('/sitemap.xml', 'index');
        Route::get('/sitemap/{page}.xml', 'page')->where('page', '[a-z]+');
    });

/* Категории объявления */
Route::controller(BoardController::class)
    ->prefix('boards')
    ->name('boards.')
    ->group(function () {
        Route::get('/{id?}', 'index')->name('index');
        Route::get('/active', 'active')->name('active');
    });

/* Объявления */
Route::controller(BoardController::class)
    ->prefix('items')
    ->name('items.')
    ->group(function () {
        Route::get('/{id}', 'view')->name('view');
        Route::get('/{id}/close', 'close')->name('close');
        Route::get('/{id}/delete', 'delete')->name('delete');
        Route::match(['get', 'post'], '/create', 'create')->name('create');
        Route::match(['get', 'post'], '/{id}/edit', 'edit')->name('edit');
    });

/* Гостевая книга */
Route::controller(GuestbookController::class)
    ->prefix('guestbook')
    ->name('guestbook.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/create', 'add')->name('create');
        Route::match(['get', 'post'], '/{id}/edit', 'edit')->name('edit');
    });

/* Категория блогов */
Route::controller(ArticleController::class)
    ->prefix('blogs')
    ->name('blogs.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{id}', 'blog')->name('blog');
        Route::get('/tags', 'tags')->name('tags');
        Route::get('/tags-search', 'searchTags')->name('tags-search');
        Route::get('/tags/{tag}', 'getTag')->where('tag', '.+')->name('tag');
        Route::get('/authors', 'authors')->name('authors');
        Route::get('/top', 'top')->name('top');
        Route::get('/rss', 'rss')->name('rss');
        Route::match(['get', 'post'], '/create', 'create')->name('create');
        Route::get('/main', 'main')->name('main');
    });

/* Статьи блогов */
Route::controller(ArticleController::class)
    ->prefix('articles')
    ->name('articles.')
    ->group(function () {
        Route::get('/', 'newArticles')->name('index');
        Route::get('/{id}', 'view')->name('view');
        Route::get('/{id}/print', 'print')->name('print');
        Route::get('/{id}/rss', 'rssComments')->name('rss-comments');
        Route::get('/comments', 'newComments')->name('new-comments');
        Route::get('/active/articles', 'userArticles')->name('user-articles');
        Route::get('/active/comments', 'userComments')->name('user-comments');
        Route::match(['get', 'post'], '/{id}/edit', 'edit')->name('edit');
        Route::match(['get', 'post'], '/{id}/comments', 'comments')->name('comments');
        Route::match(['get', 'post'], '/{id}/comments/{cid}', 'editComment')->whereNumber('cid')->name('edit-comment');
    });

/* Новости */
Route::controller(NewsController::class)
    ->prefix('news')
    ->name('news.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{id}', 'view')->name('view');
        Route::get('/rss', 'rss')->name('rss');
        Route::get('/allcomments', 'allComments')->name('all-comments');
        Route::match(['get', 'post'], '/{id}/comments', 'comments')->name('comments');
        Route::match(['get', 'post'], '/{id}/comments/{cid}', 'editComment')->whereNumber('cid')->name('edit-comment');
    });

/* Галерея */
Route::controller(PhotoController::class)
    ->prefix('photos')
    ->name('photos.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{id}', 'view')->name('view');
        Route::get('/{id}/delete', 'delete')->name('delete');
        Route::get('/albums', 'albums')->name('albums');
        Route::get('/comments', 'allComments')->name('all-comments');
        Route::get('/active/albums', 'album')->name('user-albums');
        Route::get('/active/comments', 'userComments')->name('user-comments');
        Route::match(['get', 'post'], '/{id}/comments', 'comments')->name('comments');
        Route::match(['get', 'post'], '/create', 'create')->name('create');
        Route::match(['get', 'post'], '/{id}/edit', 'edit')->name('edit');
        Route::match(['get', 'post'], '/{id}/comments/{cid}', 'editComment')->whereNumber('cid')->name('edit-comment');
    });

/* Категория форума */
Route::prefix('forums')
    ->name('forums.')
    ->group(function () {
        Route::get('/', [ForumController::class, 'index'])->name('index');
        Route::get('/{id}', [ForumController::class, 'forum'])->name('forum');
        Route::get('/top/posts', [ForumController::class, 'topPosts'])->name('top-posts');
        Route::get('/top/topics', [ForumController::class, 'topTopics'])->name('top-topics');
        Route::get('/rss', [ForumController::class, 'rss'])->name('rss');
        Route::match(['get', 'post'], '/create', [ForumController::class, 'create'])->name('create');

        Route::get('/active/posts', [ActiveController::class, 'posts'])->name('active-posts');
        Route::get('/active/topics', [ActiveController::class, 'topics'])->name('active-topics');
        Route::delete('/active/{id}/delete', [ActiveController::class, 'destroy'])->name('active-delete');

        Route::get('/bookmarks', [BookmarkController::class, 'index'])->name('bookmarks');
        Route::post('/bookmarks/delete', [BookmarkController::class, 'delete'])->name('bookmarks.delete');
        Route::post('/bookmarks/perform', [BookmarkController::class, 'perform'])->name('bookmarks.perform');
    });

/* Темы форума */
Route::prefix('topics')
    ->name('topics.')
    ->group(function () {
        Route::get('/', [NewController::class, 'topics'])->name('index');

        Route::get('/{id}', [TopicController::class, 'index'])->name('topic');
        Route::post('/{id}/vote', [TopicController::class, 'vote'])->name('vote');
        Route::get('/{id}/open', [TopicController::class, 'open'])->name('open');
        Route::get('/{id}/close', [TopicController::class, 'close'])->name('close');
        Route::post('/{id}/create', [TopicController::class, 'create'])->name('create');
        Route::post('/{id}/delete', [TopicController::class, 'delete'])->name('delete');
        Route::get('/{id}/print', [TopicController::class, 'print'])->name('print');
        Route::match(['get', 'post'], '/{id}/edit', [TopicController::class, 'edit'])->name('edit');

        Route::get('/{id}/rss', [ForumController::class, 'rssPosts'])->name('rss');
    });

/* Посты форума */
Route::prefix('posts')
    ->name('posts.')
    ->group(function () {
        Route::get('/', [NewController::class, 'posts'])->name('index');
        Route::match(['get', 'post'], '/{id}/edit', [TopicController::class, 'editPost'])->name('edit');
    });

/* Категории загрузок */
Route::prefix('loads')
    ->name('loads.')
    ->group(function () {
        Route::get('/', [LoadController::class, 'index'])->name('index');
        Route::get('/{id}', [LoadController::class, 'load'])->name('load');
        Route::get('/rss', [LoadController::class, 'rss'])->name('rss');
        Route::get('/top', [TopController::class, 'index'])->name('top');
    });

/* Загрузки */
Route::prefix('downs')
    ->name('downs.')
    ->group(function () {
        Route::get('/', [LoadNewController::class, 'files'])->name('new-files');
        Route::get('/comments', [LoadNewController::class, 'comments'])->name('new-comments');

        Route::get('/active/files', [LoadActiveController::class, 'files'])->name('active-files');
        Route::get('/active/comments', [LoadActiveController::class, 'comments'])->name('active-comments');

        Route::get('/{id}', [DownController::class, 'view'])->name('view');
        Route::get('/{id}/rss', [DownController::class, 'rss'])->name('rss');
        Route::get('/{id}/zip', [DownController::class, 'zip'])->name('zip');
        Route::get('/{id}/zip/{fid}', [DownController::class, 'zipView'])->whereNumber('fid')->name('zip-view');
        Route::get('/{id}/download', [DownController::class, 'download'])->name('download');
        Route::get('/{id}/download/{lid}', [DownController::class, 'downloadLink'])->whereNumber('lid')->name('download-link');
        Route::match(['get', 'post'], '/{id}/edit', [DownController::class, 'edit'])->name('edit');
        Route::match(['get', 'post'], '/create', [DownController::class, 'create'])->name('create');
        Route::match(['get', 'post'], '/{id}/comments', [DownController::class, 'comments'])->name('comments');
        Route::match(['get', 'post'], '/{id}/comments/{cid}', [DownController::class, 'editComment'])->whereNumber('cid')->name('edit-comment');
    });

/* Предложения и проблемы */
Route::controller(OfferController::class)
    ->prefix('offers')
    ->name('offers.')
    ->group(function () {
        Route::get('/{type?}', 'index')->where('type', 'offer|issue')->name('index');
        Route::get('/{id}', 'view')->name('view');
        Route::match(['get', 'post'], '/create', 'create')->name('create');
        Route::match(['get', 'post'], '/{id}/edit', 'edit')->name('edit');
        Route::match(['get', 'post'], '/{id}/comments', 'comments')->name('comments');
        Route::match(['get', 'post'], '/{id}/comments/{cid}', 'editComment')->whereNumber('cid')->name('edit-comment');
    });

/* Ajax */
Route::controller(AjaxController::class)
    ->middleware(['check.user', 'check.ajax'])
    ->prefix('ajax')
    ->group(function () {
        Route::get('/getstickers', 'getStickers');
        Route::post('/bbcode', 'bbCode');
        Route::post('/delcomment', 'delComment');
        Route::post('/rating', 'rating');
        Route::post('/vote', 'vote');
        Route::post('/complaint', 'complaint');
        Route::post('/file/upload', 'uploadFile');
        Route::post('/file/delete', 'deleteFile');
        Route::post('/set-theme', 'setTheme')->withoutMiddleware('check.user');
    });

/* Голосования */
Route::controller(VoteController::class)
    ->prefix('votes')
    ->group(function () {
        Route::get('/', 'index');
        Route::get('/voters/{id}', 'voters');
        Route::get('/history', 'history');
        Route::get('/history/{id}', 'viewHistory');
        Route::match(['get', 'post'], '/{id}', 'view');
        Route::match(['get', 'post'], '/create', 'create');
    });

/* Мои данные */
Route::controller(UserController::class)
    ->prefix('accounts')
    ->group(function () {
        Route::get('/', 'account');
        Route::get('/editmail', 'editMail');
        Route::post('/changemail', 'changeMail');
        Route::post('/editstatus', 'editStatus');
        Route::post('/editcolor', 'editColor');
        Route::post('/editpassword', 'editPassword');
        Route::post('/apikey', 'apikey');
    });

/* Фото профиля */
Route::controller(PictureController::class)
    ->prefix('pictures')
    ->group(function () {
        Route::match(['get', 'post'], '/', 'index');
        Route::get('/delete', 'delete');
    });

/* Поиск пользователя */
Route::controller(UserSearchController::class)
    ->prefix('searchusers')
    ->group(function () {
        Route::get('/', 'index');
        Route::get('/sort/{letter}', 'sort')->where('letter', '[0-9a-z]+');
        Route::match(['get', 'post'], '/search', 'search');
    });

/* Социальные сети */
Route::controller(SocialController::class)
    ->prefix('socials')
    ->group(function () {
        Route::match(['get', 'post'], '/', 'index');
        Route::get('/delete/{id}', 'delete');
    });

/* Стена сообщений */
Route::controller(WallController::class)
    ->prefix('walls')
    ->group(function () {
        Route::get('/{login}', 'index');
        Route::post('/{login}/create', 'create');
        Route::post('/{login}/delete', 'delete');
    });

/* Личные сообщения */
Route::controller(MessageController::class)
    ->prefix('messages')
    ->middleware('check.user')
    ->group(function () {
        Route::get('/', 'index');
        Route::get('/new', 'newMessages');
        Route::get('/talk/{login}', 'talk');
        Route::get('/delete/{uid}', 'delete')->whereNumber('uid');
        Route::match(['get', 'post'], '/send', 'send');
    });

/* Игнор-лист */
Route::controller(IgnoreController::class)
    ->prefix('ignores')
    ->group(function () {
        Route::post('/delete', 'delete');
        Route::match(['get', 'post'], '/', 'index');
        Route::match(['get', 'post'], '/note/{id}', 'note');
    });

/* Контакт-лист */
Route::controller(ContactController::class)
    ->prefix('contacts')
    ->group(function () {
        Route::post('/delete', 'delete');
        Route::match(['get', 'post'], '/', 'index');
        Route::match(['get', 'post'], '/note/{id}', 'note');
    });

/* Перевод денег */
Route::controller(TransferController::class)
    ->prefix('transfers')
    ->group(function () {
        Route::get('/', 'index');
        Route::post('/send', 'send');
    });

Route::controller(InvitationController::class)
    ->prefix('invitations')
    ->group(function () {
        Route::get('/', 'index');
        Route::post('/create', 'store');
    });

/* Личные заметки */
Route::controller(NotebookController::class)
    ->prefix('notebooks')
    ->group(function () {
        Route::get('/', 'index');
        Route::match(['get', 'post'], '/edit', 'edit');
    });

/* Реклама */
Route::controller(AdvertController::class)
    ->prefix('adverts')
    ->group(function () {
        Route::get('/', 'index');
        Route::match(['get', 'post'], '/create', 'create');
    });

/* Репутация пользователя */
Route::controller(RatingController::class)
    ->prefix('ratings')
    ->group(function () {
        Route::get('/{login}/gave', 'gave');
        Route::get('/{login}/{received?}', 'received');
        Route::post('/delete', 'delete');
    });

/* API */
Route::get('/api', [ApiController::class, 'index']);

/* Бан */
Route::match(['get', 'post'], '/ban', [BanController::class, 'ban']);

/* Авторизации пользователя */
Route::get('/authlogs', [LoginController::class, 'index']);

/* Счетчики */
Route::get('/counters', [CounterController::class, 'index']);

/* Страницы сайта */
Route::get('/files/{page?}', [FileController::class, 'index'])->where('page', '.+');

/* Рейтинг пользователей */
Route::prefix('users')
    ->group(function () {
        Route::match(['get', 'post'], '/', [ListController::class, 'userlist'])->name('users.index');
        Route::match(['get', 'post'], '/{login}/rating', [RatingController::class, 'index']);
    });

/* Профиль пользователя */
Route::controller(UserController::class)
    ->prefix('users')
    ->group(function () {
        Route::get('/{login}', 'index');
        Route::match(['get', 'post'], '/{login}/note', 'note');
    });

/* Почта */
Route::controller(MailController::class)
    ->group(function () {
        Route::get('/restore', 'restore');
        Route::match(['get', 'post'], '/recovery', 'recovery');
        Route::match(['get', 'post'], '/mails', 'index');
        Route::match(['get', 'post'], '/unsubscribe', 'unsubscribe');
    });

/* Авторизация - регистрация */
Route::controller(UserController::class)
    ->group(function () {
        Route::get('/logout', 'logout');
        Route::match(['get', 'post'], '/key', 'key');
        Route::match(['get', 'post'], '/login', 'login');
        Route::match(['get', 'post'], '/register', 'register');
        Route::match(['get', 'post'], '/profile', 'profile');
        Route::match(['get', 'post'], '/settings', 'setting');
        Route::post('/check-login', 'checkLogin');
    });

/* Страницы сайта */
Route::controller(PageController::class)
    ->group(function () {
        Route::get('/faq', 'faq');
        Route::get('/statusfaq', 'statusfaq');
        Route::get('/surprise', 'surprise');
        Route::get('/pages/{page?}', 'index')->where('page', '[\w\-]+');
        Route::get('/menu', 'menu');
        Route::get('/tags', 'tags');
        Route::get('/rules', 'rules');
        Route::get('/stickers', 'stickers');
        Route::get('/stickers/{id}', 'stickersCategory');
    });

/* Онлайн */
Route::controller(OnlineController::class)
    ->prefix('online')
    ->group(function () {
        Route::get('/', 'index');
        Route::get('/all', 'all');
    });

/* Админ-панель */
Route::middleware(['check.admin', 'admin.logger'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::controller(AdminController::class)
            ->group(function () {
                Route::get('/', 'main')->name('index');
            });

        /* Проверка обновлений */
        Route::controller(UpgradeController::class)
            ->prefix('upgrade')
            ->name('upgrade.')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/check', 'check')->name('check');
            });

        /* Админ-чат */
        Route::controller(ChatController::class)
            ->prefix('chats')
            ->group(function () {
                Route::match(['get', 'post'], '/', 'index');
                Route::match(['get', 'post'], '/edit/{id}', 'edit');
                Route::get('/clear', 'clear');
            });

        /* Гостевая */
        Route::controller(AdminGuestbookController::class)
            ->prefix('guestbook')
            ->name('guestbook.')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::match(['get', 'post'], '/{id}/edit', 'edit')->name('edit');
                Route::match(['get', 'post'], '/{id}/reply', 'reply')->name('reply');
                Route::post('/delete', 'delete')->name('delete');
                Route::post('/publish', 'publish')->name('publish');
                Route::get('/clear', 'clear')->name('clear');
            });

        /* Форум */
        Route::controller(AdminForumController::class)
            ->prefix('forums')
            ->name('forums.')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/{id}', 'forum')->name('forum');
                Route::post('/create', 'create')->name('create');
                Route::match(['get', 'post'], '/{id}/edit', 'edit')->name('edit');
                Route::get('/{id}/delete', 'delete')->name('delete');
                Route::get('/restatement', 'restatement')->name('restatement');
            });

        /* Темы */
        Route::controller(AdminForumController::class)
            ->prefix('topics')
            ->name('topics.')
            ->group(function () {
                Route::get('/{id}', 'topic')->name('topic');
                Route::match(['get', 'post'], '/{id}/edit', 'editTopic')->name('edit');
                Route::match(['get', 'post'], '/{id}/move', 'moveTopic')->name('move');
                Route::get('/{id}/action', 'actionTopic')->name('action');
                Route::get('/{id}/delete', 'deleteTopic')->name('delete');
            });

        /* Посты */
        Route::controller(AdminForumController::class)
            ->prefix('posts')
            ->name('posts.')
            ->group(function () {
                Route::match(['get', 'post'], '/{id}/edit', 'editPost')->name('edit');
                Route::post('/delete', 'deletePosts')->name('delete');
            });

        /* Галерея */
        Route::controller(AdminPhotoController::class)
            ->prefix('photos')
            ->name('photos.')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::match(['get', 'post'], '/{id}/edit', 'edit')->name('edit');
                Route::get('/{id}/delete', 'delete')->name('delete');
                Route::get('/restatement', 'restatement')->name('restatement');
            });

        /* Блоги */
        Route::controller(AdminArticleController::class)
            ->prefix('blogs')
            ->name('blogs.')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/{id}', 'blog')->name('blog');
                Route::post('/create', 'create')->name('create');
                Route::get('/restatement', 'restatement')->name('restatement');
                Route::match(['get', 'post'], '/{id}/edit', 'edit')->name('edit');
                Route::get('/{id}/delete', 'delete')->name('delete');
            });

        /* Статьи */
        Route::controller(AdminArticleController::class)
            ->prefix('articles')
            ->name('articles.')
            ->group(function () {
                Route::match(['get', 'post'], '/{id}/edit', 'editArticle')->name('edit');
                Route::match(['get', 'post'], '/{id}/move', 'moveArticle')->name('move');
                Route::get('/{id}/delete', 'deleteArticle')->name('delete');
            });

        /* Доска объявлений */
        Route::controller(AdminBoardController::class)
            ->prefix('boards')
            ->name('boards.')
            ->group(function () {
                Route::get('/{id?}', 'index')->name('index');
                Route::get('/restatement', 'restatement')->name('restatement');
                Route::get('/categories', 'categories')->name('categories');
                Route::post('/create', 'create')->name('create');
                Route::match(['get', 'post'], '/{id}/edit', 'edit')->name('edit');
                Route::get('/{id}/delete', 'delete')->name('delete');
            });

        /* Объявления */
        Route::controller(AdminBoardController::class)
            ->prefix('items')
            ->name('items.')
            ->group(function () {
                Route::match(['get', 'post'], '/{id}/edit', 'editItem')->name('edit');
                Route::get('/{id}/delete', 'deleteItem')->name('delete');
            });

        /* Админская реклама */
        Route::controller(AdminAdvertController::class)
            ->prefix('admin-adverts')
            ->group(function () {
                Route::match(['get', 'post'], '/', 'index');
                Route::get('/delete', 'delete');
            });

        /* Пользовательская реклама */
        Route::get('/adverts', [AdminUserAdvertController::class, 'index']);

        /* Модер */
        Route::middleware('check.admin:moder')->group(function () {
            /* Жалобы */
            Route::controller(SpamController::class)
                ->prefix('spam')
                ->group(function () {
                    Route::get('/', 'index');
                    Route::post('/delete', 'delete');
                });

            /* Бан / разбан */
            Route::controller(AdminBanController::class)
                ->prefix('bans')
                ->group(function () {
                    Route::get('/', 'index');
                    Route::match(['get', 'post'], '/edit', 'edit');
                    Route::match(['get', 'post'], '/change', 'change');
                    Route::get('/unban', 'unban');
                });

            /* Забаненные */
            Route::get('/banlists', [BanlistController::class, 'index']);

            /* Ожидающие */
            Route::match(['get', 'post'], '/reglists', [ReglistController::class, 'index']);

            /* Голосования */
            Route::controller(AdminVoteController::class)
                ->prefix('votes')
                ->group(function () {
                    Route::get('/', 'index');
                    Route::get('/history', 'history');
                    Route::match(['get', 'post'], '/edit/{id}', 'edit');
                    Route::get('/close/{id}', 'close');
                    Route::get('/delete/{id}', 'delete');
                    Route::get('/restatement', 'restatement');
                });

            /* Антимат */
            Route::controller(AntimatController::class)
                ->prefix('antimat')
                ->group(function () {
                    Route::match(['get', 'post'], '/', 'index');
                    Route::get('/delete', 'delete');
                    Route::get('/clear', 'clear');
                });

            /* История банов */
            Route::controller(BanhistController::class)
                ->prefix('banhists')
                ->group(function () {
                    Route::get('/', 'index');
                    Route::get('/view', 'view');
                    Route::post('/delete', 'delete');
                });

            /* Приглашения */
            Route::controller(AdminInvitationController::class)
                ->prefix('invitations')
                ->group(function () {
                    Route::get('/', 'index');
                    Route::match(['get', 'post'], '/create', 'create');
                    Route::match(['get', 'post'], '/send', 'send');
                    Route::post('/mail', 'mail');
                    Route::post('/delete', 'delete');
                });

            /* Денежный переводы */
            Route::controller(AdminTransferController::class)
                ->prefix('transfers')
                ->group(function () {
                    Route::get('/', 'index');
                    Route::get('/view', 'view');
                });
        });

        /* Админ */
        Route::middleware('check.admin:admin')->group(function () {
            /* Правила */
            Route::controller(RuleController::class)
                ->prefix('rules')
                ->group(function () {
                    Route::get('/', 'index');
                    Route::match(['get', 'post'], '/edit', 'edit');
                });

            /* Новости */
            Route::controller(AdminNewsController::class)
                ->prefix('news')
                ->name('news.')
                ->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::match(['get', 'post'], '/{id}/edit', 'edit')->name('edit');
                    Route::match(['get', 'post'], '/create', 'create')->name('create');
                    Route::get('/restatement', 'restatement')->name('restatement');
                    Route::get('/{id}/delete', 'delete')->name('delete');
                });

            /* IP-бан */
            Route::controller(IpBanController::class)
                ->prefix('ipbans')
                ->group(function () {
                    Route::match(['get', 'post'], '/', 'index');
                    Route::post('/delete', 'delete');
                    Route::get('/clear', 'clear');
                });

            /* PHP-info */
            Route::get('/phpinfo', [AdminController::class, 'phpinfo']);

            /* Загрузки */
            Route::controller(AdminLoadController::class)
                ->prefix('loads')
                ->name('loads.')
                ->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::post('/create', 'create')->name('create');
                    Route::match(['get', 'post'], '/{id}/edit', 'edit')->name('edit');
                    Route::get('/{id}/delete', 'delete')->name('delete');
                    Route::get('/restatement', 'restatement')->name('restatement');
                    Route::get('/{id}', 'load')->name('load');
                });

            Route::controller(AdminLoadController::class)
                ->prefix('downs')
                ->name('downs.')
                ->group(function () {
                    Route::match(['get', 'post'], '/{id}/edit', 'editDown')->name('edit');
                    Route::match(['get', 'post'], '/delete/{id}', 'deleteDown')->name('delete');
                    Route::get('/new', 'new')->name('new');
                    Route::get('/{id}/publish', 'publish')->name('publish');
                });

            /* Ошибки */
            Route::controller(ErrorController::class)
                ->prefix('errors')
                ->group(function () {
                    Route::get('/', 'index');
                    Route::get('/clear', 'clear');
                });

            /* Черный список */
            Route::controller(BlacklistController::class)
                ->prefix('blacklists')
                ->group(function () {
                    Route::match(['get', 'post'], '/', 'index');
                    Route::post('/delete', 'delete');
                });

            /* Предложения / проблемы */
            Route::controller(AdminOfferController::class)
                ->prefix('offers')
                ->name('offers.')
                ->group(function () {
                    Route::get('/{type?}', 'index')->where('type', 'offer|issue')->name('index');
                    Route::get('/{id}', 'view')->name('view');
                    Route::match(['get', 'post'], '/{id}/edit', 'edit')->name('edit');
                    Route::match(['get', 'post'], '/{id}/reply', 'reply')->name('reply');
                    Route::get('/restatement', 'restatement')->name('restatement');
                    Route::match(['get', 'post'], '/delete', 'delete')->name('delete');
                });

            /* Стикеры */
            Route::controller(StickerController::class)
                ->prefix('stickers')
                ->group(function () {
                    Route::get('/', 'index');
                    Route::get('/{id}', 'category');
                    Route::post('/create', 'create');
                    Route::match(['get', 'post'], '/edit/{id}', 'edit');
                    Route::get('/delete/{id}', 'delete');
                    Route::match(['get', 'post'], '/sticker/create', 'createSticker');
                    Route::match(['get', 'post'], '/sticker/edit/{id}', 'editSticker');
                    Route::get('/sticker/delete/{id}', 'deleteSticker');
                });

            /* Статусы */
            Route::controller(StatusController::class)
                ->prefix('status')
                ->group(function () {
                    Route::get('/', 'index');
                    Route::match(['get', 'post'], '/create', 'create');
                    Route::match(['get', 'post'], '/edit', 'edit');
                    Route::get('/delete', 'delete');
                });
        });

        /* Босс */
        Route::middleware('check.admin:boss')->group(function () {
            /* Настройки */
            Route::match(['get', 'post'], '/settings', [SettingController::class, 'index']);

            /* Пользователи */
            Route::controller(AdminUserController::class)
                ->prefix('users')
                ->group(function () {
                    Route::get('/', 'index');
                    Route::get('/search', 'search');
                    Route::match(['get', 'post'], '/edit', 'edit');
                    Route::match(['get', 'post'], '/delete', 'delete');
                });

            /* Очистка кеша */
            Route::controller(CacheController::class)
                ->prefix('caches')
                ->group(function () {
                    Route::get('/', 'index');
                    Route::post('/clear', 'clear');
                });

            /* Бэкап */
            Route::controller(BackupController::class)
                ->prefix('backups')
                ->group(function () {
                    Route::get('/', 'index');
                    Route::match(['get', 'post'], '/create', 'create');
                    Route::get('/delete', 'delete');
                });

            /* Сканирование */
            Route::controller(CheckerController::class)
                ->prefix('checkers')
                ->group(function () {
                    Route::match(['get', 'post'], '/', 'index');
                    Route::match(['get', 'post'], '/scan', 'scan');
                });

            /* Приват рассылка */
            Route::match(['get', 'post'], '/delivery', [DeliveryController::class, 'index']);

            /* Логи */
            Route::controller(LogController::class)
                ->prefix('logs')
                ->group(function () {
                    Route::get('/', 'index')->withoutMiddleware('admin.logger');
                    Route::get('/clear', 'clear');
                });

            /* Шаблоны писем */
            Route::controller(NoticeController::class)
                ->prefix('notices')
                ->group(function () {
                    Route::get('/', 'index');
                    Route::match(['get', 'post'], '/create', 'create');
                    Route::match(['get', 'post'], '/edit/{id}', 'edit');
                    Route::get('/delete/{id}', 'delete');
                });

            /* Редактор */
            Route::controller(AdminFileController::class)
                ->prefix('files')
                ->group(function () {
                    Route::get('/', 'index');
                    Route::match(['get', 'post'], '/edit', 'edit');
                    Route::match(['get', 'post'], '/create', 'create');
                    Route::get('/delete', 'delete');
                });

            /* Пользовательская реклама */
            Route::controller(AdminUserAdvertController::class)
                ->prefix('adverts')
                ->group(function () {
                    Route::match(['get', 'post'], '/edit/{id}', 'edit');
                    Route::post('/delete', 'delete');
                });

            /* Платная реклама */
            Route::controller(PaidAdvertController::class)
                ->prefix('paid-adverts')
                ->group(function () {
                    Route::get('/', 'index');
                    Route::match(['get', 'post'], '/create', 'create');
                    Route::match(['get', 'post'], '/edit/{id}', 'edit');
                    Route::get('/delete/{id}', 'delete');
                });

            /* Пользовательские поля */
            Route::resource('user-fields', UserFieldController::class)
                ->parameters(['user-fields' => 'id'])
                ->except('show');

            /* Чистка пользователей */
            Route::controller(DelUserController::class)
                ->prefix('delusers')
                ->group(function () {
                    Route::match(['get', 'post'], '/', 'index');
                    Route::post('/clear', 'clear');
                });

            Route::controller(SearchController::class)
                ->prefix('search')
                ->group(function () {
                    Route::get('/', 'index');
                    Route::post('/import', 'import');
                });

            /* Модули */
            Route::controller(ModuleController::class)
                ->prefix('modules')
                ->group(function () {
                    Route::get('/', 'index');
                    Route::get('/module', 'module');
                    Route::get('/install', 'install');
                    Route::get('/uninstall', 'uninstall');
                });
        });
    });

if (file_exists(app_path('Http/Controllers/InstallController.php'))) {
    Route::controller(InstallController::class)
        ->prefix('install')
        ->withoutMiddleware('web')
        ->group(function () {
            Route::get('/', 'index')->name('install');
            Route::get('/status', 'status');
            Route::get('/migrate', 'migrate');
            Route::get('/seed', 'seed');
            Route::match(['get', 'post'], '/account', 'account');
            Route::get('/finish', 'finish');
        });
}
