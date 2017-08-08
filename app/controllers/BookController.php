<?php

class BookController extends BaseController
{
    /**
     * Главная страница
     */
    public function index()
    {
        $total = Guest::count();
        $page = App::paginate(Setting::get('bookpost'), $total);

        $posts = Guest::orderBy('created_at', 'desc')
            ->limit(Setting::get('bookpost'))
            ->offset($page['offset'])
            ->with('user', 'editUser')
            ->get();

        App::view('book/index', compact('posts', 'page'));
    }

    /**
     * Добавление сообщения
     */
    public function add()
    {
        $msg = check(Request::input('msg'));
        $token = check(Request::input('token'));

        $validation = new Validation();
        $validation->addRule('equal', [$token, $_SESSION['token']], ['msg' => 'Неверный идентификатор сессии, повторите действие!'])
            ->addRule('string', $msg, ['msg' => 'Ошибка! Слишком длинное или короткое сообщение!'], true, 5, Setting::get('guesttextlength'))
            ->addRule('bool', Flood::isFlood(App::getUserId()), ['msg' => 'Антифлуд! Разрешается отправлять сообщения раз в ' . Flood::getPeriod() . ' секунд!']);

        /* Проерка для гостей */
        if (!is_user() && Setting::get('bookadds')) {
            $protect = check(strtolower(Request::input('protect')));
            $validation->addRule('equal', [$protect, $_SESSION['protect']], ['protect' => 'Проверочное число не совпало с данными на картинке!']);
        } else {
            $validation->addRule('bool', is_user(), ['msg' => 'Для добавления сообщения необходимо авторизоваться']);
        }

        if ($validation->run()) {

            $msg = antimat($msg);

            if (is_user()) {
                $bookscores = (Setting::get('bookscores')) ? 1 : 0;

                $user = User::where('id', App::getUserId());
                $user->update([
                    'allguest' => Capsule::raw('allguest + 1'),
                    'point'    => Capsule::raw('point + ' . $bookscores),
                    'money'    => Capsule::raw('money + 5'),
                ]);
            }

            $username = is_user() ? App::getUserId() : 0;

            $guest = new Guest();
            $guest->user_id = $username;
            $guest->text = $msg;
            $guest->ip = App::getClientIp();
            $guest->brow = App::getUserAgent();
            $guest->created_at = SITETIME;
            $guest->save();

            Capsule::delete('
                    DELETE FROM guest WHERE created_at < (
                        SELECT MIN(created_at) FROM (
                            SELECT created_at FROM guest ORDER BY created_at DESC LIMIT ' . Setting::get('maxpostbook') . '
                        ) AS del
                    );'
            );

            App::setFlash('success', 'Сообщение успешно добавлено!');
        } else {
            App::setInput(Request::all());
            App::setFlash('danger', $validation->getErrors());
        }

        App::redirect("/book");
    }

    /**
     * Подготовка к редактированию
     */
    public function edit()
    {
        $id = param('id');

        if (!is_user()) App::abort(403);

        $post = Guest::where('user_id', App::getUserId())->find($id);

        if (!$post) {
            App::abort('default', 'Ошибка! Сообщение удалено или вы не автор этого сообщения!');
        }

        if ($post['created_at'] + 600 < SITETIME) {
            App::abort('default', 'Редактирование невозможно, прошло более 10 минут!');
        }

        if (Request::isMethod('post')) {

            $msg = check(Request::input('msg'));
            $token = check(Request::input('token'));

            $validation = new Validation();
            $validation->addRule('equal', [$token, $_SESSION['token']], ['msg' => 'Неверный идентификатор сессии, повторите действие!'])
                ->addRule('string', $msg, ['msg' => 'Ошибка! Слишком длинное или короткое сообщение!'], true, 5, Setting::get('guesttextlength'));

            if ($validation->run()) {

                $msg = antimat($msg);

                $post->text = $msg;
                $post->edit_user_id = App::getUserId();
                $post->updated_at = SITETIME;
                $post->save();

                App::setFlash('success', 'Сообщение успешно отредактировано!');
                App::redirect('/book');
            } else {
                App::setInput(Request::all());
                App::setFlash('danger', $validation->getErrors());
            }
        }

        App::view('book/edit', compact('post', 'id'));
    }
}
