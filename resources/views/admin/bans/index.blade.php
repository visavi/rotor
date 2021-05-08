@extends('layout')

@section('title', __('index.ban_unban'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.ban_unban') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <label for="user" class="form-label">{{ __('main.user_login') }}:</label><br>
    <div class="section-form mb-3 shadow">
        <form method="get" action="/admin/bans/edit">
            <div class="input-group{{ hasError('user') }}">
                <input type="text" class="form-control" id="user" name="user" maxlength="20" value="{{ getInput('user') }}" placeholder="{{ __('main.user_login') }}" required>
                <div class="input-group-append">
                    <button class="btn btn-primary">{{ __('main.edit') }}</button>
                </div>
            </div>
            <div class="invalid-feedback">{{ textError('user') }}</div>
        </form>
    </div>

    <p class="text-muted font-italic">
        {{ __('admin.bans.login_hint') }}
    </p>
@stop
