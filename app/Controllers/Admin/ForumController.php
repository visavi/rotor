<?php

namespace App\Controllers\Admin;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\Chat;
use App\Models\User;

class ForumController extends AdminController
{
    /**
     * Главная страница
     */
    public function index()
    {


        return view('admin/forum/index');
    }
}
