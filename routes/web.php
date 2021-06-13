<?php

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

//Route::get('/install', [\App\Http\Controllers\InstallController::class, 'index'])->withoutMiddleware('web');

Route::get('/', [\App\Http\Controllers\HomeController::class, 'index']);
Route::get('/closed', [\App\Http\Controllers\HomeController::class, 'closed']);
Route::get('/search', [\App\Http\Controllers\HomeController::class, 'search']);
Route::get('/captcha', [\App\Http\Controllers\HomeController::class, 'captcha']);
Route::get('/language/{lang}', [\App\Http\Controllers\HomeController::class, 'language'])->where('lang', '[a-z]+');
Route::match(['get', 'post'], '/ipban', [\App\Http\Controllers\HomeController::class, 'ipban']);

/* Карта сайта */
Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index']);
Route::get('/sitemap/{page}.xml', [\App\Http\Controllers\SitemapController::class, 'page'])->where('page', '[a-z]+');

/* Категории объявления */
Route::group(['prefix' => 'boards'], function () {
    Route::get('/{id?}', [\App\Http\Controllers\BoardController::class, 'index']);
    Route::get('/active', [\App\Http\Controllers\BoardController::class, 'active']);
});

/* Объявления */
Route::group(['prefix' => 'items'], function () {
    Route::get('/{id}', [\App\Http\Controllers\BoardController::class, 'view']);
    Route::get('/close/{id}', [\App\Http\Controllers\BoardController::class, 'close']);
    Route::get('/delete/{id}', [\App\Http\Controllers\BoardController::class, 'delete']);
    Route::match(['get', 'post'], '/create', [\App\Http\Controllers\BoardController::class, 'create']);
    Route::match(['get', 'post'], '/edit/{id}', [\App\Http\Controllers\BoardController::class, 'edit']);
});

    /* Гостевая книга */
Route::group(['prefix' => '/guestbook'], function () {
    Route::get('', [\App\Http\Controllers\GuestbookController::class, 'index']);
    Route::post('/add', [\App\Http\Controllers\GuestbookController::class, 'add']);
    Route::match(['get', 'post'], '/edit/{id}', [\App\Http\Controllers\GuestbookController::class, 'edit']);
});

/* Категория блогов */
Route::group(['prefix' => 'blogs'], function () {
    Route::get('/', [\App\Http\Controllers\ArticleController::class, 'index']);
    Route::get('/{id}', [\App\Http\Controllers\ArticleController::class, 'blog']);
    Route::get('/tags', [\App\Http\Controllers\ArticleController::class, 'tags']);
    Route::get('/tags/{tag}', [\App\Http\Controllers\ArticleController::class, 'searchTag'])->where('tag', '.+');
    Route::get('/authors', [\App\Http\Controllers\ArticleController::class, 'authors']);
    Route::get('/active/articles', [\App\Http\Controllers\ArticleController::class, 'userArticles']);
    Route::get('/active/comments', [\App\Http\Controllers\ArticleController::class, 'userComments']);
    Route::get('/top', [\App\Http\Controllers\ArticleController::class, 'top']);
    Route::get('/rss', [\App\Http\Controllers\ArticleController::class, 'rss']);
    Route::match(['get', 'post'], '/create', [\App\Http\Controllers\ArticleController::class, 'create']);
    Route::match(['get', 'post'], '/search', [\App\Http\Controllers\ArticleController::class, 'search']);
    Route::get('/main', [\App\Http\Controllers\ArticleController::class, 'main']);
});

/* Статьи блогов */
Route::group(['prefix' => 'articles'], function () {
    Route::get('/', [\App\Http\Controllers\ArticleController::class, 'newArticles']);
    Route::get('/{id}', [\App\Http\Controllers\ArticleController::class, 'view']);
    Route::get('/print/{id}', [\App\Http\Controllers\ArticleController::class, 'print']);
    Route::get('/rss/{id}', [\App\Http\Controllers\ArticleController::class, 'rssComments']);
    Route::get('/comments', [\App\Http\Controllers\ArticleController::class, 'newComments']);
    Route::get('/end/{id}', [\App\Http\Controllers\ArticleController::class, 'end']);
    Route::get('/comment/{id}/{cid}', [\App\Http\Controllers\ArticleController::class, 'viewComment'])->whereNumber('cid');
    Route::match(['get', 'post'], '/edit/{id}', [\App\Http\Controllers\ArticleController::class, 'edit']);
    Route::match(['get', 'post'], '/comments/{id}', [\App\Http\Controllers\ArticleController::class, 'comments']);
    Route::match(['get', 'post'], '/edit/{id}/{cid}', [\App\Http\Controllers\ArticleController::class, 'editComment'])->whereNumber('cid');
});

/* Новости */
Route::group(['prefix' => 'news'], function () {
    Route::get('/', [\App\Http\Controllers\NewsController::class, 'index']);
    Route::get('/{id}', [\App\Http\Controllers\NewsController::class, 'view']);
    Route::get('/end/{id}', [\App\Http\Controllers\NewsController::class, 'end']);
    Route::get('/rss', [\App\Http\Controllers\NewsController::class, 'rss']);
    Route::get('/allcomments', [\App\Http\Controllers\NewsController::class, 'allComments']);
    Route::get('/comment/{id}/{cid}', [\App\Http\Controllers\NewsController::class, 'viewComment'])->whereNumber('cid');
    Route::match(['get', 'post'], '/comments/{id}', [\App\Http\Controllers\NewsController::class, 'comments']);
    Route::match(['get', 'post'], '/edit/{id}/{cid}', [\App\Http\Controllers\NewsController::class, 'editComment'])->whereNumber('cid');
});

