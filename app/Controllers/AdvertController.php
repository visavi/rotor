<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Classes\Validator;
use App\Models\Advert;
use Illuminate\Http\Request;

class AdvertController extends BaseController
{
    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();

        if (! setting('rekusershow')) {
            abort('default', 'Показ и размещение рекламы запрещено администрацией сайта!');
        }
    }

    /**
     * Главная страница
     *
     * @return string
     */
    public function index(): string
    {
        $total = Advert::query()->where('deleted_at', '>', SITETIME)->count();

        $page = paginate(setting('rekuserpost'), $total);

        $adverts = Advert::query()
            ->where('deleted_at', '>', SITETIME)
            ->limit($page->limit)
            ->offset($page->offset)
            ->orderBy('deleted_at', 'desc')
            ->with('user')
            ->get();

        return view('adverts/index', compact('adverts', 'page'));
    }

    /**
     * Покупка рекламы
     *
     * @param Request   $request
     * @param Validator $validator
     * @return string
     */
    public function create(Request $request, Validator $validator): string
    {
        if (! getUser()) {
            abort(403, __('main.not_authorized'));
        }

        if (getUser('point') < setting('rekuserpoint')) {
            abort('default', 'Для покупки рекламы вам необходимо набрать '.plural(50, setting('scorename')).'!');
        }

        $total = Advert::query()->where('deleted_at', '>', SITETIME)->count();
        if ($total >= setting('rekusertotal')) {
            abort('default', 'В данный момент нет свободных мест для размещения рекламы!');
        }

        $advert = Advert::query()
            ->where('user_id', getUser('id'))
            ->where('deleted_at', '>', SITETIME)
            ->first();

        if ($advert) {
            abort('default', 'Вы уже разместили рекламу, запрещено добавлять несколько сайтов подряд!');
        }

        if ($request->isMethod('post')) {
            $token = check($request->input('token'));
            $site  = check($request->input('site'));
            $name  = check($request->input('name'));
            $color = check($request->input('color'));
            $bold  = empty($request->input('bold')) ? 0 : 1;

            $price = setting('rekuserprice');

            if ($color) {
                $price += setting('rekuseroptprice');
            }

            if ($bold) {
                $price += setting('rekuseroptprice');
            }

            $validator->equal($token, $_SESSION['token'], __('validator.token'))
                ->gte(getUser('point'), setting('rekuserpoint'), 'Для покупки рекламы вам необходимо набрать '.plural(50, setting('scorename')).'!')
                ->true(captchaVerify(), ['protect' => __('validator.captcha')])
                ->regex($site, '|^https?://([а-яa-z0-9_\-\.])+(\.([а-яa-z0-9\/\-?_=#])+)+$|iu', ['site' => __('validator.url')])
                ->length($site, 5, 100, ['site' => __('validator.url_text')])
                ->length($name, 5, 35, ['name' => __('validator.text')])
                ->regex($color, '|^#+[A-f0-9]{6}$|', ['color' => __('validator.color')], false)
                ->gte(getUser('money'), $price, ['Для покупки рекламы у вас недостаточно денег!']);

            if ($validator->isValid()) {
                Advert::query()->where('deleted_at', '<', SITETIME)->delete();

                Advert::query()->create([
                    'site'       => $site,
                    'name'       => $name,
                    'color'      => $color,
                    'bold'       => $bold,
                    'user_id'    => getUser('id'),
                    'created_at' => SITETIME,
                    'deleted_at' => SITETIME + (setting('rekusertime') * 3600),
                ]);

                getUser()->decrement('money', $price);

                saveAdvertUser();

                setFlash('success', 'Рекламная ссылка успешно размещена');
                redirect('/adverts');
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('adverts/create');
    }
}
