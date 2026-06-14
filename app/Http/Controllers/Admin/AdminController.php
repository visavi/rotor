<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\GithubService;
use Illuminate\View\View;

class AdminController extends Controller
{
    /**
     * Главная страница
     */
    public function main(GithubService $github): View
    {
        $existBoss = User::query()
            ->where('level', User::BOSS)
            ->count();

        $hasNewVersion = version_compare(ROTOR_VERSION, $github->getLatestVersionClean(), '<');

        return view('admin/index', compact('existBoss', 'hasNewVersion'));
    }
}