/* Фотогалерея */
Route::group(['prefix' => 'photos'], function () {
    Route::get('/', [\App\Http\Controllers\PhotoController::class, 'index']);
    Route::get('/{id}', [\App\Http\Controllers\PhotoController::class, 'view']);
    Route::get('/delete/{id}', [\App\Http\Controllers\PhotoController::class, 'delete']);
    Route::get('/end/{id}', [\App\Http\Controllers\PhotoController::class, 'end']);
    Route::get('/albums', [\App\Http\Controllers\PhotoController::class, 'albums']);
    Route::get('/albums/{login}', [\App\Http\Controllers\PhotoController::class, 'album']);
    Route::get('/comments', [\App\Http\Controllers\PhotoController::class, 'allComments']);
    Route::get('/comments/active/{login}', [\App\Http\Controllers\PhotoController::class, 'userComments']);
    Route::get('/comment/{id}/{cid}', [\App\Http\Controllers\PhotoController::class, 'viewComment'])->whereNumber('cid');
    Route::match(['get', 'post'], '/comments/{id}', [\App\Http\Controllers\PhotoController::class, 'comments']);
    Route::match(['get', 'post'], '/create', [\App\Http\Controllers\PhotoController::class, 'create']);
    Route::match(['get', 'post'], '/edit/{id}', [\App\Http\Controllers\PhotoController::class, 'edit']);
    Route::match(['get', 'post'], '/edit/{id}/{cid}', [\App\Http\Controllers\PhotoController::class, 'editComment'])->whereNumber('cid');
    Route::match(['get', 'post'], '/top', [\App\Http\Controllers\PhotoController::class, 'top']);
});

/* Категория форума */
Route::group(['prefix' => 'forums'], function () {
    Route::get('/', [\App\Http\Controllers\Forum\ForumController::class, 'index']);
    Route::get('/{id}', [\App\Http\Controllers\Forum\ForumController::class, 'forum']);
    Route::get('/search', [\App\Http\Controllers\Forum\SearchController::class, 'index']);
    Route::get('/active/posts', [\App\Http\Controllers\Forum\ActiveController::class, 'posts']);
    Route::get('/active/topics', [\App\Http\Controllers\Forum\ActiveController::class, 'topics']);
    Route::post('/active/delete', [\App\Http\Controllers\Forum\ActiveController::class, 'delete']);
    Route::get('/top/posts', [\App\Http\Controllers\Forum\ForumController::class, 'topPosts']);
    Route::get('/top/topics', [\App\Http\Controllers\Forum\ForumController::class, 'topTopics']);
    Route::get('/rss', [\App\Http\Controllers\Forum\ForumController::class, 'rss']);
    Route::get('/bookmarks', [\App\Http\Controllers\Forum\BookmarkController::class, 'index']);
    Route::post('/bookmarks/delete', [\App\Http\Controllers\Forum\BookmarkController::class, 'delete']);
    Route::post('/bookmarks/perform', [\App\Http\Controllers\Forum\BookmarkController::class, 'perform']);
    Route::match(['get', 'post'], '/create', [\App\Http\Controllers\Forum\ForumController::class, 'create']);
});

/* Темы форума */
Route::group(['prefix' => 'topics'], function () {
    Route::get('/', [\App\Http\Controllers\Forum\NewController::class, 'topics']);
    Route::get('/{id}', [\App\Http\Controllers\Forum\TopicController::class, 'index']);
    Route::get('/{id}/{pid}', [\App\Http\Controllers\Forum\TopicController::class, 'viewpost'])->whereNumber('pid');
    Route::post('/votes/{id}', [\App\Http\Controllers\Forum\TopicController::class, 'vote']);
    Route::get('/end/{id}', [\App\Http\Controllers\Forum\TopicController::class, 'end']);
    Route::get('/open/{id}', [\App\Http\Controllers\Forum\TopicController::class, 'open']);
    Route::get('/close/{id}', [\App\Http\Controllers\Forum\TopicController::class, 'close']);
    Route::post('/create/{id}', [\App\Http\Controllers\Forum\TopicController::class, 'create']);
    Route::post('/delete/{id}', [\App\Http\Controllers\Forum\TopicController::class, 'delete']);
    Route::get('/print/{id}', [\App\Http\Controllers\Forum\TopicController::class, 'print']);
    Route::get('/rss/{id}', [\App\Http\Controllers\Forum\ForumController::class, 'rssPosts']);
    Route::match(['get', 'post'], '/edit/{id}', [\App\Http\Controllers\Forum\TopicController::class, 'edit']);
});

/* Посты форума */
Route::group(['prefix' => 'posts'], function () {
    Route::get('/', [\App\Http\Controllers\Forum\NewController::class, 'posts']);
    Route::match(['get', 'post'], '/edit/{id}', [\App\Http\Controllers\Forum\TopicController::class, 'editPost']);
});

/* Категории загрузок */
Route::group(['prefix' => 'loads'], function () {
    Route::get('/', [\App\Http\Controllers\Load\LoadController::class, 'index']);
    Route::get('/rss', [\App\Http\Controllers\Load\LoadController::class, 'rss']);
    Route::get('/{id}', [\App\Http\Controllers\Load\LoadController::class, 'load']);
    Route::get('/top', [\App\Http\Controllers\Load\TopController::class, 'index']);
    Route::get('/search', [\App\Http\Controllers\Load\SearchController::class, 'index']);
});

