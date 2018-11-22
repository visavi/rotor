@extends('layout')

@section('title')
    Авторизация
@stop

@section('content')

    <h1>Авторизация</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">Авторизация</li>
        </ol>
    </nav>

    @if (isset($_SESSION['social']))
        <div class="bg-success text-light p-1">
            <img class="rounded-circle border" alt="photo" src="{{ $_SESSION['social']->photo }}" style="width: 48px; height: 48px;">
            <span class="badge badge-primary">{{ $_SESSION['social']->network }}</span> {{ $_SESSION['social']->first_name }} {{ $_SESSION['social']->last_name }} {{ isset($_SESSION['social']->nickname) ? '('.$_SESSION['social']->nickname.')' : '' }}
        </div>
        <div class="bg-info text-light p-1 mb-3">
            Профиль не связан с какой-либо учетной записью на сайте. Войдите на сайт или зарегистирируйтесь, чтобы связать свою учетную запись с профилем социальной сети.<br>
            Или выберите другую социальную сеть для входа.
        </div>
    @endif

    <script src="//ulogin.ru/js/ulogin.js"></script>
    <div class="mb-3" id="uLogin" data-ulogin="display=panel;fields=first_name,last_name,photo;optional=sex,email,nickname;providers=vkontakte,odnoklassniki,mailru,facebook,google,yandex,instagram;hidden=;redirect_uri={{ siteUrl() }}/login;mobilebuttons=0;">
    </div>

    <div class="form">
        <form method="post">

            <div class="form-group">
                <label for="inputLogin">Логин:</label>
                <input class="form-control" name="login" id="inputLogin" maxlength="20" value="{{ getInput('login') }}" required>

                <label for="inputPassword">Пароль:</label>
                <input class="form-control" name="pass" type="password" id="inputPassword" maxlength="20" required>
            </div>

            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" value="1" name="remember" id="remember" checked>
                <label class="custom-control-label" for="remember">Запомнить меня</label>
            </div>

            <button class="btn btn-primary">Войти</button>
        </form>
    </div>
    <br>
    <a href="/register">Регистрация</a><br>
    <a href="/recovery">Забыли пароль?</a><br><br>
@stop
