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
    ->group(function () {
        Route::get('/{id?}', 'index')->name('boards.index');
        Route::get('/active', 'active')->name('boards.active');
    });

/* Объявления */
Route::controller(BoardController::class)
    ->prefix('items')
    ->group(function () {
        Route::get('/{id}', 'view');
        Route::get('/close/{id}', 'close');
        Route::get('/delete/{id}', 'delete');
        Route::match(['get', 'post'], '/create', 'create');
        Route::match(['get', 'post'], '/edit/{id}', 'edit');
    });

/* Гостевая книга */
Route::controller(GuestbookController::class)
    ->prefix('guestbook')
    ->group(function () {
        Route::get('/', 'index');
        Route::post('/add', 'add');
        Route::match(['get', 'post'], '/edit/{id}', 'edit');
    });

/* Категория блогов */
Route::controller(ArticleController::class)
    ->prefix('blogs')
    ->group(function () {
        Route::get('/', 'index');
        Route::get('/{id}', 'blog');
        Route::get('/tags', 'tags');
        Route::get('/tags-search', 'searchTags');
        Route::get('/tags/{tag}', 'getTag')->where('tag', '.+');
        Route::get('/authors', 'authors');
        Route::get('/active/articles', 'userArticles');
        Route::get('/active/comments', 'userComments');
        Route::get('/top', 'top');
        Route::get('/rss', 'rss');
        Route::match(['get', 'post'], '/create', 'create');
        Route::get('/main', 'main');
    });

/* Статьи блогов */
Route::controller(ArticleController::class)
    ->prefix('articles')
    ->group(function () {
        Route::get('/', 'newArticles');
        Route::get('/{id}', 'view');
        Route::get('/print/{id}', 'print');
        Route::get('/rss/{id}', 'rssComments');
        Route::get('/comments', 'newComments');
        Route::get('/end/{id}', 'end');
        Route::get('/comment/{id}/{cid}', 'viewComment')->whereNumber('cid');
        Route::match(['get', 'post'], '/edit/{id}', 'edit');
        Route::match(['get', 'post'], '/comments/{id}', 'comments');
        Route::match(['get', 'post'], '/edit/{id}/{cid}', 'editComment')->whereNumber('cid');
    });

/* Новости */
Route::controller(NewsController::class)
    ->prefix('news')
    ->group(function () {
        Route::get('/', 'index');
        Route::get('/{id}', 'view');
        Route::get('/end/{id}', 'end');
        Route::get('/rss', 'rss');
        Route::get('/allcomments', 'allComments');
        Route::get('/comment/{id}/{cid}', 'viewComment')->whereNumber('cid');
        Route::match(['get', 'post'], '/comments/{id}', 'comments');
        Route::match(['get', 'post'], '/edit/{id}/{cid}', 'editComment')->whereNumber('cid');
    });

/* Галерея */
Route::controller(PhotoController::class)
    ->prefix('photos')
    ->group(function () {
        Route::get('/', 'index');
        Route::get('/{id}', 'view');
        Route::get('/delete/{id}', 'delete');
        Route::get('/end/{id}', 'end');
        Route::get('/albums', 'albums');
        Route::get('/albums/{login}', 'album');
        Route::get('/comments', 'allComments');
        Route::get('/comments/active/{login}', 'userComments');
        Route::get('/comment/{id}/{cid}', 'viewComment')->whereNumber('cid');
        Route::match(['get', 'post'], '/comments/{id}', 'comments');
        Route::match(['get', 'post'], '/create', 'create');
        Route::match(['get', 'post'], '/edit/{id}', 'edit');
        Route::match(['get', 'post'], '/edit/{id}/{cid}', 'editComment')->whereNumber('cid');
        Route::match(['get', 'post'], '/top', 'top');
    });

/* Категория форума */
Route::prefix('forums')->group(function () {
    Route::get('/', [ForumController::class, 'index']);
    Route::get('/{id}', [ForumController::class, 'forum']);
    Route::get('/top/posts', [ForumController::class, 'topPosts']);
    Route::get('/top/topics', [ForumController::class, 'topTopics']);
    Route::get('/rss', [ForumController::class, 'rss']);
    Route::match(['get', 'post'], '/create', [ForumController::class, 'create']);

    Route::get('/active/posts', [ActiveController::class, 'posts']);
    Route::get('/active/topics', [ActiveController::class, 'topics']);
    Route::delete('/active/delete/{id}', [ActiveController::class, 'destroy']);

    Route::get('/bookmarks', [BookmarkController::class, 'index']);
    Route::post('/bookmarks/delete', [BookmarkController::class, 'delete']);
    Route::post('/bookmarks/perform', [BookmarkController::class, 'perform']);
});

