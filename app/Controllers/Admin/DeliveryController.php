<?php

namespace App\Controllers\Admin;

use App\Classes\Request;
use App\Models\User;

class DeliveryController extends AdminController
{
    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();

        if (! isAdmin(User::BOSS)) {
            abort(403, 'Доступ запрещен!');
        }
    }

    /**
     * Главная страница
     */
    public function index()
    {

        return view('admin/delivery/index');
    }
}
