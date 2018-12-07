<?php

declare(strict_types=1);

namespace App\Modules\Game\Controllers;

use App\Models\User;

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

}
