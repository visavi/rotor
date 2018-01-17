<?php

namespace App\Controllers\Admin;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\Blog;
use App\Models\Down;
use App\Models\Guest;
use App\Models\Inbox;
use App\Models\News;
use App\Models\Photo;
use App\Models\Post;
use App\Models\Spam;
use App\Models\User;
use App\Models\Wall;
use Illuminate\Database\Capsule\Manager as DB;

class AdminlistController extends AdminController
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
        $users = User::query()
            ->whereIn('level', User::ADMIN_GROUPS)
            ->orderByRaw("field(level, '".implode(',', User::ADMIN_GROUPS)."')")
            ->get();

        return view('admin/adminlist/index', compact('users'));
    }
}
