@extends('layout')

@section('title')
    {{ trans('users.my_details') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/users/{{ $user->login }}">{{ $user->login }}</a></li>
            <li class="breadcrumb-item active">{{ trans('users.my_details') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <h3>{{ trans('users.change_email') }}</h3>

    <div class="form mb-4">
        <form method="post" action="/accounts/changemail">
        <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('email') }}">
                <label for="email">{{ trans('users.email') }}:</label>
                <input class="form-control" id="email" name="email" maxlength="50" value="{{ getInput('email', $user->email) }}">
                {!! textError('email') !!}
            </div>

            <div class="form-group{{ hasError('password') }}">
                <label for="password">{{ trans('users.current_password') }}:</label>
                <input class="form-control" type="password" id="password" name="password" maxlength="20">
                {!! textError('password') !!}
            </div>

            <button class="btn btn-primary">{{ trans('main.change') }}</button>
        </form>

        <span class="text-muted font-italic">{{ trans('users.email_confirm_condition') }}</span>
    </div>


    <h3>{{ trans('users.status_change') }}</h3>

    @if ($user->point >= setting('editstatuspoint'))
        <div class="form mb-4">
            <form method="post" action="/accounts/editstatus">
                <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

                <label for="status">{{ trans('users.personal_status') }}:</label>
                <div class="form-inline">
                    <div class="form-group{{ hasError('status') }}">
                        <input type="text" class="form-control" id="status" name="status" maxlength="20" value="{{ getInput('status', $user->status) }}">
                    </div>

                    <button class="btn btn-primary">{{ trans('main.change') }}</button>
                </div>
                {!! textError('status') !!}
            </form>

            @if (setting('editstatusmoney'))
                <span class="text-muted font-italic">{{ trans('main.cost') }}: {{ plural(setting('editstatusmoney'), setting('moneyname')) }}</span>
            @endif

        </div>
    @else
        {!! showError(trans('users.status_change_condition', ['point' => plural(setting('editstatuspoint'), setting('scorename'))])) !!}
    @endif

    <h3>{{ trans('users.change_password') }}</h3>

    <div class="form mb-4">
        <form method="post" action="/accounts/editpassword">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('newpass') }}">
                <label for="newpass">{{ trans('users.new_password') }}:</label>
                <input class="form-control" id="newpass" name="newpass" maxlength="20" value="{{ getInput('newpass') }}">
                {!! textError('newpass') !!}
            </div>

            <div class="form-group{{ hasError('newpass2') }}">
                <label for="newpass2">{{ trans('users.confirm_password') }}:</label>
                <input class="form-control" id="newpass2" name="newpass2" maxlength="20" value="{{ getInput('newpass2') }}">
                {!! textError('newpass2') !!}
            </div>

            <div class="form-group{{ hasError('oldpass') }}">
                <label for="oldpass">{{ trans('users.current_password') }}:</label>
                <input class="form-control" type="password" id="oldpass" name="oldpass" maxlength="20">
                {!! textError('oldpass') !!}
            </div>

            <button class="btn btn-primary">{{ trans('main.change') }}</button>
        </form>
    </div>

    <h3>{{ trans('users.your_token') }}</h3>

    <div class="form mb-4">
        <form method="post" action="/accounts/apikey">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            @if ($user->apikey)
                <div class="form-group">
                    <label for="apikey">{{ trans('users.token') }}:</label>
                    <div class="input-group">
                        <input class="form-control col-sm-4" type="text" id="apikey" name="apikey" value="{{ $user->apikey }}">
                        <div class="input-group-append" onclick="return copyToClipboard(this)" data-toggle="tooltip" title="{{ trans('main.copy') }}">
                            <span class="input-group-text"><i class="far fa-clipboard"></i></span>
                        </div>
                    </div>
                </div>

                <button class="btn btn-primary">{{ trans('main.change') }}</button>
            @else
                <button class="btn btn-primary">{{ trans('main.get') }}</button>
            @endif
        </form>

        <span class="text-muted font-italic">
            {{ trans('users.token_required') }} <a href="/api">{{ trans('users.api_interface') }}</a>
        </span>
    </div>
@stop
