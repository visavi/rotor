@extends('layout')

@section('title')
    {{ trans('mails.password_recovery') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ trans('mails.password_recovery') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="form">
        <form method="post" action="/recovery">

            <div class="form-group{{ hasError('user') }}">
                <label for="inputUser">{{ trans('mails.login_or_email') }}:</label>
                <input class="form-control" name="user" id="inputUser" value="{{ getInput('user', $cookieLogin) }}" maxlength="100" required>
                {!! textError('user') !!}
            </div>

            {!! view('app/_captcha') !!}

            <button class="btn btn-primary">{{ trans('mails.restore') }}</button>
        </form>
    </div><br>

    <p class="text-muted font-italic">
        {{ trans('mails.recovery_text1') }}
        <a href="/mails">{{ trans('mails.feedback') }}</a>
    </p>

    <p>
        {{ trans('mails.recovery_text2') }}<br>
        {{ trans('mails.recovery_text3') }}<br>
        {{ trans('mails.recovery_text4') }}<br>
    </p>
@stop
