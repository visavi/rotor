@extends('layout')

@section('title')
    {{ __('statuses.edit_status') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/status">{{ __('index.user_statuses') }}</a></li>
            <li class="breadcrumb-item active">{{ __('statuses.edit_status') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="form">
        <form method="post">
            @csrf
            <div class="form-group">
                <label for="inputFrom">{{ __('main.from') }}:</label>
                <input type="text" pattern="\d*" maxlength="10" class="form-control" id="inputFrom" name="topoint" placeholder="От" value="{{ getInput('topoint', $status->topoint) }}">

                <label for="inputTo">{{ __('main.to') }}:</label>
                <input type="text" pattern="\d*" maxlength="10" class="form-control" id="inputTo" name="point" placeholder="До" value="{{ getInput('point', $status->point) }}">
            </div>

            <div class="form-group{{ hasError('name') }}">
                <label for="inputName">{{ __('main.status') }}:</label>
                <input type="text" maxlength="30" class="form-control" id="inputName" name="name" placeholder="Статус" value="{{ getInput('name', $status->name) }}" required>
                <div class="invalid-feedback">{{ textError('name') }}</div>
            </div>

            <div class="form-group{{ hasError('color') }}">
                <label for="color">{{ __('main.color') }}:</label>

                <div class="input-group colorpick">
                    <input class="form-control col-sm-4" id="color" name="color" type="text" maxlength="7" value="{{ getInput('color', $status->color) }}">
                    <div class="input-group-append">
                        <span class="input-group-text input-group-addon"><i></i></span>
                    </div>
                </div>

                <div class="invalid-feedback">{{ textError('color') }}</div>
            </div>

            <button class="btn btn-primary">{{ __('main.edit') }}</button>
        </form>
    </div>
@stop
