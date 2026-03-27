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
use Illuminate\Support\Facades\Route;

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
            ->name('chats.')
            ->group(function () {
                Route::match(['get', 'post'], '/', 'index')->name('index');
                Route::match(['get', 'post'], '/edit/{id}', 'edit')->name('edit');
                Route::post('/clear', 'clear')->name('clear');
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
                Route::post('/clear', 'clear')->name('clear');
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
                Route::delete('/{id}/delete', 'delete')->name('delete');
                Route::post('/restatement', 'restatement')->name('restatement');
            });

        /* Темы */
        Route::controller(AdminForumController::class)
            ->prefix('topics')
            ->name('topics.')
            ->group(function () {
                Route::get('/{id}', 'topic')->name('topic');
                Route::match(['get', 'post'], '/{id}/edit', 'editTopic')->name('edit');
                Route::match(['get', 'post'], '/{id}/move', 'moveTopic')->name('move');
                Route::post('/{id}/action', 'actionTopic')->name('action');
                Route::delete('/{id}/delete', 'deleteTopic')->name('delete');
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
                Route::delete('/{id}/delete', 'delete')->name('delete');
                Route::post('/restatement', 'restatement')->name('restatement');
            });

        /* Блоги */
        Route::controller(AdminArticleController::class)
            ->prefix('blogs')
            ->name('blogs.')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/{id}', 'blog')->name('blog');
                Route::post('/create', 'create')->name('create');
                Route::match(['get', 'post'], '/{id}/edit', 'edit')->name('edit');
                Route::delete('/{id}/delete', 'delete')->name('delete');
                Route::post('/restatement', 'restatement')->name('restatement');
            });

        /* Статьи */
        Route::controller(AdminArticleController::class)
            ->prefix('articles')
            ->name('articles.')
            ->group(function () {
                Route::match(['get', 'post'], '/{id}/edit', 'editArticle')->name('edit');
                Route::delete('/{id}/delete', 'deleteArticle')->name('delete');
                Route::post('/{id}/publish', 'publish')->name('publish');
                Route::get('/new', 'new')->name('new');
            });

        /* Доска объявлений */
        Route::controller(AdminBoardController::class)
            ->prefix('boards')
            ->name('boards.')
            ->group(function () {
                Route::get('/{id?}', 'index')->name('index');
                Route::get('/categories', 'categories')->name('categories');
                Route::post('/create', 'create')->name('create');
                Route::match(['get', 'post'], '/{id}/edit', 'edit')->name('edit');
                Route::delete('/{id}/delete', 'delete')->name('delete');
                Route::post('/restatement', 'restatement')->name('restatement');
            });

        /* Объявления */
        Route::controller(AdminBoardController::class)
            ->prefix('items')
            ->name('items.')
            ->group(function () {
                Route::match(['get', 'post'], '/{id}/edit', 'editItem')->name('edit');
                Route::delete('/{id}/delete', 'deleteItem')->name('delete');
            });

        /* Админская реклама */
        Route::controller(AdminAdvertController::class)
            ->prefix('admin-adverts')
            ->group(function () {
                Route::match(['get', 'post'], '/', 'index');
                Route::delete('/delete', 'delete');
            });

        /* Пользовательская реклама */
        Route::get('/adverts', [AdminUserAdvertController::class, 'index']);

        /* Модер */
        Route::middleware('check.admin:moder')->group(function () {
            /* Жалобы */
            Route::controller(SpamController::class)
                ->prefix('spam')
                ->name('spam.')
                ->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::post('/delete', 'delete')->name('delete');
                });

            /* Бан / разбан */
            Route::controller(AdminBanController::class)
                ->prefix('bans')
                ->group(function () {
                    Route::get('/', 'index');
                    Route::match(['get', 'post'], '/edit', 'edit');
                    Route::match(['get', 'post'], '/change', 'change');
                    Route::post('/unban', 'unban');
                });

            /* Забаненные */
            Route::get('/banlists', [BanlistController::class, 'index']);

            /* Ожидающие */
            Route::match(['get', 'post'], '/reglists', [ReglistController::class, 'index']);

            /* Голосования */
            Route::controller(AdminVoteController::class)
                ->prefix('votes')
                ->name('votes.')
                ->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('/history', 'history')->name('history');
                    Route::match(['get', 'post'], '/{id}/edit', 'edit')->name('edit');
                    Route::post('/close/{id}', 'close')->name('close');
                    Route::delete('/delete/{id}', 'delete')->name('delete');
                    Route::post('/restatement', 'restatement')->name('restatement');
                });

            /* Антимат */
            Route::controller(AntimatController::class)
                ->prefix('antimat')
                ->name('antimat.')
                ->group(function () {
                    Route::match(['get', 'post'], '/', 'index')->name('index');
                    Route::delete('/delete', 'delete')->name('delete');
                    Route::post('/clear', 'clear')->name('clear');
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
                    Route::delete('/{id}/delete', 'delete')->name('delete');
                    Route::post('/restatement', 'restatement')->name('restatement');
                });

            /* IP-бан */
            Route::controller(IpBanController::class)
                ->prefix('ipbans')
                ->name('ipbans.')
                ->group(function () {
                    Route::match(['get', 'post'], '/', 'index')->name('index');
                    Route::post('/delete', 'delete')->name('delete');
                    Route::post('/clear', 'clear')->name('clear');
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
                    Route::delete('/{id}/delete', 'delete')->name('delete');
                    Route::get('/{id}', 'load')->name('load');
                    Route::post('/restatement', 'restatement')->name('restatement');
                });

            Route::controller(AdminLoadController::class)
                ->prefix('downs')
                ->name('downs.')
                ->group(function () {
                    Route::match(['get', 'post'], '/{id}/edit', 'editDown')->name('edit');
                    Route::get('/new', 'new')->name('new');
                    Route::post('/{id}/publish', 'publish')->name('publish');
                    Route::delete('/delete/{id}', 'deleteDown')->name('delete');
                });

            /* Ошибки */
            Route::controller(ErrorController::class)
                ->prefix('errors')
                ->name('errors.')
                ->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::post('/clear', 'clear')->name('clear');
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
                    Route::match(['get', 'post'], '/delete', 'delete')->name('delete');
                    Route::post('/restatement', 'restatement')->name('restatement');
                });

            /* Стикеры */
            Route::controller(StickerController::class)
                ->prefix('stickers')
                ->group(function () {
                    Route::get('/', 'index');
                    Route::get('/{id}', 'category');
                    Route::post('/create', 'create');
                    Route::match(['get', 'post'], '/edit/{id}', 'edit');
                    Route::delete('/delete/{id}', 'delete');
                    Route::match(['get', 'post'], '/sticker/create', 'createSticker');
                    Route::match(['get', 'post'], '/sticker/edit/{id}', 'editSticker');
                    Route::delete('/sticker/delete/{id}', 'deleteSticker');
                });

            /* Статусы */
            Route::controller(StatusController::class)
                ->prefix('status')
                ->group(function () {
                    Route::get('/', 'index');
                    Route::match(['get', 'post'], '/create', 'create');
                    Route::match(['get', 'post'], '/edit', 'edit');
                    Route::delete('/delete', 'delete');
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
                    Route::delete('/delete', 'delete');
                });

            /* Сканирование */
            Route::controller(CheckerController::class)
                ->prefix('checkers')
                ->name('checkers.')
                ->group(function () {
                    Route::match(['get', 'post'], '/', 'index')->name('index');
                    Route::post('/scan', 'scan')->name('scan');
                });

            /* Приват рассылка */
            Route::match(['get', 'post'], '/delivery', [DeliveryController::class, 'index']);

            /* Логи */
            Route::controller(LogController::class)
                ->prefix('logs')
                ->name('logs.')
                ->group(function () {
                    Route::get('/', 'index')->name('index')->withoutMiddleware('admin.logger');
                    Route::post('/clear', 'clear')->name('clear');
                });

            /* Шаблоны писем */
            Route::controller(NoticeController::class)
                ->prefix('notices')
                ->group(function () {
                    Route::get('/', 'index');
                    Route::match(['get', 'post'], '/create', 'create');
                    Route::match(['get', 'post'], '/edit/{id}', 'edit');
                    Route::delete('/delete/{id}', 'delete');
                });

            /* Редактор */
            Route::controller(AdminFileController::class)
                ->prefix('files')
                ->group(function () {
                    Route::get('/', 'index');
                    Route::match(['get', 'post'], '/edit', 'edit');
                    Route::match(['get', 'post'], '/create', 'create');
                    Route::delete('/delete', 'delete');
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
                    Route::delete('/delete/{id}', 'delete');
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
                ->name('search.')
                ->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::post('/import', 'import')->name('import');
                });

            /* Модули */
            Route::controller(ModuleController::class)
                ->prefix('modules')
                ->name('modules.')
                ->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('/module', 'module')->name('module');
                    Route::get('/install', 'install')->name('install');
                    Route::get('/uninstall', 'uninstall')->name('uninstall');
                });
        });
    });
