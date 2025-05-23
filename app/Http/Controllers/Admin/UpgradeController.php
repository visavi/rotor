<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Services\GithubService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class UpgradeController extends AdminController
{
    /**
     * Главная страница
     */
    public function index(GithubService $githubService): View
    {
        Artisan::call('migrate', ['--force' => true]);
        $migrateOutput = Artisan::output();

        $latestRelease = $githubService->getLatestRelease();
        $latestVersion = $githubService->getLatestVersionClean();

        $hasNewVersion = version_compare(ROTOR_VERSION, $latestVersion, '<');

        return view('admin/upgrade/index', compact('migrateOutput', 'hasNewVersion', 'latestRelease'));
    }
}