/* Темы форума */
Route::prefix('topics')->group(function () {
    Route::get('/', [NewController::class, 'topics']);

    Route::get('/{id}', [TopicController::class, 'index']);
    Route::get('/{id}/{pid}', [TopicController::class, 'viewPost'])->whereNumber('pid');
    Route::post('/votes/{id}', [TopicController::class, 'vote']);
    Route::get('/end/{id}', [TopicController::class, 'end']);
    Route::get('/open/{id}', [TopicController::class, 'open']);
    Route::get('/close/{id}', [TopicController::class, 'close']);
    Route::post('/create/{id}', [TopicController::class, 'create']);
    Route::post('/delete/{id}', [TopicController::class, 'delete']);
    Route::get('/print/{id}', [TopicController::class, 'print']);
    Route::match(['get', 'post'], '/edit/{id}', [TopicController::class, 'edit']);

    Route::get('/rss/{id}', [ForumController::class, 'rssPosts']);
});

/* Посты форума */
Route::prefix('posts')->group(function () {
    Route::get('/', [NewController::class, 'posts']);
    Route::match(['get', 'post'], '/edit/{id}', [TopicController::class, 'editPost']);
});

/* Категории загрузок */
Route::prefix('loads')->group(function () {
    Route::get('/', [LoadController::class, 'index']);
    Route::get('/rss', [LoadController::class, 'rss']);
    Route::get('/{id}', [LoadController::class, 'load'])->name('loads.load');
    Route::get('/top', [TopController::class, 'index']);
});

/* Загрузки */
Route::prefix('downs')->group(function () {
    Route::get('/', [LoadNewController::class, 'files']);
    Route::get('/{id}', [DownController::class, 'index']);
    Route::get('/comment/{id}/{cid}', [DownController::class, 'viewComment'])->whereNumber('cid');
    Route::get('/end/{id}', [DownController::class, 'end']);
    Route::get('/rss/{id}', [DownController::class, 'rss']);
    Route::get('/zip/{id}', [DownController::class, 'zip']);
    Route::get('/zip/{id}/{fid}', [DownController::class, 'zipView'])->whereNumber('fid');
    Route::get('/comments', [LoadNewController::class, 'comments']);
    Route::get('/active/files', [LoadActiveController::class, 'files']);
    Route::get('/active/comments', [LoadActiveController::class, 'comments']);
    Route::get('/download/{id}', [DownController::class, 'download']);
    Route::get('/download/{id}/{lid}', [DownController::class, 'downloadLink'])->whereNumber('lid');
    Route::match(['get', 'post'], '/edit/{id}', [DownController::class, 'edit']);
    Route::match(['get', 'post'], '/create', [DownController::class, 'create']);
    Route::match(['get', 'post'], '/comments/{id}', [DownController::class, 'comments']);
    Route::match(['get', 'post'], '/edit/{id}/{cid}', [DownController::class, 'editComment'])->whereNumber('cid');
});

