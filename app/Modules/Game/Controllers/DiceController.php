<?php

declare(strict_types=1);

namespace App\Modules\Game\Controllers;

use App\Models\User;

class DiceController extends IndexController
{
    /**
     * @var User
     */
    private $user;

    /**
     * DiceController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        if (! $this->user = getUser()) {
            abort(403, 'Для игры необходимо авторизоваться!');
        }
    }

    /**
     * Кости
     *
     * @return string
     */
    public function index(): string
    {
        return $this->view('game::dice/index', ['user' => $this->user]);
    }

    /**
     * Игра в кости
     *
     * @return string
     * @throws \Exception
     */
    public function go(): string
    {
        if ($this->user->money < 5) {
            abort('default', 'Вы не можете играть! У вас недостаточно средств!');
        }

        $results = [
            'victory' => '<span class="text-success">Вы выиграли</span>',
            'lost'    => '<span class="text-danger">Вы проиграли</span>',
            'draw'    => 'Ничья',
        ];

        $num[0] = random_int(1, random_int(5, 6));
        $num[1] = random_int(1, random_int(5, 6));
        $num[2] = random_int(1, 6);
        $num[3] = random_int(1, 6);

        $sumUser = $num[0] + $num[1];
        $sumBank = $num[2] + $num[3];

        if ($sumUser > $sumBank) {
            $this->user->increment('money', 10);
            $result = $results['victory'];
        } elseif($sumUser < $sumBank) {
            $this->user->decrement('money', 5);
            $result = $results['lost'];
        } else {
            $result = $results['draw'];
        }

        $user = $this->user;

        return $this->view('game::dice/go', compact('num', 'result', 'user'));
    }
}
