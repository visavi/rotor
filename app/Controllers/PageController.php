<?php

namespace App\Controllers;

use App\Models\Rule;
use App\Models\Smile;
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
     * Смайлы
     *
     * @return string
     */
    public function smiles(): string
    {
        $total = Smile::query()->count();
        $page = paginate(setting('smilelist'), $total);

        $smiles = Smile::query()
            ->orderBy(DB::connection()->raw('CHAR_LENGTH(`code`)'))
            ->orderBy('name')
            ->limit($page->limit)
            ->offset($page->offset)
            ->get();

        return view('pages/smiles', compact('smiles', 'page'));
    }

    /**
     * Ежегодный сюрприз
     *
     * @return void
     * @throws \Exception
     */
    public function surprise(): void
    {
        $surprise['requiredPoint'] = 50;
        $surprise['requiredDate']  = '10.01';

        $surpriseMoney  = random_int(10000, 20000);
        $surprisePoint  = random_int(150, 250);
        $surpriseRating = random_int(3, 7);
        $currentYear    = date('Y');

        if (! $user = getUser()) {
            abort(403, 'Для того чтобы получить сюрприз, необходимо авторизоваться!');
        }

        if (strtotime(date('d.m.Y')) > strtotime($surprise['requiredDate'].'.'.date('Y'))) {
            abort('default', 'Срок получения сюрприза еще не начался или уже закончился!');
        }

        if ($user->point < $surprise['requiredPoint']) {
            abort('default', 'Чтобы получить сюрприз необходимо '.plural($surprise['requiredPoint'], setting('scorename')).'!');
        }

        $existSurprise = Surprise::query()
            ->where('user_id', $user->id)
            ->where('year', $currentYear)
            ->first();

        if ($existSurprise) {
            abort('default', 'В этом году сюрприз уже получен');
        }

        $user->update([
            'point'     => DB::connection()->raw('point + '.$surprisePoint),
            'money'     => DB::connection()->raw('money + '.$surpriseMoney),
            'rating'    => DB::connection()->raw('posrating - negrating + '.$surpriseRating),
            'posrating' => DB::connection()->raw('posrating + '.$surpriseRating),
        ]);

        $text = 'Поздравляем с новым '.$currentYear.' годом!'.PHP_EOL.'В качестве сюрприза вы получаете '.PHP_EOL.plural($surprisePoint, setting('scorename')).PHP_EOL.plural($surpriseMoney, setting('moneyname')).PHP_EOL.$surpriseRating.' рейтинга репутации'.PHP_EOL.'Ура!!!';

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
