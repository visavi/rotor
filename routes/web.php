<?php

declare(strict_types=1);

use App\Http\Controllers\AjaxController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CounterController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\OnlineController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\User\AccountController;
use App\Http\Controllers\User\BanController;
use App\Http\Controllers\User\ListController;
use App\Http\Controllers\User\PictureController;
use App\Http\Controllers\User\RecoveryController;
use App\Http\Controllers\User\UserController;
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

Route::controller(HomeController::class)
    ->group(function () {
        Route::get('/', 'index')->name('home');
        Route::get('/feed', 'feed')->name('feed');
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

/* Ajax */
Route::controller(AjaxController::class)
    ->middleware(['check.user'])
    ->prefix('ajax')
    ->group(function () {
        Route::get('/getstickers', 'getStickers');
        Route::get('/resolve-image', 'resolveImage');
        Route::post('/rating', 'rating');
        Route::post('/vote', 'vote');
        Route::post('/complaint', 'complaint');
        Route::post('/file/upload', 'uploadFile');
        Route::post('/file/delete', 'deleteFile');
        Route::post('/set-theme', 'setTheme')->withoutMiddleware('check.user');
    });

Route::controller(CommentController::class)
    ->middleware(['check.user'])
    ->prefix('comments')
    ->group(function () {
        Route::get('/{id}', 'show');
        Route::patch('/{id}', 'update');
        Route::delete('/{id}', 'destroy');
    });

/* Фото профиля */
Route::controller(PictureController::class)
    ->prefix('pictures')
    ->name('pictures.')
    ->group(function () {
        Route::match(['get', 'post'], '/', 'index')->name('index');
        Route::delete('/delete', 'delete')->name('delete');
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
        Route::get('/search-users', 'searchUsers')->name('search-users');
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
        Route::get('/pages/{page?}', 'index')->where('page', '[\w\-]+')->name('pages');
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
