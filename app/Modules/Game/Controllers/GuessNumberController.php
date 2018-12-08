<?php

declare(strict_types=1);

namespace App\Modules\Game\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class GuessNumberController extends IndexController
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
     * Угадай число
     *
     * @return string
     */
    public function index(): string
    {
        return view('Game::guess/index', ['user' => $this->user]);
    }

    /**
     * Попытка
     *
     * @param Request $request
     * @return string
     */
    public function go(Request $request): string
    {
        $guess = int($request->input('guess'));



        if ($this->user->money < 5) {
            abort('default', 'Вы не можете играть! У вас недостаточно средств!');
        }

        if (empty($_SESSION['guess']['hill'])) {
            $_SESSION['guess']['hill']  = mt_rand(1, 100);
            $_SESSION['guess']['count'] = 0;
        }
 var_dump($_SESSION['guess']);
        $_SESSION['guess']['count']++;
        $this->user->decrement('money', 5);

        if ($guess !== $_SESSION['guess']['hill']) {
            if ($_SESSION['guess']['count'] < 5) {

                echo '<b>Попыток: '.$_SESSION['guess']['count'].'</b><br />';

                if ($guess > $_SESSION['guess']['hill']) {
                    echo $guess.' — это большое число<br /><i class="fa fa-minus-circle"></i> Введите меньше<br /><br />';
                }
                if ($guess < $_SESSION['guess']['hill']) {
                    echo $guess.' — это маленькое число<br /><i class="fa fa-plus-circle"></i> Введите больше<br /><br />';
                }

                $count_pop = 5 - $_SESSION['guess']['count'] ;

                echo 'Осталось попыток: <b>'.$count_pop.'</b><br />';

            }
        } else {
            $this->user->increment('money', 100);



            unset($_SESSION['guess']);
        }

        return view('Game::guess/go', ['user' => $this->user]);
    }
}
