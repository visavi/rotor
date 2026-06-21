<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Services\GithubService;
use App\Services\MigrationService;
use App\Services\UpgradeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;
use Throwable;

class UpgradeController extends AdminController
{
    public function __construct(
        private readonly MigrationService $migrations,
        private readonly UpgradeService $upgrade,
    ) {
    }

    /**
     * Страница обновлений
     */
    public function index(GithubService $githubService): View
    {
        $pendingMigrations = $this->migrations->getPendingMigrations($this->migrationPaths());
        $newReleases = $this->upgrade->getNewReleases($githubService);

        // Резолвим архив, который реально скачается (upgrade/full), чтобы кнопка
        // показывала честный размер и тип, а не первый попавшийся asset
        foreach ($newReleases as &$release) {
            $split = $this->upgrade->splitAssets($release['assets'] ?? []);
            $asset = $this->upgrade->selectAsset($release['assets'] ?? [], $release['tag_name'] ?? '');
            $release['asset'] = $asset;
            $release['is_upgrade'] = $asset && str_ends_with($asset['name'] ?? '', '_upgrade.zip');
            // Полный архив как запасной вариант — только когда по умолчанию выбран upgrade
            $release['full_asset'] = $release['is_upgrade'] ? $split['full'] : null;
        }
        unset($release);

        // Переустановка текущей версии (скачать тот же релиз заново) — доступна,
        // пока текущий релиз ещё в списке последних релизов GitHub
        $reinstall = $this->reinstallInfo($githubService);

        $permErrors = ($newReleases || $reinstall) ? $this->upgrade->checkPermissions() : [];

        return view('admin/upgrade/index', compact(
            'pendingMigrations',
            'newReleases',
            'reinstall',
            'permErrors',
        ));
    }

    /**
     * Собирает данные для переустановки текущей версии
     */
    private function reinstallInfo(GithubService $githubService): ?array
    {
        $currentTag = 'v' . ROTOR_VERSION;

        foreach ($githubService->getLatestReleases() as $release) {
            if (($release['tag_name'] ?? null) !== $currentTag || empty($release['assets'])) {
                continue;
            }

            $split = $this->upgrade->splitAssets($release['assets']);
            $asset = $this->upgrade->selectAsset($release['assets'], $currentTag);

            if (! $asset) {
                return null;
            }

            $isUpgrade = str_ends_with($asset['name'] ?? '', '_upgrade.zip');

            return [
                'tag'        => $currentTag,
                'asset'      => $asset,
                'full_asset' => $isUpgrade ? $split['full'] : null,
            ];
        }

        return null;
    }

    /**
     * Скачивает и распаковывает архив релиза
     */
    public function download(Request $request, GithubService $githubService): JsonResponse
    {
        $tag = (string) $request->input('tag');
        $full = (bool) $request->input('full');
        $asset = $tag ? $this->upgrade->findAsset($githubService, $tag, $full) : null;

        if (! $asset) {
            return response()->json(['error' => __('admin.upgrade.invalid_params')], 422);
        }

        ini_set('max_execution_time', 0);
        set_time_limit(0);

        try {
            $this->upgrade->downloadRelease($tag, $asset['browser_download_url']);
        } catch (Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Применяет скачанное обновление
     */
    public function apply(Request $request): JsonResponse
    {
        $tag = (string) $request->input('tag');

        if (! $tag) {
            return response()->json(['error' => __('admin.upgrade.invalid_params')], 422);
        }

        // Копирование тысяч файлов на shared-хостинге легко превышает max_execution_time,
        // а фатальный таймаут не выполнит finally и оставит сайт в maintenance mode
        ini_set('max_execution_time', 0);
        set_time_limit(0);
        ignore_user_abort(true);

        Artisan::call('down');

        try {
            $errors = $this->upgrade->applyUpdate($tag);
            $this->upgrade->cleanup($tag);
        } catch (Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        } finally {
            Artisan::call('up');
        }

        Artisan::call('cache:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        // Обновление затирает файлы ядра — восстанавливаем симлинки
        // и опубликованные файлы активных модулей
        Artisan::call('module:sync');

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response()->json([
            'success' => true,
            'errors'  => $errors,
        ]);
    }

    /**
     * Выполняет одну следующую миграцию
     */
    public function migrateNext(): JsonResponse
    {
        ini_set('max_execution_time', 0);
        set_time_limit(0);

        $pending = $this->migrations->getPendingMigrations($this->migrationPaths());

        if (empty($pending)) {
            Artisan::call('cache:clear');
            Artisan::call('route:clear');
            Artisan::call('config:clear');

            return response()->json(['done' => true, 'migration' => null, 'output' => '']);
        }

        $name = $pending[0];
        $file = $this->migrations->findFile($name);

        if (! $file) {
            return response()->json(['error' => "Файл миграции не найден: {$name}"], 500);
        }

        $remaining = count($pending) - 1;

        try {
            $output = $this->migrations->runOne($file);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => "Ошибка миграции {$name}: " . $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'done'      => $remaining === 0,
            'migration' => $name,
            'output'    => $output,
            'remaining' => $remaining,
        ]);
    }

    /**
     * Пути к папкам с миграциями
     */
    private function migrationPaths(): array
    {
        return [database_path('migrations'), database_path('upgrades')];
    }
}
