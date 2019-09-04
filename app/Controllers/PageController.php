<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Rule;
use App\Models\Sticker;
use App\Models\StickersCategory;
use App\Models\Status;
use App\Models\Surprise;
use Illuminate\Database\Capsule\Manager as DB;

class PageController extends BaseController
{
    /**
     * Главная страница
     *
     * @param string $page
     * @return string
     */
    public function index(string $page = 'index'): string
    {
        if (
            $page === 'menu'  ||
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
     * @return string
     */
    public function menu(): string
    {
        if (! getUser()) {
            abort(404);
        }

        return view('main/layout', ['page' => 'menu']);
    }

    /**
     * Теги
     *
     * @return string
     */
    public function tags(): string
    {
        return view('pages/tags');
    }

    /**
     * Правила
     *
     * @return string
     */
    public function rules(): string
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
     * @return string
     */
    public function stickers(): string
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
     * @return string
     */
    public function stickersCategory(int $id): string
    {
        $category = StickersCategory::query()->where('id', $id)->first();

        if (! $category) {
            abort(404, 'Данной категории не существует!');
        }

        $total = Sticker::query()->where('category_id', $id)->count();
        $page = paginate(setting('stickerlist'), $total);

        $stickers = Sticker::query()
            ->where('category_id', $id)
            ->orderBy(DB::connection()->raw('CHAR_LENGTH(`code`)'))
            ->orderBy('name')
            ->limit($page->limit)
            ->offset($page->offset)
            ->with('category')
            ->get();

        return view('pages/stickers_category', compact('category', 'stickers', 'page'));
    }

    /**
     * Ежегодный сюрприз
     *
     * @return void
     */
    public function surprise(): void
    {
        $surprise['requiredDate']  = '10.01';

        $surpriseMoney  = \mt_rand(10000, 50000);
        $surprisePoint  = \mt_rand(150, 250);
        $surpriseRating = \mt_rand(3, 10);
        $currentYear    = \date('Y');

        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        if (strtotime(date('d.m.Y')) > strtotime($surprise['requiredDate'].'.'.date('Y'))) {
            abort('default', 'Срок получения сюрприза еще не начался или уже закончился!');
        }

        $existSurprise = Surprise::query()
            ->where('user_id', $user->id)
            ->where('year', $currentYear)
            ->first();

        if ($existSurprise) {
            abort('default', 'В этом году сюрприз уже получен');
        }


        $pointText = null;

        if ($user->point >= 50) {
            $user->increment('point', $surprisePoint);
            $pointText = plural($surprisePoint, setting('scorename')) . PHP_EOL;
        }

        $user->increment('money', $surpriseMoney);
        $user->increment('posrating', $surpriseRating);
        $user->update(['rating' => $user->posrating - $user->negrating]);

        $text = 'Поздравляем с новым ' . $currentYear . ' годом!' . PHP_EOL . 'В качестве сюрприза вы получаете ' . PHP_EOL . $pointText . plural($surpriseMoney, setting('moneyname')) . PHP_EOL . $surpriseRating . ' рейтинга репутации' . PHP_EOL . 'Ура!!!';

        $user->sendMessage(null, $text);

        Surprise::query()->create([
            'user_id'    => $user->id,
            'year'       => $currentYear,
            'created_at' => SITETIME,
        ]);

        setFlash('success', 'Сюрприз успешно получен!');
        redirect('/');
    }

    /**
     * FAQ по сайту
     *
     * @return string
     */
    public function faq(): string
    {
        return view('pages/faq');
    }


    /**
     * FAQ по статусам
     *
     * @return string
     */
    public function statusfaq(): string
    {
        $statuses = Status::query()
            ->orderBy('topoint', 'desc')
            ->get();

        return view('pages/statusfaq', compact('statuses'));
    }
}
