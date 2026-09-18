<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\User;
use App\Services\DashboardService;
use App\Services\GithubService;
use App\Services\MailService;
use App\Services\MigrationService;
use App\Services\QueueService;
use App\Services\ScheduleService;
use Illuminate\View\View;

class AdminController extends Controller
{
    /**
     * Главная страница
     */
    public function main(
        GithubService $github,
        MigrationService $migrations,
        DashboardService $dashboard,
        ScheduleService $schedule,
        MailService $mail,
        QueueService $queue,
    ): View {
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

        // Планировщик чинит только владелец — остальным о кроне знать незачем.
        // Шаблону нужен и факт остановки, и время последнего запуска, поэтому
        // сервис отдаётся целиком, а не парой переменных
        $scheduleStalled = isAdmin(User::BOSS) && $schedule->isStalled() ? $schedule : null;

        // Почту настраивает владелец — остальным о поломке отправки знать незачем
        $mailFailure = isAdmin(User::BOSS) ? $mail->lastFailure() : null;

        // Очередь чинит владелец — остальным о ней знать незачем
        $queuePending = isAdmin(User::BOSS) && $queue->isStalled()
            ? $queue->pendingCount()
            : 0;

        return view('admin/index', compact(
            'existBoss',
            'hasNewVersion',
            'modulesUpdates',
            'pendingMigrations',
            'widgets',
            'scheduleStalled',
            'mailFailure',
            'queuePending',
        ));
    }
}
