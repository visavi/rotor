<?php

namespace App\Controllers\Admin;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\Banhist;
use App\Models\User;

class BanhistController extends AdminController
{
    public function __construct()
    {
        parent::__construct();

        if (! isAdmin(User::MODER)) {
            abort('403', 'Доступ запрещен!');
        }
    }

    /**
     * Главная страница
     */
    public function index()
    {
        $total = Banhist::query()->count();
        $page = paginate(setting('listbanhist'), $total);

        $records = Banhist::query()
            ->orderBy('created_at', 'desc')
            ->limit($page['limit'])
            ->offset($page['offset'])
            ->with('user', 'sendUser')
            ->get();

        return view('admin/banhist/index', compact('records', 'page'));
    }
}
