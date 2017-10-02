<?php

namespace App\Controllers;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\RekUser;

class RekUserController extends BaseController
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
     */
    public function index()
    {
        $total = RekUser::query()->where('deleted_at', '>', SITETIME)->count();

        $page = paginate(setting('rekuserpost'), $total);

        $adverts = RekUser::query()
            ->where('deleted_at', '>', SITETIME)
            ->limit(setting('rekuserpost'))
            ->offset($page['offset'])
            ->orderBy('deleted_at', 'desc')
            ->get();

        return view('reklama/index', compact('adverts', 'page'));
    }

    /**
     * Покупка рекламы
     */
    public function create()
    {
        if (! getUser()) {
            abort(403, 'Для покупки рекламы необходимо авторизоваться!');
        }

        if (getUser('point') < 50) {
            abort('default', 'Для покупки рекламы вам необходимо набрать '.plural(50, setting('scorename')).'!');
        }

        $total = RekUser::query()->where('deleted_at', '>', SITETIME)->count();
        if ($total >= setting('rekusertotal')) {
            abort('default', 'В данный момент нет свободных мест для размещения рекламы!');
        }

        $advert = RekUser::query()
            ->where('user_id', getUser('id'))
            ->where('deleted_at', '>', SITETIME)
            ->first();

        if ($advert) {
            abort('default', 'Вы уже разместили рекламу, запрещено добавлять несколько сайтов подряд!');
        }

        if (Request::isMethod('post')) {
            $token   = check(Request::input('token'));
            $site    = check(Request::input('site'));
            $name    = check(Request::input('name'));
            $color   = check(Request::input('color'));
            $bold    = Request::has('bold') ? 1 : 0;
            $protect = check(strtolower(Request::input('protect')));

            $price = setting('rekuserprice');

            if ($color) {
                $price = $price + setting('rekuseroptprice');
            }

            if ($bold) {
                $price = $price + setting('rekuseroptprice');
            }

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->gte(getUser('point'), 50, 'Для покупки рекламы вам необходимо набрать '.plural(50, setting('scorename')).'!')
                ->equal($protect, $_SESSION['protect'], ['protect' => 'Проверочное число не совпало с данными на картинке!'])
                ->regex($site, '|^https?://([а-яa-z0-9_\-\.])+(\.([а-яa-z0-9\/\-?_=#])+)+$|iu', ['site' => 'Недопустимый адрес сайта!. Разрешены символы [а-яa-z0-9_-.?=#/]!'])
                ->length($site, 5, 50, ['site' => 'Слишком длинный или короткий адрес ссылки!'])
                ->length($name, 5, 35, ['name' => 'Слишком длинное или короткое название ссылки!'])
                ->regex($color, '|^#+[A-f0-9]{6}$|', ['color' => 'Недопустимый формат цвета ссылки! (пример #ff0000)'], false)
                ->gte(getUser('money'), $price, ['Для покупки рекламы у вас недостаточно денег!']);


            if ($validator->isValid()) {

                RekUser::query()->where('deleted_at', '<', SITETIME)->delete();

                RekUser::query()->create([
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
                redirect("/reklama");
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('reklama/create');
    }
}
