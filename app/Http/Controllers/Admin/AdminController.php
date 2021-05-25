<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Log;
use App\Models\User;
use Illuminate\View\View;
use Phinx\Console\PhinxApplication;
use Phinx\Wrapper\TextWrapper;

class AdminController extends Controller
{
    /**
     * AdminController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        if (! isAdmin()) {
            abort(403, __('errors.forbidden'));
        }

        Log::query()->create([
            'user_id'    => getUser('id'),
            'request'    => request()->getRequestUri(),
            'referer'    => server('HTTP_REFERER'),
            'ip'         => getIp(),
            'brow'       => getBrowser(),
            'created_at' => SITETIME,
        ]);
    }

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
        $app->setVersion(VERSION);

        $wrap->setOption('configuration', BASEDIR.'/app/migration.php');
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
