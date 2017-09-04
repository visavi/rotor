<?php

namespace App\Controllers;

use App\Classes\Request;

Class BaseController
{
    public function __construct()
    {
        // Сайт закрыт для всех
        if (setting('closedsite') == 2 && ! isAdmin() && ! Request::is('closed', 'login')) {
            redirect('/closed');
        }

        // Сайт закрыт для гостей
        if (setting('closedsite') == 1 && ! isUser() && ! Request::is('register', 'login', 'recovery', 'captcha')) {
            setFlash('danger', 'Для входа на сайт необходимо авторизоваться!');
            redirect('/login');
        }
    }
}
