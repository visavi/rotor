<?php

namespace App\Modules\Game\Controllers;

use App\Models\User;

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
     * @return string
     */
    public function go(): string
    {
        if ($this->user->money < 50) {
            abort('default', 'Вы не можете играть! У вас недостаточно средств!');
        }

/*        if () {
            $this->user->increment('money', 100);
        } else {
            $this->user->decrement('money', 50);
        }*/

        return $this->view()->make('game::thimbles/go', ['user' => $this->user]);
    }
}
