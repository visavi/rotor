<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Services\GithubService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class UpgradeController extends AdminController
{
    /**
     * Главная страница
     */
    public function index(GithubService $githubService): View
    {
        $latestRelease = $githubService->getLatestRelease();
        $latestVersion = $githubService->getLatestVersionClean();

        $hasNewVersion = version_compare(ROTOR_VERSION, $latestVersion, '<');
        $pendingMigrations = $this->getPendingMigrations();

        return view('admin/upgrade/index', compact('hasNewVersion', 'latestRelease', 'pendingMigrations'));
    }

    /**
     * Выполняет миграции
     */
    public function migrate(): RedirectResponse
    {
        Artisan::call('migrate', ['--force' => true]);
        $output = Artisan::output();

        return redirect()->route('admin.upgrade.index')->with('migrateOutput', $output);
    }

    /**
     * Получает список применяемых миграций
     */
    private function getPendingMigrations(): array
    {
        $migrator = app('migrator');

        $files = $migrator->getMigrationFiles(array_merge(
            [database_path('migrations')],
            [database_path('upgrades')]
        ));

        $ran = $migrator->getRepository()->getRan();

        return array_values(array_diff(array_keys($files), $ran));
    }
}
