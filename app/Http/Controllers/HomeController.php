<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Classes\Validator;
use App\Models\Article;
use App\Models\Ban;
use App\Models\Comment;
use App\Models\Down;
use App\Models\Post;
use App\Models\Search;
use App\Models\Topic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Mobicms\Captcha\Image as MobicmsCaptcha;
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
     */
    public function closed(): Response
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
        $posts = paginate([], 10);
        $query = (string) $request->input('query', $request->input('q', ''));
        $query = trim(preg_replace('/[^\p{L}\p{N}\s]/u', ' ', urldecode($query)));

        $types = Search::getRelateTypes();

        $sort = check($request->input('sort', 'relevance'));
        $order = match ($sort) {
            'date'     => ['created_at desc'],
            'date_asc' => ['created_at asc'],
            default    => ['match(text) against(? in boolean mode) desc', [$query . '*']],
        };

        $type = check($request->input('type'));
        $type = isset($types[$type]) ? $type : null;

        if ($query) {
            $validator->length($query, 3, 64, ['find' => __('main.request_length')]);

            if ($validator->isValid()) {
                $posts = Search::query()
                    ->when($type, function ($query) use ($type) {
                        $query->where('relate_type', $type);
                    })
                    ->whereFullText('text', $query . '*', ['mode' => 'boolean'])
                    ->with('relate')
                    ->orderByRaw(...$order)
                    ->paginate(10)
                    ->appends(compact('query', 'sort', 'type'))
                    ->loadMorph('relate', [
                        Article::class => ['category'],
                        Comment::class => ['relate'],
                        Down::class    => ['category'],
                        Post::class    => ['topic'],
                        Topic::class   => ['forum', 'lastPost'],
                    ]);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('search/index', compact('posts', 'types', 'type', 'sort', 'query'));
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
            $captcha = new MobicmsCaptcha();
            $captcha->imageWidth = 180;
            $captcha->imageHeight = 50;
            $captcha->lengthMax = setting('captcha_maxlength');
            $captcha->characterSet = (string) setting('captcha_symbols');
            $phrase = $captcha->getCode();
            $captcha = $captcha->build();
        }

        $request->session()->put('protect', $phrase);

        return response($captcha)
            ->header('Content-Type', setting('captcha_type') === 'animated' ? 'image/gif' : 'image/png');
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
