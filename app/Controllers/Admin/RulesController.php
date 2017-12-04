<?php

namespace App\Controllers\Admin;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\User;

class RulesController extends AdminController
{
    public function __construct()
    {
        parent::__construct();

        if (! isAdmin(User::ADMIN)) {
            abort(403, 'Доступ запрещен!');
        }
    }


    /**
     * Главная страница
     */
    public function index()
    {
        echo 'Правила';
    }
}
