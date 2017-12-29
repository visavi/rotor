<?php

namespace App\Controllers\Admin;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\User;

class DeliveryController extends AdminController
{
    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();

        if (! isAdmin(User::BOSS)) {
            abort(403, 'Доступ запрещен!');
        }
    }

    /**
     * Главная страница
     */
    public function index()
    {
        if (Request::isMethod('post')) {

            $token = check(Request::input('token'));
            $msg   = check(Request::input('msg'));
            $type  = int(Request::input('type'));

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], ['msg' => 'Неверный идентификатор сессии, повторите действие!'])
                ->length($msg, 5, 1000, ['msg' => 'Слишком длинный или короткий текст комментария!'])
                ->between($type, 1, 4, 'Вы не выбрали получаетелей рассылки!');




            exit;
            // Рассылка пользователям, которые в онлайне
            if ($rec==1){
                $query = DB::select("SELECT `user` FROM `visit` WHERE `nowtime`>?;", [SITETIME-600]);
                $arrusers = $query -> fetchAll(PDO::FETCH_COLUMN);
            }

            // Рассылка активным пользователям, которые посещали сайт менее недели назад
            if ($rec==2){
                $query = DB::select("SELECT `login` FROM `users` WHERE `timelastlogin`>?;", [SITETIME - (86400 * 7)]);
                $arrusers = $query->fetchAll(PDO::FETCH_COLUMN);
            }

            // Рассылка администрации
            if ($rec==3){
                $query = DB::select("SELECT `login` FROM `users` WHERE `level`>=? AND `level`<=?;", [101, 105]);
                $arrusers = $query->fetchAll(PDO::FETCH_COLUMN);
            }

            // Рассылка всем пользователям сайта
            if ($rec==4){
                $query = DB::select("SELECT `login` FROM `users`;");
                $arrusers = $query->fetchAll(PDO::FETCH_COLUMN);
            }

            $arrusers = array_diff($arrusers, [getUser('login')]);
            $total = count($arrusers);

            // Рассылка сообщений с подготовкой запросов
            if ($total>0){

                $updateusers = DB::run() -> prepare("UPDATE `users` SET `newprivat`=`newprivat`+1 WHERE `login`=? LIMIT 1;");
                $insertprivat = DB::run() -> prepare("INSERT INTO `inbox` (`user`, `author`, `text`, `time`) VALUES (?, ?, ?, ?);");

                foreach ($arrusers as $uzval){
                    $updateusers -> execute($uzval);
                    $insertprivat -> execute($uzval, getUser('login'), $msg, SITETIME);
                }

                setFlash('success', 'Сообщение успешно разослано! (Отправлено: '.$total.')');
                redirect("/admin/delivery");

            } else {
                showError('Ошибка! Отсутствуют получатели рассылки!');
            }



            if ($validator->isValid()) {
                setFlash('success', 'Сообщение успешно разослано!');
                redirect('/admin/delivery');
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/delivery/index');
    }
}
