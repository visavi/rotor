@extends('layout')

@section('title')
    Игра
@stop

@section('content')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/games">Игры / Развлечения</a></li>
            <li class="breadcrumb-item"><a href="/games/thimbles">Наперстки</a></li>
            <li class="breadcrumb-item"><a href="/games/thimbles/choice">Выбор наперстка</a></li>
            <li class="breadcrumb-item active">Игра</li>
        </ol>
    </nav>

    <h1>Игра</h1>

<?php /*

        $thimble = intval($_GET['thimble']);
        if (!isset($_SESSION['naperstki'])) {
            $_SESSION['naperstki'] = 0;
        }

            if ($_SESSION['naperstki'] < 3) {
                $_SESSION['naperstki']++;

                $rand_thimble = mt_rand(1, 3);

                if ($rand_thimble == 1) {
                    echo '<img src="/assets/img/naperstki/3.gif" alt="image" /> ';
                } else {
                    echo '<img src="/assets/img/naperstki/2.gif" alt="image" /> ';
                }

                if ($rand_thimble == 2) {
                    echo '<img src="/assets/img/naperstki/3.gif" alt="image" /> ';
                } else {
                    echo '<img src="/assets/img/naperstki/2.gif" alt="image" /> ';
                }

                if ($rand_thimble == 3) {
                    echo '<img src="/assets/img/naperstki/3.gif" alt="image" />';
                } else {
                    echo '<img src="/assets/img/naperstki/2.gif" alt="image" />';
                }
                // ------------------------------ Выигрыш ----------------------------//
                if ($thimble == $rand_thimble) {
                    DB::run()->query("UPDATE users SET money=money+100 WHERE login=?", [$log]);

                    echo '<br /><b>Вы выиграли!</b><br />';
                    // ------------------------------ Проигрыш ----------------------------//
                } else {
                    DB::run()->query("UPDATE users SET money=money-50 WHERE login=?", [$log]);

                    echo '<br /><b>Вы проиграли!</b><br />';
                }
            } else {
                show_error('Необходимо выбрать один из наперстков');
            }

            echo '<br /><b><a href="/games/naperstki?act=choice&amp;rand=' . $rand . '">К выбору</a></b><br /><br />';

            $allmoney = DB::run()->querySingle("SELECT money FROM users WHERE login=?;", [$log]);
            echo 'У вас в наличии: ' . moneys($allmoney) . '<br /><br />';



 */ ?>

    У вас в наличии: {{ plural($user->money, setting('moneyname')) }}<br><br>
@stop
