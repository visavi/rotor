<?php

namespace App\Modules\Game\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class ThimbleController extends IndexController
{
    /**
     * @var User
     */
    private $user;

    /**
     * ThimbleController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        if (! $this->user = getUser()) {
            abort(403, 'Для игры необходимо авторизоваться!');
        }
    }

    /**
     * Наперстки
     *
     * @return string
     */
    public function index(): string
    {
        return $this->view()->make('game::thimbles/index', ['user' => $this->user]);
    }

    /**
     * Выбор наперстка
     *
     * @return string
     */
    public function choice(): string
    {
        return $this->view()->make('game::thimbles/choice', ['user' => $this->user]);
    }

    /**
     * Игра в наперстки
     *
     * @param Request $request
     * @return string
     * @throws \Exception
     */
    public function go(Request $request): string
    {
        $thimble = int($request->input('thimble'));

        if ($this->user->money < 5) {
            abort('default', 'Вы не можете играть! У вас недостаточно средств!');
        }

        if (! $thimble) {
            setFlash('danger', 'Необходимо выбрать один из наперстков!');
            redirect('/games/thimbles/choice');
        }

        $results = [
            'victory' => '<span class="text-success">Вы выиграли</span>',
            'lost'    => '<span class="text-danger">Вы проиграли</span>',
        ];

        $randThimble = random_int(1, 3);

        if ($thimble === $randThimble) {
            $this->user->increment('money', 10);
            $result = $results['victory'];
        } else {
            $this->user->decrement('money', 5);
            $result = $results['lost'];
        }

        $user = $this->user;

        return $this->view()->make('game::thimbles/go', compact('user', 'randThimble', 'thimble', 'result'));
    }
}
