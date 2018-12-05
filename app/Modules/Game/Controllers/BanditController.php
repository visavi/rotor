<?php

namespace App\Modules\Game\Controllers;

use App\Models\User;

class BanditController extends IndexController
{
    /**
     * @var User
     */
    private $user;

    /**
     * BanditController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        if (! $this->user = getUser()) {
            abort(403, 'Для игры необходимо авторизоваться!');
        }
    }

    /**
     * Бандит
     *
     * @return string
     */
    public function index(): string
    {
        return $this->view()->make('game::bandit/index', ['user' => $this->user]);
    }

    /**
     * Правила игры
     *
     * @return string
     */
    public function faq(): string
    {
        return $this->view()->make('game::bandit/faq');
    }

    /**
     * Игра
     *
     * @return string
     */
    public function go(): string
    {
        if ($this->user->money < 5) {
            abort('default', 'Вы не можете играть! У вас недостаточно средств!');
        }

        $num[1] = mt_rand(1, 8);
        $num[2] = mt_rand(1, 8);
        $num[3] = mt_rand(1, 8);
        $num[4] = mt_rand(1, 8);
        $num[5] = mt_rand(1, mt_rand(7, 8));
        $num[6] = mt_rand(1, 8);
        $num[7] = mt_rand(1, 8);
        $num[8] = mt_rand(1, 8);
        $num[9] = mt_rand(1, 8);

        $sum     = 0;
        $results = [];

        // ряды
        if ($num[1] === 1 && $num[2] === 1 && $num[3] === 1) {
            $results[] = 'Вишенки - вехний ряд';
            $sum += 5;
        }
        if ($num[4] === 1 && $num[5] === 1 && $num[6] === 1) {
            $results[] = 'Вишенки - средний ряд';
            $sum += 10;
        }
        if ($num[7] === 1 && $num[8] === 1 && $num[9] === 1) {
            $results[] = 'Вишенки - нижний ряд';
            $sum += 5;
        }

        if ($num[1] === 2 && $num[2] === 2 && $num[3] === 2) {
            $results[] = 'Апельсины - вехний ряд';
            $sum += 10;
        }
        if ($num[4] === 2 && $num[5] === 2 && $num[6] === 2) {
            $results[] = 'Апельсины - средний ряд';
            $sum += 15;
        }
        if ($num[7] === 2 && $num[8] === 2 && $num[9] === 2) {
            $results[] = 'Апельсины - нижний ряд';
            $sum += 10;
        }

        if ($num[1] === 3 && $num[2] === 3 && $num[3] === 3) {
            $results[] = 'Виноград - вехний ряд';
            $sum += 15;
        }
        if ($num[4] === 3 && $num[5] === 3 && $num[6] === 3) {
            $results[] = 'Виноград - средний ряд';
            $sum += 25;
        }
        if ($num[7] === 3 && $num[8] === 3 && $num[9] === 3) {
            $results[] = 'Виноград - нижний ряд';
            $sum += 15;
        }

        if ($num[1] === 4 && $num[2] === 4 && $num[3] === 4) {
            $results[] = 'Бананы - вехний ряд';
            $sum += 25;
        }
        if ($num[4] === 4 && $num[5] === 4 && $num[6] === 4) {
            $results[] = 'Бананы - средний ряд';
            $sum += 35;
        }
        if ($num[7] === 4 && $num[8] === 4 && $num[9] === 4) {
            $results[] = 'Бананы - нижний ряд';
            $sum += 25;
        }

        if ($num[1] === 5 && $num[2] === 5 && $num[3] === 5) {
            $results[] = 'Яблоки - вехний ряд';
            $sum += 30;
        }
        if ($num[4] === 5 && $num[5] === 5 && $num[6] === 5) {
            $results[] = 'Яблоки - средний ряд';
            $sum += 50;
        }
        if ($num[7] === 5 && $num[8] === 5 && $num[9] === 5) {
            $results[] = 'Яблоки - нижний ряд';
            $sum += 30;
        }

        if ($num[1] === 6 && $num[2] === 6 && $num[3] === 6) {
            $results[] = 'BAR - вехний ряд';
            $sum += 50;
        }
        if ($num[4] === 6 && $num[5] === 6 && $num[6] === 6) {
            $results[] = 'BAR - средний ряд';
            $sum += 70;
        }
        if ($num[7] === 6 && $num[8] === 6 && $num[9] === 6) {
            $results[] = 'BAR - нижний ряд';
            $sum += 50;
        }

        if ($num[1] === 7 && $num[2] === 7 && $num[3] === 7) {
            $results[] = '$$$ - вехний ряд';
            $sum += 60;
        }
        if ($num[4] === 7 && $num[5] === 7 && $num[6] === 7) {
            $results[] = '$$$ - средний ряд';
            $sum += 100;
        }
        if ($num[7] === 7 && $num[8] === 7 && $num[9] === 7) {
            $results[] = '$$$ - нижний ряд';
            $sum += 60;
        }

        if ($num[1] === 8 && $num[2] === 8 && $num[3] === 8) {
            $results[] = '777 - вехний ряд';
            $sum += 177;
        }
        if ($num[4] === 8 && $num[5] === 8 && $num[6] === 8) {
            $results[] = '777 - средний ряд';
            $sum += 777;
        }
        if ($num[7] === 8 && $num[8] === 8 && $num[9] === 8) {
            $results[] = '777 - нижний ряд';
            $sum += 177;
        }

        // столбцы
        if ($num[1] === 1 && $num[4] === 1 && $num[7] === 1) {
            $results[] = 'Вишенки - левый столбец';
            $sum += 5;
        }
        if ($num[2] === 1 && $num[5] === 1 && $num[8] === 1) {
            $results[] = 'Вишенки - средний столбец';
            $sum += 10;
        }
        if ($num[3] === 1 && $num[6] === 1 && $num[9] === 1) {
            $results[] = 'Вишенки - правый столбец';
            $sum += 5;
        }

        if ($num[1] === 2 && $num[4] === 2 && $num[7] === 2) {
            $results[] = 'Апельсины - левый столбец';
            $sum += 10;
        }
        if ($num[2] === 2 && $num[5] === 2 && $num[8] === 2) {
            $results[] = 'Апельсины - средний столбец';
            $sum += 15;
        }
        if ($num[3] === 2 && $num[6] === 2 && $num[9] === 2) {
            $results[] = 'Апельсины - правый столбец';
            $sum += 10;
        }

        if ($num[1] === 3 && $num[4] === 3 && $num[7] === 3) {
            $results[] = 'Виноград - левый столбец';
            $sum += 15;
        }
        if ($num[2] === 3 && $num[5] === 3 && $num[8] === 3) {
            $results[] = 'Виноград - средний столбец';
            $sum += 25;
        }
        if ($num[3] === 3 && $num[6] === 3 && $num[9] === 3) {
            $results[] = 'Виноград - правый столбец';
            $sum += 15;
        }

        if ($num[1] === 4 && $num[4] === 4 && $num[7] === 4) {
            $results[] = 'Бананы - левый столбец';
            $sum += 25;
        }
        if ($num[2] === 4 && $num[5] === 4 && $num[8] === 4) {
            $results[] = 'Бананы - средний столбец';
            $sum += 35;
        }
        if ($num[3] === 4 && $num[6] === 4 && $num[9] === 4) {
            $results[] = 'Бананы - правый столбец';
            $sum += 25;
        }

        if ($num[1] === 5 && $num[4] === 5 && $num[7] === 5) {
            $results[] = 'Яблоки - левый столбец';
            $sum += 30;
        }
        if ($num[2] === 5 && $num[5] === 5 && $num[8] === 5) {
            $results[] = 'Яблоки - средний столбец';
            $sum += 50;
        }
        if ($num[3] === 5 && $num[6] === 5 && $num[9] === 5) {
            $results[] = 'Яблоки - правый столбец';
            $sum += 30;
        }

        if ($num[1] === 6 && $num[4] === 6 && $num[7] === 6) {
            $results[] = 'BAR - левый столбец';
            $sum += 50;
        }
        if ($num[2] === 6 && $num[5] === 6 && $num[8] === 6) {
            $results[] = 'BAR - средний столбец';
            $sum += 70;
        }
        if ($num[3] === 6 && $num[6] === 6 && $num[9] === 6) {
            $results[] = 'BAR - правый столбец';
            $sum += 50;
        }

        if ($num[1] === 7 && $num[4] === 7 && $num[7] === 7) {
            $results[] = '$$$ - левый столбец';
            $sum += 60;
        }
        if ($num[2] === 7 && $num[5] === 7 && $num[8] === 7) {
            $results[] = '$$$ - средний столбец';
            $sum += 100;
        }
        if ($num[3] === 7 && $num[6] === 7 && $num[9] === 7) {
            $results[] = '$$$ - правый столбец';
            $sum += 60;
        }

        if ($num[1] === 8 && $num[4] === 8 && $num[7] === 8) {
            $results[] = '777 - левый столбец';
            $sum += 100;
        }
        if ($num[2] === 8 && $num[5] === 8 && $num[8] === 8) {
            $results[] = '777 - средний столбец';
            $sum += 177;
        }
        if ($num[3] === 8 && $num[6] === 8 && $num[9] === 8) {
            $results[] = '777 - правый столбец';
            $sum += 100;
        }

        $this->user->decrement('money', 5);

        if ($sum > 0) {
            $this->user->increment('money', $sum);
        }

        $user = $this->user;

        return $this->view()->make('game::bandit/go', compact('num', 'results', 'sum', 'user'));
    }
}
