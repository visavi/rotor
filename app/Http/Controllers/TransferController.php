<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Classes\Validator;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TransferController extends Controller
{
    public ?User $user;

    /**
     * Конструктор
     */
    public function __construct()
    {
        $this->middleware('check.user');

        $this->middleware(function ($request, $next) {
            $login = $request->input('user');
            $this->user = getUserByLogin($login);

            return $next($request);
        });
    }

    /**
     * Главная страница
     */
    public function index(): View
    {
        return view('transfers/index', ['user' => $this->user]);
    }

    /**
     * Перевод денег
     */
    public function send(Request $request, Validator $validator): RedirectResponse
    {
        $money = int($request->input('money'));
        $msg = $request->input('msg');

        $validator
            ->equal($request->input('_token'), csrf_token(), ['msg' => __('validator.token')])
            ->true($this->user, ['user' => __('validator.user')])
            ->length($msg, 0, setting('comment_text_max'), ['msg' => __('validator.comment_long')])
            ->gte(getUser('point'), setting('sendmoneypoint'), ['money' => __('transfers.transfer_point', ['point' => plural(setting('sendmoneypoint'), setting('scorename'))])])
            ->gt($money, 0, ['money' => __('transfers.transfer_wrong_amount')])
            ->lte($money, getUser('money'), ['money' => __('transfers.transfer_not_money')]);

        if ($this->user) {
            $validator
                ->notEqual($this->user->id, getUser('id'), ['user' => __('transfers.transfer_yourself')])
                ->false($this->user->isIgnore(getUser()), ['user' => __('ignores.you_are_ignoring')]);
        }

        if ($validator->isValid()) {
            DB::transaction(function () use ($money, $msg) {
                getUser()->decrement('money', $money);
                $this->user->increment('money', $money);

                $comment = $msg ?? __('main.not_specified');
                $text = textNotice('transfer', ['login' => getUser('login'), 'money' => plural($money, setting('moneyname')), 'comment' => $comment]);
                $this->user->sendMessage(null, $text);

                // Запись логов
                Transfer::query()->create([
                    'user_id'      => getUser('id'),
                    'recipient_id' => $this->user->id,
                    'text'         => $comment,
                    'total'        => $money,
                    'created_at'   => SITETIME,
                ]);
            });

            setFlash('success', __('transfers.transfer_success_completed'));
        } else {
            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return redirect('transfers');
    }
}
