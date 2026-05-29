<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;

class AdminController extends Controller
{
    /**
     * Главная страница
     */
    public function main(): View
    {
        $existBoss = User::query()
            ->where('level', User::BOSS)
            ->count();

        return view('admin/index', compact('existBoss'));
    }
}
