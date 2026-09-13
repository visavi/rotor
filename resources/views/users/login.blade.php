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
    @hook('loginButtons')

    <div class="section-form mb-3 shadow">
        <div class="section-title"><i class="fas fa-right-to-bracket"></i> {{ __('index.login') }}</div>

        <form method="post">
            @csrf
            <div class="mb-3">
                <label for="inputLogin" class="form-label">{{ __('users.login_or_email') }}:</label>
                <input class="form-control" name="login" id="inputLogin" maxlength="50" value="{{ old('login') }}" required>
            </div>

            <div class="mb-3">
                <label for="inputPassword" class="form-label">{{ __('users.password') }}:</label>
                <input class="form-control" name="password" type="password" id="inputPassword" maxlength="20" value="{{ old('password') }}" required>
            </div>

            <div class="form-check mb-3">
                <input type="checkbox" class="form-check-input" value="1" name="remember" id="remember" checked>
                <label class="form-check-label" for="remember">{{ __('users.remember_me') }}</label>
            </div>

            @if ($isFlood)
                {{ getCaptcha() }}
            @endif

            <button class="btn btn-primary">{{ __('users.enter') }}</button>
        </form>
    </div>

    <div class="auth-links">
        <a href="{{ route('register') }}"><i class="fa-solid fa-pen-to-square"></i> {{ __('index.register') }}</a>
        <a href="{{ route('recovery') }}"><i class="fa-solid fa-unlock-keyhole"></i> {{ __('users.forgot_password') }}</a>
    </div>
@stop
