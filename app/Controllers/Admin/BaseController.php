<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

Class AdminController extends BaseController
{
    public function __construct()
    {
        if (! is_admin()) {
            abort('403', 'Доступ запрещен!');
        }
    }
}
