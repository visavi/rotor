<?php

declare(strict_types=1);

namespace App\Modules\Game\Controllers;

use App\Classes\Validator;
use App\Models\User;
use Illuminate\Http\Request;

class BlackjackController extends \App\Controllers\BaseController
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
     * Очко
     *
     * @return string
     */
    public function index(): string
    {
        return view('Game::blackjack/index', ['user' => $this->user]);
    }

    /**
     * Ставка
     *
     * @param Request   $request
     * @param Validator $validator
     * @return void
     */
    public function bet(Request $request, Validator $validator): void
    {
        $bet   = int($request->input('bet'));
        $token = check($request->input('token'));

        if (! empty($_SESSION['blackjack']['bet'])) {
            redirect('/games/blackjack/game');
        }

        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
            ->gt($bet, 0, ['bet' => 'Вы не указали ставку!'])
            ->gte($this->user->money, $bet, ['bet' => 'У вас недостаточно денег для игры!']);


        if ($validator->isValid()) {
            $_SESSION['blackjack']['bet'] = $bet;

            $this->user->decrement('money', $bet);

            setFlash('success', 'Ставка сделана!');
            redirect('/games/blackjack/game?rand=' . mt_rand(1000, 99999));
        } else {
            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        redirect('/games/blackjack');
    }

    /**
     * Игра
     *
     * @param Request $request
     * @return string
     */
    public function game(Request $request): string
    {
        $case = check($request->input('case'));

        $results = [
            'victory' => '<span class="text-success">Вы выиграли</span>',
            'lost'    => '<span class="text-danger">Вы проиграли</span>',
            'draw'    => 'Ничья',
        ];

        if (empty($_SESSION['blackjack']['bet'])) {
            setFlash('danger', 'Необходимо сделать ставку!');
            redirect('/games/blackjack');
        }

        $scores = $this->takeCard($case);

        $text   = false;
        $result = false;

        if ($case === 'end') {
            if ($scores['user'] > $scores['banker']) {
                $result = $results['victory'];
            }
            if ($scores['user'] < $scores['banker']) {
                $result = $results['lost'];
            }
            if ($scores['user'] === $scores['banker']) {
                $result = $results['draw'];
            }
            if ($scores['banker'] > 21) {
                $result = $results['victory'];
            }
        }

        if ($scores['user'] > 21 && $scores['userCards'] !== 2) {
            $text = 'У вас перебор!';
            $result = $results['lost'];
        }
        if ($scores['user'] === 22 && $scores['userCards'] === 2) {
            $text = 'У вас 2 туза!';
            $result = $results['victory'];
        }
        if ($scores['banker'] === 22 && $scores['bankerCards'] === 2) {
            $text = 'У банкира 2 туза!';
            $result = $results['lost'];
        }
        if ($scores['user'] === 21) {
            $text = 'У вас очко!';
            $result = $results['victory'];
        }
        if ($scores['banker'] === 21) {
            $text = 'У банкира очко!';
            $result = $results['lost'];
        }
        if (($scores['user'] === 21 && $scores['banker'] === 21) || ($scores['user'] === 22 && $scores['banker'] === 22)) {
            $result = $results['draw'];
        }

        $blackjack = $_SESSION['blackjack'];

        if ($result) {
            if ($result === $results['victory']) {
                $this->user->increment('money', $blackjack['bet'] * 2);
            } elseif ($result === $results['draw']) {
                $this->user->increment('money', $blackjack['bet']);
            }

            unset($_SESSION['blackjack']);
        }

        $user = $this->user;

        return view('Game::blackjack/game', compact('user', 'blackjack', 'scores', 'result', 'text'));
    }

    /**
     * Правила игры
     *
     * @return string
     */
    public function rules(): string
    {
        return view('Game::blackjack/rules');
    }

    /**
     * Подсчитывает очки карт
     *
     * @param array $cards
     * @return int
     */
    private function cardsScore(array $cards): int
    {
        $score = [];

        foreach ($cards as $card) {
            if ($card > 48) {
                $score[] =  11;
                continue;
            }

            if ($card > 36) {
                $score[] = (int) (($card - 1) / 4) - 7;
                continue;
            }

            $score[] = (int) (($card - 1) / 4) + 2;
        }

        return array_sum($score);
    }

    /**
     * Взятие карты
     *
     * @param string $case
     * @return array
     */
    private function takeCard($case): array
    {
        $rand = mt_rand(16, 18);

        if (empty($_SESSION['blackjack']['deck'])) {
            $_SESSION['blackjack']['deck'] = array_combine(range(1, 52), range(1, 52));
        }

        if (empty($_SESSION['blackjack']['cards'])) {
            $_SESSION['blackjack']['cards'] = [];
            $case = 'take';
        }

        if (empty($_SESSION['blackjack']['bankercards'])) {
            $_SESSION['blackjack']['bankercards'] = [];
        }

        if ($case === 'take') {
            $card = array_rand($_SESSION['blackjack']['deck']);
            $_SESSION['blackjack']['cards'][] = $card;
            unset($_SESSION['blackjack']['deck'][$card]);

            if ($this->cardsScore($_SESSION['blackjack']['bankercards']) < $rand) {
                $card2 = array_rand($_SESSION['blackjack']['deck']);
                $_SESSION['blackjack']['bankercards'][] = $card2;
                unset($_SESSION['blackjack']['deck'][$card2]);
            }
        }

        if ($case === 'end') {
            while ($this->cardsScore($_SESSION['blackjack']['bankercards']) < $rand) {
                $card2 = array_rand($_SESSION['blackjack']['deck']);
                $_SESSION['blackjack']['bankercards'][] = $card2;
                unset($_SESSION['blackjack']['deck'][$card2]);
            }
        }

        return [
            'user'        => $this->cardsScore($_SESSION['blackjack']['cards']),
            'userCards'   => count($_SESSION['blackjack']['cards']),
            'banker'      => $this->cardsScore($_SESSION['blackjack']['bankercards']),
            'bankerCards' => count($_SESSION['blackjack']['bankercards']),
        ];
    }
}
