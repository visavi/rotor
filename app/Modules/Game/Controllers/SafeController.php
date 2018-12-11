<?php

declare(strict_types=1);

namespace App\Modules\Game\Controllers;

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
     * @param Request $request
     * @return string
     */
    public function go(Request $request): string
    {
        $token = check($request->input('token'));
        $code0 = int($request->input('code0'));
        $code1 = int($request->input('code1'));
        $code2 = int($request->input('code2'));
        $code3 = int($request->input('code3'));

        if ($this->user->money < 100) {
            abort('default', 'Вы не можете играть! У вас недостаточно средств!');
        }

        if (empty($_SESSION['safe']['code'])) {
            $_SESSION['safe']['code'] = sprintf('%04d', mt_rand(0, 9999));
            $_SESSION['safe']['try'] = 5;
            $this->user->decrement('money', 100);
        }

        $_SESSION['safe']['try']--;
        $split = array_map('intval', str_split($_SESSION['safe']['code']));

        $safe = [$split[0], $split[1], $split[2], $split[3]];
        $hack = ['-', '-', '-', '-'];


        if ($code0 === $safe[0] || $code0 === $safe[1] || $code0 === $safe[2] || $code0 === $safe[3] ) {$g1 = '*';} else {$g1 = '-';}
        if ($code1 === $safe[0] || $code1 === $safe[1] || $code1 === $safe[2] || $code1 === $safe[3] ) {$g2 = '*';} else {$g2 = '-';}
        if ($code2 === $safe[0] || $code2 === $safe[1] || $code2 === $safe[2] || $code2 === $safe[3] ) {$g3 = '*';} else {$g3 = '-';}
        if ($code3 === $safe[0] || $code3 === $safe[1] || $code3 === $safe[2] || $code3 === $safe[3] ) {$g4 = '*';} else {$g4 = '-';}

        if ($code0 === $safe[0]) {$g1=$safe[0];}
        if ($code1 === $safe[1]) {$g2=$safe[1];}
        if ($code2 === $safe[2]) {$g3=$safe[2];}
        if ($code3 === $safe[3]) {$g4=$safe[3];}

var_dump($g1, $g2, $g3, $g4);



        if ($code0 === $safe[1]) {$hack[1] = 'x';}
        if ($code0 === $safe[2]) {$hack[2] = 'x';}
        if ($code0 === $safe[3]) {$hack[3] = 'x';}
        if ($code0 === $safe[1] && $code0 === $safe[2]) {$hack[1] = 'x'; $hack[2] = 'x';}
        if ($code0 === $safe[1] && $code0 === $safe[3]) {$hack[1] = 'x'; $hack[3] = 'x';}
        if ($code0 === $safe[3] && $code0 === $safe[2]) {$hack[3] = 'x'; $hack[2] = 'x';}
        if ($code0 === $safe[1] && $code0 === $safe[2] && $code0 === $safe[3]) {$hack[1] = 'x'; $hack[2] = 'x'; $hack[3] = 'x';}
        if ($code1 === $safe[0]) {$hack[0] = 'x';}
        if ($code1 === $safe[2]) {$hack[2] = 'x';}
        if ($code1 === $safe[3]) {$hack[3] = 'x';}
        if ($code1 === $safe[0] && $code1 === $safe[2]) {$hack[0] = 'x'; $hack[2] = 'x';}
        if ($code1 === $safe[1] && $code1 === $safe[3]) {$hack[0] = 'x'; $hack[3] = 'x';}
        if ($code1 === $safe[3] && $code1 === $safe[2]) {$hack[3] = 'x'; $hack[2] = 'x';}
        if ($code1 === $safe[0] && $code1 === $safe[2] && $code1 === $safe[3]) {$hack[0] = 'x'; $hack[2] = 'x'; $hack[3] = 'x';}
        if ($code2 === $safe[0]) {$hack[0] = 'x';}
        if ($code2 === $safe[1]) {$hack[1] = 'x';}
        if ($code2 === $safe[3]) {$hack[3] = 'x';}
        if ($code2 === $safe[0] && $code2 === $safe[1]) {$hack[0] = 'x'; $hack[1] = 'x';}
        if ($code2 === $safe[1] && $code2 === $safe[3]) {$hack[0] = 'x'; $hack[3] = 'x';}
        if ($code2 === $safe[3] && $code2 === $safe[1]) {$hack[3] = 'x'; $hack[1] = 'x';}
        if ($code2 === $safe[0] && $code2 === $safe[1] && $code2 === $safe[3]) {$hack[0] = 'x'; $hack[1] = 'x'; $hack[3] = 'x';}
        if ($code3 === $safe[0]) {$hack[0] = 'x';}
        if ($code3 === $safe[1]) {$hack[1] = 'x';}
        if ($code3 === $safe[2]) {$hack[2] = 'x';}
        if ($code3 === $safe[0] && $code3 === $safe[1]) {$hack[0] = 'x'; $hack[1] = 'x';}
        if ($code3 === $safe[1] && $code3 === $safe[2]) {$hack[0] = 'x'; $hack[2] = 'x';}
        if ($code3 === $safe[2] && $code3 === $safe[1]) {$hack[2] = 'x'; $hack[1] = 'x';}
        if ($code3 === $safe[0] && $code3 === $safe[1] && $code3 === $safe[3]) {$hack[0] = 'x'; $hack[1] = 'x'; $hack[2] = 'x';}
        if ($code0 === $safe[0]) {$hack[0] = $safe[0];}
        if ($code1 === $safe[1]) {$hack[1] = $safe[1];}
        if ($code2 === $safe[2]) {$hack[2] = $safe[2];}
        if ($code3 === $safe[3]) {$hack[3] = $safe[3];}

        $user = $this->user;

        var_dump($hack, $safe, $code0, $code1, $code2, $code3);

        return view('Game::safe/go', compact('hack', 'safe', 'user'));
    }

}
