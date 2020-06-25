<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Classes\Validator;
use App\Models\Social;
use Curl\Curl;
use Exception;
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
            abort(403, __('main.not_authorized'));
        }
    }

    /**
     * Главная страница
     *
     * @param Request $request
     *
     * @return string
     */
    public function index(Request $request): string
    {
        if ($request->isMethod('post')) {
            $curl    = new Curl();
            $network = $curl->get('//ulogin.ru/token.php',
                [
                    'token' => $request->input('token'),
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

                    setFlash('success', __('socials.success_binding'));
                } else {
                    setFlash('danger',  __('socials.already_binding'));
                }

                redirect('/socials');
            }

            setFlash('danger', __('socials.failed_binding'));
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
     *
     * @throws Exception
     */
    public function delete(int $id, Request $request, Validator $validator): void
    {
        $social = Social::query()->where('user_id', $this->user->id)->find($id);

        $validator->equal($request->input('token'), $_SESSION['token'], __('validator.token'))
            ->notEmpty($social, __('socials.not_found_binding'));

        if ($validator->isValid()) {
            $social->delete();

            setFlash('success', __('socials.success_deleted'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/socials');
    }
}
