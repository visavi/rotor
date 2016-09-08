@extends('layout')

@section('title', 'Регистрация - @parent')

@section('content')
    Регистрация на сайте означает что вы ознакомлены и согласны с <b><a href="/rules">правилами</a></b> нашего сайта<br />
    Длина логина или пароля должна быть от 3 до 20 символов<br />
    В полях логин и пароль разрешено использовать только знаки латинского алфавита и цифры, а также знак дефис!<br />

    @if ($config['regkeys'] == 1 && ! empty($config['regmail']))
        <i class="fa fa-pencil text-muted"></i> <span style="color:#ff0000"><b>Включено подтверждение регистрации!</b> Вам на почтовый ящик будет выслан мастер-ключ, который необходим для подтверждения регистрации!</span><br />
    @endif

    @if ($config['regkeys'] == 2)
        <i class="fa fa-pencil text-muted"></i> <span style="color:#ff0000"><b>Включена модерация регистрации!</b> Ваш аккаунт будет активирован только после проверки администрацией!</span><br />
    @endif

    @if (!empty($config['invite']))
        <i class="fa fa-pencil text-muted"></i> <span style="color:#ff0000"><b>Включена регистрация по приглашениям!</b> Регистрация пользователей возможна только по специальным пригласительным ключам</span><br />
    @endif

    <br /><div class="form">
        <form action="/register" method="post">

            <div class="form-group{{ App::hasError('logs') }}">
                <label for="inputLogin">Логин:</label>
                <input class="form-control" name="logs" id="inputLogin" maxlength="20" value="{{ App::getInput('logs') }}" required>
                {!! App::textError('logs') !!}
            </div>

            <div class="form-group{{ App::hasError('pars') }}">
                <label for="inputPassword">Пароль:</label>
                <input class="form-control" name="pars" type="password" id="inputPassword" maxlength="20" required>
                {!! App::textError('pars') !!}
                <span class="help-block">Минимальная длина пароля 6 символов</span>
            </div>

            <div class="form-group{{ App::hasError('pars2') }}">
                <label for="inputPassword2">Повтор пароля:</label>
                <input class="form-control" name="pars2" type="password" id="inputPassword2" maxlength="20" required>
                {!! App::textError('pars2') !!}
            </div>

            @if (!empty($config['regmail']))
                <div class="form-group{{ App::hasError('meil') }}">
                    <label for="inputEmail">Email:</label>
                    <input class="form-control" name="meil" id="inputEmail" maxlength="50" value="{{ App::getInput('meil') }}" required>
                    {!! App::textError('meil') !!}
                </div>
            @endif

            @if (!empty($config['invite']))
                <div class="form-group{{ App::hasError('invite') }}">
                    <label for="inputInvite">Пригласительный ключ:</label>
                    <input class="form-control" name="invite" id="inputInvite" maxlength="32" value="{{ App::getInput('invite') }}" required>
                    {!! App::textError('invite') !!}
                </div>
            @endif

            <label for="inputGender">Пол:</label>
            <div class="form-group{{ App::hasError('gender') }}">

                <input type="radio" name="gender" id="inputGenderMale" value="1"{{ (!App::getInput('gender') || App::getInput('gender') == 1 ? ' checked' : '') }}>
                <label for="inputGenderMale">Мужской</label>

                <input type="radio" name="gender" id="inputGenderFemale" value="2"{{ (App::getInput('gender') == 2 ? ' checked' : '') }}>
                <label for="inputGenderFemale">Женский</label>
                {!! App::textError('gender') !!}
            </div>

            <div class="form-group{{ App::hasError('protect') }}">
                <label for="inputProtect">Проверочный код:</label>
                <img src="/captcha" id="captcha" onclick="this.src='/captcha?'+Math.random()" class="img-rounded" alt="" style="cursor: pointer;">
                <input class="form-control" name="protect" id="inputProtect" maxlength="6" required>
                {!! App::textError('protect') !!}
            </div>

            <button type="submit" class="btn btn-primary">Регистрация</button>
        </form>
    </div>
    <br />

    Все поля обязательны для заполнения, более полную информацию о себе вы можете добавить в своем профиле после регистрации<br />
    Указывайте верный е-мэйл, на него будут высланы регистрационные данные<br /><br />
@stop
