@extends('layout')

@section('title')
    Обратная связь
@stop

@section('content')

    <h1>Обратная связь</h1>

    <div class="form">
        <form method="post" action="/mail">

            @if (! getUser())
                Ваше имя:<br><input name="name" maxlength="20"><br>
                Ваш email:<br><input name="email" maxlength="50"><br>
            @else
                @if (empty(getUser('email')))
                    Ваш email:<br><input name="email" maxlength="50"><br>
                @endif
            @endif

            Сообщение:<br>
            <textarea cols="25" rows="5" name="message"></textarea><br>

            Проверочный код:<br>
            <img src="/captcha" onclick="this.src='/captcha?'+Math.random()" class="rounded" alt="" style="cursor: pointer;" alt=""><br>

            <input name="protect" size="6" maxlength="6"><br>
            <input value="Отправить" type="submit"></form>
    </div><br>

    Обновите страницу если вы не видите проверочный код!<br><br>

@stop
