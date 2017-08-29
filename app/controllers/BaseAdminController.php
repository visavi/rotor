<?php

Class BaseAdminController
{
    public function __construct()
    {
        if (! is_admin()) {
            App::abort('403', 'Доступ запрещен!');
        }
    }
}
