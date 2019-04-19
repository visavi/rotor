@extends('layout')

@section('title')
    {{ trans('users.confirm_registration') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ trans('users.confirm_registration') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    {{ trans('users.welcome') }}, <b>{{ getUser('login') }}!</b><br>
    {{ trans('users.confirm_enter_code') }}<br><br>

    <div class="form">
        <label for="code">{{ trans('users.confirm_code') }}:</label>
        <form method="get" action="/key">
            <input class="form-control" name="code" id="code" maxlength="30" required>
            <button class="btn btn-primary">{{ trans('main.confirm') }}</button>
        </form>
    </div><br>

    <?php $checkEmail = getInput('email') ? true : false; ?>
    <?php $display = $checkEmail ? '' : ' style="display: none"'; ?>

    <div class="js-resending-form"{!! $display !!}>
        <div class="form">
            <form method="post" action="/key">
                <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

                <div class="form-group{{ hasError('email') }}">
                    <label for="email">{{ trans('users.email') }}:</label>
                    <input class="form-control" name="email" id="email" maxlength="50" value="{{ getInput('email', $user->email) }}" required>
                    {!! textError('email') !!}
                </div>

                {!! view('app/_captcha') !!}
                <button class="btn btn-primary">{{ trans('users.resend_code') }}</button>
            </form>
        </div><br>

        <p class="text-muted font-italic">
            {{ trans('users.old_code_invalid') }}
        </p>
    </div>

    @if (! $checkEmail)
        <div class="js-resending-link">
            <i class="fas fa-redo"></i> <a href="#" onclick="return resendingCode();">{{ trans('users.resend_code') }}</a>
        </div>
    @endif

    <p class="text-muted font-italic">
        {!! trans('users.confirm_text') !!}
    </p>

    <i class="fa fa-times"></i> <a href="/logout?token={{ $_SESSION['token'] }}">{!! trans('users.logout') !!}</a><br>
@stop
