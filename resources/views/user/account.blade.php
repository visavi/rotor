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

    <div class="form mb-4">
        <form method="post" action="/account/changemail">
        <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('email') }}">
                <label for="email">Е-mail:</label>
                <input class="form-control" id="email" name="email" maxlength="50" value="{{ getInput('email', $user->email) }}">
                {!! textError('email') !!}
            </div>

            <div class="form-group{{ hasError('password') }}">
                <label for="password">Текущий пароль:</label>
                <input class="form-control" type="password" id="password" name="password" maxlength="20">
                {!! textError('password') !!}
            </div>

            <button class="btn btn-primary">Изменить</button>
        </form>

        <span class="text-muted font-italic">После изменения, новый email необходимо подтвердить</span>
    </div>


    <h3>Изменение статуса</h3>

    @if ($user->point >= setting('editstatuspoint'))
        <div class="form mb-4">
            <form method="post" action="/account/editstatus">
                <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

                <label for="status">Персональный статус:</label>
                <div class="form-inline">
                    <div class="form-group{{ hasError('status') }}">
                        <input type="text" class="form-control" id="status" name="status" maxlength="20" value="{{ getInput('status', $user->status) }}">
                    </div>

                    <button class="btn btn-primary">Изменить</button>
                </div>
                {!! textError('status') !!}
            </form>

            @if (setting('editstatusmoney'))
                <span class="text-muted font-italic">Стоимость: {{ plural(setting('editstatusmoney'), setting('moneyname')) }}</span>
            @endif

        </div>
    @else
        {!! showError('Для изменения стасута необходимо иметь '.plural(setting('editstatuspoint'), setting('scorename')).'!') !!}
    @endif

    <h3>Изменение пароля</h3>

    <div class="form mb-4">
        <form method="post" action="/account/editpassword">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('newpass') }}">
                <label for="newpass">Новый пароль:</label>
                <input class="form-control" id="newpass" name="newpass" maxlength="20" value="{{ getInput('newpass') }}">
                {!! textError('email') !!}
            </div>

            <div class="form-group{{ hasError('newpass2') }}">
                <label for="newpass2">Повторите пароль:</label>
                <input class="form-control" id="newpass2" name="newpass2" maxlength="20" value="{{ getInput('newpass2') }}">
                {!! textError('newpass2') !!}
            </div>

            <div class="form-group{{ hasError('oldpass') }}">
                <label for="oldpass">Текущий пароль:</label>
                <input class="form-control" type="password" id="oldpass" name="oldpass" maxlength="20">
                {!! textError('oldpass') !!}
            </div>

            <button class="btn btn-primary">Изменить</button>
        </form>
    </div>


    <h3>Ваш API-токен</h3>

    <div class="form mb-4">
        <form method="post" action="/account/apikey">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            @if ($user->apikey)
                Токен: <br>
                <strong>{{ $user->apikey }}</strong><br>
                <button class="btn btn-primary">Изменить токен</button>
            @else
                <button class="btn btn-primary">Получить токен</button>
            @endif
        </form>

        <span class="text-muted font-italic">
            Данный токен необходим для работы через <a href="/api">API интерфейс</a>
        </span>
    </div>
@stop