/* Загрузки */
Route::group(['prefix' => 'downs'], function () {
    Route::get('/', [\App\Http\Controllers\Load\NewController::class, 'files']);
    Route::get('/{id}', [\App\Http\Controllers\Load\DownController::class, 'index']);
    Route::get('/delete/{id}/{fid}', [\App\Http\Controllers\Load\DownController::class, 'deleteFile'])->whereNumber('fid');
    Route::post('/votes/{id}', [\App\Http\Controllers\Load\DownController::class, 'vote']);
    Route::get('/comment/{id}/{cid}', [\App\Http\Controllers\Load\DownController::class, 'viewComment'])->whereNumber('cid');
    Route::get('/end/{id}', [\App\Http\Controllers\Load\DownController::class, 'end']);
    Route::get('/rss/{id}', [\App\Http\Controllers\Load\DownController::class, 'rss']);
    Route::get('/zip/{id}', [\App\Http\Controllers\Load\DownController::class, 'zip']);
    Route::get('/zip/{id}/{fid}', [\App\Http\Controllers\Load\DownController::class, 'zipView'])->whereNumber('fid');
    Route::get('/comments', [\App\Http\Controllers\Load\NewController::class, 'comments']);
    Route::get('/active/files', [\App\Http\Controllers\Load\ActiveController::class, 'files']);
    Route::get('/active/comments', [\App\Http\Controllers\Load\ActiveController::class, 'comments']);
    Route::match(['get', 'post'], '/edit/{id}', [\App\Http\Controllers\Load\DownController::class, 'edit']);
    Route::match(['get', 'post'], '/create', [\App\Http\Controllers\Load\DownController::class, 'create']);
    Route::match(['get', 'post'], '/download/{id}', [\App\Http\Controllers\Load\DownController::class, 'download']);
    Route::match(['get', 'post'], '/comments/{id}', [\App\Http\Controllers\Load\DownController::class, 'comments']);
    Route::match(['get', 'post'], '/edit/{id}/{cid}', [\App\Http\Controllers\Load\DownController::class, 'editComment'])->whereNumber('cid');
});

/* Предложения и проблемы */
Route::group(['prefix' => 'offers'], function () {
    Route::get('/{type?}', [\App\Http\Controllers\OfferController::class, 'index'])->where('type', 'offer|issue');
    Route::get('/{id}', [\App\Http\Controllers\OfferController::class, 'view']);
    Route::get('/end/{id}', [\App\Http\Controllers\OfferController::class, 'end']);
    Route::get('/comment/{id}/{cid}', [\App\Http\Controllers\OfferController::class, 'viewComment'])->whereNumber('cid');
    Route::match(['get', 'post'], '/create', [\App\Http\Controllers\OfferController::class, 'create']);
    Route::match(['get', 'post'], '/edit/{id}', [\App\Http\Controllers\OfferController::class, 'edit']);
    Route::match(['get', 'post'], '/comments/{id}', [\App\Http\Controllers\OfferController::class, 'comments']);
    Route::match(['get', 'post'], '/edit/{id}/{cid}', [\App\Http\Controllers\OfferController::class, 'editComment'])->whereNumber('cid');
});

/* Ajax */
Route::group(['prefix' => 'ajax'], function () {
    Route::get('/getstickers', [\App\Http\Controllers\AjaxController::class, 'getStickers']);
    Route::post('/bbcode', [\App\Http\Controllers\AjaxController::class, 'bbCode']);
    Route::post('/delcomment', [\App\Http\Controllers\AjaxController::class, 'delComment']);
    Route::post('/rating', [\App\Http\Controllers\AjaxController::class, 'rating']);
    Route::post('/vote', [\App\Http\Controllers\AjaxController::class, 'vote']);
    Route::post('/complaint', [\App\Http\Controllers\AjaxController::class, 'complaint']);
    Route::post('/file/upload', [\App\Http\Controllers\AjaxController::class, 'uploadFile']);
    Route::post('/file/delete', [\App\Http\Controllers\AjaxController::class, 'deleteFile']);
});

/* Голосования */
Route::group(['prefix' => 'votes'], function () {
    Route::get('/', [\App\Http\Controllers\VoteController::class, 'index']);
    Route::get('/voters/{id}', [\App\Http\Controllers\VoteController::class, 'voters']);
    Route::get('/history', [\App\Http\Controllers\VoteController::class, 'history']);
    Route::get('/history/{id}', [\App\Http\Controllers\VoteController::class, 'viewHistory']);
    Route::match(['get', 'post'], '/{id}', [\App\Http\Controllers\VoteController::class, 'view']);
    Route::match(['get', 'post'], '/create', [\App\Http\Controllers\VoteController::class, 'create']);
});

/* Мои данные */
Route::group(['prefix' => 'accounts'], function () {
    Route::get('/', [\App\Http\Controllers\User\UserController::class, 'account']);
    Route::get('/editmail', [\App\Http\Controllers\User\UserController::class, 'editMail']);
    Route::post('/changemail', [\App\Http\Controllers\User\UserController::class, 'changeMail']);
    Route::post('/editstatus', [\App\Http\Controllers\User\UserController::class, 'editStatus']);
    Route::post('/editcolor', [\App\Http\Controllers\User\UserController::class, 'editColor']);
    Route::post('/editpassword', [\App\Http\Controllers\User\UserController::class, 'editPassword']);
    Route::post('/apikey', [\App\Http\Controllers\User\UserController::class, 'apikey']);
});

