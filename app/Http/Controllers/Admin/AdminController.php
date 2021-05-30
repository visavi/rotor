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
     * @param PhinxApplication $app
     *
     * @return View
     */
    public function upgrade(PhinxApplication $app): View
    {
        $wrap = new TextWrapper($app);

        $app->setName('Rotor by Vantuz - https://visavi.net');
        $app->setVersion(ROTOR_VERSION);

        $wrap->setOption('configuration', base_path('app/migration.php'));
        $wrap->setOption('parser', 'php');
        $wrap->setOption('environment', 'default');

        return view('admin/upgrade', compact('wrap'));
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
        $gdInfo  = null;

        if (function_exists('ini_get_all')) {
            $iniInfo = ini_get_all();
        }

        if ($gdInfo = gd_info()) {
            $gdInfo = parseVersion($gdInfo['GD Version']);
        }

        return view('admin/phpinfo', compact('iniInfo', 'gdInfo'));
    }
}
