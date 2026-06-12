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
        $latestRelease = $githubService->getLatestRelease();
        $latestVersion = $githubService->getLatestVersionClean();

        $hasNewVersion = version_compare(ROTOR_VERSION, $latestVersion, '<');
        $pendingMigrations = $this->migrations->getPendingMigrations($this->migrationPaths());
        $newReleases = $this->upgrade->getNewReleases($githubService);
        $permErrors = $newReleases ? $this->upgrade->checkPermissions() : [];

        return view('admin/upgrade/index', compact(
            'hasNewVersion',
            'latestRelease',
            'pendingMigrations',
            'newReleases',
            'permErrors',
        ));
    }

    /**
     * Скачивает и распаковывает архив релиза
     */
    public function download(Request $request, GithubService $githubService): JsonResponse
    {
        $tag = (string) $request->input('tag');
        $asset = $tag ? $this->upgrade->findAsset($githubService, $tag) : null;

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

        return response()->json([
            'done'      => $remaining === 0,
            'migration' => $name,
            'output'    => $this->migrations->runOne($file),
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