/* Фото профиля */
Route::group(['prefix' => 'pictures'], function () {
    Route::match(['get', 'post'], '', [\App\Http\Controllers\User\PictureController::class, 'index']);
    Route::get('/delete', [\App\Http\Controllers\User\PictureController::class, 'delete']);
});

/* Социальные сети */
Route::group(['prefix' => 'socials'], function () {
    Route::match(['get', 'post'], '', [\App\Http\Controllers\SocialController::class, 'index']);
    Route::get('/delete/{id}', [\App\Http\Controllers\SocialController::class, 'delete']);
});

/* Поиск пользователя */
Route::group(['prefix' => 'searchusers'], function () {
    Route::get('/', [\App\Http\Controllers\User\SearchController::class, 'index']);
    Route::get('/{letter}', [\App\Http\Controllers\User\SearchController::class, 'sort'])->where('letter', '[0-9a-z]+');
    Route::match(['get', 'post'], '/search', [\App\Http\Controllers\User\SearchController::class, 'search']);
});

/* Стена сообщений */
Route::group(['prefix' => 'walls'], function () {
    Route::get('/{login}', [\App\Http\Controllers\WallController::class, 'index']);
    Route::post('/{login}/create', [\App\Http\Controllers\WallController::class, 'create']);
    Route::post('/{login}/delete', [\App\Http\Controllers\WallController::class, 'delete']);
});

/* Личные сообщения */
Route::group(['prefix' => 'messages', 'middleware' => 'check.user'], function () {
    Route::get('/', [\App\Http\Controllers\MessageController::class, 'index']);
    Route::get('/new', [\App\Http\Controllers\MessageController::class, 'newMessages']);
    Route::get('/talk/{login}', [\App\Http\Controllers\MessageController::class, 'talk']);
    Route::get('/delete/{uid}', [\App\Http\Controllers\MessageController::class, 'delete'])->whereNumber('uid');
    Route::match(['get', 'post'], '/send', [\App\Http\Controllers\MessageController::class, 'send']);
});

/* Игнор-лист */
Route::group(['prefix' => 'ignores'], function () {
    Route::post('/delete', [\App\Http\Controllers\IgnoreController::class, 'delete']);
    Route::match(['get', 'post'], '', [\App\Http\Controllers\IgnoreController::class, 'index']);
    Route::match(['get', 'post'], '/note/{id}', [\App\Http\Controllers\IgnoreController::class, 'note']);
});

/* Контакт-лист */
Route::group(['prefix' => 'contacts'], function () {
    Route::post('/delete', [\App\Http\Controllers\ContactController::class, 'delete']);
    Route::match(['get', 'post'], '', [\App\Http\Controllers\ContactController::class, 'index']);
    Route::match(['get', 'post'], '/note/{id}', [\App\Http\Controllers\ContactController::class, 'note']);
});

/* Перевод денег */
Route::group(['prefix' => 'transfers'], function () {
    Route::get('/', [\App\Http\Controllers\TransferController::class, 'index']);
    Route::post('/send', [\App\Http\Controllers\TransferController::class, 'send']);
});

/* Личные заметки */
Route::group(['prefix' => 'notebooks'], function () {
    Route::get('/', [\App\Http\Controllers\NotebookController::class, 'index']);
    Route::match(['get', 'post'], '/edit', [\App\Http\Controllers\NotebookController::class, 'edit']);
});

/* Реклама */
Route::group(['prefix' => 'adverts'], function () {
    Route::get('/', [\App\Http\Controllers\AdvertController::class, 'index']);
    Route::match(['get', 'post'], '/create', [\App\Http\Controllers\AdvertController::class, 'create']);
});

/* Репутация пользователя */
Route::group(['prefix' => 'ratings'], function () {
    Route::get('/{login}/{received?}', [\App\Http\Controllers\RatingController::class, 'received']);
    Route::get('/{login}/gave', [\App\Http\Controllers\RatingController::class, 'gave']);
    Route::post('/delete', [\App\Http\Controllers\RatingController::class, 'delete']);
});

/* API */
Route::group(['prefix' => 'api'], function () {
    Route::get('/', [\App\Http\Controllers\ApiController::class, 'index']);
});

Route::group(['prefix' => 'users'], function () {
    Route::match(['get', 'post'], '/', [\App\Http\Controllers\User\ListController::class, 'userlist']);
    Route::match(['get', 'post'], '/{login}/rating', [\App\Http\Controllers\RatingController::class, 'index']);
    Route::match(['get', 'post'], '/{login}/note', [\App\Http\Controllers\User\UserController::class, 'note']);
    Route::get('/{login}', [\App\Http\Controllers\User\UserController::class, 'index']);
});

