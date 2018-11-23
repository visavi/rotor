<?php

namespace App\Modules\Game\Controllers;

use App\Controllers\BaseController;

class IndexController extends BaseController
{
    /**
     * @var \Illuminate\View\Factory|\Jenssegers\Blade\Blade
     */
    private $view;

    public function __construct()
    {
        $this->view = blade()->addNamespace('game', APP . '/Modules/Game/views');

        parent::__construct();
    }

    /**
     * Главная страница
     */
    public function index(): string
    {
        return $this->view->make('game::index');
    }

    /**
     * Кости
     */
    public function dice(): string
    {
        return $this->view->make('game::dice/index');
    }

    /**
     * Игра в кости
     */
    public function go(): string
    {
        if (getUser('money') < 5) {
            abort('default', 'Вы не можете играть! На вашем счету недостаточно средств!');
        }

        $res = [
            'victory' => 'Вы выиграли',
            'lost'    => 'Вы проиграли',
            'draw'    => 'Ничья',
        ];

        $num[0] = mt_rand(1, mt_rand(5, 6));
        $num[1] = mt_rand(1, mt_rand(5, 6));
        $num[2] = mt_rand(1, 6);
        $num[3] = mt_rand(1, 6);

        $sumUser = $num[0] + $num[1];
        $sumBank = $num[2] + $num[3];

        if ($sumUser > $sumBank) {
            getUser()->increment('money', 10);
            $result = 'victory';
        } elseif($sumUser < $sumBank) {
            getUser()->decrement('money', 5);
            $result = 'lost';
        } else {
            $result = 'draw';
        }

        return $this->view->make('game::dice/go', compact('num', 'result'));
    }
}
