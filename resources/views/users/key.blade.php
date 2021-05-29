@extends('layout')

@section('title', __('index.confirm_register'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ __('index.confirm_register') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    {{ __('users.welcome', ['login' => getUser('login')]) }}<br>
    {{ __('users.confirm_enter_code') }}<br><br>

    <div class="section-form mb-3 shadow">
        <label for="code" class="form-label">{{ __('users.confirm_code') }}:</label>
        <form method="get" action="/key">
            <input class="form-control" name="code" id="code" maxlength="30" required>
            <button class="btn btn-primary">{{ __('main.confirm') }}</button>
        </form>
    </div>

    <?php $checkEmail = getInput('email') ? true : false; ?>
    <?php $display = $checkEmail ? '' : ' style="display: none"'; ?>

    <div class="js-resending-form"{!! $display !!}>
        <div class="section-form my-3 shadow">
            <form method="post" action="/key">
                @csrf
                <div class="mb-3{{ hasError('email') }}">
                    <label for="email" class="form-label">{{ __('users.email') }}:</label>
                    <input class="form-control" name="email" id="email" maxlength="50" value="{{ getInput('email', $user->email) }}" required>
                    <div class="invalid-feedback">{{ textError('email') }}</div>
                </div>

                {{ getCaptcha() }}
                <button class="btn btn-primary">{{ __('users.resend_code') }}</button>
            </form>
        </div>

        <p class="text-muted fst-italic">
            {{ __('users.old_code_invalid') }}
        </p>
    </div>

    @if (! $checkEmail)
        <div class="js-resending-link">
            <i class="fas fa-redo"></i> <a href="#" onclick="return resendingCode();">{{ __('users.resend_code') }}</a>
        </div>
    @endif

    <p class="text-muted fst-italic">
        {!! __('users.confirm_text') !!}
    </p>

    <i class="fa fa-times"></i> <a href="/logout?_token={{ csrf_token() }}">{{ __('users.logout') }}</a><br>
@stop
