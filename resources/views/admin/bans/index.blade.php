@extends('layout')

@section('title')
    {{ trans('index.ban_unban') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('index.ban_unban') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <label for="user">{{ trans('main.user_login') }}:</label><br>
    <div class="form">
        <form method="get" action="/admin/bans/edit">
            <div class="form-inline">
                <div class="form-group{{ hasError('user') }}">
                    <input type="text" class="form-control" id="user" name="user" maxlength="20" value="{{ getInput('user') }}" placeholder="{{ trans('main.user_login') }}" required>
                </div>

                <button class="btn btn-primary">{{ trans('main.edit') }}</button>
            </div>
            <div class="invalid-feedback">{{ textError('user') }}</div>
        </form>
    </div>

    <p class="text-muted font-italic">
        {{ trans('admin.bans.login_hint') }}
    </p>
@stop
