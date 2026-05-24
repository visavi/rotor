<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\AdminAdvertController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdvertController as AdminUserAdvertController;
use App\Http\Controllers\Admin\AntimatController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\BanController as AdminBanController;
use App\Http\Controllers\Admin\BanhistController;
use App\Http\Controllers\Admin\BanlistController;
use App\Http\Controllers\Admin\BlacklistController;
use App\Http\Controllers\Admin\CacheController;
use App\Http\Controllers\Admin\ChatController;
use App\Http\Controllers\Admin\CheckerController;
use App\Http\Controllers\Admin\DeliveryController;
use App\Http\Controllers\Admin\DelUserController;
use App\Http\Controllers\Admin\ErrorController;
use App\Http\Controllers\Admin\FileController as AdminFileController;
use App\Http\Controllers\Admin\InvitationController as AdminInvitationController;
use App\Http\Controllers\Admin\IpBanController;
use App\Http\Controllers\Admin\LogController;
use App\Http\Controllers\Admin\ModuleController;
use App\Http\Controllers\Admin\ModuleRegistryController;
use App\Http\Controllers\Admin\NoticeController;
use App\Http\Controllers\Admin\PaidAdvertController;
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
                    Route::get('/marketplace', 'marketplace')->name('marketplace');
                    Route::get('/upload', 'upload')->name('upload');
                    Route::post('/upload', 'uploadZip')->name('upload.zip');
                    Route::post('/download', 'download')->name('download');
                    Route::get('/install', 'install')->name('install');
                    Route::get('/uninstall', 'uninstall')->name('uninstall');
                    Route::get('/delete', 'deleteFiles')->name('delete');
                });

            /* Реестры модулей */
            Route::controller(ModuleRegistryController::class)
                ->prefix('registries')
                ->name('registries.')
                ->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::post('/', 'store')->name('store');
                    Route::post('/{id}/refresh', 'refresh')->name('refresh');
                    Route::post('/{id}/toggle', 'toggle')->name('toggle');
                    Route::delete('/{id}', 'destroy')->name('destroy');
                });
        });
    });
