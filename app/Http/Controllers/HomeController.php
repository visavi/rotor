<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Ban;
use App\Services\CaptchaService;
use App\Services\FeedService;
use App\Services\SearchService;
use App\Support\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

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
     * Лента (AJAX)
     */
    public function feed(Request $request): string|RedirectResponse
    {
        if (! $request->ajax()) {
            return redirect(url('/') . ($request->has('page') ? '?page=' . $request->input('page') : ''));
        }

        return (string) (new FeedService())->getFeed();
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
        $query = SearchService::clean((string) $request->input('query', $request->input('q', '')));
        $searchQuery = SearchService::terms($query);

        $types = SearchService::types();
        $sort = SearchService::sort(check($request->input('sort', 'relevance')));
        $type = SearchService::type(check($request->input('type')));

        if ($query) {
            $validator->length($searchQuery, SearchService::MIN_LENGTH, SearchService::MAX_LENGTH, ['find' => __('main.request_length')]);

            if ($validator->isValid()) {
                $posts = SearchService::paginate($searchQuery, $type, $sort, 10)
                    ->appends(compact('query', 'sort', 'type'));
            } else {
                // GET-страница рендерится сразу, редирект тут неуместен —
                // flash() пишет в сессию немедленно и корректно истекает
                session()->flash('danger', $validator->getErrors());
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
            clearCache('ipBan');

            return redirect('/');
        }

        if (
            ! $ban->user_id
            && $ban->created_at->lt(now()->subMinute())
            && $request->isMethod('post')
            && captchaVerify()
        ) {
            $ban->delete();
            clearCache('ipBan');

            return redirect('/')
                ->with('success', __('pages.ip_success_unbanned'));
        }

        return response()->view('pages/ipban', compact('ban'), 429);
    }

    /**
     * Защитная картинка
     */
    public function captcha(Request $request, CaptchaService $service): Response
    {
        $captcha = $service->build();

        $request->session()->put('protect', $captcha['phrase']);

        return response($captcha['image'])
            ->header('Content-Type', $captcha['mime'])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT');
    }

    /**
     * Быстрое изменение языка
     */
    public function language(string $lang, Request $request): JsonResponse
    {
        $languages = getAvailableLanguages();

        if (preg_match('/^[a-z]+$/', $lang) && in_array($lang, $languages, true)) {
            if ($user = $request->user()) {
                $user->update([
                    'language' => $lang,
                ]);
            } else {
                $request->session()->put('language', $lang);
            }
        }

        return response()->json(['success' => true]);
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
