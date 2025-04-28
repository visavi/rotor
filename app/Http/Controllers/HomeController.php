<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Classes\Validator;
use App\Models\Ban;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Search;
use Gregwar\Captcha\CaptchaBuilder;
use Gregwar\Captcha\PhraseBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Visavi\Captcha\CaptchaBuilder as AnimatedCaptchaBuilder;
use Visavi\Captcha\PhraseBuilder as AnimatedPhraseBuilder;

class HomeController extends Controller
{
    /**
     * Главная страница
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
     */
    public function search(Request $request, Validator $validator): View|RedirectResponse
    {
        $user = getUser();
        $posts = collect();
        $query = $request->input('query');

        if ($query) {
            $query = trim(preg_replace('/[^\p{L}\p{N}\s]/u', ' ', urldecode($query)));

            $validator->length($query, 3, 64, ['find' => __('main.request_length')]);

            if ($validator->isValid()) {
                $posts = Search::query()
                    ->selectRaw('*, MATCH(text) AGAINST("' . $query . '*" IN BOOLEAN MODE) AS relevance')
                    ->whereFullText('text', $query . '*', ['mode' => 'boolean'])
                    ->with('relate')
                    ->orderByDesc('relevance')
                    ->paginate(10)
                    ->appends(compact('query'))
                    ->loadMorph('relate', [
                        Post::class    => ['topic'],
                        Comment::class => ['relate'],
                    ]);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('search/index', compact('posts', 'user', 'query'));
    }

    /**
     * Бан по IP
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
            ! $ban->user_id
            && $ban->created_at < strtotime('-1 minute', SITETIME)
            && $request->isMethod('post')
            && captchaVerify()
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
     */
    public function captcha(Request $request): Response
    {
        if (setting('captcha_type') === 'animated') {
            $phrase = new AnimatedPhraseBuilder();
            $phrase = $phrase->getPhrase(setting('captcha_maxlength'), setting('captcha_symbols'));

            $captcha = new AnimatedCaptchaBuilder($phrase);
            $captcha = $captcha->render();
        } else {
            $phrase = new PhraseBuilder();
            $phrase = $phrase->build(setting('captcha_maxlength'), setting('captcha_symbols'));

            $captcha = new CaptchaBuilder($phrase);
            $captcha->setMaxOffset(setting('captcha_offset'))
                ->setMaxAngle(setting('captcha_angle'))
                ->setDistortion(setting('captcha_distortion'))
                ->setInterpolation(setting('captcha_interpolation'))
                ->build();

            $captcha = $captcha->get();
        }

        $request->session()->put('protect', $phrase);

        return response($captcha)
            ->header('Content-Type', setting('captcha_type') === 'animated' ? 'image/gif' : 'image/jpeg');
    }

    /**
     * Быстрое изменение языка
     */
    public function language(string $lang, Request $request): RedirectResponse
    {
        $return = $request->input('return');
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

    public function error403(): View
    {
        abort(403);
    }

    public function error404(): View
    {
        abort(404);
    }
}
