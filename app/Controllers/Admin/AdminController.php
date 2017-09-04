<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Admlog;

Class AdminController extends BaseController
{
    public function __construct()
    {
        parent::__construct();

        if (! isAdmin()) {
            abort('403', 'Доступ запрещен!');
        }

        Admlog::where('created_at', '<', SITETIME - 3600 * 24 * setting('maxlogdat'))
            ->delete();

        Admlog::create([
            'user_id'    => getUserId(),
            'request'    => server('REQUEST_URI'),
            'referer'    => server('HTTP_REFERER'),
            'ip'         => getClientIp(),
            'brow'       => getUserAgent(),
            'created_at' => SITETIME,
        ]);

    }
}
