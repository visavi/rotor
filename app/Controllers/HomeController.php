<?php

namespace App\Controllers;

use App\Models\Ban;
use Gregwar\Captcha\PhraseBuilder;
use Gregwar\Captcha\CaptchaBuilder;
use Illuminate\Http\Request;

class HomeController extends BaseController
{
    /**
     * Главная страница
     *
     * @return string
     */
    public function index(): string
    {
        return view('index');
    }

    /**
     * Закрытие сайта
     *
     * @return string
     */
    public function closed(): string
    {
        if (setting('closedsite') !== 2) {
            redirect('/');
        }

        return view('pages/closed');
    }

    /**
     * Поиск по сайту
     *
     * @return string
     */
    public function search(): string
    {
        return view('search/index');
    }
}
