<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Ban;
use Gregwar\Captcha\PhraseBuilder;
use Gregwar\Captcha\CaptchaBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

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
     * @return Response
     */
    public function closed()
    {
        if (setting('closedsite') !== 2) {
            return redirect('/');
        }

        return response()->view('pages/closed', [], 503);
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
     * @return Response
     */
    public function ipban(Request $request): Response
    {
        $ban = Ban::query()
            ->where('ip', getIp())
            ->first();

        if (! $ban) {
            ipBan(true);

            return redirect('/');
        }

        if (
            ! $ban->user_id &&
            $ban->created_at < strtotime('-1 minute', SITETIME) &&
            $request->isMethod('post') &&
            captchaVerify()
        ) {
            $ban->delete();
            ipBan(true);

            setFlash('success', __('pages.ip_success_unbanned'));

            return redirect('/');
        }

        return response()->view('pages/ipban', compact('ban'), 429);
    }

    /**
     * Защитная картинка
     *
     * @param Request $request
     *
     * @return Response
     */
    public function captcha(Request $request): Response
    {
        $phrase = new PhraseBuilder();
        $phrase = $phrase->build(setting('captcha_maxlength'), setting('captcha_symbols'));

        $builder = new CaptchaBuilder($phrase);
        $builder->setMaxOffset(setting('captcha_offset'));
        $builder->setMaxAngle(setting('captcha_angle'));
        $builder->setDistortion(setting('captcha_distortion'));
        $builder->setInterpolation(setting('captcha_interpolation'));

        $request->session()->put('protect', $builder->getPhrase());

        return response($builder->build()->get())->header('Content-Type', 'image/jpeg');
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
        $languages = array_map('basename', glob(resource_path('lang/*'), GLOB_ONLYDIR));

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
