@extends('layout')

@section('title')
    Мои данные
@stop

@section('content')

    <h1>Мои данные</h1>

    <i class="fa fa-book"></i>
    <a href="user/{{ $user->login }}">Моя анкета</a> /
    <a href="/profile">Мой профиль</a> /
    <b>Мои данные</b> /
    <a href="/setting">Настройки</a><hr>

    <h3>Изменение email</h3>

    <div class="form">
        <form method="post" action="/account/changemail">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">
            Е-mail:<br>
            <input name="meil" maxlength="50" value="{{ $user->email }}"><br>
            Текущий пароль:<br>
            <input name="provpass" type="password" maxlength="20"><br>
            <input value="Изменить" type="submit">
        </form>
    </div><br>


    <h3>Изменение статуса</h3><br>

    @if ($user->point >= setting('editstatuspoint'))
        <div class="form">
            <form method="post" action="/account/editstatus">
                <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">
                Персональный статус:<br>
                <input name="status" maxlength="20" value="{{ $user->status }}">
                <input value="Изменить" type="submit">
            </form>

            @if (setting('editstatusmoney'))
                <br><i>Стоимость: {{ plural(setting('editstatusmoney'), setting('moneyname')) }}</i>
            @endif

        </div><br>
    @else
        {!! showError('Для изменения стасута необходимо иметь '.plural(setting('editstatuspoint'), setting('scorename')).'!') !!}
    @endif

    <h3>Изменение пароля</h3><br>

    <div class="form">
        <form method="post" action="/account/editpassword">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">
            Новый пароль:<br><input name="newpass" maxlength="20"><br>
            Повторите пароль:<br><input name="newpass2" maxlength="20"><br>
            Текущий пароль:<br><input name="oldpass" type="password" maxlength="20"><br>
            <input value="Изменить" type="submit">
        </form>
    </div><br>


    <h3>Ваш API-токен</h3><br>

    <div class="form">
        <form method="post" action="/account/apikey">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            @if ($user->apikey)
                Токен: <strong>{{ $user->apikey }}</strong><br>
                <input value="Изменить токен" type="submit">
            @else
                <input value="Получить токен" type="submit">
            @endif

        </form>

        Данный токен необходим для работы через <a href="/api">API интерфейс</a>
    </div><br>
@stop
