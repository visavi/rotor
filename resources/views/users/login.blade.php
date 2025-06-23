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
    <div class="section-form mb-3 shadow">
        <form method="post">

            <div class="mb-3">
                <label for="inputLogin" class="form-label">{{ __('users.login_or_email') }}:</label>
                <input class="form-control" name="login" id="inputLogin" maxlength="50" value="{{ getInput('login', $cookieLogin) }}" required>

                <label for="inputPassword" class="form-label">{{ __('users.password') }}:</label>
                <input class="form-control" name="password" type="password" id="inputPassword" maxlength="20" value="{{ getInput('password') }}" required>
            </div>

            <div class="form-check">
                <input type="checkbox" class="form-check-input" value="1" name="remember" id="remember" checked>
                <label class="form-check-label" for="remember">{{ __('users.remember_me') }}</label>
            </div>

            @if ($isFlood)
                {{ getCaptcha() }}
            @endif

            <button class="btn btn-primary">{{ __('users.enter') }}</button>
        </form>
    </div>

    <a href="/register">{{ __('index.register') }}</a><br>
    <a href="/recovery">{{ __('users.forgot_password') }}</a><br><br>
@stop
