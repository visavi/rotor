<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Services\GithubService;
use App\Services\MigrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class UpgradeController extends AdminController
{
    public function __construct(private readonly MigrationService $migrations)
    {
    }

    public function index(GithubService $githubService): View
    {
        $latestRelease = $githubService->getLatestRelease();
        $latestVersion = $githubService->getLatestVersionClean();

        $hasNewVersion = version_compare(ROTOR_VERSION, $latestVersion, '<');
        $pendingMigrations = $this->migrations->getPendingMigrations($this->paths());

        return view('admin/upgrade/index', compact('hasNewVersion', 'latestRelease', 'pendingMigrations'));
    }

    public function migrate(Request $request): JsonResponse|RedirectResponse
    {
        Artisan::call('migrate', ['--force' => true]);
        $output = Artisan::output();

        if ($request->ajax()) {
            return response()->json(['output' => $output]);
        }

        return redirect()->route('admin.upgrade.index')->with('migrateOutput', $output);
    }

    public function migrateNext(): JsonResponse
    {
        ini_set('max_execution_time', 0);
        set_time_limit(0);

        $pending = $this->migrations->getPendingMigrations($this->paths());

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

    private function paths(): array
    {
        return [database_path('migrations'), database_path('upgrades')];
    }
}
