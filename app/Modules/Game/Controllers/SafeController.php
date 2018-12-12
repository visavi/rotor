<?php

declare(strict_types=1);

namespace App\Modules\Game\Controllers;

use App\Classes\Validator;
use App\Models\User;
use Illuminate\Http\Request;

class SafeController extends \App\Controllers\BaseController
{
    /**
     * @var User
     */
    private $user;

    /**
     * Controller constructor.
     */
    public function __construct()
    {
        parent::__construct();

        if (! $this->user = getUser()) {
            abort(403, 'Для игры необходимо авторизоваться!');
        }
    }

    /**
     * Взлом сейфа
     *
     * @return string
     */
    public function index(): string
    {
        return view('Game::safe/index', ['user' => $this->user]);
    }

    /**
     * Игра
     *
     * @param Request   $request
     * @param Validator $validator
     * @return string
     */
    public function go(Request $request, Validator $validator): string
    {
        $token = check($request->input('token'));
        $code0 = int($request->input('code0'));
        $code1 = int($request->input('code1'));
        $code2 = int($request->input('code2'));
        $code3 = int($request->input('code3'));
        $code4 = int($request->input('code4'));

        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
            ->gte($this->user->money, 100, ['guess' => 'У вас недостаточно денег для игры!']);

        if (! $validator->isValid()) {
            setInput($request->all());
            setFlash('danger', $validator->getErrors());
            redirect('/games/safe');
        }

        if (empty($_SESSION['safe']['cipher'])) {
            $_SESSION['safe']['cipher'] = [mt_rand(0, 9), mt_rand(0, 9), mt_rand(0, 9), mt_rand(0, 9), mt_rand(0, 9)];
            $_SESSION['safe']['try'] = 5;
            $this->user->decrement('money', 100);
        }

        $_SESSION['safe']['try']--;

        $safe = $_SESSION['safe'];
        $hack = ['-', '-', '-', '-', '-'];

        if ($code0 === $safe['cipher'][1] || $code0 === $safe['cipher'][2] || $code0 === $safe['cipher'][3] || $code0 === $safe['cipher'][4]) {$hack[0] = '*';}
        if ($code1 === $safe['cipher'][0] || $code1 === $safe['cipher'][2] || $code1 === $safe['cipher'][3] || $code1 === $safe['cipher'][4]) {$hack[1] = '*';}
        if ($code2 === $safe['cipher'][0] || $code2 === $safe['cipher'][1] || $code2 === $safe['cipher'][3] || $code2 === $safe['cipher'][4]) {$hack[2] = '*';}
        if ($code3 === $safe['cipher'][0] || $code3 === $safe['cipher'][1] || $code3 === $safe['cipher'][2] || $code3 === $safe['cipher'][4]) {$hack[3] = '*';}
        if ($code4 === $safe['cipher'][0] || $code4 === $safe['cipher'][1] || $code4 === $safe['cipher'][2] || $code3 === $safe['cipher'][3]) {$hack[3] = '*';}

        if ($code0 === $safe['cipher'][1]) {$hack[1] = 'x';}
        if ($code0 === $safe['cipher'][2]) {$hack[2] = 'x';}
        if ($code0 === $safe['cipher'][3]) {$hack[3] = 'x';}
        if ($code0 === $safe['cipher'][4]) {$hack[4] = 'x';}

        if ($code1 === $safe['cipher'][0]) {$hack[0] = 'x';}
        if ($code1 === $safe['cipher'][2]) {$hack[2] = 'x';}
        if ($code1 === $safe['cipher'][3]) {$hack[3] = 'x';}
        if ($code1 === $safe['cipher'][4]) {$hack[4] = 'x';}

        if ($code2 === $safe['cipher'][0]) {$hack[0] = 'x';}
        if ($code2 === $safe['cipher'][1]) {$hack[1] = 'x';}
        if ($code2 === $safe['cipher'][3]) {$hack[3] = 'x';}
        if ($code2 === $safe['cipher'][4]) {$hack[4] = 'x';}

        if ($code3 === $safe['cipher'][0]) {$hack[0] = 'x';}
        if ($code3 === $safe['cipher'][1]) {$hack[1] = 'x';}
        if ($code3 === $safe['cipher'][2]) {$hack[2] = 'x';}
        if ($code3 === $safe['cipher'][4]) {$hack[4] = 'x';}

        if ($code4 === $safe['cipher'][0]) {$hack[0] = 'x';}
        if ($code4 === $safe['cipher'][1]) {$hack[1] = 'x';}
        if ($code4 === $safe['cipher'][2]) {$hack[2] = 'x';}
        if ($code4 === $safe['cipher'][3]) {$hack[3] = 'x';}

        if ($code0 === $safe['cipher'][0]) {$hack[0] = $safe['cipher'][0];}
        if ($code1 === $safe['cipher'][1]) {$hack[1] = $safe['cipher'][1];}
        if ($code2 === $safe['cipher'][2]) {$hack[2] = $safe['cipher'][2];}
        if ($code3 === $safe['cipher'][3]) {$hack[3] = $safe['cipher'][3];}
        if ($code4 === $safe['cipher'][4]) {$hack[4] = $safe['cipher'][4];}

        if (implode($safe['cipher']) === implode($hack)) {
            unset($_SESSION['safe']);
            $this->user->increment('money', 1000);
        }

        if (empty($_SESSION['safe']['try'])) {
            unset($_SESSION['safe']);
        }

        $user = $this->user;

        return view('Game::safe/go', compact('hack', 'safe', 'user'));
    }

}
