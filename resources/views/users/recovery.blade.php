@extends('layout')

@section('title', __('mails.password_recovery'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ __('mails.password_recovery') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="section-form mb-3 shadow">
        <div class="section-title"><i class="fas fa-unlock-keyhole"></i> {{ __('mails.password_recovery') }}</div>

        <form method="post" action="{{ route('recovery') }}">
            @csrf
            <div class="mb-3{{ hasError('user') }}">
                <label for="inputUser" class="form-label">{{ __('users.login_or_email') }}:</label>
                <input class="form-control" name="user" id="inputUser" value="{{ old('user') }}" maxlength="50" required>
                <div class="invalid-feedback">{{ textError('user') }}</div>
            </div>

            {{ getCaptcha() }}

            <button class="btn btn-primary">{{ __('mails.restore') }}</button>
        </form>
    </div>

    <div class="text-muted mb-3">
        {{ __('mails.recovery_text2') }}<br>
        {{ __('mails.recovery_text3') }}<br>
        {{ __('mails.recovery_text4') }}
    </div>

    <p class="text-muted fst-italic">
        {{ __('mails.recovery_text1') }}
        <a href="/mails">{{ __('index.mails') }}</a>
    </p>

    <div class="auth-links">
        <a href="{{ route('login') }}"><i class="fas fa-right-to-bracket"></i> {{ __('index.login') }}</a>
        <a href="{{ route('register') }}"><i class="fa-solid fa-pen-to-square"></i> {{ __('index.register') }}</a>
    </div>
@stop
