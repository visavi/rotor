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
     */
    public function __call($action, $params)
    {
        if (! preg_match('|^[a-z0-9_\-]+$|i', $action)) {
            abort(404);
        }

        if (! file_exists(RESOURCES.'/views/main/'.$action.'.blade.php')){
            abort(404);
        }

        if (! getUser() && $action == 'menu'){
            abort(404);
        }

        return view('main/layout', compact('action'));
    }

    /**
     * Теги
     */
    public function tags()
    {
        return view('pages/tags');
    }

    /**
     * Правила
     */
    public function rules()
    {
        $rules = Rule::query()->first();

        if ($rules) {
            $rules['text'] = str_replace('%SITENAME%', setting('title'), $rules['text']);
        }

        return view('pages/rules', compact('rules'));
    }

    /**
     * Смайлы
     */
    public function smiles()
    {
        $total = Smile::query()->count();
        $page = paginate(setting('smilelist'), $total);

        $smiles = Smile::query()
            ->orderBy(DB::raw('CHAR_LENGTH(`code`)'))
            ->orderBy('name')
            ->limit($page->limit)
            ->offset($page->offset)
            ->get();

        return view('pages/smiles', compact('smiles', 'page'));
    }

    /**
     * Ежегодный сюрприз
     */
    public function surprise()
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
            'point'     => DB::raw('point + '.$surprisePoint),
            'money'     => DB::raw('money + '.$surpriseMoney),
            'rating'    => DB::raw('posrating - negrating + '.$surpriseRating),
            'posrating' => DB::raw('posrating + '.$surpriseRating),
        ]);

        $text = 'Поздравляем с новым '.$currentYear.' годом!'.PHP_EOL.'В качестве сюрприза вы получаете '.PHP_EOL.plural($surprisePoint, setting('scorename')).PHP_EOL.plural($surpriseMoney, setting('moneyname')).PHP_EOL.$surpriseRating.' рейтинга репутации'.PHP_EOL.'Ура!!!';

        sendPrivate($user, null, $text);

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
     */
    public function faq()
    {
        return view('pages/faq');
    }


    /**
     * FAQ по статусам
     */
    public function statusfaq()
    {
        $statuses = Status::query()
            ->orderBy('topoint', 'desc')
            ->get();

        return view('pages/statusfaq', compact('statuses'));
    }
}
