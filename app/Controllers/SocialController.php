<?php

namespace App\Controllers;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\Social;
use Curl\Curl;

class SocialController extends BaseController
{
    public $user;

    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();

        if (! $this->user = getUser()) {
            abort(403, 'Для управления социальными сетями необходимо авторизоваться!');
        }
    }

    /**
     * Главная страница
     */
    public function index()
    {
        $token = check(Request::input('token'));

        if (Request::isMethod('post')) {
            $curl    = new Curl();
            $network = $curl->get('//ulogin.ru/token.php',
                [
                    'token' => $token,
                    'host'  => $_SERVER['HTTP_HOST']
                ]
            );

            if ($network && empty($network->error)) {

                $social = Social::query()
                    ->where('network', $network->network)
                    ->where('uid', $network->uid)
                    ->first();

                if (! $social) {
                    Social::query()->create([
                        'user_id'    => $this->user->id,
                        'network'    => $network->network,
                        'uid'        => $network->uid,
                        'created_at' => SITETIME,
                    ]);

                    setFlash('success', 'Привязка успешно добавлена!');
                } else {
                    setFlash('danger', 'Данная социальная сеть уже привязана!');
                }

                redirect('/socials');
            }

            setFlash('danger', 'Не удалось добавить привязку!');
        }

        $socials = Social::query()
            ->where('user_id', $this->user->id)
            ->get();

        return view('socials/index', compact('socials'));
    }

    /**
     * Удаление привязки
     */
    public function delete($id)
    {
        $token = check(Request::input('token'));

        $social = Social::query()->where('user_id', $this->user->id)->find($id);

        $validator = new Validator();
        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
            ->notEmpty($social, 'Не найдена привязка к социальной сети!');

        if ($validator->isValid()) {

            $social->delete();

            setFlash('success', 'Привязка к социальной сети успешно удалена!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/socials');
    }
}