Route::get('/restore', [\App\Http\Controllers\MailController::class, 'restore']);
Route::match(['get', 'post'], '/recovery', [\App\Http\Controllers\MailController::class, 'recovery']);
Route::match(['get', 'post'], '/mails', [\App\Http\Controllers\MailController::class, 'index']);
Route::match(['get', 'post'], '/unsubscribe', [\App\Http\Controllers\MailController::class, 'unsubscribe']);
Route::get('/authlogs', [\App\Http\Controllers\LoginController::class, 'index']);
Route::match(['get', 'post'], '/ban', [\App\Http\Controllers\User\BanController::class, 'ban']);
Route::get('/faq', [\App\Http\Controllers\PageController::class, 'faq']);
Route::get('/statusfaq', [\App\Http\Controllers\PageController::class, 'statusfaq']);
Route::get('/surprise', [\App\Http\Controllers\PageController::class, 'surprise']);
Route::get('/logout', [\App\Http\Controllers\User\UserController::class, 'logout']);
Route::match(['get', 'post'], '/key', [\App\Http\Controllers\User\UserController::class, 'key']);
Route::match(['get', 'post'], '/login', [\App\Http\Controllers\User\UserController::class, 'login']);
Route::match(['get', 'post'], '/register', [\App\Http\Controllers\User\UserController::class, 'register']);
Route::match(['get', 'post'], '/profile', [\App\Http\Controllers\User\UserController::class, 'profile']);
Route::match(['get', 'post'], '/settings', [\App\Http\Controllers\User\UserController::class, 'setting']);
Route::post('/check-login', [\App\Http\Controllers\User\UserController::class, 'checkLogin']);
Route::get('/pages/{page?}', [\App\Http\Controllers\PageController::class, 'index'])->where('page', '[\w\-]+');
Route::get('/menu', [\App\Http\Controllers\PageController::class, 'menu']);
Route::get('/tags', [\App\Http\Controllers\PageController::class, 'tags']);
Route::get('/rules', [\App\Http\Controllers\PageController::class, 'rules']);
Route::get('/stickers', [\App\Http\Controllers\PageController::class, 'stickers']);
Route::get('/stickers/{id}', [\App\Http\Controllers\PageController::class, 'stickersCategory']);
Route::get('/online', [\App\Http\Controllers\OnlineController::class, 'index']);
Route::get('/online/all', [\App\Http\Controllers\OnlineController::class, 'all']);
Route::get('/counters', [\App\Http\Controllers\CounterController::class, 'index']);
Route::get('/files/{page?}', [\App\Http\Controllers\FileController::class, 'index'])->where('page', '.+');

