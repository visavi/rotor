@extends('layout')

@section('title')
    Регистрация
@stop

@section('content')

    <h1>Регистрация</h1>

    Регистрация на сайте означает что вы ознакомлены и согласны с <b><a href="/rules">правилами</a></b> нашего сайта<br>
    Длина логина должна быть от 3 до 20 символов<br>
    Логин должен состоять только из знаков латинского алфавита и цифр, допустим знак дефиса!<br>

    @if (setting('regkeys'))
        <i class="fa fa-pencil-alt text-muted"></i> <span style="color:#ff0000"><b>Включено подтверждение регистрации!</b> Вам на почтовый ящик будет выслан мастер-ключ, который необходим для подтверждения регистрации!</span><br>
    @endif

    @if (!empty(setting('invite')))
        <i class="fa fa-pencil-alt text-muted"></i> <span style="color:#ff0000"><b>Включена регистрация по приглашениям!</b> Регистрация пользователей возможна только по специальным пригласительным ключам</span><br>
    @endif

    <br>

    @if (isset($_SESSION['social']))
        <div class="bg-success padding">
            <img class="img-circle border" alt="photo" src="{{ $_SESSION['social']->photo }}" style="width: 48px; height: 48px;">
            <span class="label label-primary">{{ $_SESSION['social']->network }}</span> {{ $_SESSION['social']->first_name }} {{ $_SESSION['social']->last_name }} {{ isset($_SESSION['social']->nickname) ? '('.$_SESSION['social']->nickname.')' : '' }}
        </div>
        <div class="bg-info padding" style="margin-bottom: 30px;">
            Профиль не связан с какой-либо учетной записью на сайте. Войдите на сайт или зарегистирируйтесь, чтобы связать свою учетную запись с профилем социальной сети.<br>
            Или выберите другую социальную сеть для входа.
        </div>
    @endif

    <script src="//ulogin.ru/js/ulogin.js"></script>
    <div style="padding: 5px;" id="uLogin" data-ulogin="display=panel;fields=first_name,last_name,photo;optional=sex,email,nickname;providers=vkontakte,odnoklassniki,mailru,facebook,twitter,google,yandex;redirect_uri={{ siteUrl() }}%2Flogin">
    </div>

    <div class="form">
        <form action="/register" method="post">

            <div class="form-group{{ hasError('logs') }}">
                <label for="inputLogin">Логин:</label>
                <input class="form-control" name="logs" id="inputLogin" maxlength="20" value="{{ getInput('logs') }}" required>
                {!! textError('logs') !!}
            </div>

            <div class="form-group{{ hasError('pars') }}">
                <label for="inputPassword">Пароль:</label>
                <input class="form-control" name="pars" type="password" id="inputPassword" maxlength="20" required>
                {!! textError('pars') !!}
                <span class="help-block">Минимальная длина пароля 6 символов</span>
            </div>

            <div class="form-group{{ hasError('pars2') }}">
                <label for="inputPassword2">Повтор пароля:</label>
                <input class="form-control" name="pars2" type="password" id="inputPassword2" maxlength="20" required>
                {!! textError('pars2') !!}
            </div>

            <div class="form-group{{ hasError('meil') }}">
                <label for="inputEmail">Email:</label>
                <input class="form-control" name="meil" id="inputEmail" maxlength="50" value="{{ getInput('meil') }}" required>
                {!! textError('meil') !!}
            </div>

            @if (!empty(setting('invite')))
                <div class="form-group{{ hasError('invite') }}">
                    <label for="inputInvite">Пригласительный ключ:</label>
                    <input class="form-control" name="invite" id="inputInvite" maxlength="32" value="{{ getInput('invite') }}" required>
                    {!! textError('invite') !!}
                </div>
            @endif

            <label for="inputGender">Пол:</label>
            <div class="form-group{{ hasError('gender') }}">

                <?php $inputGender = getInput('gender', 1); ?>
                <input type="radio" name="gender" id="inputGenderMale" value="1"{{ $inputGender == 1 ? ' checked' : '' }}>
                <label for="inputGenderMale">Мужской</label>

                <input type="radio" name="gender" id="inputGenderFemale" value="2"{{ $inputGender == 2 ? ' checked' : '' }}>
                <label for="inputGenderFemale">Женский</label>
                {!! textError('gender') !!}
            </div>

            <div class="form-group{{ hasError('protect') }}">
                <label for="inputProtect">Проверочный код:</label>
                <img src="/captcha" id="captcha" onclick="this.src='/captcha?'+Math.random()" class="rounded" alt="" style="cursor: pointer;">
                <input class="form-control" name="protect" id="inputProtect" maxlength="6" required>
                {!! textError('protect') !!}
            </div>

            <button class="btn btn-primary">Регистрация</button>
        </form>
    </div>
    <br>

    Все поля обязательны для заполнения, более полную информацию о себе вы можете добавить в своем профиле после регистрации<br>
    Указывайте верный email, на него будут высланы регистрационные данные<br><br>
@stop
