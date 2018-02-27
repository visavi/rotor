<?php

namespace App\Controllers;

use App\Classes\Request;
use App\Models\Ban;
use Gregwar\Captcha\PhraseBuilder;
use Gregwar\Captcha\CaptchaBuilder;

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
     * Закрытие сайта
     */
    public function closed()
    {
        if (setting('closedsite') != 2) {
            redirect('/');
        }

        return view('pages/closed');
    }

    /**
     * Бан по IP
     */
    public function banip()
    {
        header($_SERVER["SERVER_PROTOCOL"].' 403 Forbidden');

        $ban = Ban::query()
            ->where('ip', getIp())
            ->whereNull('user_id')
            ->first();

        if (Request::isMethod('post')) {

            if ($ban && captchaVerify()) {

                $ban->delete();

                ipBan(true);

                setFlash('success', 'IP успешно разбанен!');
                redirect('/');
            }
        }

        return view('pages/banip', compact('ban'));
    }

    /**
     * Защитная картинка
     */
    public function captcha()
    {
        header('Content-type: image/jpeg');
        $phrase = new PhraseBuilder;
        $phrase = $phrase->build(setting('captcha_maxlength'), setting('captcha_symbols'));

        $builder = new CaptchaBuilder($phrase);
        $builder->setBackgroundColor(random_int(200,255), random_int(200,255), random_int(200,255));
        $builder->setMaxOffset(setting('captcha_offset'));
        $builder->setMaxAngle(setting('captcha_angle'));
        $builder->setDistortion(setting('captcha_distortion'));
        $builder->setInterpolation(setting('captcha_interpolation'));
        $builder->build()->output();

        $_SESSION['protect'] = $builder->getPhrase();
    }
}
