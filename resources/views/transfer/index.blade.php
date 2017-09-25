@extends('layout')

@section('title')
    Перевод денег - @parent
@stop

@section('content')

    <h1>Перевод денег</h1>

    В наличии: {{ plural(getUser('money'), setting('moneyname')) }}<br><br>

    @if (getUser('point') >= setting('sendmoneypoint'))
        @if ($user)
            <div class="form">
                Перевод для <b>{{ $user->login }}</b>:<br><br>
                <form action="/transfer/send?user={{ $user->login }}" method="post">
                    <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">
                    Кол-во денег:<br>
                    <input type="text" name="money"><br>
                    Примечание:<br>
                    <textarea cols="25" rows="5" name="msg"></textarea><br>
                    <input type="submit" value="Перевести">
                </form>
            </div><br>
        @else
            <div class="form">
                <form action="/transfer/send" method="post">
                    <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">
                    Логин юзера:<br>
                    <input type="text" name="user" maxlength="20"><br>
                    Кол-во денег:<br>
                    <input type="text" name="money"><br>
                    Примечание:<br>
                    <textarea cols="25" rows="5" name="msg"></textarea><br>
                    <input type="submit" value="Перевести">
                </form>
            </div><br>
        @endif
    @else
       {{ showError('Ошибка! Для перевода денег вам необходимо набрать '.plural(setting('sendmoneypoint'), setting('scorename')).'!') }}
    @endif

@stop
