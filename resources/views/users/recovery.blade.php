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
        <form method="post" action="{{ route('recovery') }}">

            <div class="mb-3{{ hasError('user') }}">
                <label for="inputUser" class="form-label">{{ __('users.login_or_email') }}:</label>
                <input class="form-control" name="user" id="inputUser" value="{{ getInput('user') }}" maxlength="50" required>
                <div class="invalid-feedback">{{ textError('user') }}</div>
            </div>

            {{ getCaptcha() }}

            <button class="btn btn-primary">{{ __('mails.restore') }}</button>
        </form>
    </div>

    <p class="text-muted fst-italic">
        {{ __('mails.recovery_text1') }}
        <a href="/mails">{{ __('index.mails') }}</a>
    </p>

    <p>
        {{ __('mails.recovery_text2') }}<br>
        {{ __('mails.recovery_text3') }}<br>
        {{ __('mails.recovery_text4') }}<br>
    </p>
@stop
