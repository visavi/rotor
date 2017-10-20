@extends('layout')

@section('title')
    Авторизация
@stop

@section('content')

    <h1>Авторизация</h1>

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
        <form method="post">


            <div class="form-group">
                <label for="inputLogin">Логин:</label>
                <input class="form-control" name="login" id="inputLogin" maxlength="20" value="{{ getInput('login') }}" required>

                <label for="inputPassword">Пароль:</label>
                <input class="form-control" name="pass" type="password" id="inputPassword" maxlength="20" required>
                <label>
                    <input name="remember" type="checkbox" value="1" checked="checked"> Запомнить меня
                </label>
            </div>

            <button class="btn btn-primary">Войти</button>
        </form>
    </div>
    <br>
    <a href="/register">Регистрация</a><br>
    <a href="/recovery">Забыли пароль?</a><br><br>
@stop
