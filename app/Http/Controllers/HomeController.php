<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Ban;
use Exception;
use Gregwar\Captcha\PhraseBuilder;
use Gregwar\Captcha\CaptchaBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Главная страница
     *
     * @return View
     */
    public function index(): View
    {
        return view('index');
    }

    /**
     * Закрытие сайта
     *
     * @return View
     */
    public function closed(): View
    {
        if (setting('closedsite') !== 2) {
            redirect('/');
        }

        header($_SERVER['SERVER_PROTOCOL'] . ' 503 Service Unavailable');

        return view('pages/closed');
    }

    /**
     * Поиск по сайту
     *
     * @return View
     */
    public function search(): View
    {
        return view('search/index');
    }

    /**
     * Бан по IP
     *
     * @param Request $request
     *
     * @return View
     * @throws Exception
     */
    public function ipban(Request $request): View
    {
        $ban = Ban::query()
            ->where('ip', getIp())
            ->first();

        if (! $ban) {
            ipBan(true);
            redirect('/');
        }

        if (! $ban->user_id
            && $ban->created_at < strtotime('-1 minute', SITETIME)
            && $request->isMethod('post')
            && captchaVerify()
        ) {
            $ban->delete();
            ipBan(true);

            setFlash('success', __('pages.ip_success_unbanned'));
            redirect('/');
        }

        header($_SERVER['SERVER_PROTOCOL'] . ' 429 Too Many Requests');

        return view('pages/ipban', compact('ban'));
    }

    /**
     * Защитная картинка
     *
     * @param Request $request
     *
     * @return void
     */
    public function captcha(Request $request): void
    {
        header('Content-type: image/jpeg');
        $phrase = new PhraseBuilder();
        $phrase = $phrase->build(setting('captcha_maxlength'), setting('captcha_symbols'));

        $builder = new CaptchaBuilder($phrase);
        $builder->setBackgroundColor(mt_rand(200, 255), mt_rand(200, 255), mt_rand(200, 255));
        $builder->setMaxOffset(setting('captcha_offset'));
        $builder->setMaxAngle(setting('captcha_angle'));
        $builder->setDistortion(setting('captcha_distortion'));
        $builder->setInterpolation(setting('captcha_interpolation'));
        $builder->build()->output();

        $request->session()->put('protect', $builder->getPhrase());
    }

    /**
     * Быстрое изменение языка
     *
     * @param string  $lang
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function language(string $lang, Request $request): RedirectResponse
    {
        $return    = $request->input('return');
        $languages = array_map('basename', glob(RESOURCES . '/lang/*', GLOB_ONLYDIR));

        if (preg_match('/^[a-z]+$/', $lang) && in_array($lang, $languages, true)) {
            if ($user = getUser()) {
                $user->update([
                    'language' => $lang,
                ]);
            } else {
                $request->session()->put('language', $lang);
            }
        }

        return redirect($return ?? '/');
    }
}
