<?php

namespace App\Controllers;

use App\Classes\Request;
use App\Classes\Validation;
use App\Classes\Validator;
use App\Models\User;
use Illuminate\Database\Capsule\Manager as DB;

class TransferController extends BaseController
{
    public $user;

    public function __construct()
    {
        parent::__construct();

        if (! getUser()) {
            abort(403, 'Для совершения операций необходимо авторизоваться');
        }

        $login = check(Request::input('user'));
        $this->user = User::query()->where('login', $login)->first();
    }

    /**
     * Главная страница
     */
    public function index()
    {
        return view('transfer/index', ['user' => $this->user]);
    }

    /**
     * Перевод денег
     */
    public function send()
    {
        $money = abs(intval(Request::input('money')));
        $msg   = check(Request::input('msg'));
        $token = check(Request::input('token'));


        $validator = new Validator();
        $validator
            ->equal($token, $_SESSION['token'], ['msg' => 'Неверный идентификатор сессии, повторите действие!'])
            ->equal(3, 4, ['msg' => 'Неверный идентификатор сессии, повторите действие!'])
            ->greaterThan($money, 0, ['money' => 'Перевод невозможен указана неверная сумма!'])
            ->greaterThan(6, 7, ['money' => '2Перевод невозможен указана неверная сумма!'])
            ->greaterThan(6, 3, ['money' => '3Перевод невозможен указана неверная сумма!'])
            ;


        var_dump($validator->getErrors());
        var_dump($validator->isValid());
        $validator->greaterThanOrEqual(4, 5, ['money' => '3Перевод невозможен указана неверная сумма!']);
        $validator->length($msg, 50, 1000, ['money' => 'атата'], true);
        var_dump($validator->getErrors());
        var_dump($validator->isValid());

        exit;



























        $validation = new Validation();
        $validation->addRule('equal', [$token, $_SESSION['token']], ['msg' => 'Неверный идентификатор сессии, повторите действие!'])
            ->addRule('bool', $this->user, 'Ошибка! Пользователь не найден!')
            ->addRule('string', $msg, ['msg' => 'Слишком длинное сообщение, не более 1000 символов!'], true, 0, 1000)
            ->addRule('max', [getUser('point'), setting('sendmoneypoint')], 'Для перевода денег вам необходимо набрать '.plural(setting('sendmoneypoint'), setting('scorename')))
            ->addRule('max', [$money, 0], 'Перевод невозможен указана неверная сумма!')
            ->addRule('min', [$money, getUser('money')], 'Недостаточно средств для перевода такого количества денег!');

        if ($this->user) {
            $validation
                ->addRule('not_equal', [$this->user->id, getUser('id')], ['user' => 'Запещено переводить деньги самому себе!'])
                ->addRule('custom', ! isIgnore($this->user, getUser()), 'Вы внесены в игнор-лист получателя!');
        }

        var_dump($validation->getErrors()); exit;

        if ($validation->run()) {

            setFlash('success', 'Перевод успешно завершен!');
        } else {
            setInput(Request::all());
            setFlash('danger', $validation->getErrors());
        }

        redirect("/transfer");


                                        DB::update("UPDATE `users` SET `money`=`money`-? WHERE `login`=?;", [$money, getUser('login')]);
                                        DB::update("UPDATE `users` SET `money`=`money`+?, `newprivat`=`newprivat`+1 WHERE `login`=?;", [$money, $uz]);

                                        $comment = (!empty($msg)) ? $msg : 'Не указано';
                                        // ------------------------Уведомление по привату------------------------//
                                        $textpriv = 'Пользователь [b]'.getUser('login').'[/b] перечислил вам '.plural($money, setting('moneyname')).''.PHP_EOL.'Примечание: '.$comment;

                                        DB::insert("INSERT INTO `inbox` (`user`, `author`, `text`, `time`) VALUES (?, ?, ?, ?);", [$uz, getUser('login'), $textpriv, SITETIME]);
                                        // ------------------------ Запись логов ------------------------//
                                        DB::insert("INSERT INTO `transfers` (`user`, `login`, `text`, `summ`, `time`) VALUES (?, ?, ?, ?, ?);", [getUser('login'), $uz, $comment, $money, SITETIME]);

                                        DB::delete("DELETE FROM `transfers` WHERE `time` < (SELECT MIN(`time`) FROM (SELECT `time` FROM `transfers` ORDER BY `time` DESC LIMIT 1000) AS del);");

                                        setFlash('success', 'Перевод успешно завершен! Пользователь уведомлен о переводе');
                                        redirect("/games/transfer");






    }
}
