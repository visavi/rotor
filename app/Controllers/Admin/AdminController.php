<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

Class AdminController extends BaseController
{
    public function __construct()
    {
        parent::__construct();

        if (! isAdmin()) {
            abort('403', 'Доступ запрещен!');
        }
    }
}