/* Предложения и проблемы */
Route::controller(OfferController::class)
    ->prefix('offers')
    ->group(function () {
        Route::get('/{type?}', 'index')->where('type', 'offer|issue');
        Route::get('/{id}', 'view');
        Route::get('/end/{id}', 'end');
        Route::get('/comment/{id}/{cid}', 'viewComment')->whereNumber('cid');
        Route::match(['get', 'post'], '/create', 'create');
        Route::match(['get', 'post'], '/edit/{id}', 'edit');
        Route::match(['get', 'post'], '/comments/{id}', 'comments');
        Route::match(['get', 'post'], '/edit/{id}/{cid}', 'editComment')->whereNumber('cid');
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
        Route::match(['get', 'post'], '/', [ListController::class, 'userlist']);
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
    ->group(function () {
        Route::controller(AdminController::class)
            ->group(function () {
                Route::get('/', 'main');
            });

        /* Проверка обновлений */
        Route::controller(UpgradeController::class)
            ->prefix('upgrade')
            ->group(function () {
                Route::get('/', 'index');
                Route::get('/check', 'check');
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
            ->group(function () {
                Route::get('/', 'index');
                Route::match(['get', 'post'], '/edit/{id}', 'edit');
                Route::match(['get', 'post'], '/reply/{id}', 'reply');
                Route::post('/delete', 'delete');
                Route::post('/publish', 'publish');
                Route::get('/clear', 'clear');
            });

        /* Форум */
        Route::controller(AdminForumController::class)
            ->prefix('forums')
            ->group(function () {
                Route::get('/', 'index');
                Route::post('/create', 'create');
                Route::match(['get', 'post'], '/edit/{id}', 'edit');
                Route::get('/delete/{id}', 'delete');
                Route::get('/restatement', 'restatement');
                Route::get('/{id}', 'forum');
            });

        /* Темы */
        Route::controller(AdminForumController::class)
            ->prefix('topics')
            ->group(function () {
                Route::match(['get', 'post'], '/edit/{id}', 'editTopic');
                Route::match(['get', 'post'], '/move/{id}', 'moveTopic');
                Route::get('/action/{id}', 'actionTopic');
                Route::get('/delete/{id}', 'deleteTopic');
                Route::get('/{id}', 'topic');
                Route::get('/end/{id}', 'end');
            });

        /* Посты */
        Route::controller(AdminForumController::class)
            ->prefix('posts')
            ->group(function () {
                Route::match(['get', 'post'], '/edit/{id}', 'editPost');
                Route::post('/delete', 'deletePosts');
            });

        /* Галерея */
        Route::controller(AdminPhotoController::class)
            ->prefix('photos')
            ->group(function () {
                Route::get('/', 'index');
                Route::match(['get', 'post'], '/edit/{id}', 'edit');
                Route::get('/restatement', 'restatement');
                Route::get('/delete/{id}', 'delete');
            });

        /* Блоги */
        Route::controller(AdminArticleController::class)
            ->prefix('blogs')
            ->group(function () {
                Route::get('/', 'index');
                Route::post('/create', 'create');
                Route::get('/restatement', 'restatement');
                Route::match(['get', 'post'], '/edit/{id}', 'edit');
                Route::get('/delete/{id}', 'delete');
                Route::get('/{id}', 'blog');
            });

        /* Статьи */
        Route::controller(AdminArticleController::class)
            ->prefix('articles')
            ->group(function () {
                Route::match(['get', 'post'], '/edit/{id}', 'editArticle');
                Route::match(['get', 'post'], '/move/{id}', 'moveArticle');
                Route::get('/delete/{id}', 'deleteArticle');
            });

        /* Доска объявлений */
        Route::controller(AdminBoardController::class)
            ->prefix('boards')
            ->group(function () {
                Route::get('/{id?}', 'index');
                Route::get('/restatement', 'restatement');
                Route::get('/categories', 'categories');
                Route::post('/create', 'create');
                Route::match(['get', 'post'], '/edit/{id}', 'edit');
                Route::get('/delete/{id}', 'delete');
            });

        /* Объявления */
        Route::controller(AdminBoardController::class)
            ->prefix('items')
            ->group(function () {
                Route::match(['get', 'post'], '/edit/{id}', 'editItem');
                Route::get('/delete/{id}', 'deleteItem');
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
                ->group(function () {
                    Route::get('/', 'index');
                    Route::match(['get', 'post'], '/edit/{id}', 'edit');
                    Route::match(['get', 'post'], '/create', 'create');
                    Route::get('/restatement', 'restatement');
                    Route::get('/delete/{id}', 'delete');
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
                ->group(function () {
                    Route::get('/loads', 'index');
                    Route::post('/loads/create', 'create');
                    Route::match(['get', 'post'], '/loads/edit/{id}', 'edit');
                    Route::get('/loads/delete/{id}', 'delete');
                    Route::get('/loads/restatement', 'restatement');
                    Route::get('/loads/{id}', 'load')->name('admin.loads.load');
                    ;

                    Route::match(['get', 'post'], '/downs/edit/{id}', 'editDown');
                    Route::match(['get', 'post'], '/downs/delete/{id}', 'deleteDown');
                    Route::get('/downs/new', 'new');
                    Route::get('/downs/publish/{id}', 'publish');
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
                ->group(function () {
                    Route::get('/{type?}', 'index')->where('type', 'offer|issue');
                    Route::get('/{id}', 'view');
                    Route::match(['get', 'post'], '/edit/{id}', 'edit');
                    Route::match(['get', 'post'], '/reply/{id}', 'reply');
                    Route::get('/restatement', 'restatement');
                    Route::match(['get', 'post'], '/delete', 'delete');
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