/* Админ-панель */
Route::group(['prefix' => 'admin', 'middleware' => 'check.admin'], function () {
    Route::get('/', [\App\Http\Controllers\Admin\AdminController::class, 'main']);
    Route::get('/upgrade', [\App\Http\Controllers\Admin\AdminController::class, 'upgrade']);

    /* Админ-чат */
    Route::match(['get', 'post'], '/chats', [\App\Http\Controllers\Admin\ChatController::class, 'index']);
    Route::match(['get', 'post'], '/chats/edit/{id}', [\App\Http\Controllers\Admin\ChatController::class, 'edit']);
    Route::get('/chats/clear', [\App\Http\Controllers\Admin\ChatController::class, 'clear']);

    /* Гостевая */
    Route::get('/guestbook', [\App\Http\Controllers\Admin\GuestbookController::class, 'index']);
    Route::match(['get', 'post'], '/guestbook/edit/{id}', [\App\Http\Controllers\Admin\GuestbookController::class, 'edit']);
    Route::match(['get', 'post'], '/guestbook/reply/{id}', [\App\Http\Controllers\Admin\GuestbookController::class, 'reply']);
    Route::post('/guestbook/delete', [\App\Http\Controllers\Admin\GuestbookController::class, 'delete']);
    Route::get('/guestbook/clear', [\App\Http\Controllers\Admin\GuestbookController::class, 'clear']);

    /* Форум */
    Route::get('/forums', [\App\Http\Controllers\Admin\ForumController::class, 'index']);
    Route::post('/forums/create', [\App\Http\Controllers\Admin\ForumController::class, 'create']);
    Route::match(['get', 'post'], '/forums/edit/{id}', [\App\Http\Controllers\Admin\ForumController::class, 'edit']);
    Route::get('/forums/delete/{id}', [\App\Http\Controllers\Admin\ForumController::class, 'delete']);
    Route::get('/forums/restatement', [\App\Http\Controllers\Admin\ForumController::class, 'restatement']);
    Route::get('/forums/{id}', [\App\Http\Controllers\Admin\ForumController::class, 'forum']);
    Route::match(['get', 'post'], '/topics/edit/{id}', [\App\Http\Controllers\Admin\ForumController::class, 'editTopic']);
    Route::match(['get', 'post'], '/topics/move/{id}', [\App\Http\Controllers\Admin\ForumController::class, 'moveTopic']);
    Route::get('/topics/action/{id}', [\App\Http\Controllers\Admin\ForumController::class, 'actionTopic']);
    Route::get('/topics/delete/{id}', [\App\Http\Controllers\Admin\ForumController::class, 'deleteTopic']);
    Route::get('/topics/{id}', [\App\Http\Controllers\Admin\ForumController::class, 'topic']);
    Route::match(['get', 'post'], '/posts/edit/{id}', [\App\Http\Controllers\Admin\ForumController::class, 'editPost']);
    Route::post('/posts/delete', [\App\Http\Controllers\Admin\ForumController::class, 'deletePosts']);
    Route::get('/topics/end/{id}', [\App\Http\Controllers\Admin\ForumController::class, 'end']);

    /* Галерея */
    Route::get('/photos', [\App\Http\Controllers\Admin\PhotoController::class, 'index']);
    Route::match(['get', 'post'], '/photos/edit/{id}', [\App\Http\Controllers\Admin\PhotoController::class, 'edit']);
    Route::get('/photos/restatement', [\App\Http\Controllers\Admin\PhotoController::class, 'restatement']);
    Route::get('/photos/delete/{id}', [\App\Http\Controllers\Admin\PhotoController::class, 'delete']);

    /* Блоги */
    Route::get('/blogs', [\App\Http\Controllers\Admin\ArticleController::class, 'index']);
    Route::post('/blogs/create', [\App\Http\Controllers\Admin\ArticleController::class, 'create']);
    Route::get('/blogs/restatement', [\App\Http\Controllers\Admin\ArticleController::class, 'restatement']);
    Route::match(['get', 'post'], '/blogs/edit/{id}', [\App\Http\Controllers\Admin\ArticleController::class, 'edit']);
    Route::get('/blogs/delete/{id}', [\App\Http\Controllers\Admin\ArticleController::class, 'delete']);
    Route::get('/blogs/{id}', [\App\Http\Controllers\Admin\ArticleController::class, 'blog']);
    Route::match(['get', 'post'], '/articles/edit/{id}', [\App\Http\Controllers\Admin\ArticleController::class, 'editArticle']);
    Route::match(['get', 'post'], '/articles/move/{id}', [\App\Http\Controllers\Admin\ArticleController::class, 'moveArticle']);
    Route::get('/articles/delete/{id}', [\App\Http\Controllers\Admin\ArticleController::class, 'deleteArticle']);

    /* Доска объявлений */
    Route::get('/boards/{id?}', [\App\Http\Controllers\Admin\BoardController::class, 'index']);
    Route::get('/boards/restatement', [\App\Http\Controllers\Admin\BoardController::class, 'restatement']);
    Route::match(['get', 'post'], '/items/edit/{id}', [\App\Http\Controllers\Admin\BoardController::class, 'editItem']);
    Route::get('/items/delete/{id}', [\App\Http\Controllers\Admin\BoardController::class, 'deleteItem']);
    Route::get('/boards/categories', [\App\Http\Controllers\Admin\BoardController::class, 'categories']);
    Route::post('/boards/create', [\App\Http\Controllers\Admin\BoardController::class, 'create']);
    Route::match(['get', 'post'], '/boards/edit/{id}', [\App\Http\Controllers\Admin\BoardController::class, 'edit']);
    Route::get('/boards/delete/{id}', [\App\Http\Controllers\Admin\BoardController::class, 'delete']);

    /* Админская реклама */
    Route::match(['get', 'post'], '/admin-adverts', [\App\Http\Controllers\Admin\AdminAdvertController::class, 'index']);

    /* Пользовательская реклама */
    Route::get('/adverts', [\App\Http\Controllers\Admin\AdvertController::class, 'index']);

    /* Модер */
    Route::group(['middleware' => 'check.admin:moder'], function () {
        /* Жалобы */
        Route::get('/spam', [\App\Http\Controllers\Admin\SpamController::class, 'index']);
        Route::post('/spam/delete', [\App\Http\Controllers\Admin\SpamController::class, 'delete']);

        /* Бан / разбан */
        Route::get('/bans', [\App\Http\Controllers\Admin\BanController::class, 'index']);
        Route::match(['get', 'post'], '/bans/edit', [\App\Http\Controllers\Admin\BanController::class, 'edit']);
        Route::match(['get', 'post'], '/bans/change', [\App\Http\Controllers\Admin\BanController::class, 'change']);
        Route::get('/bans/unban', [\App\Http\Controllers\Admin\BanController::class, 'unban']);

        /* Забаненные */
        Route::get('/banlists', [\App\Http\Controllers\Admin\BanlistController::class, 'index']);

        /* Ожидающие */
        Route::match(['get', 'post'], '/reglists', [\App\Http\Controllers\Admin\ReglistController::class, 'index']);

        /* Голосования */
        Route::get('/votes', [\App\Http\Controllers\Admin\VoteController::class, 'index']);
        Route::get('/votes/history', [\App\Http\Controllers\Admin\VoteController::class, 'history']);
        Route::match(['get', 'post'], '/votes/edit/{id}', [\App\Http\Controllers\Admin\VoteController::class, 'edit']);
        Route::get('/votes/close/{id}', [\App\Http\Controllers\Admin\VoteController::class, 'close']);
        Route::get('/votes/delete/{id}', [\App\Http\Controllers\Admin\VoteController::class, 'delete']);
        Route::get('/votes/restatement', [\App\Http\Controllers\Admin\VoteController::class, 'restatement']);

        /* Антимат */
        Route::match(['get', 'post'], '/antimat', [\App\Http\Controllers\Admin\AntimatController::class, 'index']);
        Route::get('/antimat/delete', [\App\Http\Controllers\Admin\AntimatController::class, 'delete']);
        Route::get('/antimat/clear', [\App\Http\Controllers\Admin\AntimatController::class, 'clear']);

        /* История банов */
        Route::get('/banhists', [\App\Http\Controllers\Admin\BanhistController::class, 'index']);
        Route::get('/banhists/view', [\App\Http\Controllers\Admin\BanhistController::class, 'view']);
        Route::post('/banhists/delete', [\App\Http\Controllers\Admin\BanhistController::class, 'delete']);

        /* Приглашения */
        Route::get('/invitations', [\App\Http\Controllers\Admin\InvitationController::class, 'index']);
        Route::match(['get', 'post'], '/invitations/create', [\App\Http\Controllers\Admin\InvitationController::class, 'create']);
        Route::get('/invitations/keys', [\App\Http\Controllers\Admin\InvitationController::class, 'keys']);
        Route::post('/invitations/send', [\App\Http\Controllers\Admin\InvitationController::class, 'send']);
        Route::post('/invitations/mail', [\App\Http\Controllers\Admin\InvitationController::class, 'mail']);
        Route::post('/invitations/delete', [\App\Http\Controllers\Admin\InvitationController::class, 'delete']);

        /* Денежный переводы*/
        Route::get('/transfers', [\App\Http\Controllers\Admin\TransferController::class, 'index']);
        Route::get('/transfers/view', [\App\Http\Controllers\Admin\TransferController::class, 'view']);
    });

    /* Админ */
    Route::group(['middleware' => 'check.admin:admin'], function () {
        /* Правила */
        Route::get('/rules', [\App\Http\Controllers\Admin\RuleController::class, 'index']);
        Route::match(['get', 'post'], '/rules/edit', [\App\Http\Controllers\Admin\RuleController::class, 'edit']);

        /* Новости */
        Route::get('/news', [\App\Http\Controllers\Admin\NewsController::class, 'index']);
        Route::match(['get', 'post'], '/news/edit/{id}', [\App\Http\Controllers\Admin\NewsController::class, 'edit']);
        Route::match(['get', 'post'], '/news/create', [\App\Http\Controllers\Admin\NewsController::class, 'create']);
        Route::get('/news/restatement', [\App\Http\Controllers\Admin\NewsController::class, 'restatement']);
        Route::get('/news/delete/{id}', [\App\Http\Controllers\Admin\NewsController::class, 'delete']);


        /* IP-бан */
        Route::match(['get', 'post'], '/ipbans', [\App\Http\Controllers\Admin\IpBanController::class, 'index']);
        Route::post('/ipbans/delete', [\App\Http\Controllers\Admin\IpBanController::class, 'delete']);
        Route::get('/ipbans/clear', [\App\Http\Controllers\Admin\IpBanController::class, 'clear']);

        /* PHP-info */
        Route::get('/phpinfo', [\App\Http\Controllers\Admin\AdminController::class, 'phpinfo']);

        /* Загрузки */
        Route::get('/loads', [\App\Http\Controllers\Admin\LoadController::class, 'index']);
        Route::post('/loads/create', [\App\Http\Controllers\Admin\LoadController::class, 'create']);
        Route::match(['get', 'post'], '/loads/edit/{id}', [\App\Http\Controllers\Admin\LoadController::class, 'edit']);
        Route::get('/loads/delete/{id}', [\App\Http\Controllers\Admin\LoadController::class, 'delete']);
        Route::get('/loads/restatement', [\App\Http\Controllers\Admin\LoadController::class, 'restatement']);
        Route::get('/loads/{id}', [\App\Http\Controllers\Admin\LoadController::class, 'load']);
        Route::match(['get', 'post'], '/downs/edit/{id}', [\App\Http\Controllers\Admin\LoadController::class, 'editDown']);
        Route::match(['get', 'post'], '/downs/delete/{id}', [\App\Http\Controllers\Admin\LoadController::class, 'deleteDown']);
        Route::get('/downs/delete/{id}/{fid}', [\App\Http\Controllers\Admin\LoadController::class, 'deleteFile'])->whereNumber('fid');
        Route::get('/downs/new', [\App\Http\Controllers\Admin\LoadController::class, 'new']);
        Route::get('/downs/publish/{id}', [\App\Http\Controllers\Admin\LoadController::class, 'publish']);

        /* Ошибки */
        Route::get('/errors', [\App\Http\Controllers\Admin\ErrorController::class, 'index']);
        Route::get('/errors/clear', [\App\Http\Controllers\Admin\ErrorController::class, 'clear']);

        /* Черный список */
        Route::match(['get', 'post'], '/blacklists', [\App\Http\Controllers\Admin\BlacklistController::class, 'index']);
        Route::post('/blacklists/delete', [\App\Http\Controllers\Admin\BlacklistController::class, 'delete']);

        /* Предложения / проблемы */
        Route::get('/offers/{type?}', [\App\Http\Controllers\Admin\OfferController::class, 'index'])->where('type', 'offer|issue');
        Route::get('/offers/{id}', [\App\Http\Controllers\Admin\OfferController::class, 'view']);
        Route::match(['get', 'post'], '/offers/edit/{id}', [\App\Http\Controllers\Admin\OfferController::class, 'edit']);
        Route::match(['get', 'post'], '/offers/reply/{id}', [\App\Http\Controllers\Admin\OfferController::class, 'reply']);
        Route::get('/offers/restatement', [\App\Http\Controllers\Admin\OfferController::class, 'restatement']);
        Route::match(['get', 'post'], '/offers/delete', [\App\Http\Controllers\Admin\OfferController::class, 'delete']);

        /* Стикеры */
        Route::get('/stickers', [\App\Http\Controllers\Admin\StickerController::class, 'index']);
        Route::get('/stickers/{id}', [\App\Http\Controllers\Admin\StickerController::class, 'category']);
        Route::post('/stickers/create', [\App\Http\Controllers\Admin\StickerController::class, 'create']);
        Route::match(['get', 'post'], '/stickers/edit/{id}', [\App\Http\Controllers\Admin\StickerController::class, 'edit']);
        Route::get('/stickers/delete/{id}', [\App\Http\Controllers\Admin\StickerController::class, 'delete']);
        Route::match(['get', 'post'], '/stickers/sticker/create', [\App\Http\Controllers\Admin\StickerController::class, 'createSticker']);
        Route::match(['get', 'post'], '/stickers/sticker/edit/{id}', [\App\Http\Controllers\Admin\StickerController::class, 'editSticker']);
        Route::get('/stickers/sticker/delete/{id}', [\App\Http\Controllers\Admin\StickerController::class, 'deleteSticker']);

        /* Статусы */
        Route::get('/status', [\App\Http\Controllers\Admin\StatusController::class, 'index']);
        Route::match(['get', 'post'], '/status/create', [\App\Http\Controllers\Admin\StatusController::class, 'create']);
        Route::match(['get', 'post'], '/status/edit', [\App\Http\Controllers\Admin\StatusController::class, 'edit']);
        Route::get('/status/delete', [\App\Http\Controllers\Admin\StatusController::class, 'delete']);
    });

    /* Босс */
    Route::group(['middleware' => 'check.admin:boss'], function () {
        /* Настройки */
        Route::match(['get', 'post'], '/settings', [\App\Http\Controllers\Admin\SettingController::class, 'index']);

        /* Пользователи */
        Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index']);
        Route::get('/users/search', [\App\Http\Controllers\Admin\UserController::class, 'search']);
        Route::match(['get', 'post'], '/users/edit', [\App\Http\Controllers\Admin\UserController::class, 'edit']);
        Route::match(['get', 'post'], '/users/delete', [\App\Http\Controllers\Admin\UserController::class, 'delete']);

        /* Очистка кеша */
        Route::get('/caches', [\App\Http\Controllers\Admin\CacheController::class, 'index']);
        Route::post('/caches/clear', [\App\Http\Controllers\Admin\CacheController::class, 'clear']);

        /* Бэкап */
        Route::get('/backups', [\App\Http\Controllers\Admin\BackupController::class, 'index']);
        Route::match(['get', 'post'], '/backups/create', [\App\Http\Controllers\Admin\BackupController::class, 'create']);
        Route::get('/backups/delete', [\App\Http\Controllers\Admin\BackupController::class, 'delete']);

        /* Сканирование */
        Route::match(['get', 'post'], '/checkers', [\App\Http\Controllers\Admin\CheckerController::class, 'index']);
        Route::match(['get', 'post'], '/checkers/scan', [\App\Http\Controllers\Admin\CheckerController::class, 'scan']);

        /* Приват рассылка */
        Route::match(['get', 'post'], '/delivery', [\App\Http\Controllers\Admin\DeliveryController::class, 'index']);

        /* Логи */
        Route::get('/logs', [\App\Http\Controllers\Admin\LogController::class, 'index']);
        Route::get('/logs/clear', [\App\Http\Controllers\Admin\LogController::class, 'clear']);

        /* Шаблоны писем */
        Route::get('/notices', [\App\Http\Controllers\Admin\NoticeController::class, 'index']);
        Route::match(['get', 'post'], '/notices/create', [\App\Http\Controllers\Admin\NoticeController::class, 'create']);
        Route::match(['get', 'post'], '/notices/edit/{id}', [\App\Http\Controllers\Admin\NoticeController::class, 'edit']);
        Route::get('/notices/delete/{id}', [\App\Http\Controllers\Admin\NoticeController::class, 'delete']);

        /* Редактор */
        Route::get('/files', [\App\Http\Controllers\Admin\FileController::class, 'index']);
        Route::match(['get', 'post'], '/files/edit', [\App\Http\Controllers\Admin\FileController::class, 'edit']);
        Route::match(['get', 'post'], '/files/create', [\App\Http\Controllers\Admin\FileController::class, 'create']);
        Route::get('/files/delete', [\App\Http\Controllers\Admin\FileController::class, 'delete']);

        /* Платная реклама */
        Route::match(['get', 'post'], '/adverts/edit/{id}', [\App\Http\Controllers\Admin\AdvertController::class, 'edit']);
        Route::post('/adverts/delete', [\App\Http\Controllers\Admin\AdvertController::class, 'delete']);
        Route::get('/paid-adverts', [\App\Http\Controllers\Admin\PaidAdvertController::class, 'index']);
        Route::match(['get', 'post'], '/paid-adverts/create', [\App\Http\Controllers\Admin\PaidAdvertController::class, 'create']);
        Route::match(['get', 'post'], '/paid-adverts/edit/{id}', [\App\Http\Controllers\Admin\PaidAdvertController::class, 'edit']);
        Route::get('/paid-adverts/delete/{id}', [\App\Http\Controllers\Admin\PaidAdvertController::class, 'delete']);

        /* Чистка пользователей */
        Route::match(['get', 'post'], '/delusers', [\App\Http\Controllers\Admin\DelUserController::class, 'index']);
        Route::post('/delusers/clear', [\App\Http\Controllers\Admin\DelUserController::class, 'clear']);

        /* Модули */
        Route::get('/modules', [\App\Http\Controllers\Admin\ModuleController::class, 'index']);
        Route::get('/modules/module', [\App\Http\Controllers\Admin\ModuleController::class, 'module']);
        Route::get('/modules/install', [\App\Http\Controllers\Admin\ModuleController::class, 'install']);
        Route::get('/modules/uninstall', [\App\Http\Controllers\Admin\ModuleController::class, 'uninstall']);
    });
});

$modules = \App\Models\Module::getEnabledModules();
foreach ($modules as $module) {
    if (file_exists(base_path('modules/' . $module . '/routes.php'))) {
        include_once base_path('modules/' . $module . '/routes.php');
    }
}
