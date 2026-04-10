<?php

declare(strict_types=1);

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
use App\Http\Controllers\User\AccountController;
use App\Http\Controllers\User\BanController;
use App\Http\Controllers\User\ListController;
use App\Http\Controllers\User\PictureController;
use App\Http\Controllers\User\RecoveryController;
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
Route::pattern('cid', '\d+');
Route::pattern('fid', '\d+');
Route::pattern('slug', '[a-z0-9-\.]+');
Route::pattern('login', '[\w\-]+');

require __DIR__ . '/redirects.php';

Route::controller(HomeController::class)
    ->group(function () {
        Route::get('/', 'index')->name('home');
        Route::get('/closed', 'closed')->name('closed');
        Route::get('/search', 'search')->name('search');
        Route::get('/captcha', 'captcha')->name('captcha');
        Route::get('/language/{lang}', 'language')->where('lang', '[a-z]+')->name('language');
        Route::match(['get', 'post'], '/ipban', 'ipban')->name('ipban')
            ->withoutMiddleware('web');

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
        Route::post('/{id}/close', 'close')->name('close');
        Route::delete('/{id}/delete', 'delete')->name('delete');
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
        Route::get('/{slug}', 'view')->name('view');
        Route::get('/{id}/print', 'print')->name('print');
        Route::get('/{id}/rss', 'rssComments')->name('rss-comments');
        Route::get('/new/comments', 'newComments')->name('new-comments');
        Route::get('/active/articles', 'userArticles')->name('user-articles');
        Route::get('/active/comments', 'userComments')->name('user-comments');
        Route::match(['get', 'post'], '/{id}/edit', 'edit')->name('edit');
        Route::match(['get', 'post'], '/{id}/comments', 'comments')->name('comments');
        Route::match(['get', 'post'], '/{id}/comments/{cid}', 'editComment')->name('edit-comment');
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
        Route::match(['get', 'post'], '/{id}/comments/{cid}', 'editComment')->name('edit-comment');
    });

/* Галерея */
Route::controller(PhotoController::class)
    ->prefix('photos')
    ->name('photos.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{id}', 'view')->name('view');
        Route::delete('/{id}/delete', 'delete')->name('delete');
        Route::get('/albums', 'albums')->name('albums');
        Route::get('/comments', 'allComments')->name('all-comments');
        Route::get('/active/albums', 'album')->name('user-albums');
        Route::get('/active/comments', 'userComments')->name('user-comments');
        Route::match(['get', 'post'], '/{id}/comments', 'comments')->name('comments');
        Route::match(['get', 'post'], '/create', 'create')->name('create');
        Route::match(['get', 'post'], '/{id}/edit', 'edit')->name('edit');
        Route::match(['get', 'post'], '/{id}/comments/{cid}', 'editComment')->name('edit-comment');
    });

/* Категория форума */
Route::prefix('forums')
    ->name('forums.')
    ->group(function () {
        Route::get('/', [ForumController::class, 'index'])->name('index');
        Route::get('/{id}', [ForumController::class, 'forum'])->name('forum');

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
        Route::post('/{id}/open', [TopicController::class, 'open'])->name('open');
        Route::post('/{id}/close', [TopicController::class, 'close'])->name('close');
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

        Route::get('/{id}/download/{fid}', [DownController::class, 'download'])->name('download');
        Route::get('/{id}/link/{lid}', [DownController::class, 'downloadLink'])->whereNumber('lid')->name('download-link');

        Route::get('/{id}/zip/{fid}', [DownController::class, 'zip'])->name('zip');
        Route::get('/{id}/zip/{fid}/{zid}', [DownController::class, 'zipView'])->whereNumber('zid')->name('zip-view');

        Route::match(['get', 'post'], '/create', [DownController::class, 'create'])->name('create');
        Route::match(['get', 'post'], '/{id}/edit', [DownController::class, 'edit'])->name('edit');
        Route::match(['get', 'post'], '/{id}/comments', [DownController::class, 'comments'])->name('comments');
        Route::match(['get', 'post'], '/{id}/comments/{cid}', [DownController::class, 'editComment'])->name('edit-comment');
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
        Route::match(['get', 'post'], '/{id}/comments/{cid}', 'editComment')->name('edit-comment');
    });

