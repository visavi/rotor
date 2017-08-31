<?php

namespace App\Controllers\Admin;

Class BaseController
{
    public function __construct()
    {
        if (! is_admin()) {
            abort('403', 'Доступ запрещен!');
        }
    }
}
