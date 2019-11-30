<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Classes\Validator;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Http\Request;
use Throwable;

class TransferController extends BaseController
{
    /**
     * @var User
     */
    public $user;

    /**
     * Конструктор
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        parent::__construct();

        if (! getUser()) {
            abort(403, __('main.not_authorized'));
        }

        $login      = check($request->input('user'));
        $this->user = getUserByLogin($login);
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
     * @throws Throwable
     */
    public function send(Request $request, Validator $validator): void
    {
        $money = int($request->input('money'));
        $msg   = check($request->input('msg'));
        $token = check($request->input('token'));

        $validator
            ->equal($token, $_SESSION['token'], ['msg' => __('validator.token')])
            ->true($this->user, ['user' => __('validator.user')])
            ->length($msg, 0, setting('comment_length'), ['msg' => __('validator.comment_long')])
            ->gte(getUser('point'), setting('sendmoneypoint'), ['money' => __('transfers.transfer_point', ['point' => plural(setting('sendmoneypoint'), setting('scorename'))])])
            ->gt($money, 0, ['money' => __('transfers.transfer_wrong_amount')])
            ->lte($money, getUser('money'), ['money' => __('transfers.transfer_not_money')]);

        if ($this->user) {
            $validator
                ->notEqual($this->user->id, getUser('id'), ['user' => __('transfers.transfer_yourself')])
                ->false($this->user->isIgnore(getUser()), ['user' => __('ignores.you_are_ignoring')]);
        }

        if ($validator->isValid()) {
            DB::connection()->transaction(function () use ($money, $msg) {
                getUser()->decrement('money', $money);
                $this->user->increment('money', $money);

                $comment = $msg ?? __('ignores.not_specified');
                $text = textNotice('transfer', ['login' => getUser('login'), 'money' => plural($money, setting('moneyname')), 'comment' => $comment]);
                $this->user->sendMessage(null, $text);

                // Запись логов
                Transfer::query()->create([
                    'user_id'      => getUser('id'),
                    'recipient_id' => $this->user->id,
                    'text'         => $comment,
                    'total'        => $money,
                    'created_at'   => SITETIME
                ]);
            });

            setFlash('success', __('transfers.transfer_success_completed'));
        } else {
            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        redirect('/transfers');
    }
}
