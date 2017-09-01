<?php

namespace App\Controllers;

class HomeController extends BaseController
{
    /**
     * Главная страница
     */
    public function index()
    {
        return view('index');
    }

    /**
     * Защитная картинка
     */
    public function captcha()
    {
        header('Content-type: image/jpeg');
        $phrase = new Gregwar\Captcha\PhraseBuilder;
        $phrase = $phrase->build(setting('captcha_maxlength'), setting('captcha_symbols'));

        $builder = new Gregwar\Captcha\CaptchaBuilder($phrase);
        $builder->setBackgroundColor(mt_rand(200,255), mt_rand(200,255), mt_rand(200,255));
        $builder->setMaxOffset(setting('captcha_offset'));
        $builder->setMaxAngle(setting('captcha_angle'));
        $builder->setDistortion(setting('captcha_distortion'));
        $builder->setInterpolation(setting('captcha_interpolation'));
        $builder->build()->output();

        $_SESSION['protect'] = $builder->getPhrase();
    }
}