/* Ajax */
Route::controller(AjaxController::class)
    ->middleware(['check.user'])
    ->prefix('ajax')
    ->group(function () {
        Route::get('/getstickers', 'getStickers');
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
    ->name('votes.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::match(['get', 'post'], '/{id}', 'view')->name('view');
        Route::get('/{id}/voters', 'voters')->name('voters');
        Route::get('/history', 'history')->name('history');
        Route::get('/{id}/history', 'viewHistory')->name('view-history');
        Route::match(['get', 'post'], '/create', 'create')->name('create');
    });

/* Фото профиля */
Route::controller(PictureController::class)
    ->prefix('pictures')
    ->name('pictures.')
    ->group(function () {
        Route::match(['get', 'post'], '/', 'index')->name('index');
        Route::delete('/delete', 'delete')->name('delete');
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
    ->name('socials.')
    ->group(function () {
        Route::match(['get', 'post'], '/', 'index')->name('index');
        Route::delete('/delete/{id}', 'delete')->name('delete');
    });

/* Стена сообщений */
Route::controller(WallController::class)
    ->prefix('walls')
    ->name('walls.')
    ->group(function () {
        Route::get('/{login}', 'index')->name('index');
        Route::post('/{login}/create', 'create')->name('create');
        Route::post('/{login}/delete', 'delete')->name('delete');
    });

/* Личные сообщения */
Route::controller(MessageController::class)
    ->prefix('messages')
    ->name('messages.')
    ->middleware('check.user')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/new', 'newMessages')->name('new');
        Route::get('/talk/{login}', 'talk')->name('talk');
        Route::delete('/delete/{uid}', 'delete')->whereNumber('uid')->name('delete');
        Route::match(['get', 'post'], '/send', 'send')->name('send');
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
    ->name('invitations.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/create', 'store')->name('store');
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
Route::match(['get', 'post'], '/ban', [BanController::class, 'ban'])->name('ban');

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
        Route::get('/{login}', 'index')->name('users.user');
        Route::match(['get', 'post'], '/{login}/note', 'note')->name('users.note');
    });

/* Почта */
Route::controller(MailController::class)
    ->name('mails.')
    ->group(function () {
        Route::match(['get', 'post'], '/mails', 'index')->name('index');
        Route::match(['get', 'post'], '/unsubscribe', 'unsubscribe')->name('unsubscribe');
    });

/* Авторизация - регистрация */
Route::controller(UserController::class)
    ->group(function () {
        Route::post('/logout', 'logout')->name('logout');
        Route::match(['get', 'post'], '/verify', 'verify')->name('verify');
        Route::get('/confirm/{token}', 'confirm')->name('confirm')->where('token', '[\w]+');
        Route::match(['get', 'post'], '/login', 'login')->name('login');
        Route::match(['get', 'post'], '/register', 'register')->name('register');
        Route::match(['get', 'post'], '/profile', 'profile')->name('profile');
        Route::match(['get', 'post'], '/settings', 'setting')->name('settings');
        Route::post('/check-login', 'checkLogin')->name('check-login');
    });

Route::controller(RecoveryController::class)
    ->group(function () {
        Route::match(['get', 'post'], '/recovery', 'recovery')->name('recovery');
        Route::get('/restore/{token}', 'restore')->name('restore')->where('token', '[\w]+');
    });

/* Мои данные */
Route::controller(AccountController::class)
    ->prefix('accounts')
    ->name('accounts.')
    ->group(function () {
        Route::get('/', 'account')->name('account');
        Route::post('/changemail', 'changeMail')->name('change-mail');
        Route::get('/editmail/{token}', 'editMail')->name('edit-mail');
        Route::post('/editstatus', 'editStatus')->name('edit-status');
        Route::post('/editcolor', 'editColor')->name('edit-color');
        Route::post('/editpassword', 'editPassword')->name('edit-password');
        Route::post('/apikey', 'apikey')->name('apikey');
    });

/* Страницы сайта */
Route::controller(PageController::class)
    ->group(function () {
        Route::get('/faq', 'faq')->name('faq');
        Route::get('/statusfaq', 'statusfaq')->name('statusfaq');
        Route::get('/surprise', 'surprise')->name('surprise');
        Route::get('/pages/{page?}', 'index')->where('page', '[\w\-]+')->name('pages');
        Route::get('/menu', 'menu')->name('menu');
        Route::get('/tags', 'tags')->name('tags');
        Route::get('/rules', 'rules')->name('rules');
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

require __DIR__ . '/admin.php';

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
