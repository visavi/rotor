<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class AdminController extends Controller
{
    /**
     * Главная страница
     *
     * @return View
     */
    public function main(): View
    {
        $existBoss = User::query()
            ->where('level', User::BOSS)
            ->count();

        return view('admin/index', compact('existBoss'));
    }

    /**
     * Проверка обновлений
     *
     * @return View
     */
    public function upgrade(): View
    {
        Artisan::call('migrate', ['--force' => true]);
        $output = Artisan::output();

        return view('admin/upgrade', compact('output'));
    }

    /**
     * Просмотр информации о PHP
     *
     * @return View
     */
    public function phpinfo(): View
    {
        if (! isAdmin(User::ADMIN)) {
            abort(403, __('errors.forbidden'));
        }

        $iniInfo = null;

        if (function_exists('ini_get_all')) {
            $iniInfo = ini_get_all();
        }

        if ($gdInfo = gd_info()) {
            $gdInfo = parseVersion($gdInfo['GD Version']);
        }

        return view('admin/phpinfo', compact('iniInfo', 'gdInfo'));
    }
}
