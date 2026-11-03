@extends('layout')
@use('App\Services\UserService')

@section('title', __('index.register'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ __('index.register') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @hook('loginButtons')

    @if (UserService::isEmailConfirm())
        <div class="alert alert-warning">
            <i class="fa fa-pencil-alt text-muted"></i>
            <b>{{ __('users.confirm_registration') }}</b><br>
            {{ __('users.confirm_registration_hint') }}
        </div>
    @endif

    <div class="section-form mb-3 shadow">
        <div class="section-title"><i class="fa-solid fa-pen-to-square"></i> {{ __('index.register') }}</div>

        <form action="{{ route('register') }}" method="post">
            @csrf
            <div class="mb-3{{ hasError('login') }}">
                <label for="inputLogin" class="form-label">{{ __('users.login') }}:</label>
                <input data-check-login class="form-control" name="login" id="inputLogin" maxlength="20" value="{{ old('login') }}" required>
                <div class="invalid-feedback">{{ textError('login') }}</div>
                <div class="form-text">{{ __('users.login_requirements') }}</div>
            </div>

            <div class="mb-3{{ hasError('password') }}">
                <label for="inputPassword" class="form-label">{{ __('users.password') }}:</label>
                <input class="form-control" name="password" type="password" id="inputPassword" maxlength="20" value="{{ old('password') }}" required>
                <div class="invalid-feedback">{{ textError('password') }}</div>
                <div class="form-text">{{ __('users.password_requirements') }}</div>
            </div>

            <div class="mb-3{{ hasError('password2') }}">
                <label for="inputPassword2" class="form-label">{{ __('users.confirm_password') }}:</label>
                <input class="form-control" name="password2" type="password" id="inputPassword2" maxlength="20" value="{{ old('password2') }}" required>
                <div class="invalid-feedback">{{ textError('password2') }}</div>
            </div>

            @unless (UserService::isEmailHidden())
                <?php $emailRequired = UserService::isEmailRequired(); ?>
                <div class="mb-3{{ hasError('email') }}">
                    <label for="inputEmail" class="form-label">{{ __('users.email') }}:</label>
                    <input class="form-control" name="email" id="inputEmail" maxlength="50" value="{{ old('email') }}"{{ $emailRequired ? ' required' : '' }}>
                    <div class="invalid-feedback">{{ textError('email') }}</div>
                    @if (! $emailRequired)
                        <div class="form-text">{{ __('users.email_optional_hint') }}</div>
                    @endif
                </div>
            @endunless

            {{-- Поля модулей: проверяет Registry::onRegisterValidate, сохраняет onRegisterSave --}}
            @hook('registerFields')

            {{ getCaptcha() }}

            <button class="btn btn-primary">{{ __('index.register') }}</button>
        </form>
    </div>

    <p class="text-muted">{!! __('users.register_text') !!}</p>

    <div class="auth-links">
        <a href="{{ route('login') }}"><i class="fas fa-right-to-bracket"></i> {{ __('index.login') }}</a>
        <a href="{{ route('recovery') }}"><i class="fa-solid fa-unlock-keyhole"></i> {{ __('users.forgot_password') }}</a>
    </div>
@stop
