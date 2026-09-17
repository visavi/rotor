<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\User;
use App\Services\DashboardService;
use App\Services\GithubService;
use App\Services\MigrationService;
use Illuminate\View\View;

class AdminController extends Controller
{
    /**
     * Главная страница
     */
    public function main(GithubService $github, MigrationService $migrations, DashboardService $dashboard): View
    {
        $existBoss = User::query()
            ->where('level', User::BOSS)
            ->count();

        $hasNewVersion = version_compare(ROTOR_VERSION, $github->getLatestVersionClean(), '<');
        $modulesUpdates = isAdmin(User::BOSS) ? Module::updatesCount() : 0;

        // Миграции накатывает только владелец, остальным о них знать незачем
        $pendingMigrations = isAdmin(User::BOSS)
            ? count($migrations->getPendingMigrations($migrations->paths()))
            : 0;

        $widgets = $dashboard->widgets();

        return view('admin/index', compact('existBoss', 'hasNewVersion', 'modulesUpdates', 'pendingMigrations', 'widgets'));
    }
}
