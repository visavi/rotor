<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Classes\Validator;
use App\Models\Social;
use Curl\Curl;
use Illuminate\Http\Request;

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
            abort(403, trans('main.not_authorized'));
        }
    }

    /**
     * Главная страница
     *
     * @param Request $request
     * @return string
     * @throws \ErrorException
     */
    public function index(Request $request): string
    {
        $token = check($request->input('token'));

        if ($request->isMethod('post')) {
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
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     * @throws \Exception
     */
    public function delete(int $id, Request $request, Validator $validator): void
    {
        $token = check($request->input('token'));

        $social = Social::query()->where('user_id', $this->user->id)->find($id);

        $validator->equal($token, $_SESSION['token'], trans('validator.token'))
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
