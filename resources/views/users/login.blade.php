@extends('layout')

@section('title', __('index.login'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ __('index.login') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if (isset($_SESSION['social']))
        <div class="bg-success text-light p-1">
            <img class="rounded-circle border" alt="photo" src="{{ $_SESSION['social']->photo }}" style="width: 48px; height: 48px;">
            <span class="badge badge-primary">{{ $_SESSION['social']->network }}</span> {{ $_SESSION['social']->first_name }} {{ $_SESSION['social']->last_name }} {{ isset($_SESSION['social']->nickname) ? '('.$_SESSION['social']->nickname.')' : '' }}
        </div>
        <div class="bg-info text-light p-1 mb-3">
            {!! __('users.social_auth_text') !!}
        </div>
    @endif

    <script src="//ulogin.ru/js/ulogin.js" async defer></script>
    <div class="mb-3" id="uLogin" data-ulogin="display=panel;fields=first_name,last_name,photo;optional=sex,email,nickname;providers=vkontakte,odnoklassniki,mailru,facebook,google,yandex,instagram;hidden=;redirect_uri={{ siteUrl() }}/login;mobilebuttons=0;">
    </div>

    <div class="section-form p-2 shadow">
        <form method="post">

            <div class="form-group">
                <label for="inputLogin">{{ __('users.login_or_email') }}:</label>
                <input class="form-control" name="login" id="inputLogin" maxlength="50" value="{{ getInput('login') }}" required>

                <label for="inputPassword">{{ __('users.password') }}:</label>
                <input class="form-control" name="pass" type="password" id="inputPassword" maxlength="20" required>
            </div>

            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" value="1" name="remember" id="remember" checked>
                <label class="custom-control-label" for="remember">{{ __('users.remember_me') }}</label>
            </div>

            @if ($isFlood)
                {!! view('app/_captcha') !!}
            @endif

            <button class="btn btn-primary">{{ __('users.enter') }}</button>
        </form>
    </div>

    <a href="/register">{{ __('index.register') }}</a><br>
    <a href="/recovery">{{ __('users.forgot_password') }}</a><br><br>
@stop
