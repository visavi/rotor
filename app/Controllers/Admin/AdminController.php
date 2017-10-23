<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Admlog;
use App\Models\User;

Class AdminController extends BaseController
{
    public function __construct()
    {
        parent::__construct();

        if (! isAdmin()) {
            abort('403', 'Доступ запрещен!');
        }

        Admlog::query()
            ->where('created_at', '<', SITETIME - 3600 * 24 * setting('maxlogdat'))
            ->delete();

        Admlog::query()->create([
            'user_id'    => getUser('id'),
            'request'    => server('REQUEST_URI'),
            'referer'    => server('HTTP_REFERER'),
            'ip'         => getIp(),
            'brow'       => getBrowser(),
            'created_at' => SITETIME,
        ]);

    }

    /**
     * Главная страница
     */
    public function index()
    {
        $existBoss = User::query()
            ->where('level', User::BOSS)
            ->count();

        return view('admin/index', compact('existBoss'));
    }
}
