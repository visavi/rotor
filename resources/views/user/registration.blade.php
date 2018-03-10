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
            <span class="badge badge-primary">{{ $_SESSION['social']->network }}</span> {{ $_SESSION['social']->first_name }} {{ $_SESSION['social']->last_name }} {{ isset($_SESSION['social']->nickname) ? '('.$_SESSION['social']->nickname.')' : '' }}
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

            <div class="form-group{{ hasError('login') }}">
                <label for="inputLogin">Логин:</label>
                <input class="form-control" name="login" id="inputLogin" maxlength="20" value="{{ getInput('login') }}" required>
                {!! textError('login') !!}
                <span class="text-muted font-italic">Только символы латинского алфавита и цифры</span>
            </div>

            <div class="form-group{{ hasError('password') }}">
                <label for="inputPassword">Пароль:</label>
                <input class="form-control" name="password" type="password" id="inputPassword" maxlength="20" required>
                {!! textError('password') !!}
                <span class="text-muted font-italic">Минимальная длина пароля 6 символов</span>
            </div>

            <div class="form-group{{ hasError('password2') }}">
                <label for="inputPassword2">Повтор пароля:</label>
                <input class="form-control" name="password2" type="password" id="inputPassword2" maxlength="20" required>
                {!! textError('password2') !!}
            </div>

            <div class="form-group{{ hasError('email') }}">
                <label for="inputEmail">Email:</label>
                <input class="form-control" name="email" id="inputEmail" maxlength="50" value="{{ getInput('email') }}" required>
                {!! textError('email') !!}
            </div>

            @if (setting('invite'))
                <div class="form-group{{ hasError('invite') }}">
                    <label for="inputInvite">Пригласительный ключ:</label>
                    <input class="form-control" name="invite" id="inputInvite" maxlength="32" value="{{ getInput('invite') }}" required>
                    {!! textError('invite') !!}
                </div>
            @endif

            <?php $inputGender = getInput('gender', 'male'); ?>
            Пол:
            <div class="form-group{{ hasError('gender') }}">
                <div class="custom-control custom-radio">
                    <input class="custom-control-input" type="radio" id="inputGenderMale" name="gender" value="male"{{ $inputGender == 'male' ? ' checked' : '' }}>
                    <label class="custom-control-label" for="inputGenderMale">Мужской</label>
                </div>
                <div class="custom-control custom-radio">
                    <input class="custom-control-input" type="radio" id="inputGenderFemale" name="gender" value="female"{{ $inputGender == 'female' ? ' checked' : '' }}>
                    <label class="custom-control-label" for="inputGenderFemale">Женский</label>
                </div>
                {!! textError('gender') !!}
            </div>

            {!! view('app/_captcha') !!}

            <button class="btn btn-primary">Регистрация</button>
        </form>
    </div>
    <br>

    Все поля обязательны для заполнения, более полную информацию о себе вы можете добавить в своем профиле после регистрации<br>
    Указывайте верный email, на него будут высланы регистрационные данные<br><br>
@stop
