<?php

class HomeController extends BaseController
{
    /**
     * Главная страница
     */
    public function index()
    {
        App::view('index');
    }
}
