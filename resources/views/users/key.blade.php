@extends('layout')

@section('title')
    Подтверждение регистрации
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">Подтверждение регистрации</li>
        </ol>
    </nav>
@stop

@section('content')
    Добро пожаловать, <b>{{ getUser('login') }}!</b><br>
    Для подтверждения регистрации вам необходимо ввести код, который был отправлен вам на email<br><br>

    <div class="form">
        <label for="code">Код подтверждения:</label>
        <form method="get" action="/key">
            <input class="form-control" name="code" id="code" maxlength="30" required>
            <button class="btn btn-primary">Подтвердить</button>
        </form>
    </div><br>

    <?php $checkEmail = getInput('email') ? true : false; ?>
    <?php $display = $checkEmail ? '' : ' style="display: none"'; ?>

    <div class="js-resending-form"{!! $display !!}>
        <div class="form">
            <form method="post" action="/key">
                <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

                <div class="form-group{{ hasError('email') }}">
                    <label for="email">Email:</label>
                    <input class="form-control" name="email" id="email" maxlength="50" value="{{ getInput('email', $user->email) }}" required>
                    {!! textError('email') !!}
                </div>

                {!! view('app/_captcha') !!}
                <button class="btn btn-primary">Повторить отправку</button>
            </form>
        </div><br>

        <p class="text-muted font-italic">
            При повторной отправке старый код подтверждения станет недействительным
        </p>
    </div>

    @if (! $checkEmail)
        <div class="js-resending-link">
            <i class="fas fa-redo"></i> <a href="#" onclick="return resendingCode(this);">Отправить код повторно</a>
        </div>
    @endif

    <p class="text-muted font-italic">
        Пока вы не подтвердите регистрацию вы не сможете войти на сайт<br>
        Активацию аккаунта необходимо произвести в течение 24 часов<br>
        После 24 часов неподтвержденные аккаунты автоматически удаляются
    </p>

    <i class="fa fa-times"></i> <a href="/logout?token={{ $_SESSION['token'] }}">Выход</a><br>
@stop
