@extends('layout')

@section('title', __('statuses.create_status'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/status">{{ __('index.user_statuses') }}</a></li>
            <li class="breadcrumb-item active">{{ __('statuses.create_status') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="section-form mb-3 shadow">
        <form method="post">
            @csrf
            <div class="mb-3">
                <label for="inputFrom" class="form-label">{{ __('main.from') }}:</label>
                <input type="text" pattern="\d*" maxlength="10" class="form-control" id="inputFrom" name="topoint" placeholder="От" value="{{ getInput('topoint') }}">

                <label for="inputTo" class="form-label">{{ __('main.to') }}:</label>
                <input type="text" pattern="\d*" maxlength="10" class="form-control" id="inputTo" name="point" placeholder="До" value="{{ getInput('point') }}">
            </div>

            <div class="mb-3{{ hasError('name') }}">
                <label for="inputName" class="form-label">{{ __('main.status') }}:</label>
                <input type="text" maxlength="30" class="form-control" id="inputName" name="name" placeholder="Статус" value="{{ getInput('name') }}" required>
                <div class="invalid-feedback">{{ textError('name') }}</div>
            </div>

            <div class="mb-3{{ hasError('color') }}">
                <label for="color" class="form-label">{{ __('main.color') }}:</label>

                <div class="input-group colorpick">
                    <input class="form-control col-sm-4" id="color" name="color" type="text" maxlength="7" value="{{ getInput('color') }}">
                    <span class="input-group-text colorpicker-input-addon"><i></i></span>
                </div>

                <div class="invalid-feedback">{{ textError('color') }}</div>
            </div>

            <button class="btn btn-primary">{{ __('main.add') }}</button>
        </form>
    </div>
@stop
