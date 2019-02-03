@extends('layout')

@section('title')
    Восстановление пароля
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">Восстановление пароля</li>
        </ol>
    </nav>
@stop

@section('content')


    <div class="form">
        <form method="post" action="/recovery">

            <div class="form-group{{ hasError('user') }}">
                <label for="inputUser">Логин или email:</label>
                <input class="form-control" name="user" id="inputUser" value="{{ getInput('user', $cookieLogin) }}" maxlength="100" required>
                {!! textError('user') !!}
            </div>

            {!! view('app/_captcha') !!}

            <button class="btn btn-primary">Восстановить</button>
        </form>
    </div><br>

    <p class="text-muted font-italic">
        Если по какой-то причине письмо не приходит или утрачен доступ к почтовому ящику, то вам необходимо связаться с <a href="/mails">поддержкой</a>
    </p>

    Письмо с инструкцией по восстановлению пароля будет выслано на email указанный в профиле<br>
    Внимательно прочтите письмо и выполните все необходимые действия<br>
    Отправлять данные для восстанавления пароля можно не чаще чем раз в час<br>
@stop
