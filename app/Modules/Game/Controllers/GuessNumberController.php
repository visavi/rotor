<?php

declare(strict_types=1);

namespace App\Modules\Game\Controllers;

use App\Classes\Validator;
use App\Models\User;
use Illuminate\Http\Request;

class GuessNumberController extends \App\Controllers\BaseController
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
     * @param Request $request
     * @return string
     */
    public function index(Request $request): string
    {
        $newGame = int($request->input('new'));

        if ($newGame) {
            unset($_SESSION['guess']);
        }

        return view('Game::guess/index', ['user' => $this->user]);
    }

    /**
     * Попытка
     *
     * @param Request   $request
     * @param Validator $validator
     * @return string
     */
    public function go(Request $request, Validator $validator): string
    {
        $token       = check($request->input('token'));
        $guessNumber = int($request->input('guess'));

        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
            ->between($guessNumber, 1, 100, ['guess' => 'Необходимо указать число!'])
            ->gte($this->user->money, 3, ['guess' => 'У вас недостаточно денег для игры!']);

        if (! $validator->isValid()) {
            setInput($request->all());
            setFlash('danger', $validator->getErrors());
            redirect('/games/guess');
        }

        if (empty($_SESSION['guess']['number'])) {
            $_SESSION['guess']['count']  = 0;
            $_SESSION['guess']['number'] = mt_rand(1, 100);
        }

        $_SESSION['guess']['count']++;
        $this->user->decrement('money', 3);
        $hint = null;

        $guess = $_SESSION['guess'];

        if ($guessNumber !== $guess['number']) {
            if ($guess['count'] < 5) {
                if ($guessNumber > $guess['number']) {
                    $hint = 'большое число, введите меньше!';
                }

                if ($guessNumber < $guess['number']) {
                    $hint = 'маленькое число, введите больше!';
                }
            } else {
                unset($_SESSION['guess']);
            }
        } else {
            unset($_SESSION['guess']);
            $this->user->increment('money', 100);
        }

        $user = $this->user;

        return view('Game::guess/go', compact('user', 'guess', 'hint', 'guessNumber'));
    }
}
