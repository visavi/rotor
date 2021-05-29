<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Rule;
use App\Models\Sticker;
use App\Models\StickersCategory;
use App\Models\Status;
use App\Models\Surprise;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PageController extends Controller
{
    /**
     * Главная страница
     *
     * @param string $page
     *
     * @return View
     */
    public function index(string $page = 'index'): View
    {
        if ($page === 'menu'  ||
            ! preg_match('|^[a-z0-9_\-]+$|i', $page) ||
            ! file_exists(RESOURCES . '/views/main/' . $page . '.blade.php')
        ) {
            abort(404);
        }

        return view('main/layout', compact('page'));
    }

    /**
     * Меню
     *
     * @return View
     */
    public function menu(): View
    {
        if (! getUser()) {
            abort(404);
        }

        return view('main/layout', ['page' => 'menu']);
    }

    /**
     * Теги
     *
     * @return View
     */
    public function tags(): View
    {
        return view('pages/tags');
    }

    /**
     * Правила
     *
     * @return View
     */
    public function rules(): View
    {
        $rules = Rule::query()->first();

        if ($rules) {
            $rules['text'] = str_replace('%SITENAME%', setting('title'), $rules['text']);
        }

        return view('pages/rules', compact('rules'));
    }

    /**
     * Стикеры
     *
     * @return View
     */
    public function stickers(): View
    {
        $categories = StickersCategory::query()
            ->selectRaw('sc.id, sc.name, count(s.id) cnt')
            ->from('stickers_categories as sc')
            ->leftJoin('stickers as s', 's.category_id', 'sc.id')
            ->groupBy('sc.id')
            ->orderBy('sc.id')
            ->get();

        return view('pages/stickers', compact('categories'));
    }

    /**
     * Стикеры
     *
     * @param int $id
     *
     * @return View
     */
    public function stickersCategory(int $id): View
    {
        $category = StickersCategory::query()->where('id', $id)->first();

        if (! $category) {
            abort(404, __('stickers.category_not_exist'));
        }

        $stickers = Sticker::query()
            ->where('category_id', $id)
            ->orderBy(DB::raw('CHAR_LENGTH(code)'))
            ->orderBy('name')
            ->with('category')
            ->paginate(setting('stickerlist'));

        return view('pages/stickers_category', compact('category', 'stickers'));
    }

    /**
     * Ежегодный сюрприз
     *
     * @return RedirectResponse
     */
    public function surprise(): RedirectResponse
    {
        $surprise['requiredDate']  = '10.01';

        $money  = mt_rand(10000, 50000);
        $point  = mt_rand(150, 250);
        $rating = mt_rand(3, 10);
        $year   = date('Y');

        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        if (strtotime(date('d.m.Y')) > strtotime($surprise['requiredDate'].'.'.date('Y'))) {
            abort(200, __('pages.surprise_date_receipt'));
        }

        $existSurprise = Surprise::query()
            ->where('user_id', $user->id)
            ->where('year', $year)
            ->first();

        if ($existSurprise) {
            abort(200, __('pages.surprise_already_received'));
        }

        if ($user->point >= 50) {
            $user->increment('point', $point);
        } else {
            $point = 0;
        }

        $user->increment('money', $money);
        $user->increment('posrating', $rating);
        $user->update(['rating' => $user->posrating - $user->negrating]);

        $text = textNotice('surprise', ['year' => $year, 'point' => plural($point, setting('scorename')), 'money' => plural($money, setting('moneyname')), 'rating' => $rating]);
        $user->sendMessage(null, $text);

        Surprise::query()->create([
            'user_id'    => $user->id,
            'year'       => $year,
            'created_at' => SITETIME,
        ]);

        setFlash('success', __('pages.surprise_success_received'));
        return redirect('/');
    }

    /**
     * FAQ по сайту
     *
     * @return View
     */
    public function faq(): View
    {
        return view('pages/faq');
    }


    /**
     * FAQ по статусам
     *
     * @return View
     */
    public function statusfaq(): View
    {
        $statuses = Status::query()
            ->orderByDesc('topoint')
            ->get();

        return view('pages/statusfaq', compact('statuses'));
    }
}
