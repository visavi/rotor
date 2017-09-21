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
            'user_id'    => getUserId(),
            'request'    => server('REQUEST_URI'),
            'referer'    => server('HTTP_REFERER'),
            'ip'         => getClientIp(),
            'brow'       => getUserAgent(),
            'created_at' => SITETIME,
        ]);

    }

    /**
     * Главная страница
     */
    public function index()
    {
        $isOwner = isAdmin([User::OWNER]);
        $isAdmin = isAdmin(User::ADMIN_GROUP);
        $isModer = isAdmin(User::MODER_GROUP);

        return view('admin/index', compact('isOwner', 'isAdmin', 'isModer'));
    }
}
