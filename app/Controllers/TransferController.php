<?php

namespace App\Controllers;

use App\Classes\Validator;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Http\Request;

class TransferController extends BaseController
{
    /* @var User user */
    public $user;

    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();

        if (! getUser()) {
            abort(403, 'Для совершения операций необходимо авторизоваться');
        }
        $request    = Request::createFromGlobals();
        $login      = check($request->input('user'));
        $this->user = User::query()->where('login', $login)->first();
    }

    /**
     * Главная страница
     *
     * @return string
     */
    public function index(): string
    {
        return view('transfers/index', ['user' => $this->user]);
    }

    /**
     * Перевод денег
     *
     * @param Request   $request
     * @param Validator $validator
     * @return void
     */
    public function send(Request $request, Validator $validator): void
    {
        $money = int($request->input('money'));
        $msg   = check($request->input('msg'));
        $token = check($request->input('token'));

        $validator
            ->equal($token, $_SESSION['token'], ['msg' => 'Неверный идентификатор сессии, повторите действие!'])
            ->true($this->user, ['user' => 'Ошибка! Пользователь не найден!'])
            ->length($msg, 0, 1000, ['msg' => 'Слишком длинный комментарий!'])
            ->gte(getUser('point'), setting('sendmoneypoint'), ['money' => 'Для перевода денег вам необходимо набрать '.plural(setting('sendmoneypoint'), setting('scorename'))])
            ->gt($money, 0, ['money' => 'Перевод невозможен указана неверная сумма!'])
            ->lte($money, getUser('money'), ['money' => 'Недостаточно средств для перевода такого количества денег!']);

        if ($this->user) {
            $validator
                ->notEqual($this->user->id, getUser('id'), ['user' => 'Запрещено переводить деньги самому себе!'])
                ->false($this->user->isIgnore(getUser()), ['user' => 'Вы внесены в игнор-лист получателя!']);
        }

        if ($validator->isValid()) {

            DB::transaction(function () use ($money, $msg) {
                getUser()->decrement('money', $money);
                $this->user->increment('money', $money);
                $this->user->increment('newprivat');

                $comment = $msg ?? 'Не указано';
                $message = 'Пользователь @' . getUser('login') . ' перечислил вам ' . plural($money, setting('moneyname')) . PHP_EOL . 'Примечание: ' . $comment;

                // Уведомление по привату
                $this->user->sendMessage(getUser(), $message);

                // Запись логов
                Transfer::query()->create([
                    'user_id'      => getUser('id'),
                    'recipient_id' => $this->user->id,
                    'text'         => $comment,
                    'total'        => $money,
                    'created_at'   => SITETIME
                ]);
            });

            setFlash('success', 'Перевод успешно завершен!');
        } else {
            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        redirect('/transfers');
    }
}
